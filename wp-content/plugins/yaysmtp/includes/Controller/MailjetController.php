<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MailjetController {
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
		$dataLogsDB['mailer'] = 'Mailjet';
		$this->log_id 		  = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$this->headers['Content-Type'] = 'application/json';

		$apiKey                         = $this->getApiKey();
		$secretKey                      = $this->getSecretKey();
		$this->headers['Authorization'] = 'Basic ' . base64_encode( $apiKey . ':' . $secretKey );

		$headers     = $phpmailer->getCustomHeaders();
		$headersData = array();
		foreach ( $headers as $head ) {
			$nameHead  = isset( $head[0] ) ? $head[0] : false;
			$valueHead = isset( $head[1] ) ? $head[1] : false;
			if ( empty( $nameHead ) ) {
				$headersData[ $nameHead ] = $valueHead;
			}
		}

		if ( ! empty( $headersData ) ) {
			$this->body['Messages'][0]['Headers'] = $headersData;
		}

		$this->body['Messages'][0]['Subject'] = $phpmailer->Subject;

		if ( ! empty( $phpmailer->FromName ) ) {
			$dataFrom['Name'] = $phpmailer->FromName;
		}
		$dataFrom['Email']                 = $phpmailer->From;
		$this->body['Messages'][0]['From'] = $dataFrom;

		// Recipients - start
		$toAddresses_log = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses_log ) && is_array( $toAddresses_log ) ) {
			$dataRecips['To'] = array();
			foreach ( $toAddresses_log as $toEmail ) {
				$address        = isset( $toEmail[0] ) ? $toEmail[0] : false;
				$name           = isset( $toEmail[1] ) ? $toEmail[1] : false;
				$arrTo          = array();
				$arrTo['Email'] = $address;
				if ( ! empty( $name ) ) {
					$arrTo['Name'] = $name;
				}
				$dataRecips['To'][] = $arrTo;
			}
		}

		$ccAddresses = $phpmailer->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			$dataRecips['Cc'] = array();
			foreach ( $ccAddresses as $ccEmail ) {
				$address        = isset( $ccEmail[0] ) ? $ccEmail[0] : false;
				$name           = isset( $ccEmail[1] ) ? $ccEmail[1] : false;
				$arrCc          = array();
				$arrCc['Email'] = $address;
				if ( ! empty( $name ) ) {
					$arrCc['Name'] = $name;
				}
				$dataRecips['Cc'][] = $arrCc;
			}
		}

		$bccAddresses = $phpmailer->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			$dataRecips['Bcc'] = array();
			foreach ( $bccAddresses as $bccEmail ) {
				$address         = isset( $bccEmail[0] ) ? $bccEmail[0] : false;
				$name            = isset( $bccEmail[1] ) ? $bccEmail[1] : false;
				$arrBcc          = array();
				$arrBcc['Email'] = $address;
				if ( ! empty( $name ) ) {
					$arrBcc['Name'] = $name;
				}
				$dataRecips['Bcc'][] = $arrBcc;
			}
		}

		if ( ! empty( $dataRecips ) ) {
			foreach ( $dataRecips as $type => $type_recipients ) {
				$this->body['Messages'][0][ $type ] = $type_recipients;
			}
		}
		// Recipients - end

		if ( 'text/plain' === $phpmailer->ContentType ) {
			$content                               = $phpmailer->Body;
			$this->body['Messages'][0]['TextPart'] = $content;
		} else {
			$content = array(
				'text' => $phpmailer->AltBody,
				'html' => $phpmailer->Body,
			);
			if ( ! empty( $content['text'] ) ) {
				$this->body['Messages'][0]['TextPart'] = $content['text'];
			}
			if ( ! empty( $content['html'] ) ) {
				$this->body['Messages'][0]['HTMLPart'] = $content['html'];
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

				$dataReplyTo['Email'] = $addrReplyTo;
				if ( ! empty( $nameReplyTo ) ) {
					$dataReplyTo['Name'] = $nameReplyTo;
				}
				break;
			}

			if ( ! empty( $dataReplyTo ) ) {
				$this->body['Messages'][0]['ReplyTo'] = $dataReplyTo;
			}
		}

	}

	public function send() {
		$apiLink = 'https://api.mailjet.com/v3.1/send';

		$resp = wp_safe_remote_post(
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
			$errorBody     = $resp['body'];
			$errorResponse = $resp['response'];
			$message       = '';

			if ( ! empty( $errorBody ) ) {
				$message = '[' . sanitize_key( $errorResponse['code'] ) . ']: ' . $errorBody;
			}
			
			if ( $this->use_fallback_smtp ) {
				LogErrors::clearErrFallback();
				LogErrors::setErrFallback( $message );
			} else {
				LogErrors::clearErr();
				LogErrors::setErr( 'Mailer: Mailjet' );
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

	public function getApiKey() {
		$apiKey   = '';
		$settings = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailjet'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailjet']['api_key'] ) ) {
					$apiKey = $settings['fallback_service_provider_mailer_settings']['mailjet']['api_key'];
				}
			} else {
				if ( ! empty( $settings[ 'mailjet' ] ) && ! empty( $settings[ 'mailjet' ]['api_key'] ) ) {
					$apiKey = $settings[ 'mailjet' ]['api_key'];
				}
			}

		}

		return $apiKey;
	}

	public function getSecretKey() {
		$secretKey = '';
		$settings  = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailjet'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailjet']['secret_key'] ) ) {
					$secretKey = $settings['fallback_service_provider_mailer_settings']['mailjet']['secret_key'];
				}
			} else {
				if ( ! empty( $settings[ 'mailjet' ] ) && ! empty( $settings[ 'mailjet' ]['secret_key'] ) ) {
					$secretKey = $settings[ 'mailjet' ]['secret_key'];
				}
			}

		}

		return $secretKey;
	}
}
