<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class PostmarkController {
	private $headers 		   = array();
	private $body    		   = array();
	private $use_fallback_smtp = false;
	private $log_id            = null;

	public function getApiKey($settings = array()) {
		$apiKey = '';
		if ( ! empty( $settings ) ) {
			if ( ! empty( $settings['postmark'] ) && ! empty( $settings['postmark']['api_key'] ) ) {
				$apiKey = $settings['postmark']['api_key'];
			}
		}
		return $apiKey;
	}

	public function __construct( $phpmailer ) {
		// Set wp_mail_from && wp_mail_from_name - start
		$settings                = Utils::getYaySmtpSetting();
		$this->use_fallback_smtp = Utils::conditionUseFallbackSmtp();
		$apiKey                  = $this->getApiKey($settings);
		if( $this->use_fallback_smtp ) {
			Utils::setFromFallback($phpmailer, $settings);
			if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['postmark'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['postmark']['api_key'] ) ) {
				$apiKey = $settings['fallback_service_provider_mailer_settings']['postmark']['api_key'];
			}
		} else {
			Utils::setFrom($phpmailer);
		}
		// Set wp_mail_from && wp_mail_from_name - end

		// create log - start
		$dataLogsDB           = Utils::prepareDataLogInit( $phpmailer );
		$dataLogsDB['mailer'] = 'Postmark';
		$this->log_id 		  = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$this->headers['Accept']                  = 'application/json';
		$this->headers['Content-Type']            = 'application/json';
		$this->headers['X-Postmark-Server-Token'] = $apiKey;

		$headers = $phpmailer->getCustomHeaders();
		foreach ( $headers as $head ) {
			$nameHead  = isset( $head[0] ) ? $head[0] : false;
			$valueHead = isset( $head[1] ) ? $head[1] : false;
			if ( empty( $nameHead ) ) {
				$headersData              = isset( $this->body['Headers'] ) ? (array) $this->body['Headers'] : array();
				$headersData[ $nameHead ] = $valueHead;

				$this->body = array_merge( $this->body, array( 'Headers' => $headersData ) );
			}
		}

		$this->body = array_merge( $this->body, array( 'Subject' => $phpmailer->Subject ) );
		if ( ! empty( $phpmailer->FromName ) ) {
			$this->body = array_merge( $this->body, array( 'From' => $phpmailer->FromName . ' <' . $phpmailer->From . '>' ) );
		} else {
			$this->body = array_merge( $this->body, array( 'From' => $phpmailer->From ) );
		}

		$toAddresses = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			$bodyContentTo = array();
			foreach ( $toAddresses as $toEmail ) {
				$address         = isset( $toEmail[0] ) ? $toEmail[0] : false;
				$bodyContentTo[] = $address;
			}
			if ( ! empty( $bodyContentTo ) ) {
				$this->body = array_merge( $this->body, array( 'To' => implode( ',', $bodyContentTo ) ) );
			}
		}

		$ccAddresses = $phpmailer->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			$bodyContentCc = array();
			foreach ( $ccAddresses as $ccEmail ) {
				$address         = isset( $ccEmail[0] ) ? $ccEmail[0] : false;
				$bodyContentCc[] = $address;
			}
			if ( ! empty( $bodyContentCc ) ) {
				$this->body = array_merge( $this->body, array( 'Cc' => implode( ',', $bodyContentCc ) ) );
			}
		}

		$bccAddresses = $phpmailer->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			$bodyContentBcc = array();
			foreach ( $bccAddresses as $bccEmail ) {
				$address          = isset( $bccEmail[0] ) ? $bccEmail[0] : false;
				$bodyContentBcc[] = $address;
			}
			if ( ! empty( $bodyContentBcc ) ) {
				$this->body = array_merge( $this->body, array( 'Bcc' => implode( ',', $bodyContentBcc ) ) );
			}
		}

		if ( 'text/plain' === $phpmailer->ContentType ) {
			$contentTp = 'TextBody';
			if ( ! empty( $phpmailer->Body ) ) {
				$this->body = array_merge( $this->body, array( $contentTp => $phpmailer->Body ) );
			}
		} else {
			$content = array(
				'TextBody' => $phpmailer->AltBody,
				'HtmlBody' => $phpmailer->Body,
			);
			foreach ( $content as $type => $mail ) {
				if ( empty( $mail ) ) {
					continue;
				}
				$this->body = array_merge( $this->body, array( $type => $mail ) );
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

				if ( ! filter_var( $addrReplyTo, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$dataReplyTo[] = $phpmailer->addrFormat( $emailReplys );
			}

			if ( ! empty( $dataReplyTo ) ) {
				$this->body = array_merge( $this->body, array( 'ReplyTo' => implode( ',', $dataReplyTo ) ) );
			}
		}
	}

	public function send() {
		$apiLink = 'https://api.postmarkapp.com/email';
		$resp    = wp_safe_remote_post(
			$apiLink,
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
		if ( is_wp_error( $resp ) || 200 !== wp_remote_retrieve_response_code( $resp ) ) {
			$errorBody     = json_decode( $resp['body'] );
			$errorResponse = $resp['response'];
			$message       = '';
			if ( ! empty( $errorBody ) ) {
				$message = '[' . sanitize_key( $errorResponse['code'] ) . ']: ' . $errorBody->Message;
			}

			if ( $this->use_fallback_smtp ) {
				LogErrors::clearErrFallback();
				LogErrors::setErrFallback( $message );
			} else {
				LogErrors::clearErr();
				LogErrors::setErr( 'Mailer: Postmark' );
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

		return $sent;
	}
}
