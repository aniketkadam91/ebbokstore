<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class SparkPostController {
	private $headers 			= array();
	private $body    			= array();
	private $use_fallback_smtp  = false;
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
		$dataLogsDB['mailer'] = 'SparkPost';
		$this->log_id 		  = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $phpmailer, $this->log_id);
		// create log - end

		$this->headers['Content-Type']  = 'application/json';
		$this->headers['Authorization'] = $this->getApiKey();

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
			$this->body['content']['headers'] = $headersData;
		}

		$this->body['content']['subject'] = $phpmailer->Subject;

		if ( ! empty( $phpmailer->FromName ) ) {
			$dataFrom['name'] = $phpmailer->FromName;
		}
		$dataFrom['email']             = $phpmailer->From;
		$this->body['content']['from'] = $dataFrom;

		// Recipients - start
		$dataHeaderTo = array();
		$toAddresses  = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			foreach ( $toAddresses as $toEmail ) {
				if ( empty( $toEmail[1] ) ) {
					$dataHeaderTo[] = $toEmail[0];
				} else {
					$dataHeaderTo[] = sprintf( '%s <%s>', $toEmail[1], $toEmail[0] );
				}
			}
		}
		$dataHeaderTo = implode( ', ', $dataHeaderTo );

		$bodyContentAddress = array();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			foreach ( $toAddresses as $toEmail ) {
				$address = array(
					'address' => array(
						'email' => $toEmail[0],
						'name'  => isset( $toEmail[1] ) ? $toEmail[1] : '',
					),
				);

				if ( ! empty( $dataHeaderTo ) ) {
					$address['address']['header_to'] = $dataHeaderTo;
					unset( $address['address']['name'] );
				}

				$bodyContentAddress[] = $address;
			}
		}

		$ccAddresses = $phpmailer->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			foreach ( $ccAddresses as $toEmail ) {
				$address = array(
					'address' => array(
						'email' => $toEmail[0],
						'name'  => isset( $toEmail[1] ) ? $toEmail[1] : '',
					),
				);

				if ( ! empty( $dataHeaderTo ) ) {
					$address['address']['header_to'] = $dataHeaderTo;
					unset( $address['address']['name'] );
				}

				$bodyContentAddress[] = $address;
			}
		}

		$bccAddresses = $phpmailer->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			foreach ( $bccAddresses as $toEmail ) {
				$address = array(
					'address' => array(
						'email' => $toEmail[0],
						'name'  => isset( $toEmail[1] ) ? $toEmail[1] : '',
					),
				);

				if ( ! empty( $dataHeaderTo ) ) {
					$address['address']['header_to'] = $dataHeaderTo;
					unset( $address['address']['name'] );
				}

				$bodyContentAddress[] = $address;
			}
		}

		$this->body['recipients'] = $bodyContentAddress;
		// Recipients - end

		if ( 'text/plain' === $phpmailer->ContentType ) {
			if ( ! empty( $phpmailer->Body ) ) {
				$this->body['content']['text'] = $phpmailer->Body;
			}
		} elseif ( 'multipart/alternative' === $phpmailer->ContentType ) {
			$this->body['content']['html'] = $phpmailer->Body;
			$this->body['content']['text'] = $phpmailer->AltBody;
		} else {
			$this->body['content']['html'] = $phpmailer->Body;
		}

		// Sandbox
		if ( isset( $this->body['content']['from']['email'] ) ) {
			$emailFrom  = $this->body['content']['from']['email'];
			$emailSlice = array_slice( explode( '@', $emailFrom ), -1 );
			if ( 'sparkpostbox.com' === $emailSlice[0] ) {
				$this->body['options']['sandbox'] = true;
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
				$this->body['content']['reply_to'] = implode( ',', $dataReplyTo );
			}
		}
	}

	public function send() {
		$apiLink = 'https://api.sparkpost.com';
		if ( 'EU' === $this->getRegion() ) {
			$apiLink = 'https://api.eu.sparkpost.com';
		}
		$apiLink .= '/api/v1/transmissions';

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
			$errorBody     = json_decode( $resp['body'] );
			$errorResponse = $resp['response'];
			$message       = '';

			if ( ! empty( $errorBody ) ) {
				$message = '[' . sanitize_key( $errorResponse['code'] ) . ']: ' . $errorBody->errors[0]->message;
			}
			
			if ( $this->use_fallback_smtp ) {
				LogErrors::clearErrFallback();
				LogErrors::setErrFallback( $message );
			} else {
				LogErrors::clearErr();
				LogErrors::setErr( 'Mailer: SparkPost' );
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
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sparkpost'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sparkpost']['api_key'] ) ) {
					$apiKey = $settings['fallback_service_provider_mailer_settings']['sparkpost']['api_key'];
				}
			} else {
				if ( ! empty( $settings[ 'sparkpost' ] ) && ! empty( $settings[ 'sparkpost' ]['api_key'] ) ) {
					$apiKey = $settings[ 'sparkpost' ]['api_key'];
				}
			}

		}

		return $apiKey;
	}

	public function getRegion() {
		$region   = '';
		$settings = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sparkpost'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['sparkpost']['region'] ) ) {
					$region = $settings['fallback_service_provider_mailer_settings']['sparkpost']['region'];
				}
			} else {
				if ( ! empty( $settings[ 'sparkpost' ] ) && ! empty( $settings[ 'sparkpost' ]['region'] ) ) {
					$region = $settings[ 'sparkpost' ]['region'];
				}
			}

		}
		return $region;
	}
}
