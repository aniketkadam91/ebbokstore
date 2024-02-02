<?php
namespace YaySMTP;

use YaySMTP\Controller\GmailServiceVendController;
use YaySMTP\Controller\OutlookMsServicesController;
use YaySMTP\Controller\ZohoServiceVendController;
use YaySMTP\Helper\Utils;

defined( 'ABSPATH' ) || exit;

class PluginCore {
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	private function doHooks() {
		$this->getProcessor();
		global $phpmailer;
		$phpmailer = new PhpMailerExtends();//phpcs:ignore
		add_action( 'init', array( $this, 'actionForSmtpsHasAuth' ) );
	}

	private function __construct() {}

	public function getProcessor() {
		add_action( 'phpmailer_init', array( $this, 'doSmtperInit' ) );
		add_filter( 'wp_mail_from', array( $this, 'getFromAddress' ), PHP_INT_MAX );
		add_filter( 'wp_mail_from_name', array( $this, 'getFromName' ), PHP_INT_MAX );
	}

	public function actionForSmtpsHasAuth() {
		if ( is_admin() ) {
			$currentEmail = Utils::getCurrentMailer();
			if ( 'gmail' === $currentEmail ) {
				$gmailService = new GmailServiceVendController();
				$gmailService->processAuthorizeServive();
			} elseif ( 'zoho' === $currentEmail ) {
				$zohoService = new ZohoServiceVendController();
				$zohoService->processAuthorizeServive();
			} elseif ( 'outlookms' === $currentEmail ) {
				$outlookmsService = new OutlookMsServicesController();
				$outlookmsService->processAuthorizeServive();
			}
		}

	}

	public function getDefaultMailFrom() {
		$sitename = \wp_parse_url( \network_home_url(), PHP_URL_HOST );
		if ( 'www.' === substr( $sitename, 0, 4 ) ) {
			$sitename = substr( $sitename, 4 );
		}

		$from_email = 'wordpress@' . $sitename;

		return $from_email;
	}

	public function getFromAddress( $email ) {
		$emailDefault = $this->getDefaultMailFrom();
		$fromEmail    = Utils::getCurrentFromEmail();
		if ( Utils::getForceFromEmail() == 1 ) {
			return $fromEmail;
		}
		if ( ! empty( $emailDefault ) && $email !== $emailDefault ) {
			return $email;
		}

		return $fromEmail;
	}

	public function getFromName( $name ) {
		$nameDefault   = 'WordPress';
		$forceFromName = Utils::getForceFromName();
		if ( 0 === $forceFromName && $name !== $nameDefault ) {
			return $name;
		}

		return Utils::getCurrentFromName();
	}

	public function doSmtperInit( $obj ) {
		$useFallbackSmtp = Utils::conditionUseFallbackSmtp();
		$currentMailer   = Utils::getCurrentMailer();
		$obj->Mailer     = $currentMailer;
		$settings        = Utils::getYaySmtpSetting();

		if ( $useFallbackSmtp ) {
			$obj->Mailer = Utils::getCurrentMailerFallback();
			if( 'smtp' === $obj->Mailer ) {
				if ( ! empty( $settings['fallback_host'] ) ) {
					$obj->Host = $settings['fallback_host'];
				}
	
				if ( ! empty( $settings['fallback_port'] ) ) {
					$obj->Port = (int) $settings['fallback_port'];
				}
	
				if ( ! empty( $settings['fallback_auth_type'] ) ) {
					$obj->SMTPSecure = $settings['fallback_auth_type'];
				}
	
				if ( ! empty( $settings['fallback_auth'] ) && 'yes' === $settings['fallback_auth'] ) {
					$obj->SMTPAuth = true;
	
					if ( ! empty( $settings['fallback_smtp_user'] ) ) {
						$obj->Username = $settings['fallback_smtp_user'];
					}
	
					if ( ! empty( $settings['fallback_smtp_pass'] ) ) {
						$obj->Password = Utils::decrypt( $settings['fallback_smtp_pass'], 'smtppass' );
					}
				}
	
				// Set wp_mail_from && wp_mail_from_name - start
				Utils::setFromFallback($obj, $settings);
				// Set wp_mail_from && wp_mail_from_name - end
			}
		} else {
			$smtpSettings = ( ! empty( $settings ) && ! empty( $settings['smtp'] ) ) ? $settings['smtp'] : array();
			if ( 'smtp' == $currentMailer ) {
				if ( ! empty( $smtpSettings['host'] ) ) {
					$obj->Host = $smtpSettings['host'];
				}

				if ( ! empty( $smtpSettings['port'] ) ) {
					$obj->Port = (int) $smtpSettings['port'];
				}

				if ( ! empty( $smtpSettings['encryption'] ) ) {
					$obj->SMTPSecure = $smtpSettings['encryption'];
				}

				if ( ! empty( $smtpSettings['auth'] ) && 'yes' === $smtpSettings['auth'] ) {
					$obj->SMTPAuth = true;

					if ( ! empty( $smtpSettings['user'] ) ) {
						$obj->Username = $smtpSettings['user'];
					}

					if ( ! empty( $smtpSettings['pass'] ) ) {
						$obj->Password = Utils::decrypt( $smtpSettings['pass'], 'smtppass' );
					}
				}

				Utils::setFrom($obj);

			} else {
				$obj->SMTPSecure  = '';
			}
		}

	}
}
