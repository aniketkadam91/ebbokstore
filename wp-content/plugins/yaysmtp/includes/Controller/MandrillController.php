<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MandrillController {
	private $headers           = array();
	private $body              = array();
	private $use_fallback_smtp = false;
	private $log_id            = null;

	public function getApiKey($settings = array()) {
		$apiKey = '';
		if ( ! empty( $settings ) && is_array( $settings ) ) {
			if ( ! empty( $settings['mandrill'] ) && ! empty( $settings['mandrill']['api_key'] ) ) {
				$apiKey = $settings['mandrill']['api_key'];
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
			if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mandrill'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mandrill']['api_key'] ) ) {
				$apiKey = $settings['fallback_service_provider_mailer_settings']['mandrill']['api_key'];
			}
		} else {
			Utils::setFrom($phpmailer);
		}
		// Set wp_mail_from && wp_mail_from_name - end

		// create log - start
		$dataLogsDB           = Utils::prepareDataLogInit( $phpmailer );
		$dataLogsDB['mailer'] = 'Mandrill';
		$this->log_id         = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$this->headers['Content-Type']  = 'application/json';
		$this->headers['Accept']        = 'application/json';

		$this->body['key'] = $apiKey;
		$this->body['message']['subject'] = $phpmailer->Subject;

		// Set body - message - header - start
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
			$this->body['message']['headers'] = $headersData;
		}

		if ( ! empty( $phpmailer->ContentType ) ) {
			$this->body['message']['headers']['Content-Type'] = $phpmailer->ContentType;
		}

		$this->body['message']['headers']['Sender'] = $phpmailer->From;

		// Reply to
		$replyToAddresses = $phpmailer->getReplyToAddresses();
		if ( ! empty( $replyToAddresses[0] ) ) {
			$addrReplyTo = isset( $replyToAddresses[0][0] ) ? $replyToAddresses[0][0] : false;
			if ( ! empty( $addrReplyTo ) ) {
				$this->body['message']['headers']['reply-to'] = $addrReplyTo;
			}
		}
		// Set body - message - header - end

		// Set From email
		if ( ! empty( $phpmailer->FromName ) ) {
			$this->body['message']['from_name'] = $phpmailer->FromName;
		}
		$this->body['message']['from_email'] = $phpmailer->From;

		// Set To email
		$toAddresses = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			foreach ( $toAddresses as $toEmail ) {
				$address = isset( $toEmail[0] ) ? $toEmail[0] : "";
				$name    = isset( $toEmail[1] ) ? $toEmail[1] : "";
				$arrTo   = array(
					'email' => $address,
					'name'  => $name,
					'type'  => 'to',
				);

				$this->body['message']['to'][] = $arrTo;
			}
		}

		// Set Cc email
		$ccAddresses = $phpmailer->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			foreach ( $ccAddresses as $ccEmail ) {
				$address = isset( $ccEmail[0] ) ? $ccEmail[0] : "";
				$name    = isset( $ccEmail[1] ) ? $ccEmail[1] : "";
				$arrCc   = array(
					'email' => $address,
					'name'  => $name,
					'type'  => 'cc',
				);

				$this->body['message']['to'][] = $arrCc;
			}
		}

		// Set Bcc email
		$bccAddresses = $phpmailer->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			foreach ( $bccAddresses as $bccEmail ) {
				$address = isset( $bccEmail[0] ) ? $bccEmail[0] : "";
				$name    = isset( $bccEmail[1] ) ? $bccEmail[1] : "";
				$arrBcc   = array(
					'email' => $address,
					'name'  => $name,
					'type'  => 'bcc',
				);

				$this->body['message']['to'][] = $arrBcc;
			}
		}

		// Set content
		if ( 'text/plain' === $phpmailer->ContentType ) {
			$this->body['message']['text'] = $phpmailer->Body;
		} else {
			$content = array(
				'text' => $phpmailer->AltBody,
				'html' => $phpmailer->Body,
			);

			if ( ! empty( $content['html'] ) ) {
				$this->body['message']['html'] = $content['html'];
			} else {
				$this->body['message']['text'] = $content['text'];
			}
		}

		// Set attachments.
		$attachments = $phpmailer->getAttachments();
		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $attach ) {
				if ( ! empty( $attach ) ) {
					$attachment = array(
						'type'    => 'attachment',
						'name'    => basename( $attach ),
						'content' => base64_encode( file_get_contents( $attach ) ),
					);
					$this->body['message']['attachments'][] = $attachment;
				}
			}
		}
	}

	public function send() {
		$response = wp_safe_remote_post(
			'https://mandrillapp.com/api/1.0/messages/send.json',
			array(
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => $this->headers,
				'body'        => wp_json_encode( $this->body ),
				'timeout'     => ini_get( 'max_execution_time' ) ? (int) ini_get( 'max_execution_time' ) : 30,
			)
		);

		$respBody = json_decode( $response['body'] );
		$respBody = is_object( $respBody ) ? $respBody : $respBody[0];
		$respResponse = $response['response'];

		if ( 200 !== $respResponse['code'] ) {
			$errorMsg = '';
			if( ! empty( $respBody->code ) && ! empty( $respBody->message ) ) {
				$errorMsg = '[' . $respBody->code . ']: ' . $respBody->message;
			}
			
			$message = $errorMsg;
			if ( $this->use_fallback_smtp ) {
				LogErrors::clearErrFallback();
				LogErrors::setErrFallback( $message );
			} else {
				LogErrors::clearErr();
				LogErrors::setErr( 'Mailer: Mandrill' );
				LogErrors::setErr( $message );
			}

			if ( ! empty( $this->log_id ) ) {
				$updateData['id']           = $this->log_id;
				$updateData['date_time']    = current_time( 'mysql', true );
				$updateData['reason_error'] = $message;
				Utils::updateEmailLog( $updateData );
			}
		} else {
			$respStatus = $respBody->status;
			$sent       = in_array($respStatus, ['queued','sent']);
			if( ! $sent ) {
				$errorMsg = '';
				if( ! empty( $respStatus ) && ! empty( $respBody->reject_reason ) ) {
					$errorMsg = '[' . $respStatus . ']: ' . $respBody->reject_reason;
				}
				
				$message = $errorMsg;
				if ( $this->use_fallback_smtp ) {
					LogErrors::clearErrFallback();
					LogErrors::setErrFallback( $message );
				} else {
					LogErrors::clearErr();
					LogErrors::setErr( 'Mailer: Mandrill' );
					LogErrors::setErr( $message );
				}
	
				if ( ! empty( $this->log_id ) ) {
					$updateData['id']           = $this->log_id;
					$updateData['date_time']    = current_time( 'mysql', true );
					$updateData['reason_error'] = $message;
					Utils::updateEmailLog( $updateData );
				}
			} else {
				if ( $this->use_fallback_smtp ) {
					LogErrors::clearErrFallback();
				} else {
					LogErrors::clearErr();
				}
	
				if ( ! empty( $logId ) ) { 
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
