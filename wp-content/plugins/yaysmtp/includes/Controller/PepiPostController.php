<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class PepiPostController {
	private $headers           = array();
	private $body              = array();
	private $use_fallback_smtp = false;
	private $log_id            = null;

	public function getApiKey($settings = array()) {
		$apiKey = '';
		if ( ! empty( $settings ) && is_array( $settings ) ) {
			if ( ! empty( $settings['pepipost'] ) && ! empty( $settings['pepipost']['api_key'] ) ) {
				$apiKey = $settings['pepipost']['api_key'];
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
			if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['pepipost'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['pepipost']['api_key'] ) ) {
				$apiKey = $settings['fallback_service_provider_mailer_settings']['pepipost']['api_key'];
			}
		} else {
			Utils::setFrom($phpmailer);
		}
		// Set wp_mail_from && wp_mail_from_name - end

		// create log - start
		$dataLogsDB           = Utils::prepareDataLogInit( $phpmailer );
		$dataLogsDB['mailer'] = 'Pepipost';
		$this->log_id         = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$this->headers['content-type'] = 'application/json';
		$this->headers['api_key']      = $apiKey;

		$this->body = array_merge( $this->body, array( 'subject' => $phpmailer->Subject ) );

		if ( ! empty( $phpmailer->FromName ) ) {
			$dataFrom['name'] = $phpmailer->FromName;
		}
		$dataFrom['email'] = $phpmailer->From; //'confirmation@pepisandbox.com';

		$this->body = array_merge( $this->body, array( 'from' => $dataFrom ) );

		$dataRecips  = array();
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

		// Attachments
		$attachments = $phpmailer->getAttachments();
		if ( is_array( $attachments ) ) {
			$dataRecips['attachments'] = array();
			foreach ( $attachments as $k => $attachment ) {
				$dataRecips['attachments'][] = array(
					'name'    => $attachment[7],
					'content' => $phpmailer->encodeString( \file_get_contents( $attachment[0] ) ),
				);
			}
		}

		if ( ! empty( $dataRecips ) ) {
			$this->body = array_merge( $this->body, array( 'personalizations' => array( $dataRecips ) ) );
		}

		if ( 'text/plain' === $phpmailer->ContentType ) {
			$content              = $phpmailer->Body;
			$dataContent['type']  = 'amp-content';
			$dataContent['value'] = $content;
			$this->body           = array_merge( $this->body, array( 'content' => array( $dataContent ) ) );
		} else {
			$content     = array(
				'text' => $phpmailer->AltBody,
				'html' => $phpmailer->Body,
			);
			$dataContent = array();
			foreach ( $content as $type => $body ) {
				if ( empty( $body ) ) {
					continue;
				}

				$ctype = $type;
				if ( 'html' !== $type ) {
					$ctype = 'amp-content';
				}

				$dataContent[] = array(
					'type'  => $ctype,
					'value' => $body,
				);
			}

			$this->body = array_merge( $this->body, array( 'content' => $dataContent ) );
		}

		// Reply to
		$replyToAddresses = $phpmailer->getReplyToAddresses();
		if ( ! empty( $replyToAddresses ) ) {
			$emailReplyTo = array_shift( $replyToAddresses );
			if ( ! empty( $emailReplyTo ) && is_array( $emailReplyTo ) ) {
				$addrReplyTo = isset( $emailReplyTo[0] ) ? $emailReplyTo[0] : false;
				if ( ! empty( $addrReplyTo ) && filter_var( $addrReplyTo, FILTER_VALIDATE_EMAIL ) ) {
					$this->body = array_merge( $this->body, array( 'reply_to' => $addrReplyTo ) );
				}
			}
		}

	}

	public function send() {
		$apiLink = 'https://api.pepipost.com/v5/mail/send';

		$response = wp_safe_remote_post(
			$apiLink,
			array(
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => $this->headers,
				'body'        => wp_json_encode( $this->body ),
				'timeout'     => ini_get( 'max_execution_time' ) ? (int) ini_get( 'max_execution_time' ) : 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$errors = $response->get_error_messages();
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
		if ( ! empty( $response['response'] ) && ! empty( $response['response']['code'] ) ) {
			$code        = (int) $response['response']['code'];
			$codeSucArrs = array( 200, 201, 202, 203, 204, 205, 206, 207, 208, 300, 301, 302, 303, 304, 305, 306, 307, 308 );
			if ( ! in_array( $code, $codeSucArrs ) && ! empty( $response['response'] ) ) {
				$error   = $response['response'];
				$message = '';
				if ( ! empty( $error ) ) {
					$message = '[' . $error['code'] . ']: ' . $error['message'];
				}
				
				if ( $this->use_fallback_smtp ) {
					LogErrors::clearErrFallback();
					LogErrors::setErrFallback( $message );
				} else {
					LogErrors::clearErr();
					LogErrors::setErr( 'Mailer: Pepipost' );
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
