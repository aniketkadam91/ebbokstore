<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class GmailServiceVendController {
	private $settings = array();
	private $client;

	public function __construct( $is_fallback_settings = false ) {
		$this->settings = Utils::getYaySmtpSetting();
		if ( $this->clientIDSerect( 'gmail', $is_fallback_settings ) ) {
			$this->client = $this->getclientWebService( $is_fallback_settings );
		}
	}

	/**
	 * Update access token in our DB.
	 */
	public function saveAccessToken( $token, $is_fallback_settings = false ) {
		if ( ! empty( $is_fallback_settings ) ) {
			Utils::setValueMailerSettingFallback( 'gmail_access_token', $token, 'gmail' );
		} else {
			Utils::setYaySmtpSetting( 'gmail_access_token', $token, 'gmail' );
		}
	}

	/**
	 * Update refresh token in our DB.
	 */
	public function saveRefToken( $token, $is_fallback_settings = false ) {
		if ( !empty( $is_fallback_settings ) ) {
			Utils::setValueMailerSettingFallback( 'gmail_refresh_token', $token, 'gmail' );
		} else {
			Utils::setYaySmtpSetting( 'gmail_refresh_token', $token, 'gmail' );
		}
	}

	// Setting on main page or fallback page, not base on use_fallback_smtp
	public function saveAuthorizeCode( $code, $is_fallback_settings = false ) {
		if ( !empty( $is_fallback_settings ) ) {
			Utils::setValueMailerSettingFallback( 'gmail_auth_code', $code, 'gmail' );
		} else {
			Utils::setYaySmtpSetting( 'gmail_auth_code', $code, 'gmail' );
		}
	}

	public function clientIDSerect( $currentMailer = '', $is_fallback_settings = false ) {
		$currentMailer = ! empty( $currentMailer ) ? $currentMailer : 'gmail';
		$settings      = $this->settings;
		if ( ! empty( $is_fallback_settings ) ) {
			return ! empty( $settings ) && ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $settings['fallback_service_provider_mailer_settings'][ $currentMailer ] ) && ! empty( $settings['fallback_service_provider_mailer_settings'][ $currentMailer ]['client_id'] ) && ! empty( $settings['fallback_service_provider_mailer_settings'][ $currentMailer ]['client_secret'] );
		} else {
			return ! empty( $settings ) && ! empty( $settings[ $currentMailer ] ) && ! empty( $settings[ $currentMailer ]['client_id'] ) && ! empty( $settings[ $currentMailer ]['client_secret'] );
		}
	}

	public function tokenEmpty( $currentMailer = '', $is_fallback_settings = false ) {
		$currentMailer = ! empty( $currentMailer ) ? $currentMailer : 'gmail';
		$settings      = $this->settings;
		if ( ! empty( $is_fallback_settings ) ) {
			return empty( $settings ) || empty( $settings['fallback_service_provider_mailer_settings'] ) || empty( $settings['fallback_service_provider_mailer_settings'][ $currentMailer ] ) || empty( $settings['fallback_service_provider_mailer_settings'][ $currentMailer ]['gmail_access_token'] ) || empty( $settings['fallback_service_provider_mailer_settings'][ $currentMailer ]['gmail_refresh_token'] );
		} else {
			return empty( $settings ) || empty( $settings[ $currentMailer ] ) || empty( $settings[ $currentMailer ]['gmail_access_token'] ) || empty( $settings[ $currentMailer ]['gmail_refresh_token'] );
		}
	}

	public function getclientWebService( $is_fallback_settings = false ) {
		if ( ! empty( $this->client ) ) {
			return $this->client;
		}

		$Google_Client = $this->googleClientObj( $is_fallback_settings );
		$Google_Client->setScopes( 'https://mail.google.com/' );
		$Google_Client->setApprovalPrompt( 'force' );
		$Google_Client->setAccessType( 'offline' );
		if ( $is_fallback_settings ) {
			$Google_Client->setRedirectUri(
				add_query_arg(
					array(
						'page'   => 'yaysmtp',
						'tab'    => 'additional-setting',
						'action' => 'serviceauthyaysmtpfallback',
					),
					admin_url( 'admin.php' )
				)
			);
		} else {
			$Google_Client->setRedirectUri(
				add_query_arg(
					array(
						'page'   => 'yaysmtp',
						'action' => 'serviceauthyaysmtp',
					),
					admin_url( 'admin.php' )
				)
			);
		}
	
		$this->saveAccessTokenWithCode( $Google_Client, $is_fallback_settings );
		$this->saveAccessTokenExpire( $Google_Client, $is_fallback_settings );
		return $Google_Client;
	}

	public function processAuthorizeServive() {
		if ( isset( $_GET['action'] ) && 
			( 'serviceauthyaysmtp' === sanitize_key( $_GET['action'] ) ||
			  'serviceauthyaysmtpfallback' === sanitize_key( $_GET['action'] )
			)
		) {
			$actionAuth      = sanitize_key( $_GET['action'] );
			$scopeMailGoogle = '';
			if ( isset( $_GET['scope'] ) ) {
				$scopeMailGoogle = sanitize_text_field( $_GET['scope'] );
				if ( 'https://mail.google.com/' == $scopeMailGoogle ) {
					$codeMailGoogle = '';
					if ( isset( $_GET['code'] ) ) {
						$codeMailGoogle = urldecode( sanitize_text_field( $_GET['code'] ) );
					}
					if ( 'serviceauthyaysmtpfallback' === $actionAuth ) {
						$this->saveAuthorizeCode( $codeMailGoogle, true );
					} else {
						$this->saveAuthorizeCode( $codeMailGoogle );
					}	
				}
			}

			if ( 'serviceauthyaysmtpfallback' === $actionAuth ) {
				$authUrl = add_query_arg(
					array(
						'page'   => 'yaysmtp',
						'tab'    => 'additional-setting',
					),
					Utils::adminUrl( 'admin.php' )
				);
			} else {
				$authUrl = add_query_arg(
					array(
						'page'   => 'yaysmtp',
					),
					Utils::adminUrl( 'admin.php' )
				);
			}

			wp_safe_redirect($authUrl);
		}
	}

	public function setUserInf( $clientWebService, $is_fallback_settings = false ) {
		$ServiceGmail = new \Google_Service_Gmail( $clientWebService );

		try {
			$mail = $ServiceGmail->users->getProfile( 'me' )->getEmailAddress();
		} catch ( \Exception $e ) {
			$mail = '';
		}

		if ( ! empty($is_fallback_settings) ) {
			Utils::setValueMailerSettingFallback( 'gmail_auth_email', $mail, 'gmail' );
		} else {
			Utils::setYaySmtpSetting( 'gmail_auth_email', $mail, 'gmail' );
		}
	}

	public function getSetting( $name, $is_fallback_settings = false) {
		$settings = $this->settings;
		if ( !empty($is_fallback_settings) ) {
			if ( ! empty( $settings ) && 
			! empty( $settings['fallback_service_provider_mailer_settings'] ) && 
			! empty( $settings['fallback_service_provider_mailer_settings']['gmail'] ) && 
			! empty( $settings['fallback_service_provider_mailer_settings']['gmail'][ $name ] ) 
			) {
				return $settings['fallback_service_provider_mailer_settings']['gmail'][ $name ];
			} 
		} else {
			if ( ! empty( $settings ) && ! empty( $settings['gmail'] ) && ! empty( $settings['gmail'][ $name ] ) ) {
				return $settings['gmail'][ $name ];
			} 
		}
		
		return '';
	}

	private function googleClientObj( $is_fallback_settings ) {
		$args = array(
			'client_id'     => $this->getSetting( 'client_id', $is_fallback_settings ),
			'client_secret' => $this->getSetting( 'client_secret', $is_fallback_settings ),
		);

		if ( $is_fallback_settings ) {
			$args['redirect_uris'] = array(
				add_query_arg(
					array(
						'page'   => 'yaysmtp',
						'tab'    => 'additional-setting',
						'action' => 'serviceauthyaysmtpfallback',
					),
					admin_url( 'admin.php' )
				),
			);
		} else {
			$args['redirect_uris'] = array(
				add_query_arg(
					array(
						'page'   => 'yaysmtp',
						'action' => 'serviceauthyaysmtp',
					),
					admin_url( 'admin.php' )
				),
			);
		}
		return new \Google_Client($args);
	}

	private function saveAccessTokenExpire( $googleClient, $is_fallback_settings ) {
		$gmail_access_token = $this->getSetting( 'gmail_access_token', $is_fallback_settings );
		if ( ! empty( $gmail_access_token ) ) {
			$googleClient->setAccessToken( $gmail_access_token );
		}
		if ( $googleClient->isAccessTokenExpired() ) {
			$refTokenVal         = $googleClient->getRefreshToken();
			$gmail_refresh_token = $this->getSetting( 'gmail_refresh_token', $is_fallback_settings );
			if ( ! empty( $gmail_refresh_token ) && empty( $refTokenVal ) ) {
				$refTokenVal = $gmail_refresh_token;
			}
			if ( ! empty( $refTokenVal ) ) {
				$googleClient->fetchAccessTokenWithRefreshToken( $refTokenVal );
				$this->saveAccessToken( $googleClient->getAccessToken(), $is_fallback_settings );
				$this->saveRefToken( $refTokenVal, $is_fallback_settings );
			}
		}
	}

	private function saveAccessTokenWithCode( $googleClient, $is_fallback_settings ) {
		$gmail_auth_code = $this->getSetting( 'gmail_auth_code', $is_fallback_settings );
		if ( ! empty( $gmail_auth_code ) && $this->tokenEmpty( 'gmail', $is_fallback_settings) ) {
			$googleClient->fetchAccessTokenWithAuthCode( $gmail_auth_code );
			$this->setUserInf( $googleClient, $is_fallback_settings );
			$this->saveRefToken( $googleClient->getRefreshToken(), $is_fallback_settings );
			$this->saveAccessToken( $googleClient->getAccessToken(), $is_fallback_settings );
		}
	}
}
