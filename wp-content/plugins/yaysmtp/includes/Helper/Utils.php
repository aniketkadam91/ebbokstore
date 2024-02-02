<?php
namespace YaySMTP\Helper;

use YaySMTP\Helper\LogErrors;

defined( 'ABSPATH' ) || exit;

class Utils {
	//getTemplatePart('temp-small/forder',array('groupedMetaKimonoPlans' => $groupedMetaPlans[MasterValues::MV_GROUP_KIMONO], 'sexAgeType' => $SEX_AGE_TYPE, 'planShopList' => $planShopList, 'planTypeKimonoMap' => $planTypeKimonoMap));
	public static function getTemplatePart( $templateFolder, $slug = null, array $params = array() ) {
		global $wp_query;
		//BN_PLUGIN_PATH . "/views/frontside/"."{$slug}.php";
		$_template_file = $templateFolder . "/{$slug}.php";
		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP ); // phpcs:ignore
		}
		extract( $params, EXTR_SKIP );// phpcs:ignore
		require $_template_file;
	}

	public static function saniVal( $val ) {
		return sanitize_text_field( $val );
	}

	public static function saniValArray( $array ) {
		$newArray = array();
		foreach ( $array as $key => $val ) { // level 1
			if ( is_array( $val ) ) {
				foreach ( $val as $key_1 => $val_1 ) { // level 2
					if ( is_array( $val_1 ) ) {
						foreach ( $val_1 as $key_2 => $val_2 ) { // level 3
							$newArray[ $key ][ $key_1 ][ $key_2 ] = ( isset( $array[ $key ][ $key_1 ][ $key_2 ] ) ) ? sanitize_text_field( $val_2 ) : '';
						}
					} else {
						if ('pass' === $key_1) {
							$newArray[ $key ][ $key_1 ] = ( isset( $array[ $key ][ $key_1 ] ) ) ? $val_1 : '';
						} else {
							$newArray[ $key ][ $key_1 ] = ( isset( $array[ $key ][ $key_1 ] ) ) ? sanitize_text_field( $val_1 ) : '';
						}
					}
				}
			} else {
				$newArray[ $key ] = ( isset( $array[ $key ] ) ) ? sanitize_text_field( $val ) : '';
			}
		}
		return $newArray;
	}

	public static function isJson( $string ) {
		return is_string( $string ) && is_array( json_decode( $string, true ) ) && ( json_last_error() === JSON_ERROR_NONE ) ? true : false;
	}

	public static function checkNonce() {
		$nonce = sanitize_text_field( $_POST['nonce'] ); //phpcs:ignore
		if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( array( 'mess' => 'Nonce is invalid' ) );
		}
	}

	public static function getDisableEmailsDeliverySett() {
		$disableDelivery = 'no';
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['disable_emails_delivery'] ) ) {
				$disableDelivery = $yaysmtpSettings['disable_emails_delivery'];
			}
		}
		return $disableDelivery;
	}

	public static function getCurrentMailer() {
		$mailer          = 'mail';
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['currentMailer'] ) ) {
				$mailer = $yaysmtpSettings['currentMailer'];
			}
		}
		return $mailer;
	}

	public static function getCurrentMailerFallback() {
		$mailer          = 'smtp';
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['fallback_mailer_provider'] ) ) {
				$mailer = $yaysmtpSettings['fallback_mailer_provider'];
			}
		}
		return $mailer;
	}

	public static function getCurrentFromEmail() {
		$mailer          = self::getAdminEmail();
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['fromEmail'] ) ) {
				$mailer = $yaysmtpSettings['fromEmail'];
			}
		}
		return $mailer;
	}

	public static function getCurrentFromName() {
		$name          = get_bloginfo( 'name' );
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['fromName'] ) ) {
				$name = $yaysmtpSettings['fromName'];
			}
		}
		return str_replace( '\\', '', wp_kses_post( $name ) );
	}

	public static function getCurrentFromEmailFallback() {
		$result          = self::getAdminEmail();
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['fallback_from_email'] ) ) {
				$result = $yaysmtpSettings['fallback_from_email'];
			}
		}
		return $result;
	}

	public static function getCurrentFromNameFallback() {
		$result          = get_bloginfo( 'name' );
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['fallback_from_name'] ) ) {
				$result = $yaysmtpSettings['fallback_from_name'];
			}
		}
		return str_replace( '\\', '', wp_kses_post( $result ) );
	}

	public static function getFallbackHasSettingMail() {
		$result          = false;
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['fallback_has_setting_mail'] ) && 'yes' == $yaysmtpSettings['fallback_has_setting_mail'] ) {
				$result = true;
			}
		}
		return $result;
	}

	public static function getForceFromName() {
		$forceFromName   = 0;
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['forceFromName'] ) ) {
				$forceFromName = $yaysmtpSettings['forceFromName'];
			}
		}
		return intval($forceFromName);
	}

	public static function getForceFromEmail() {
		$forceFromEmail  = 1;
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( empty( $yaysmtpSettings['forceFromEmail'] ) ) {
				$forceFromEmail = 0;
			}
		}
		return intval($forceFromEmail);
	}

	public static function getMultisiteSetting() {
		$result = 'no';
		if ( ! is_multisite() ) {
			return $result;
		}

		$yaysmtpSettings = get_option( 'yaysmtp_settings', array() );
		if ( ! empty( $yaysmtpSettings ) && ! empty( $yaysmtpSettings['allowMultisite'] ) ) {
			$result = $yaysmtpSettings['allowMultisite'];
		}

		return $result;
	}

	public static function getMainSiteMultisiteSetting() {
		$result = 'no';
		if ( ! is_multisite() ) {
			return $result;
		}

		$yaysmtpSettings = get_blog_option( get_main_site_id(), 'yaysmtp_settings', array() );
		if ( ! empty( $yaysmtpSettings ) && ! empty( $yaysmtpSettings['allowMultisite'] ) ) {
			$result = $yaysmtpSettings['allowMultisite'];
		}

		return $result;
	}

	public static function getAdminEmail() {
		return get_option( 'admin_email' );
	}

	public static function getAdminFromName() {
		return get_bloginfo( 'name' );
	}

	public static function getAllMailerSetting() {
		return array(
			'mail'       => array(),
			'smtp'       => array( 'host', 'port' ),
			'sendgrid'   => array( 'api_key' ),
			'sendinblue' => array( 'api_key' ),
			'gmail'      => array( 'client_id', 'client_secret', 'gmail_access_token', 'gmail_refresh_token' ),
			'zoho'       => array( 'client_id', 'client_secret', 'access_token' ),
			'mailgun'    => array( 'api_key', 'domain' ),
			'smtpcom'    => array( 'api_key', 'sender' ),
			'amazonses'  => array( 'region', 'access_key_id', 'secret_access_key' ),
			'postmark'   => array( 'api_key' ),
			'sparkpost'  => array( 'api_key' ),
			'mailjet'    => array( 'api_key', 'secret_key' ),
			'pepipost'   => array( 'api_key' ),
			'sendpulse'  => array( 'api_key', 'secret_key' ),
			'outlookms'  => array( 'client_id', 'client_secret', 'outlookms_access_token', 'outlookms_refresh_token' ),
			'mandrill'   => array( 'api_key' ),
		);
	}

	public static function prepareDataLogInit( $phpmailer ) {
		$emailTo     = array();
		$toAddresses = $phpmailer->getToAddresses();
		if ( ! empty( $toAddresses ) && is_array( $toAddresses ) ) {
			foreach ( $toAddresses as $toEmail ) {
				if ( ! empty( $toEmail[0] ) ) {
					$emailTo[] = $toEmail[0];
				}
			}
		};

		return array(
			'subject'      => $phpmailer->Subject,
			'email_from'   => $phpmailer->From,
			'email_to'     => $emailTo, // require is array
			'date_time'    => current_time( 'mysql', true ),
			'status'       => 0, // 0: false, 1: true, 2: waiting
			'content_type' => $phpmailer->ContentType,
			'body_content' => $phpmailer->Body,
		);
	}

	public static function isMailerComplete() {
		$isComplete    = true;
		$currentMailer = self::getCurrentMailer();
		if ( 'mail' === $currentMailer ) {
			return true;
		}

		$mailerSettingAll = self::getAllMailerSetting();

		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) && ! empty( $mailerSettingAll[ $currentMailer ] ) ) {
			$settingArrRequireds = $mailerSettingAll[ $currentMailer ];
			if ( ! empty( $yaysmtpSettings[ $currentMailer ] ) ) {
				foreach ( $settingArrRequireds as $setting ) {
					if ( empty( $yaysmtpSettings[ $currentMailer ][ $setting ] ) ) {
						$isComplete = false;
					}
				}
			}
		}
		return $isComplete;
	}

	/** ----------------------------------- Auth - start -----------------------*/

	public static function getYaySmtpSetting( $forceChildSite = false ) {
		$rst = array();

		$multisite = self::getMainSiteMultisiteSetting();
		if ( 'yes' === $multisite && ! $forceChildSite ) {
			$yaysmtpSettings = get_blog_option( get_main_site_id(), 'yaysmtp_settings', array() );
			if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
				$rst = $yaysmtpSettings;
			}
			return $rst;
		}

		$yaysmtpSettings = get_option( 'yaysmtp_settings' );
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			$rst = $yaysmtpSettings;
		}
		return $rst;
	}

	public static function setYaySmtpSetting( $key, $value = '', $mailer = '' ) {
		if ( empty( $mailer ) && ! empty( $key ) ) { // Update: fromEmail / fromName / currentMailer. Ex: ['fromEmail' => 'admin']
			$setting         = self::getYaySmtpSetting();
			$setting[ $key ] = $value;
			update_option( 'yaysmtp_settings', $setting );
		} elseif ( ! empty( $mailer ) && ! empty( $key ) ) { // Update settings of mailer. Ex: ['sendgrid' => ['api_key' => '123abc']]
			$setting                    = self::getYaySmtpSetting();
			$setting[ $mailer ][ $key ] = $value;
			update_option( 'yaysmtp_settings', $setting );
		}
	}

	public static function setYaySmtpSettingFallback( $key, $value ) {
		if ( 'fallback_service_provider_mailer_settings' === $key ) { 
			if( ! empty( $value ) && is_array( $value ) ) {
				$setting = self::getYaySmtpSetting();
				foreach( $value as $mailer => $mailerVals) {
					foreach( $mailerVals as $key1 => $val1) {
						$setting['fallback_service_provider_mailer_settings'][ $mailer ][ $key1 ] = $val1;
					}
				}
				update_option( 'yaysmtp_settings', $setting );
			}
		} else {
			$setting         = self::getYaySmtpSetting();
			$setting[ $key ] = $value;
			update_option( 'yaysmtp_settings', $setting );
		}	
	}

	public static function setValueMailerSettingFallback( $key, $value = '', $mailer = '' ) {
		if ( ! empty( $mailer ) && ! empty( $key ) ) { 
			$setting = self::getYaySmtpSetting();
			$setting['fallback_service_provider_mailer_settings'][ $mailer ][ $key ] = $value;
			update_option( 'yaysmtp_settings', $setting );
		}	
	}

	public static function getYaySmtpEmailLogSetting() {
		$rst             = array();
		$yaysmtpSettings = get_option( 'yaysmtp_email_log_settings' );
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			$rst = $yaysmtpSettings;
		}
		return $rst;
	}

	public static function setYaySmtpEmailLogSetting( $key, $value = '' ) {
		if ( ! empty( $key ) ) {
			$setting         = self::getYaySmtpEmailLogSetting();
			$setting[ $key ] = $value;
			update_option( 'yaysmtp_email_log_settings', $setting );
		}
	}

	public static function getAdminPageUrl( $page = '' ) {
		if ( empty( $page ) ) {
			$page = 'yaysmtp';
		}

		return add_query_arg(
			'page',
			$page,
			self::adminUrl( 'admin.php' )
		);
	}

	public static function adminUrl( $path = '', $scheme = 'admin' ) {
		$multisiteSetting = self::getMainSiteMultisiteSetting();
		if ( is_multisite() && 'yes' == $multisiteSetting ) {
			return network_admin_url( $path, $scheme );
		}

		return admin_url( $path, $scheme );
	}
	/** ----------------------------------- Auth - end -----------------------*/

	public static function encrypt_basic( $string, $class = '' ) {
		return base64_encode( $string . '-' . substr( sha1( $class . $string . 'yay_smtp123098' ), 0, 6 ) );
	}

	public static function decrypt_basic( $string, $class = '' ) {
		$parts = explode( '-', base64_decode( $string ) );

		$numberLast = count( $parts ) - 1;
		$sha1       = $parts[ $numberLast ];
		$result     = 0;

		$stringArrTemp = array();
		for ( $i = 0; $i < $numberLast; $i++ ) {
			array_push( $stringArrTemp, $parts[ $i ] );
		}

		$result = implode( '-', $stringArrTemp );

		return substr( sha1( $class . $result . 'yay_smtp123098' ), 0, 6 ) === $sha1 ? $result : 0;
	}

	public static function encrypt ($value, $passphrase = '') {
		$passphrase = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'yay_smtp123098';
        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx . $passphrase . $salt, true);
            $salted .= $dx;
        }
        $key            = substr($salted, 0, 32);
        $iv             = substr($salted, 32, 16);
        $encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
        $data           = array("ct" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "s" => bin2hex($salt));
        return json_encode($data);
    }

    public static function decrypt ($jsonStr, $passphrase = '') {
        $json = json_decode($jsonStr, true);
		
		// New decrypt
		if ( isset( $json["s"] ) && isset( $json["iv"] ) && isset( $json["ct"] )) {
			$passphrase = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'yay_smtp123098';
			$salt       = hex2bin($json["s"]);
			$iv   		= hex2bin($json["iv"]);
			$ct   		= base64_decode($json["ct"]);
			$concatedPassphrase = $passphrase . $salt;
			$md5    = array();
			$md5[0] = md5($concatedPassphrase, true);
			$result = $md5[0];
			for ($i = 1; $i < 3; $i++) {
				$md5[$i] = md5($md5[$i - 1] . $concatedPassphrase, true);
				$result .= $md5[$i];
			}
	
			$key  = substr($result, 0, 32);
			$data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
			return json_decode($data, true);
		} else { // Old decrypt
			return self::decrypt_basic( $jsonStr, $passphrase );
		}
    }

	public static function insertEmailLogs( $data, $disable_email_delivery = 'no' ) {
		$emailLogSetting = self::getYaySmtpEmailLogSetting();
		$saveSetting     = isset( $emailLogSetting ) && isset( $emailLogSetting['save_email_log'] ) ? $emailLogSetting['save_email_log'] : 'yes';
		$infTypeSetting  = isset( $emailLogSetting ) && isset( $emailLogSetting['email_log_inf_type'] ) ? $emailLogSetting['email_log_inf_type'] : 'full_inf';

		if ( 'yes' === $saveSetting && ! empty( $data ) && is_array( $data['email_to'] ) ) {
			global $wpdb;
			$tableName = $wpdb->prefix . 'yaysmtp_email_logs';
			$content   = array(
				'subject'    => wp_kses_post( $data['subject'] ),
				'email_from' => $data['email_from'],
				'email_to'   => maybe_serialize( $data['email_to'] ),
				'mailer'     => $data['mailer'],
				'date_time'  => $data['date_time'],
				'status'     => $data['status'],
			);

			if ( ! empty( $data['reason_error'] ) ) {
				$content['reason_error'] = $data['reason_error'];
			}

			if ( 'basic_inf' !== $infTypeSetting ) {
				$content['content_type'] = $data['content_type'];
				$content['body_content'] = maybe_serialize( $data['body_content'] );
			}

			// Get email source ( what plugin, theme, or wp core ? )
			$DEBUG_BACKTRACE = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
			foreach ( $DEBUG_BACKTRACE as $data ) {
				if ( 'wp_mail' === $data['function'] && ! empty( $data['file'] ) ) {
					$filePath             = $data['file'];
					$root                 = self::getRoot( $filePath );
					if ( 'yes' !== $disable_email_delivery ) {
						$content['root_name'] = $root;
					} else {
						$content['root_name'] = '[' . $root . '] - Development Mode';
					}
					break;
				}
			}

			$wpdb->insert( $tableName, $content );
			$logId = $wpdb->insert_id;
			return $logId;
		}
		return false;
	}

	public static function updateEmailLog( $data ) {
		$emailLogSetting = self::getYaySmtpEmailLogSetting();
		$saveSetting     = isset( $emailLogSetting ) && isset( $emailLogSetting['save_email_log'] ) ? $emailLogSetting['save_email_log'] : 'yes';

		if ( 'yes' === $saveSetting && ! empty( $data ) && ! empty( $data['id'] ) ) {
			global $wpdb;
			$tableName = $wpdb->prefix . 'yaysmtp_email_logs';
			$logId = $data['id'];
			unset( $data['id'] );
			$wpdb->update( $tableName, $data, array( 'id' => $logId ) );
		}
	}

	public static function getRoot( $file ) {
		$cacheData = get_transient( 'YAYSMTP_ROOT' );
		$cacheData = isset( $cacheData ) ? $cacheData : array();

		if ( ! empty( $cacheData[ $file ] ) ) {
			return $cacheData[ $file ];
		}

		$result = self::getNameOfPlugin( $file );

		if ( empty( $result ) ) {
			$result = self::getNameOfPlugin( $file, true );
		}

		if ( empty( $result ) ) {
			$result = self::getNameOfTheme( $file );
		}

		if ( empty( $result ) ) {
			$result = self::getNameOfWPSource( $file );
		}

		if ( empty( $result ) ) {
			$result = '';
		}

		$cacheData[ $file ] = $result;
		set_transient( 'YAYSMTP_ROOT', $cacheData, WEEK_IN_SECONDS );

		return $result;
	}

	public static function getChartData( $groupBy = 'day', $year = '', $start = '', $end = '' ) {
		global $wpdb;

		$startDate   = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : $start;
		$endDate     = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : $end;
		$currentYear = gmdate( 'Y' );

		if ( ! $startDate ) {
			$startDate = gmdate( 'Y-m-d', strtotime( gmdate( 'Ym', current_time( 'timestamp' ) ) . '01' ) );

			if ( 'year' === $groupBy ) {
				$startDate = $year . '-01-01';
			}
		}

		if ( ! $endDate ) {
			$endDate = gmdate( 'Y-m-d', current_time( 'timestamp' ) );

			if ( 'year' === $groupBy && ( $year < $currentYear ) ) {
				$endDate = $year . '-12-31';
			}
		}

		$dateWhere = '';

		if ( 'day' == $groupBy ) {
			$mailSuccessData = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT
					COUNT(el.id) as total_emails,
					el.date_time as date_time
					FROM {$wpdb->prefix}yaysmtp_email_logs el
					WHERE
					el.status = 1
					AND DATE(el.date_time) >= %s AND DATE(el.date_time) <= %s
					GROUP BY YEAR(el.date_time), MONTH(el.date_time), DAY(el.date_time)",
					$startDate,
					$endDate
				)
			);

			$mailFailData = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT
					COUNT(el.id) as total_emails,
					el.date_time as date_time
					FROM {$wpdb->prefix}yaysmtp_email_logs el
					WHERE
					el.status = 0
					AND DATE(el.date_time) >= %s AND DATE(el.date_time) <= %s
					GROUP BY YEAR(el.date_time), MONTH(el.date_time), DAY(el.date_time)",
					$startDate,
					$endDate
				)
			);
		} else {
			$mailSuccessData = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT
					COUNT(el.id) as total_emails,
					el.date_time as date_time
					FROM {$wpdb->prefix}yaysmtp_email_logs el
					WHERE
					el.status = 1
					AND DATE(el.date_time) >= %s AND DATE(el.date_time) <= %s
					GROUP BY YEAR(el.date_time), MONTH(el.date_time)",
					$startDate,
					$endDate
				)
			);

			$mailFailData = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT
					COUNT(el.id) as total_emails,
					el.date_time as date_time
					FROM {$wpdb->prefix}yaysmtp_email_logs el
					WHERE
					el.status = 0
					AND DATE(el.date_time) >= %s AND DATE(el.date_time) <= %s
					GROUP BY YEAR(el.date_time), MONTH(el.date_time)",
					$startDate,
					$endDate
				)
			);
		}

		$data = array(
			'successData' => $mailSuccessData,
			'failData'    => $mailFailData,
		);
		return $data;
	}

	public static function getMailBankSettingsTable() {
		global $wpdb;
		$tablePrefix = $wpdb->prefix;
		if ( is_multisite() ) {
			$tableExist = $wpdb->query(
				'SHOW TABLES LIKE "' . $wpdb->base_prefix . 'mail_bank_meta"'
			);

			if ( ! empty( $tableExist ) ) {
				$settings    = $wpdb->get_var(
					$wpdb->prepare(
						'SELECT meta_value FROM ' . $wpdb->base_prefix . 'mail_bank_meta WHERE meta_key=%s',
						'settings'
					)
				);
				  $dataArray = maybe_unserialize( $settings );
				if ( isset( $dataArray['fetch_settings'] ) && 'network_site' === $dataArray['fetch_settings'] ) {
						$tablePrefix = $wpdb->base_prefix;
				}
			}
		}

		$result = null;
		if ( is_multisite() ) {
			$tableExist2 = $wpdb->query(
				'SHOW TABLES LIKE "{$wpdb->base_prefix}mail_bank_meta"'
			);
		} else {
			$tableExist2 = $wpdb->query(
				'SHOW TABLES LIKE "{$wpdb->prefix}mail_bank_meta"'
			);
		}

		if ( is_multisite() ) {
			if ( ! empty( $tableExist2 ) ) {
				$result = $wpdb->get_var(
					$wpdb->prepare(
						'SELECT meta_value FROM {$wpdb->base_prefix}mail_bank_meta WHERE meta_key=%s',
						'email_configuration'
					)
				);
			}
		} else {
			if ( ! empty( $tableExist2 ) ) {
				$result = $wpdb->get_var(
					$wpdb->prepare(
						'SELECT meta_value FROM {$wpdb->prefix}mail_bank_meta WHERE meta_key=%s',
						'email_configuration'
					)
				);
			}
		}

		return maybe_unserialize( $result );
	}

	public static function getYaysmtpImportPlugins() {
		$yaysmtpImportPlugins = array();
		$esyWpSmtpSettings    = get_option( 'swpsmtp_options', array() );
		$wpMailSettings       = get_option( 'wp_mail_smtp', array() );
		$smtpMailerSettings   = get_option( 'smtp_mailer_options', array() );
		$wpSmtpsettings       = get_option( 'wp_smtp_options', array() );
		$mailBankSettings     = self::getMailBankSettingsTable();
		$postSmtpSettings     = get_option( 'postman_options', array() );

		if ( ! empty( $esyWpSmtpSettings ) ) {
			$setts = array(
				'val'   => 'easywpsmtp',
				'title' => 'Easy WP SMTP',
				'img'   => 'easywpsmtp.png',
			);
			array_push( $yaysmtpImportPlugins, $setts );
		}
		if ( ! empty( $wpMailSettings ) ) {
			$setts = array(
				'val'   => 'wpmailsmtp',
				'title' => __( 'WP Mail SMTP', 'yay-smtp' ),
				'img'   => 'wpmailsmtp.png',
			);
			array_push( $yaysmtpImportPlugins, $setts );
		}
		if ( ! empty( $smtpMailerSettings ) ) {
			$setts = array(
				'val'   => 'smtpmailer',
				'title' => __( 'SMTP Mailer', 'yay-smtp' ),
				'img'   => 'smtpmailer.png',
			);
			array_push( $yaysmtpImportPlugins, $setts );
		}
		if ( ! empty( $wpSmtpsettings ) ) {
			$setts = array(
				'val'   => 'wpsmtp',
				'title' => __( 'WP SMTP', 'yay-smtp' ),
				'img'   => 'wpsmtp.png',
			);
			array_push( $yaysmtpImportPlugins, $setts );
		}
		if ( ! empty( $mailBankSettings ) ) {
			$setts = array(
				'val'   => 'mailbank',
				'title' => __( 'Mail Bank', 'yay-smtp' ),
				'img'   => 'mailbank.png',
			);
			array_push( $yaysmtpImportPlugins, $setts );
		}
		if ( ! empty( $postSmtpSettings ) ) {
			$setts = array(
				'val'   => 'postsmtp',
				'title' => __( 'Post SMTP Mailer', 'yay-smtp' ),
				'img'   => 'postsmtp.png',
			);
			array_push( $yaysmtpImportPlugins, $setts );
		}

		return $yaysmtpImportPlugins;
	}

	public static function getTemplateHtml( $template_name, $template_path = '' ) {
		ob_start();
		$_template_file = $template_path . "/{$template_name}.php";
		include $_template_file;
		return ob_get_clean();
	}

	public static function getMailReportData( $start = '', $end = '' ) {
		global $wpdb;

		$startDate = $start;
		$endDate   = $end;
		if ( ! $startDate ) {
			$startDate = gmdate( 'Y-m-d', strtotime( gmdate( 'Ym', current_time( 'timestamp' ) ) . '01' ) );
		}
		if ( ! $endDate ) {
			$endDate = gmdate( 'Y-m-d', current_time( 'timestamp' ) );
		}

		// $dateWhere = " AND DATE(el.date_time) >= '$startDate' AND DATE(el.date_time) <= '$endDate'";

		$mailSuccessData = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT
				COUNT(el.id) as total_emails
				FROM {$wpdb->prefix}yaysmtp_email_logs el
				WHERE
				el.status = 1
				AND DATE(el.date_time) >= %s AND DATE(el.date_time) <= %s",
				$startDate,
				$endDate
			)
		);

		$mailFailData = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT
			COUNT(el.id) as total_emails
			FROM {$wpdb->prefix}yaysmtp_email_logs el
			WHERE
			el.status = 0
			AND DATE(el.date_time) >= %s AND DATE(el.date_time) <= %s",
				$startDate,
				$endDate
			)
		);

		$data = array(
			'total_mail'  => $mailSuccessData + $mailFailData,
			'sent_mail'   => $mailSuccessData,
			'failed_mail' => $mailFailData,
		);

		return $data;
	}

	public static function percentClass( $current = 0, $last = 0 ) {
		$percent = 0;
		$class   = 'up';
		if ( 0 == $current ) {
			$percent = $last * 100;
			$class   = 'down';
		} elseif ( 0 == $last ) {
			$percent = $current * 100;
		} elseif ( $current > $last ) {
			$percent = ( $current - $last ) / $last * 100;
		} elseif ( $current < $last ) {
			$percent = ( $last - $current ) / $last * 100;
			$class   = 'down';
		}

		$result = array(
			'percent' => round( $percent, 1 ),
			'class'   => $class,
		);

		return $result;
	}

	public static function getMailReportGroupByData( $groupBy = 'subject', $start = '', $end = '', $limit = 5 ) {
		global $wpdb;

		$startDate = $start;
		$endDate   = $end;
		if ( ! $startDate ) {
			$startDate = gmdate( 'Y-m-d', strtotime( gmdate( 'Ym', current_time( 'timestamp' ) ) . '01' ) );
		}
		if ( ! $endDate ) {
			$endDate = gmdate( 'Y-m-d', current_time( 'timestamp' ) );
		}

		// $dateWhere = "DATE( el . date_time ) >= '$startDate' and DATE( el . date_time ) <= '$endDate'";

		// Get 5 items have the most number
		$mailReportGroupByDataLimitQuery = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				el . subject, COUNT( el . id ) as total_emails
				FROM {$wpdb->prefix}yaysmtp_email_logs el
				WHERE DATE( el . date_time ) >= %s and DATE( el . date_time ) <= %s
				GROUP BY el.subject
				ORDER BY total_emails DESC
				LIMIT %d",
				$startDate,
				$endDate,
				$limit
			)
		);

		$mailReportGroupByDataLimitData = array();
		if ( ! empty( $mailReportGroupByDataLimitQuery ) ) {
			foreach ( $mailReportGroupByDataLimitQuery as $el ) {
				$title                                    = trim( $el->subject );
				$mailReportGroupByDataLimitData[ $title ] = (int) $el->total_emails;
			}
		}

		// Get total items
		$mailReportGroupByDataQuery = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				el . subject, COUNT( el . id ) as total_emails, el . status
				FROM {$wpdb->prefix}yaysmtp_email_logs el
				WHERE DATE( el . date_time ) >= %s and DATE( el . date_time ) <= %s
				GROUP BY el.subject, el.status
				ORDER BY total_emails DESC",
				$startDate,
				$endDate
			)
		);

		$mailReportGroupByData = array();
		if ( ! empty( $mailReportGroupByDataQuery ) ) {
			foreach ( $mailReportGroupByDataQuery as $el ) {
				$title  = trim( $el->subject );
				$status = (int) $el->status;

				$totalSent   = 0;
				$totalFailed = 0;
				if ( 1 == $status ) {
					$totalSent                                     = (int) $el->total_emails;
					$mailReportGroupByData[ $title ]['total_sent'] = $totalSent;
				} elseif ( 0 == $status ) {
					$totalFailed                                     = (int) $el->total_emails;
					$mailReportGroupByData[ $title ]['total_failed'] = $totalFailed;
				}
			}
		}

		// Merge data
		if ( ! empty( $mailReportGroupByDataLimitData ) && ! empty( $mailReportGroupByData ) ) {
			foreach ( $mailReportGroupByDataLimitData as $title => $el ) {
				$mailReportGroupByDataLimitData[ $title ] = $mailReportGroupByData[ $title ];
			}
		}

		return $mailReportGroupByDataLimitData;
	}

	public static function conditionUseFallbackSmtp( $force = false ) {
		if ( $force ) {
			return true;
		}

		if ( self::isTestMailFallback() ) {
			return true;
		}

		if ( ! self::getFallbackHasSettingMail() ) {
			return false;
		}

		if ( ! self::isFullSettingsFallbackSmtp() ) {
			return false;
		}

		$hasFallbackSettingsFail = LogErrors::getErrFallback();
		if ( ! empty( $hasFallbackSettingsFail ) ) {
			return false;
		}

		global $wpdb;
		$result = false;

		$sqlResult = $wpdb->get_results(
			"SELECT *
		FROM {$wpdb->prefix}yaysmtp_email_logs
		ORDER BY date_time DESC
		LIMIT 1"
		);

		$countFailedEmail = 0;
		if ( ! empty( $sqlResult ) ) {
			foreach ( $sqlResult as $mail ) {
				$status = (int) $mail->status;
				$mailer = $mail->mailer;
				if ( 0 == $status && 'Fallback' != $mailer ) {
					++$countFailedEmail;
				}
			}
		}

		if ( 1 == $countFailedEmail ) {
			$result = true;
		}

		return $result;
	}

	public static function isFullSettingsFallbackSmtp() {
		$currentMailerFallback = self::getCurrentMailerFallback();
		$settings              = self::getYaySmtpSetting();
		if ( 'mail' === $currentMailerFallback ) {
			return true;
		} else if ( 'smtp' === $currentMailerFallback ) {
			if ( ! empty( $settings ) ) {
				if ( empty( $settings['fallback_host'] ) ) {
					return false;
				}
				if ( empty( $settings['fallback_port'] ) ) {
					return false;
				}
				// if ( empty( $settings['fallback_auth_type'] ) ) {
				// 	return false;
				// }
				if ( empty( $settings['fallback_auth'] ) ) {
					return false;
				} elseif ( ! empty( $settings['fallback_auth'] ) && 'yes' == $settings['fallback_auth'] ) {
					if ( empty( $settings['fallback_smtp_user'] ) ) {
						return false;
					}
					if ( empty( $settings['fallback_smtp_pass'] ) ) {
						return false;
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			$isComplete = true;
			$mailerSettingAll = self::getAllMailerSetting();
			if ( ! empty( $settings['fallback_service_provider_mailer_settings'] ) && ! empty( $mailerSettingAll[$currentMailerFallback] ) ) {
				$settingArrRequireds = $mailerSettingAll[$currentMailerFallback];
				$fallbackSettings    = $settings['fallback_service_provider_mailer_settings'];
				if ( ! empty( $fallbackSettings[ $currentMailerFallback ] ) ) {
					foreach ( $settingArrRequireds as $setting ) {
						if ( empty( $fallbackSettings[ $currentMailerFallback ][ $setting ] ) ) {
							$isComplete = false;
							break;
						}
					}
				}
			}
			return $isComplete;
		}	
	}

	public static function isTestMailFallback() {
		$result          = false;
		$yaysmtpSettings = self::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['flag_test_mail_fallback'] ) && 'yes' == $yaysmtpSettings['flag_test_mail_fallback'] ) {
				$result = true;
			}
		}
		return $result;
	}

	public static function setFrom(&$phpmailer) {
		$currentFromEmail = self::getCurrentFromEmail();
		$currentFromName  = self::getCurrentFromName();
		$from_email       = $phpmailer->From;
		$from_name        = wp_kses_post( $phpmailer->FromName );
		if ( self::getForceFromEmail() == 1 ) {
			$from_email = $currentFromEmail;
		}
		if ( self::getForceFromName() == 1 ) {
			$from_name = $currentFromName;
		}

		$phpmailer->setFrom( $from_email, $from_name, false );
	}

	public static function setFromFallback(&$phpmailer, $settings) {
		$currentFromEmail = self::getCurrentFromEmailFallback();
		$currentFromName  = self::getCurrentFromNameFallback();
		$from_email       = $phpmailer->From;
		$from_name        = wp_kses_post( $phpmailer->FromName );
		if ( isset( $settings['fallback_force_from_email'] ) && 'yes' == $settings['fallback_force_from_email'] ) {
			$from_email = $currentFromEmail;
		}
		if ( isset( $settings['fallback_force_from_name'] ) && 'yes' == $settings['fallback_force_from_name'] ) {
			$from_name = $currentFromName;
		}

		$phpmailer->setFrom( $from_email, $from_name, false );
	}

	public static function getDeleteDatetimeSetting() {
		$emailLogSetting = self::getYaySmtpEmailLogSetting();
		$result          = isset( $emailLogSetting ) && isset( $emailLogSetting['email_log_delete_time'] ) ? (int) $emailLogSetting['email_log_delete_time'] : 60;
		return $result;
	}

	public static function deleteAllEmailLogs() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'yaysmtp_email_logs' ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'yaysmtp_event_email_clicked_link' ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'yaysmtp_event_email_opened' ) );
	}
	
	public static function getFullUrl() {
		$http        = isset( $_SERVER['HTTPS'] ) && ( 'on' === $_SERVER['HTTPS'] ) ? 'https' : 'http';
		$http_host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		$result      = $http . '://' . $http_host . $request_uri;
		return $result;
	}

	public static function getParamUrl( $param ) {
		$fullUrl       = self::getFullUrl();
		$urlComponents = parse_url( $fullUrl );
		parse_str( $urlComponents['query'], $urlParams );
		if ( empty( $urlParams[ $param ] ) ) {
			return '';
		}
		return $urlParams[ $param ];
	}

	public static function getNameOfTheme( $file ) {
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			return '';
		}

		$DIRECTORY_SEPARATOR = '\/';
		if ( defined( 'DIRECTORY_SEPARATOR' ) ) {
			$DIRECTORY_SEPARATOR = '\\' . DIRECTORY_SEPARATOR;
		}
		$WP_CONTENT_DIR_ROOT = basename( WP_CONTENT_DIR );

		preg_match( "/$DIRECTORY_SEPARATOR$WP_CONTENT_DIR_ROOT{$DIRECTORY_SEPARATOR}themes{$DIRECTORY_SEPARATOR}(.[^$DIRECTORY_SEPARATOR]+)/", $file, $matches );
		if ( ! empty( $matches[1] ) ) {
			$themeSlug = $matches[1];
			$themeObj  = wp_get_theme( $themeSlug );
			if ( method_exists( $themeObj, 'get' ) ) {
				return $themeObj->get( 'Name' );
			}
			return $themeSlug;
		}

		return '';
	}

	public static function getNameOfPlugin( $file, $mulPlugin = false ) {
		$PLUGIN_DIR = empty( $mulPlugin ) ? 'WP_PLUGIN_DIR' : 'WPMU_PLUGIN_DIR';
		if ( ! defined( $PLUGIN_DIR ) ) {
			return '';
		}

		$DIRECTORY_SEPARATOR = '\/';
		if ( defined( 'DIRECTORY_SEPARATOR' ) ) {
			$DIRECTORY_SEPARATOR = '\\' . DIRECTORY_SEPARATOR;
		}
		$PLUGIN_DIR_BASE = basename( constant( $PLUGIN_DIR ) );

		preg_match( "/$DIRECTORY_SEPARATOR$PLUGIN_DIR_BASE$DIRECTORY_SEPARATOR(.[^$DIRECTORY_SEPARATOR]+)($DIRECTORY_SEPARATOR|\.php)/", $file, $matches );
		if ( ! empty( $matches[1] ) ) {
			$slug = $matches[1];
			if ( ! function_exists( 'get_mu_plugins' ) || ! function_exists( 'get_plugins' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}
			$plugins = ! $mulPlugin ? get_plugins() : get_mu_plugins();

			foreach ( $plugins as $plugin => $plgData ) {
				if ( isset( $plgData['Name'] ) ) {
					if ( 1 === preg_match( "/^$slug(\/|\.php)/", $plugin ) ) {
						return $plgData['Name'];
					}
				}
			}

			return $slug;
		}

		return '';
	}

	public static function getNameOfWPSource( $file ) {
		if ( ! defined( 'ABSPATH' ) ) {
			return '';
		}

		$includesDir = defined( 'WPINC' ) ? trailingslashit( ABSPATH . WPINC ) : false;
		$adminDir    = trailingslashit( ABSPATH . 'wp-admin' );
		if ( 0 === strpos( $file, $includesDir ) || 0 === strpos( $file, $adminDir ) ) {
			return __( 'WP Source', 'yay-smtp' );
		}

		return '';
	}

	public static function stringThreeDot( $string = '', $length = 50 ) {
		if ( empty( $string ) ) {
			return '';
		}

		$rest = $string;
		if ( strlen( $string ) > $length ) {
			$rest = substr( $string, 0, $length ) . '...';
		}
		return $rest;
	}

	public static function getAllMailer() {
		return array(
			'mail'       => 'Default',
			'sendgrid'   => 'SendGrid',
			'sendinblue' => 'Sendinblue',
			'amazonses'  => 'Amazon SES',
			'mailgun'    => 'Mailgun',
			'smtpcom'    => 'SMTP.com',
			'gmail'      => 'Gmail',
			'zoho'       => 'Zoho',
			'postmark'   => 'Postmark',
			'sparkpost'  => 'SparkPost',
			'mailjet'    => 'Mailjet',
			'pepipost'   => 'Pepipost',
			'sendpulse'  => 'SendPulse',
			'outlookms'  => 'Outlook Microsoft',
			'mandrill'   => 'Mandrill',
			'smtp'       => 'Other SMTP',
		);
	}

	public static function getTrackingEmailOpenedByLogId( $logId ) {
		global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare( "Select * FROM {$wpdb->prefix}yaysmtp_event_email_opened WHERE log_id = %d", $logId ) );
		if ( $result ) {
			return $result;
		}

		return false;
	}

	public static function getTrackingEmailClickedLinkByLogId( $logId ) {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( "Select * FROM {$wpdb->prefix}yaysmtp_event_email_clicked_link WHERE log_id = %d", $logId ));
		if ( $result ) {
			return $result;
		}

		return false;
	}

	public static function getGeneralFieldsExport() { 
		return array(
			'email_to' 	   => esc_html__( 'To Email', 'yay-smtp' ),
			'email_from'   => esc_html__( 'From Email', 'yay-smtp' ),
			'subject' 	   => esc_html__( 'Subject', 'yay-smtp' ),
			'date_time'    => esc_html__( 'Time', 'yay-smtp' ),
			'body_content' => esc_html__( 'Body', 'yay-smtp' ),
			'status'	   => esc_html__( 'Status', 'yay-smtp' ),
		);
	}
	
	public static function getAdditionalFieldsExport() { 
		return array(
			'mailer' 	       => esc_html__( 'Mailer', 'yay-smtp' ),
			'id'   		   	   => esc_html__( 'Email log ID', 'yay-smtp' ),
			'reason_error' 	   => esc_html__( 'Error details', 'yay-smtp' ),
			'tracking_opened'  => esc_html__( 'Opened', 'yay-smtp' ),
			'tracking_clicked' => esc_html__( 'Clicked', 'yay-smtp' ),
			'root_name'    	   => esc_html__( 'Generated by', 'yay-smtp' )
		);
	}
}
