<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class SendPulseController {
	private $headers = array();
	private $body    = array();
	private $token;
	private $use_fallback_smtp = false;
	private $settings = array();
	private $log_id = null;

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
		$dataLogsDB['mailer'] = 'SendPulse';
		$this->log_id 		  = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$this->token = $this->getAccessTokenDB();
		if ( empty( $this->token ) || $this->isExpiredAccessToken() ) {
			if ( ! $this->getAccessToken() ) {
				if( $this->use_fallback_smtp ) {
					LogErrors::clearErrFallback();
					LogErrors::setErrFallback( 'Could not connect to api, check your ID and SECRET' );
				} else {
					LogErrors::clearErr();
					LogErrors::setErr( 'Could not connect to api, check your ID and SECRET' );
				}
				
				return;
			}
		}

		$this->body   = array_merge( $this->body, array( 'subject' => $phpmailer->Subject ) );

		if ( ! empty( $phpmailer->FromName ) ) {
			$dataFrom['name'] = $phpmailer->FromName;
		}
		$dataFrom['email'] = $phpmailer->From;
		$this->body        = array_merge( $this->body, array( 'from' => $dataFrom ) );

		$toAddresses = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			$dataRecips = array();
			foreach ( $toAddresses as $toEmail ) {
				$address        = isset( $toEmail[0] ) ? $toEmail[0] : false;
				$name           = isset( $toEmail[1] ) ? $toEmail[1] : false;
				$arrTo          = array();
				$arrTo['email'] = $address;
				if ( ! empty( $name ) ) {
					$arrTo['name'] = $name;
				}
				$dataRecips[] = $arrTo;
			}
			$this->body = array_merge( $this->body, array( 'to' => $dataRecips ) );
		}

		$ccAddresses = $phpmailer->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			$dataRecips = array();
			foreach ( $ccAddresses as $ccEmail ) {
				$address        = isset( $ccEmail[0] ) ? $ccEmail[0] : false;
				$name           = isset( $ccEmail[1] ) ? $ccEmail[1] : false;
				$arrCc          = array();
				$arrCc['email'] = $address;
				if ( ! empty( $name ) ) {
					$arrCc['name'] = $name;
				}
				$dataRecips[] = $arrCc;
			}
			$this->body = array_merge( $this->body, array( 'cc' => $dataRecips ) );
		}

		$bccAddresses = $phpmailer->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			$dataRecips = array();
			foreach ( $bccAddresses as $bccEmail ) {
				$address         = isset( $bccEmail[0] ) ? $bccEmail[0] : false;
				$name            = isset( $bccEmail[1] ) ? $bccEmail[1] : false;
				$arrBcc          = array();
				$arrBcc['email'] = $address;
				if ( ! empty( $name ) ) {
					$arrBcc['name'] = $name;
				}
				$dataRecips[] = $arrBcc;
			}
			$this->body = array_merge( $this->body, array( 'bcc' => $dataRecips ) );
		}

		if ( 'text/plain' === $phpmailer->ContentType ) {
			$this->body = array_merge( $this->body, array( 'text' => $phpmailer->Body ) );
		} else {
			$content = array(
				'text' => $phpmailer->AltBody,
				'html' => $phpmailer->Body,
			);
			if ( ! empty( $content['text'] ) ) {
				$this->body = array_merge( $this->body, array( 'text' => $content['text'] ) );
			}
			if ( ! empty( $content['html'] ) ) {
				$this->body = array_merge( $this->body, array( 'html' => $content['html'] ) );
			}
		}
	}

	private function getAccessToken() {
		$param = array(
			'grant_type'    => 'client_credentials',
			'client_id'     => $this->getApiKey(),
			'client_secret' => $this->getSecretKey(),
		);

		$resp = wp_safe_remote_post(
			'https://api.sendpulse.com/oauth/access_token',
			array(
				'httpversion' => '1.1',
				'blocking'    => true,
				'body'        => http_build_query( $param ),
				'timeout'     => ini_get( 'max_execution_time' ) ? (int) ini_get( 'max_execution_time' ) : 300,
				'sslverify'   => false,
			)
		);

		$responseCode = wp_remote_retrieve_response_code( $resp );
		$responseBody = wp_remote_retrieve_body( $resp );
		$resultBody   = json_decode( $responseBody );

		if ( 200 !== $responseCode ) {
			return false;
		}

		$this->token = $resultBody->access_token;
		$this->saveAccessToken( $this->token );

		if( $this->use_fallback_smtp ) {
			Utils::setValueMailerSettingFallback( 'created_at', strtotime( 'now' ), 'sendpulse' );
		} else {
			Utils::setYaySmtpSetting( 'created_at', strtotime( 'now' ), 'sendpulse' );
		}
		
		return true;
	}

	public function send() {
		$sent = false;

		if ( $this->isExpiredAccessToken() ) { 
			$this->getAccessToken();
		}
			
		if ( ! empty( $this->body['html'] ) ) {
			$this->body['html'] = base64_encode( $this->body['html'] );
		}

		$param = array(
			'email' => serialize( $this->body ),
		);

		if ( ! empty( $this->token ) ) {
			$token                          = $this->token;
			$this->headers['Content-Type']  = 'application/json';
			$this->headers['Authorization'] = 'Bearer ' . $token;
		} else {
			LogErrors::clearErr();
			LogErrors::setErr( 'Could not connect to api, check your ID and SECRET' );
			return $sent;	
		}

		$resp = wp_safe_remote_post(
			'https://api.sendpulse.com/smtp/emails',
			array(
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => $this->headers,
				'body'        => wp_json_encode( $param ),
				'timeout'     => ini_get( 'max_execution_time' ) ? (int) ini_get( 'max_execution_time' ) : 300,
				'sslverify'   => false,
			)
		);

		$headerCode   = wp_remote_retrieve_response_code( $resp );
		$responseBody = wp_remote_retrieve_body( $resp );

		$respBodyObj = json_decode( $responseBody );

		if ( 200 !== $headerCode ) {
			$message = '';
			if ( ! empty( $respBodyObj ) ) {
				$message = '[' . $headerCode . ']: ' . $respBodyObj->message;
			}

			if ( $this->use_fallback_smtp ) {
				LogErrors::clearErrFallback();
				LogErrors::setErrFallback( $message );
			} else {
				LogErrors::clearErr();
				LogErrors::setErr( 'Mailer: SendPulse' );
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
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendpulse'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendpulse']['api_key'] ) ) {
					$apiKey = $settings['fallback_service_provider_mailer_settings']['sendpulse']['api_key'];
				}
			} else {
				if ( ! empty( $settings[ 'sendpulse' ] ) && ! empty( $settings[ 'sendpulse' ]['api_key'] ) ) {
					$apiKey = $settings[ 'sendpulse' ]['api_key'];
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
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendpulse'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendpulse']['secret_key'] ) ) {
					$secretKey = $settings['fallback_service_provider_mailer_settings']['sendpulse']['secret_key'];
				}
			} else {
				if ( ! empty( $settings[ 'sendpulse' ] ) && ! empty( $settings[ 'sendpulse' ]['secret_key'] ) ) {
					$secretKey = $settings[ 'sendpulse' ]['secret_key'];
				}
			}

		}
		return $secretKey;
	}

	public function saveAccessToken( $token ) {
		if ( $this->use_fallback_smtp ) {
			Utils::setValueMailerSettingFallback( 'access_token', $token, 'sendpulse' );
		} else {
			Utils::setYaySmtpSetting( 'access_token', $token, 'sendpulse' );
		}
	}

	public function getAccessTokenDB() {
		$accessToken = '';
		$settings    = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendpulse'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendpulse']['access_token'] ) ) {
					$accessToken = $settings['fallback_service_provider_mailer_settings']['sendpulse']['access_token'];
				}
			} else {
				if ( ! empty( $settings[ 'sendpulse' ] ) && ! empty( $settings[ 'sendpulse' ]['access_token'] ) ) {
					$accessToken = $settings[ 'sendpulse' ]['access_token'];
				}
			}

		}

		return $accessToken;
	}

	public function getCreatedTimes() {
		$createdAt = '';
		$settings  = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendpulse'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sendpulse']['created_at'] ) ) {
					$createdAt = $settings['fallback_service_provider_mailer_settings']['sendpulse']['created_at'];
				}
			} else {
				if ( ! empty( $settings[ 'sendpulse' ] ) && ! empty( $settings[ 'sendpulse' ]['created_at'] ) ) {
					$createdAt = $settings[ 'sendpulse' ]['created_at'];
				}
			}

		}

		return $createdAt;
	}

	public function isExpiredAccessToken() {
		$now = strtotime( 'now' );
		if ( $this->getCreatedTimes() === '' ) {
			return true;
		}

		if ( $this->getCreatedTimes() + 3600 < $now ) {
			return true;
		}

		return false;
	}
}
