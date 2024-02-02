<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class SendinblueController {
	private $headers  		   = array();
	private $body    		   = array();
	private $use_fallback_smtp = false;
	private $log_id            = null;

	private function getApiKey($settings = array()) {
		$apiKey = '';
		if ( ! empty( $settings ) && is_array( $settings ) ) {
			if ( ! empty( $settings['sendinblue'] ) && ! empty( $settings['sendinblue']['api_key'] ) ) {
				$apiKey = $settings['sendinblue']['api_key'];
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
			if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendinblue'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendinblue']['api_key'] ) ) {
				$apiKey = $settings['fallback_service_provider_mailer_settings']['sendinblue']['api_key'];
			}
		} else {
			Utils::setFrom($phpmailer);
		}
		// Set wp_mail_from && wp_mail_from_name - end

		// create log - start
		$dataLogsDB           = Utils::prepareDataLogInit( $phpmailer );
		$dataLogsDB['mailer'] = 'Sendinblue';
		$this->log_id 		  = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$headers                       = $phpmailer->getCustomHeaders();
		$this->headers['api-key']      = $apiKey;
		$this->headers['content-type'] = 'application/json';
		$this->headers['accept']       = 'application/json';
		foreach ( $headers as $header ) {
			$nameHead  = isset( $header[0] ) ? $header[0] : false;
			$valueHead = isset( $header[1] ) ? $header[1] : false;
			if ( empty( $nameHead ) ) {
				return;
			}
			$headersData              = isset( $this->body['headers'] ) ? (array) $this->body['headers'] : array();
			$headersData[ $nameHead ] = Utils::saniVal( $valueHead );
			$this->body               = array_merge( $this->body, array( 'headers' => $headersData ) );
		}

		$this->body           = array_merge( $this->body, array( 'subject' => $phpmailer->Subject ) );
		$this->body['sender'] = array(
			'email' => $phpmailer->From,
			'name'  => ! empty( $phpmailer->FromName ) ? Utils::saniVal( $phpmailer->FromName ) : '',
		);

		$toAddresses = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			$dataRecips['to'] = array();
			foreach ( $toAddresses as $toEmail ) {
				$address        = isset( $toEmail[0] ) ? $toEmail[0] : false;
				$name           = isset( $toEmail[1] ) ? $toEmail[1] : false;
				$arrTo          = array();
				$arrTo['email'] = $address;
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
				$address        = isset( $ccEmail[0] ) ? $ccEmail[0] : false;
				$name           = isset( $ccEmail[1] ) ? $ccEmail[1] : false;
				$arrCc          = array();
				$arrCc['email'] = $address;
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
				$address         = isset( $bccEmail[0] ) ? $bccEmail[0] : false;
				$name            = isset( $bccEmail[1] ) ? $bccEmail[1] : false;
				$arrBcc          = array();
				$arrBcc['email'] = $address;
				if ( ! empty( $name ) ) {
					$arrBcc['name'] = $name;
				}
				$dataRecips['bcc'][] = $arrBcc;
			}
		}

		if ( ! empty( $dataRecips ) ) {
			foreach ( $dataRecips as $type => $type_recipients ) {
				$this->body[ $type ] = $type_recipients;
			}
		}

		if ( 'text/plain' === $phpmailer->ContentType ) {
			$content                   = $phpmailer->Body;
			$this->body['textContent'] = $content;
		} else {
			$content = array(
				'text' => $phpmailer->AltBody,
				'html' => $phpmailer->Body,
			);
			if ( ! empty( $content['text'] ) ) {
				$this->body['textContent'] = $content['text'];
			}
			if ( ! empty( $content['html'] ) ) {
				$this->body['htmlContent'] = $content['html'];
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

				$dataReplyTo['email'] = $addrReplyTo;
				if ( ! empty( $name ) ) {
					$dataReplyTo['name'] = $nameReplyTo;
				}
			}

			if ( ! empty( $dataReplyTo ) ) {
				$this->body = array_merge( $this->body, array( 'replyTo' => $dataReplyTo ) );
			}
		}

		// Set attachments.
		$attachments = $phpmailer->getAttachments();
		if ( ! empty( $attachments ) ) {
			$allowedAttach = array( 'xlsx', 'xls', 'ods', 'docx', 'docm', 'doc', 'csv', 'pdf', 'txt', 'gif', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'rtf', 'bmp', 'cgm', 'css', 'shtml', 'html', 'htm', 'zip', 'xml', 'ppt', 'pptx', 'tar', 'ez', 'ics', 'mobi', 'msg', 'pub', 'eps', 'odt', 'mp3', 'm4a', 'm4v', 'wma', 'ogg', 'flac', 'wav', 'aif', 'aifc', 'aiff', 'mp4', 'mov', 'avi', 'mkv', 'mpeg', 'mpg', 'wmv' );
			foreach ( $attachments as $attach ) {
				$attachFile = false;
				try {
					if ( is_file( $attach[0] ) && is_readable( $attach[0] ) ) {
						$extension = pathinfo( $attach[0], PATHINFO_EXTENSION );
						if ( in_array( $extension, $allowedAttach, true ) ) {
							  $attachFile = file_get_contents( $attach[0] );
						}
					}
				} catch ( \Exception $except ) {
					$attachFile = false;
				}

				if ( false === $attachFile ) {
					continue;
				}

				$this->body['attachment'][] = array(
					'name'    => $attach[2],
					'content' => base64_encode( $attachFile ),
				);
			}
		}
	}

	public function send() {
		$params = array(
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => $this->headers,
			'body'        => wp_json_encode( $this->body ),
			'timeout'     => ini_get( 'max_execution_time' ) ? (int) ini_get( 'max_execution_time' ) : 30,
		);
		$resp   = wp_safe_remote_post( 'https://api.sendinblue.com/v3/smtp/email', $params );

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
					LogErrors::setErr( 'Mailer: Sendinblue' );
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
}
