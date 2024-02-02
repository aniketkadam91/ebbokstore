<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MailgunController {
	private $headers           = array();
	private $body    		   = array();
	private $use_fallback_smtp = false;
	private $settings 		   = array();
	private $log_id            = null;

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
		$dataLogsDB['mailer'] = 'Mailgun';
		$this->log_id         = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$this->headers['Authorization'] = 'Basic ' . base64_encode( 'api:' . $this->getApiKey() );

		$headers = $phpmailer->getCustomHeaders();
		foreach ( $headers as $header ) {
			$name       = isset( $header[0] ) ? $header[0] : false;
			$value      = isset( $header[1] ) ? $header[1] : false;
			$this->body = array_merge( $this->body, array( 'h:' . $name => Utils::saniVal( $value ) ) );
		}

		$this->body = array_merge( $this->body, array( 'subject' => $phpmailer->Subject ) );
		if ( ! empty( $phpmailer->FromName ) ) {
			$this->body = array_merge( $this->body, array( 'from' => $phpmailer->FromName . ' <' . $phpmailer->From . '>' ) );
		} else {
			$this->body = array_merge( $this->body, array( 'from' => $phpmailer->From ) );
		}

		$toAddresses = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			$bodyContentTo = array();
			foreach ( $toAddresses as $toEmail ) {
				$address = isset( $toEmail[0] ) ? $toEmail[0] : false;
				$name    = isset( $toEmail[1] ) ? $toEmail[1] : false;
				if ( ! empty( $name ) ) {
					$bodyContentTo[] = $name . ' <' . $address . '>';
				} else {
					$bodyContentTo[] = $address;
				}
			}
			if ( ! empty( $bodyContentTo ) ) {
				$this->body = array_merge( $this->body, array( 'to' => implode( ', ', $bodyContentTo ) ) );
			}
		}

		$ccAddresses = $phpmailer->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			$bodyContentCc = array();
			foreach ( $ccAddresses as $ccEmail ) {
				$address = isset( $ccEmail[0] ) ? $ccEmail[0] : false;
				$name    = isset( $ccEmail[1] ) ? $ccEmail[1] : false;
				if ( ! empty( $name ) ) {
					$bodyContentCc[] = $name . ' <' . $address . '>';
				} else {
					$bodyContentCc[] = $address;
				}
			}
			if ( ! empty( $bodyContentCc ) ) {
				$this->body = array_merge( $this->body, array( 'cc' => implode( ', ', $bodyContentCc ) ) );
			}
		}

		$bccAddresses = $phpmailer->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			$bodyContentBcc = array();
			foreach ( $bccAddresses as $bccEmail ) {
				$address = isset( $bccEmail[0] ) ? $bccEmail[0] : false;
				$name    = isset( $bccEmail[1] ) ? $bccEmail[1] : false;
				if ( ! empty( $name ) ) {
					$bodyContentBcc[] = $name . ' <' . $address . '>';
				} else {
					$bodyContentBcc[] = $address;
				}
			}
			if ( ! empty( $bodyContentBcc ) ) {
				$this->body = array_merge( $this->body, array( 'bcc' => implode( ', ', $bodyContentBcc ) ) );
			}
		}

		if ( 'text/plain' === $phpmailer->ContentType ) {
			$contentTp = 'text';
			if ( ! empty( $phpmailer->Body ) ) {
				$this->body = array_merge( $this->body, array( $contentTp => $phpmailer->Body ) );
			}
		} else {
			$content = array(
				'text' => $phpmailer->AltBody,
				'html' => $phpmailer->Body,
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
				$nameReplyTo = isset( $emailReplys[1] ) ? $emailReplys[1] : false;

				if ( ! filter_var( $addrReplyTo, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				if ( ! empty( $nameReplyTo ) ) {
					$dataReplyTo[] = $nameReplyTo . ' <' . $addrReplyTo . '>';
				} else {
					$dataReplyTo[] = $addrReplyTo;
				}
			}

			if ( ! empty( $dataReplyTo ) ) {
				$this->body = array_merge( $this->body, array( 'h:Reply-To' => implode( ',', $dataReplyTo ) ) );
			}
		}
	}

	public function send() {
		$apiLink = 'https://api.mailgun.net/v3/';
		if ( 'EU' === $this->getRegion() ) {
			$apiLink = 'https://api.eu.mailgun.net/v3/';
		}
		$apiLink .= $this->getDomain() . '/messages';

		$resp = wp_safe_remote_post(
			$apiLink,
			array(
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => $this->headers,
				'body'        => $this->body,
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
					LogErrors::setErr( 'Mailer: Mailgun' );
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
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailgun'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailgun']['api_key'] ) ) {
					$apiKey = $settings['fallback_service_provider_mailer_settings']['mailgun']['api_key'];
				}
			} else {
				if ( ! empty( $settings[ 'mailgun' ] ) && ! empty( $settings[ 'mailgun' ]['api_key'] ) ) {
					$apiKey = $settings[ 'mailgun' ]['api_key'];
				}
			}

		}
		return $apiKey;
	}

	public function getDomain() {
		$domain   = '';
		$settings = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailgun'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailgun']['domain'] ) ) {
					$domain = $settings['fallback_service_provider_mailer_settings']['mailgun']['domain'];
				}
			} else {
				if ( ! empty( $settings[ 'mailgun' ] ) && ! empty( $settings[ 'mailgun' ]['domain'] ) ) {
					$domain = $settings[ 'mailgun' ]['domain'];
				}
			}

		}
		return $domain;
	}

	public function getRegion() {
		$region   = 'US';
		$settings = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailgun'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['mailgun']['region'] ) ) {
					$region = $settings['fallback_service_provider_mailer_settings']['mailgun']['region'];
				}
			} else {
				if ( ! empty( $settings[ 'mailgun' ] ) && ! empty( $settings[ 'mailgun' ]['region'] ) ) {
					$region = $settings[ 'mailgun' ]['region'];
				}
			}

		}
		return $region;
	}
}
