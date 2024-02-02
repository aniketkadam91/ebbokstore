<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SMTPcomController {
	private $headers 			= array();
	private $body    			= array();
	private $use_fallback_smtp 	= false;
	private $settings 			= array();
	private $log_id            	= null;

	public function __construct( $phpmailer ) {
		// Set wp_mail_from && wp_mail_from_name - start
		$this->settings          = Utils::getYaySmtpSetting();
		$this->use_fallback_smtp = Utils::conditionUseFallbackSmtp();

		if( $this->use_fallback_smtp ) {
			Utils::setFromFallback($phpmailer, $this->settings);
		} else {
			Utils::setFrom($phpmailer);
		}
		// Set wp_mail_from && wp_mail_from_name - end

		// create log - start
		$dataLogsDB           = Utils::prepareDataLogInit( $phpmailer );
		$dataLogsDB['mailer'] = 'SMTP.com';
		$this->log_id 		  = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$this->headers['Accept']        = 'application/json';
		$this->headers['content-type']  = 'application/json';
		$this->headers['Authorization'] = 'Bearer ' . $this->getApiKey();
		$this->body                     = array_merge( $this->body, array( 'channel' => $this->getChannel() ) );

		$headers = $phpmailer->getCustomHeaders();
		foreach ( $headers as $header ) {
			$name                 = isset( $header[0] ) ? $header[0] : false;
			$value                = isset( $header[1] ) ? $header[1] : false;
			$headersData          = isset( $this->body['custom_headers'] ) ? (array) $this->body['custom_headers'] : array();
			$headersData[ $name ] = $value;
			$this->body           = array_merge( $this->body, array( 'custom_headers' => $headersData ) );
		}

		$this->body = array_merge( $this->body, array( 'subject' => $phpmailer->Subject ) );

		if ( filter_var( $phpmailer->From, FILTER_VALIDATE_EMAIL ) ) {
			$from['address'] = $phpmailer->From;
			if ( ! empty( $phpmailer->FromName ) ) {
				$from['name'] = $phpmailer->FromName;
			}
			$this->body = array_merge( $this->body, array( 'originator' => array( 'from' => $from ) ) );
		}

		$toAddresses = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			$dataRecips['to'] = array();
			foreach ( $toAddresses as $toEmail ) {
				$address          = isset( $toEmail[0] ) ? $toEmail[0] : false;
				$name             = isset( $toEmail[1] ) ? $toEmail[1] : false;
				$arrTo            = array();
				$arrTo['address'] = $address;
				if ( ! empty( $name ) ) {
					$arrTo['name'] = $name;
				}
				$dataRecips['to'][] = $arrTo;
			}
		}

		$ccAddresses = $phpmailer->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			$dataRecips['cc'] = array();
			foreach ( $ccAddresses as $ccEmail ) {
				$address          = isset( $ccEmail[0] ) ? $ccEmail[0] : false;
				$name             = isset( $ccEmail[1] ) ? $ccEmail[1] : false;
				$arrCc            = array();
				$arrCc['address'] = $address;
				if ( ! empty( $name ) ) {
					$arrCc['name'] = $name;
				}
				$dataRecips['cc'][] = $arrCc;
			}
		}

		$bccAddresses = $phpmailer->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			$dataRecips['bcc'] = array();
			foreach ( $bccAddresses as $bccEmail ) {
				$address           = isset( $bccEmail[0] ) ? $bccEmail[0] : false;
				$name              = isset( $bccEmail[1] ) ? $bccEmail[1] : false;
				$arrBcc            = array();
				$arrBcc['address'] = $address;
				if ( ! empty( $name ) ) {
					$arrBcc['name'] = $name;
				}
				$dataRecips['bcc'][] = $arrBcc;
			}
		}

		if ( ! empty( $dataRecips ) ) {
			$this->body = array_merge( $this->body, array( 'recipients' => $dataRecips ) );
		}

		if ( 'text/plain' === $phpmailer->ContentType ) {
			$contentBody = $phpmailer->Body;

			if ( ! empty( $contentBody ) ) {
				$bodyParts   = array();
				$ctype       = 'text/plain';
				$bodyParts[] = array(
					'type'     => $ctype,
					'content'  => $contentBody,
					'charset'  => $phpmailer->CharSet,
					'encoding' => $phpmailer->Encoding,
				);
				$this->body  = array_merge( $this->body, array( 'body' => array( 'parts' => $bodyParts ) ) );
			}
		} else {
				$contentBody = array(
					'text' => $phpmailer->AltBody,
					'html' => $phpmailer->Body,
				);

				if ( ! empty( $contentBody ) ) {
					$bodyParts = array();
					foreach ( $contentBody as $type => $body ) {
						if ( empty( $body ) ) {
								  continue;
						}

						if ( 'html' === $type ) {
							$ctype = 'text/html';
						} else {
								$ctype = 'text/plain';
						}

						$bodyParts[] = array(
							'type'     => $ctype,
							'content'  => $body,
							'charset'  => $phpmailer->CharSet,
							'encoding' => $phpmailer->Encoding,
						);
					}

					$this->body = array_merge( $this->body, array( 'body' => array( 'parts' => $bodyParts ) ) );
				}
		}

		// Reply to
		$replyToAddresses = $phpmailer->getReplyToAddresses();
		if ( ! empty( $replyToAddresses ) ) {
			$dataReplyTo = array();

			foreach ( $replyToAddresses as $emailReplys ) {
				if ( empty( $emailReplys ) || ! is_array( $emailReplys ) ) {
					continue;
				}

				$addrReplyTo = isset( $emailReplys[0] ) ? $emailReplys[0] : false;
				$nameReplyTo = isset( $emailReplys[1] ) ? $emailReplys[1] : false;

				if ( ! filter_var( $addrReplyTo, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$dataReplyTo['address'] = $addrReplyTo;
				if ( ! empty( $name ) ) {
					$dataReplyTo['name'] = $nameReplyTo;
				}
				break;
			}

			if ( ! empty( $dataReplyTo ) ) {
				$this->body = array_merge( $this->body, array( 'originator' => array( 'reply_to' => $dataReplyTo ) ) );
			}
		}
	}

	public function send() {
		$resp = wp_safe_remote_post(
			'https://api.smtp.com/v4/messages',
			array(
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => $this->headers,
				'body'        => wp_json_encode( $this->body ),
				'timeout'     => ini_get( 'max_execution_time' ) ? (int) ini_get( 'max_execution_time' ) : 30,
			)
		);

		if ( is_wp_error( $resp ) ) {
			$errors = $resp->get_error_messages();
			foreach ( $errors as $error ) {
				if ( $this->use_fallback_smtp ) {
					LogErrors::setErrFallback( $error );
				} else {
					LogErrors::setErr( $error );
				}
			}
			return;
		}

		$sent = false;
		if ( ! empty( $resp['response'] ) && ! empty( $resp['response']['code'] ) ) {
			$code        = (int) $resp['response']['code'];
			$codeSucArrs = array( 200, 201, 202, 203, 204, 205, 206, 207, 208, 300, 301, 302, 303, 304, 305, 306, 307, 308 );
			if ( ! in_array( $code, $codeSucArrs ) && ! empty( $resp['response'] ) ) {
				$error   = $resp['response'];
				$message = '';
				if ( ! empty( $error ) ) {
					$message = '[' . sanitize_key( $error['code'] ) . ']: ' . $error['message'];
				}
				
				if ( $this->use_fallback_smtp ) {
					LogErrors::clearErrFallback();
					LogErrors::setErrFallback( $message );
				} else {
					LogErrors::clearErr();
					LogErrors::setErr( 'Mailer: SMTPcom' );
					LogErrors::setErr( $message );
				}

				if ( ! empty( $this->log_id ) ) {
					$updateData['id']           = $this->log_id;
					$updateData['date_time']    = current_time( 'mysql', true );
					$updateData['reason_error'] = $message;
					Utils::updateEmailLog( $updateData );
				}
			} else {
				$sent = true;
				if ( $this->use_fallback_smtp ) {
					LogErrors::clearErrFallback();
				} else {
					LogErrors::clearErr();
				}

				if ( ! empty( $this->log_id ) ) { 
					$updateData['id']        = $this->log_id;
					$updateData['date_time'] = current_time( 'mysql', true );
					$updateData['status']    = 1;
					Utils::updateEmailLog( $updateData );
				}
			}
		}

		return $sent;
	}

	public function getApiKey() {
		$apiKey   = '';
		$settings = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['smtpcom'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['smtpcom']['api_key'] ) ) {
					$apiKey = $settings['fallback_service_provider_mailer_settings']['smtpcom']['api_key'];
				}
			} else {
				if ( ! empty( $settings[ 'smtpcom' ] ) && ! empty( $settings[ 'smtpcom' ]['api_key'] ) ) {
					$apiKey = $settings[ 'smtpcom' ]['api_key'];
				}
			}

		}
		return $apiKey;
	}

	public function getChannel() {
		$sender   = '';
		$settings = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['smtpcom'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['smtpcom']['sender'] ) ) {
					$sender = $settings['fallback_service_provider_mailer_settings']['smtpcom']['sender'];
				}
			} else {
				if ( ! empty( $settings[ 'smtpcom' ] ) && ! empty( $settings[ 'smtpcom' ]['sender'] ) ) {
					$sender = $settings[ 'smtpcom' ]['sender'];
				}
			}

		}
		return $sender;
	}
}
