<?php

namespace YaySMTP\Controller;

use YaySMTP\Aws3\Aws\Ses\SesClient;
use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AmazonWebServicesController {

	private $client;
	private $use_fallback_smtp = false;
	private $settings = array();

	public function __construct() { 
		$this->settings          = Utils::getYaySmtpSetting();
		$this->use_fallback_smtp = Utils::conditionUseFallbackSmtp();
	}

	public function get_region() {
		$region   = 'us-east-1';
		$settings = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['amazonses'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['amazonses']['region'] ) ) {
					$region = $settings['fallback_service_provider_mailer_settings']['amazonses']['region'];
				}
			} else {
				if ( ! empty( $settings['amazonses'] ) && ! empty( $settings['amazonses']['region'] ) ) {
					$region = $settings['amazonses']['region'];
				}
			}
		}
		return $region;
	}

	public function get_access_key_id() {
		$accessKeyId = '';
		$settings    = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['amazonses'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['amazonses']['access_key_id'] ) ) {
					$accessKeyId = $settings['fallback_service_provider_mailer_settings']['amazonses']['access_key_id'];
				}
			} else {
				if ( ! empty( $settings['amazonses'] ) && ! empty( $settings['amazonses']['access_key_id'] ) ) {
					$accessKeyId = $settings['amazonses']['access_key_id'];
				}
			}
		}
		return $accessKeyId;
	}

	public function get_secret_access_key() {
		$secretAccessKey = '';
		$settings        = $this->settings;
		if ( ! empty( $settings ) ) {
			if ( $this->use_fallback_smtp ) {
				if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['amazonses'] ) && ! empty( $settings['fallback_service_provider_mailer_settings']['amazonses']['secret_access_key'] ) ) {
					$secretAccessKey = $settings['fallback_service_provider_mailer_settings']['amazonses']['secret_access_key'];
				}
			} else {
				if ( ! empty( $settings['amazonses'] ) && ! empty( $settings['amazonses']['secret_access_key'] ) ) {
					$secretAccessKey = $settings['amazonses']['secret_access_key'];
				}
			}
		}
		return $secretAccessKey;
	}

	public function getClient() {
		if ( is_null( $this->client ) ) {
			$args = array(
				'credentials' => array(
					'key'    => $this->get_access_key_id(),
					'secret' => $this->get_secret_access_key(),
				),
			);

			$args['version'] = '2010-12-01';
			$args['region']  = $this->get_region();

			try {
				$this->client = new SesClient( $args );
			} catch ( \Exception $e ) {
				if ( $this->use_fallback_smtp ) { 
					LogErrors::clearErrFallback();
					LogErrors::setErrFallback( 'Missing access keys, region.' );
				} else {
					LogErrors::clearErr();
					LogErrors::setErr( 'Mailer: Amazon SES' );
					LogErrors::setErr( 'Missing access keys, region.' );
				}
			}
		}

		return $this->client;
	}

}
