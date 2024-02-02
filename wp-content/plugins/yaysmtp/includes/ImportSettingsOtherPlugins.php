<?php
namespace YaySMTP;

defined( 'ABSPATH' ) || exit;

use YaySMTP\Helper\Utils;

class ImportSettingsOtherPlugins {
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}

	private function __construct() {
	}

	private function doHooks() {
		add_action( 'wp_ajax_yaysmtp_close_popup_import_smtp_settings', array( $this, 'closePopupImportSmtpSettings' ) );
		add_action( 'wp_ajax_yaysmtp_import_smtp_settings', array( $this, 'importSmtpSettings' ) );
		add_action( 'wp_ajax_yaysmtp_export_email_log', array( $this, 'exportEmailLog' ) );
		// add_action( 'admin_menu', array( $this, 'adminMenuSettings' ) );
		add_action( 'admin_notices', array( $this, 'popupImportSmtpSettings' ) );
	}

	// public function adminMenuSettings() {
	// 	add_options_page( __( 'YaySMTP Setting', 'yay-smtp' ), __( 'YaySMTP', 'yay-smtp' ), 'manage_options', 'yaysmtp_settings', array( $this, 'yaysmtpSettingOther' ) );
	// }

	// public function yaysmtpSettingOther() {
	// 	include YAY_SMTP_PLUGIN_PATH . '/includes/Views/yaysmtp-settings-other.php';
	// }

	public function popupImportSmtpSettings() {
		$flagImported         = $this->getFlagImportSettsSmtpPopup();
		$yaysmtpImportPlugins = Utils::getYaysmtpImportPlugins();
		if ( 'yes' == $flagImported && ! empty( $yaysmtpImportPlugins ) ) {
			$this->popupSmtpNotices();
		}
	}

	public function getFlagImportSettsSmtpPopup() {
		$flag            = 'yes';
		$yaysmtpSettings = Utils::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
			if ( ! empty( $yaysmtpSettings['flagImpotedSettsSmtp'] ) ) {
				$flag = $yaysmtpSettings['flagImpotedSettsSmtp'];
			}
		}
		return $flag;
	}

	public function popupSmtpNotices() {
		$link = add_query_arg(
			array(
				'page'   => 'yaysmtp',
				'tab'    => 'additional-setting',
			),
			admin_url( 'admin.php' )
		);

		$html  = '<div class="notice is-dismissible notice-warning yaysmtp-import-settings-notice">';
		$html .= '<h2 class="yaysmtp-notices-title">' . esc_html__( 'Import SMTP settings to YaySMTP', 'yay-smtp' ) . '</h2>';
		$html .= '<div>';
		$html .= '<div class="yaysmtp-mess-notices">';
		$html .= '<p>We found previous SMTP settings from other plugins on your site. Would you like to import it to YaySMTP now? You can import later from WordPress: <b>Settings > YaySMTP</b>.</p>';
		$html .= '</div>';
		$html .= '<div>';
		$html .= '<a href="' . $link . '" class="button button-primary">' . esc_html__( 'Go to Import', 'yay-smtp' ) . '</a>';
		$html .= '<a class="button button-default close-btn">' . esc_html__( 'Got it', 'yay-smtp' ) . '</a>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		echo wp_kses_post( $html );
	}

	public function closePopupImportSmtpSettings() {
		try {
			Utils::checkNonce();
			Utils::setYaySmtpSetting( 'flagImpotedSettsSmtp', 'no' );
			wp_send_json_success();
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'mess' => $e->getMessage() ) );
		}
	}

	public function importSmtpSettings() {
		try {
			Utils::checkNonce();
			if ( ! empty( $_REQUEST['plugin_name'] ) ) {
				$pluginName = Utils::saniVal( $_REQUEST['plugin_name'] ); //phpcs:ignore

				if ( 'easywpsmtp' == $pluginName ) {
					$this->importSettingsOfEasyWpSmtp();
				} elseif ( 'wpmailsmtp' == $pluginName ) {
					$this->importSettingsOfWpMailSmtp();
				} elseif ( 'smtpmailer' == $pluginName ) {
					$this->importSettingsOfSmtpMailer();
				} elseif ( 'wpsmtp' == $pluginName ) {
					$this->importSettingsOfWpSmtp();
				} elseif ( 'mailbank' == $pluginName ) {
					$this->importSettingsOfMailBank();
				} elseif ( 'postsmtp' == $pluginName ) {
					$this->importSettingsOfPostSMTP();
				}

				wp_send_json_success(
					array(
						'mess' => __( 'Import SMTP Settings successful.', 'yay-smtp' ),
					)
				);
			}
			wp_send_json_error( array( 'mess' => __( 'Please choose a SMTP plugin to import', 'yay-smtp' ) ) );

		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'mess' => $e->getMessage() ) );
		}
	}

	public function exportEmailLog() {
		try {
			Utils::checkNonce();
			if ( isset( $_POST['params'] ) ) {
				$params = Utils::saniValArray( $_POST['params'] ); //phpcs:ignore

				if ( ! empty($params['fieldsDisplay']) ) {
					$fields_display      = $params['fieldsDisplay'];
					$filename            = 'yaysmtp-email-log-' . time();
					
					$email_logs_query    = $this->queryEmailLogs($params);
					if( empty( $email_logs_query ) ) {
						wp_send_json_error(array('mess' => __( 'Data is empty.', 'yay-smtp' )));
					} else {
						$headers             = $this->emailLogExportHeaders( $fields_display );
						$data_content_export = $this->emailLogExportContents( $email_logs_query, $fields_display );
						$this->downloaCSV( $filename, $headers, $data_content_export );
					}	
				} else {
					wp_send_json_error(array('mess' => __( 'No field is selectd.', 'yay-smtp' )));
				}
				
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'mess' => $e->getMessage() ) );
		}
	}

	private function emailLogExportHeaders( $fields_selected = array() ) {
		$general_fields    = Utils::getGeneralFieldsExport();
		$additional_fields = Utils::getAdditionalFieldsExport();
		$all_fields 	   = array_merge( $general_fields, $additional_fields );

		$result = array();
		foreach( $fields_selected as $field_selected ) {
			if( isset( $all_fields[ $field_selected ] ) ) {
				$result[] = $all_fields[ $field_selected ];
			}
		}

		return $result;
	}

	private function emailLogExportContents( $email_logs, $fields_selected = array() ) {
		$data_content_export = array();
		foreach ( $email_logs as $email_log ) {
			$content_row = array();
			foreach( $fields_selected as $field_selected ) {
				if( property_exists($email_log, $field_selected) ) {
					if( 'email_to' === $field_selected) {
						$content_row[] = implode('&&', maybe_unserialize( $email_log->{$field_selected} ) );
					} elseif( 'body_content' === $field_selected ) {
						$content_row[] = wp_kses_post( maybe_serialize( $email_log->{$field_selected} ) ); 
					} else {
						$content_row[] = sanitize_text_field( $email_log->{$field_selected} );
					}	
				} elseif ( 'tracking_opened' === $field_selected || 'tracking_clicked' === $field_selected) {
					if ( 'tracking_opened' === $field_selected ) {
						$email_opened = Utils::getTrackingEmailOpenedByLogId( intval( $email_log->id ));
						if( ! empty( $email_opened )) {
							$email_opened_count = intval( $email_opened->count );
							$content_row[] = $email_opened_count > 0 ? __( 'Yes', 'yay-smtp' ) : __( 'No', 'yay-smtp' ) ;
						} else {
							$content_row[] = __( 'No', 'yay-smtp' ) ;
						}
					} elseif ( 'tracking_clicked' === $field_selected ) {
						$email_clicked_links = Utils::getTrackingEmailClickedLinkByLogId( intval( $email_log->id ));
						if( ! empty( $email_clicked_links )) {
							$email_clicked_links_count = 0;
							foreach( $email_clicked_links as $email_clicked_link) {
								if ( intval($email_clicked_link->count) > 0) {
									$email_clicked_links_count = 1;
									break;
								}
							}
							$content_row[] = $email_clicked_links_count > 0 ? __( 'Yes', 'yay-smtp' ) : __( 'No', 'yay-smtp' ) ;
						} else {
							$content_row[] = __( 'No', 'yay-smtp' ) ;
						}
					}
				}
			}

			$data_content_export[] = $content_row;
		}

		return $data_content_export;
	}

	private function queryEmailLogs($params) {
		global $wpdb;
		// From date and To date
		$where_clause = array();
		if ( ! empty( $params['from'] ) && ! empty( $params['to'] ) ) {
			$start_date_obj  = new \DateTime( $params['from'] );
			$end_date_Obj    = new \DateTime( $params['to'] );
			$start_date      = $start_date_obj->format( 'Y-m-d' );
			$end_date        = $end_date_Obj->format( 'Y-m-d' );
			$where_clause[]  = "DATE(date_time) >= '$start_date' AND DATE(date_time) <= '$end_date'";
		}

		// Search value
		if ( ! empty( $params['searchValue'] ) && ! empty( $params['searchKey'] ) ) {
			$search_key     = ( ! empty( $params['searchKey'] ) ) ? $params['searchKey'] : "";
			$search_value   = ( ! empty( $params['searchValue'] ) ) ? $params['searchValue'] : "";
			$where_clause[] = $search_key . ' LIKE "%%' . $search_value . '%%"';
		}

		if ( $where_clause ) {
			$where_clause = implode(' AND ', $where_clause);
			$sql_repare = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}yaysmtp_email_logs WHERE $where_clause" );
		} else {
			$sql_repare = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}yaysmtp_email_logs" );
		}
		
		return $wpdb->get_results( $sql_repare ); // phpcs:ignore
	}

	private function downloaCSV($filename, $headers, $data_content_export) {
		$filename = $filename . '.csv';
		$filename = sanitize_file_name( $filename );

		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		header( 'Content-Type: text/csv; charset=utf-8' );

		$f = fopen("php://output", "w");
		
		$delimiter = ","; 
		fputcsv($f, $headers, $delimiter);// CSV column headings
		      
		foreach($data_content_export as $row_data){ 
			// Adding data into CSV
			fputcsv($f, $row_data, $delimiter); 
		}

		fclose($f);

		exit();
	}

	// Easy Wp Mail
	public function importSettingsOfEasyWpSmtp() {
		$esyWpSmtpSettings = get_option( 'swpsmtp_options', array() );
		if ( ! empty( $esyWpSmtpSettings ) ) {
			Utils::setYaySmtpSetting( 'currentMailer', 'smtp' );
			if ( ! empty( $esyWpSmtpSettings['from_email_field'] ) ) {
				Utils::setYaySmtpSetting( 'fromEmail', $esyWpSmtpSettings['from_email_field'] );
			}
			if ( ! empty( $esyWpSmtpSettings['from_name_field'] ) ) {
				Utils::setYaySmtpSetting( 'fromName', $esyWpSmtpSettings['from_name_field'] );
			}
			if ( isset( $esyWpSmtpSettings['force_from_name_replace'] ) ) {
				$forceFromNameReplace = $esyWpSmtpSettings['force_from_name_replace'];
				if ( $forceFromNameReplace ) {
					Utils::setYaySmtpSetting( 'forceFromName', '1' );
				} else {
					Utils::setYaySmtpSetting( 'forceFromName', '0' );
				}
			}

			$smtpSettings = $esyWpSmtpSettings['smtp_settings'];
			if ( ! empty( $smtpSettings ) ) {
				if ( ! empty( $smtpSettings['host'] ) ) {
					Utils::setYaySmtpSetting( 'host', $smtpSettings['host'], 'smtp' );
				}
				if ( ! empty( $smtpSettings['type_encryption'] ) ) {
					Utils::setYaySmtpSetting( 'encryption', $smtpSettings['type_encryption'], 'smtp' );
				}
				if ( ! empty( $smtpSettings['port'] ) ) {
					Utils::setYaySmtpSetting( 'port', $smtpSettings['port'], 'smtp' );
				}
				if ( ! empty( $smtpSettings['autentication'] ) ) {
					Utils::setYaySmtpSetting( 'auth', $smtpSettings['autentication'], 'smtp' );
				}
				if ( ! empty( $smtpSettings['username'] ) ) {
					Utils::setYaySmtpSetting( 'user', $smtpSettings['username'], 'smtp' );
				}
			}
		}
	}


	// Wp Mail SMTP
	public function importSettingsOfWpMailSmtp() {
		$settingAlls = get_option( 'wp_mail_smtp', array() );
		if ( ! empty( $settingAlls ) ) {
			// General Settings
			if ( ! empty( $settingAlls['mail'] ) ) {
				$generalSetts = $settingAlls['mail'];

				if ( ! empty( $generalSetts['mailer'] ) ) {
					if ( 'outlook' == $generalSetts['mailer'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'outlookms' );
					} elseif ( 'mail' == $generalSetts['mailer'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'mail' );
					} elseif ( 'smtpcom' == $generalSetts['mailer'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'smtpcom' );
					} elseif ( 'sendinblue' == $generalSetts['mailer'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'sendinblue' );
					} elseif ( 'mailgun' == $generalSetts['mailer'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'mailgun' );
					} elseif ( 'sendgrid' == $generalSetts['mailer'] ) {
							  Utils::setYaySmtpSetting( 'currentMailer', 'sendgrid' );
					} elseif ( 'amazonses' == $generalSetts['mailer'] ) {
						  Utils::setYaySmtpSetting( 'currentMailer', 'amazonses' );
					} elseif ( 'gmail' == $generalSetts['mailer'] ) {
										  Utils::setYaySmtpSetting( 'currentMailer', 'gmail' );
					} elseif ( 'zoho' == $generalSetts['mailer'] ) {
									  Utils::setYaySmtpSetting( 'currentMailer', 'zoho' );
					} elseif ( 'smtp' == $generalSetts['mailer'] ) {
								  Utils::setYaySmtpSetting( 'currentMailer', 'smtp' );
					}
				}
				if ( ! empty( $generalSetts['from_email'] ) ) {
					Utils::setYaySmtpSetting( 'fromEmail', $generalSetts['from_email'] );
				}
				if ( ! empty( $generalSetts['from_name'] ) ) {
					Utils::setYaySmtpSetting( 'fromName', $generalSetts['from_name'] );
				}

				if ( isset( $generalSetts['from_email_force'] ) ) {
					$forceFromEmail = $generalSetts['from_email_force'];
					if ( $forceFromEmail ) {
						Utils::setYaySmtpSetting( 'forceFromEmail', '1' );
					} else {
						Utils::setYaySmtpSetting( 'forceFromEmail', '0' );
					}
				}
				if ( isset( $generalSetts['from_name_force'] ) ) {
					$forceFromName = $generalSetts['from_name_force'];
					if ( $forceFromName ) {
						Utils::setYaySmtpSetting( 'forceFromName', '1' );
					} else {
						Utils::setYaySmtpSetting( 'forceFromName', '0' );
					}
				}
			}

			// Other SMTP Settings
			if ( ! empty( $settingAlls['smtp'] ) ) {
				$smtpSetts = $settingAlls['smtp'];

				if ( ! empty( $smtpSetts['host'] ) ) {
					Utils::setYaySmtpSetting( 'host', $smtpSetts['host'], 'smtp' );
				}
				if ( ! empty( $smtpSetts['encryption'] ) ) {
					if ( 'ssl' == $smtpSetts['encryption'] ) {
						Utils::setYaySmtpSetting( 'encryption', 'ssl', 'smtp' );
					} elseif ( 'tls' == $smtpSetts['encryption'] ) {
						Utils::setYaySmtpSetting( 'encryption', 'tls', 'smtp' );
					} else {
						Utils::setYaySmtpSetting( 'encryption', '', 'smtp' );
					}
				}
				if ( ! empty( $smtpSetts['port'] ) ) {
					Utils::setYaySmtpSetting( 'port', $smtpSetts['port'], 'smtp' );
				}
				if ( isset( $smtpSetts['auth'] ) ) {
					if ( $smtpSetts['auth'] ) {
						Utils::setYaySmtpSetting( 'auth', 'yes', 'smtp' );
					} else {
						Utils::setYaySmtpSetting( 'auth', 'no', 'smtp' );
					}
				}
				if ( ! empty( $smtpSetts['user'] ) ) {
					Utils::setYaySmtpSetting( 'user', $smtpSetts['user'], 'smtp' );
				}
			}

			// Sendgrid Settings
			if ( ! empty( $settingAlls['sendgrid'] ) ) {
				$sendgridSetts = $settingAlls['sendgrid'];

				if ( ! empty( $sendgridSetts['api_key'] ) ) {
					Utils::setYaySmtpSetting( 'api_key', $sendgridSetts['api_key'], 'sendgrid' );
				}
			}

			// SMTPCom Settings
			if ( ! empty( $settingAlls['smtpcom'] ) ) {
				$smtpcomSetts = $settingAlls['smtpcom'];

				if ( ! empty( $smtpcomSetts['api_key'] ) ) {
					Utils::setYaySmtpSetting( 'api_key', $smtpcomSetts['api_key'], 'smtpcom' );
				}
				if ( ! empty( $smtpcomSetts['channel'] ) ) {
					Utils::setYaySmtpSetting( 'sender', $smtpcomSetts['channel'], 'smtpcom' );
				}
			}

			// Sendinblue Settings
			if ( ! empty( $settingAlls['sendinblue'] ) ) {
				$sendinblueSetts = $settingAlls['sendinblue'];

				if ( ! empty( $sendinblueSetts['api_key'] ) ) {
					Utils::setYaySmtpSetting( 'api_key', $sendinblueSetts['api_key'], 'sendinblue' );
				}
			}

			// Mailgun Settings
			if ( ! empty( $settingAlls['mailgun'] ) ) {
				$mailgunSetts = $settingAlls['mailgun'];

				if ( ! empty( $mailgunSetts['api_key'] ) ) {
					Utils::setYaySmtpSetting( 'api_key', $mailgunSetts['api_key'], 'mailgun' );
				}
				if ( ! empty( $mailgunSetts['domain'] ) ) {
					Utils::setYaySmtpSetting( 'domain', $mailgunSetts['domain'], 'mailgun' );
				}
				if ( ! empty( $mailgunSetts['region'] ) ) {
					Utils::setYaySmtpSetting( 'region', $mailgunSetts['region'], 'mailgun' );
				}
			}

			// Zoho Settings
			if ( ! empty( $settingAlls['zoho'] ) ) {
				$zohoSetts = $settingAlls['zoho'];

				if ( ! empty( $zohoSetts['client_id'] ) ) {
					Utils::setYaySmtpSetting( 'client_id', $zohoSetts['client_id'], 'zoho' );
				}
				if ( ! empty( $zohoSetts['client_secret'] ) ) {
					Utils::setYaySmtpSetting( 'client_secret', $zohoSetts['client_secret'], 'zoho' );
				}
			}

			// Amazon Settings
			if ( ! empty( $settingAlls['amazonses'] ) ) {
				$amazonsesSetts = $settingAlls['amazonses'];

				if ( ! empty( $amazonsesSetts['client_id'] ) ) {
					Utils::setYaySmtpSetting( 'access_key_id', $amazonsesSetts['client_id'], 'amazonses' );
				}
				if ( ! empty( $amazonsesSetts['client_secret'] ) ) {
					Utils::setYaySmtpSetting( 'secret_access_key', $amazonsesSetts['client_secret'], 'amazonses' );
				}
				if ( ! empty( $amazonsesSetts['region'] ) ) {
					Utils::setYaySmtpSetting( 'region', $amazonsesSetts['region'], 'amazonses' );
				}
			}

			// Gmail Settings
			if ( ! empty( $settingAlls['gmail'] ) ) {
				$gmailSetts = $settingAlls['gmail'];

				if ( ! empty( $gmailSetts['client_id'] ) ) {
					Utils::setYaySmtpSetting( 'client_id', $gmailSetts['client_id'], 'gmail' );
				}
				if ( ! empty( $gmailSetts['client_secret'] ) ) {
					Utils::setYaySmtpSetting( 'client_secret', $gmailSetts['client_secret'], 'gmail' );
				}
			}
			// Outlook Settings
			if ( ! empty( $settingAlls['outlook'] ) ) {
				$outlookSetts = $settingAlls['outlook'];

				if ( ! empty( $outlookSetts['client_id'] ) ) {
					Utils::setYaySmtpSetting( 'client_id', $outlookSetts['client_id'], 'outlookms' );
				}
				if ( ! empty( $outlookSetts['client_secret'] ) ) {
					Utils::setYaySmtpSetting( 'client_secret', $outlookSetts['client_secret'], 'outlookms' );
				}
			}
		}
	}

	// SMTP Mailer
	public function importSettingsOfSmtpMailer() {
		$settings = get_option( 'smtp_mailer_options', array() );
		if ( ! empty( $settings ) ) {
			Utils::setYaySmtpSetting( 'currentMailer', 'smtp' );

			if ( ! empty( $settings['from_email'] ) ) {
				Utils::setYaySmtpSetting( 'fromEmail', $settings['from_email'] );
			}

			if ( ! empty( $settings['from_name'] ) ) {
				Utils::setYaySmtpSetting( 'fromName', $settings['from_name'] );
			}

			if ( ! empty( $settings['smtp_host'] ) ) {
				Utils::setYaySmtpSetting( 'host', $settings['smtp_host'], 'smtp' );
			}

			if ( ! empty( $settings['smtp_port'] ) ) {
				Utils::setYaySmtpSetting( 'port', $settings['smtp_port'], 'smtp' );
			}

			if ( isset( $settings['smtp_auth'] ) ) {
				if ( 'true' == $settings['smtp_auth'] ) {
					Utils::setYaySmtpSetting( 'auth', 'yes', 'smtp' );
				} else {
					Utils::setYaySmtpSetting( 'auth', 'no', 'smtp' );
				}
			}

			if ( ! empty( $settings['type_of_encryption'] ) ) {
				if ( 'ssl' == $settings['type_of_encryption'] ) {
					Utils::setYaySmtpSetting( 'encryption', 'ssl', 'smtp' );
				} elseif ( 'tls' == $settings['type_of_encryption'] ) {
					Utils::setYaySmtpSetting( 'encryption', 'tls', 'smtp' );
				} else {
					Utils::setYaySmtpSetting( 'encryption', '', 'smtp' );
				}
			}

			if ( ! empty( $settings['smtp_username'] ) ) {
				Utils::setYaySmtpSetting( 'user', $settings['smtp_username'], 'smtp' );
			}

			if ( ! empty( $settings['smtp_password'] ) ) {
				$pass   = base64_decode( $settings['smtp_password'] );
				$passDb = Utils::encrypt( $pass, 'smtppass' );
				Utils::setYaySmtpSetting( 'pass', $passDb, 'smtp' );
			}
		}
	}

	// WP SMTP
	public function importSettingsOfWpSmtp() {
		$settings = get_option( 'wp_smtp_options', array() );
		if ( ! empty( $settings ) ) {
			Utils::setYaySmtpSetting( 'currentMailer', 'smtp' );

			if ( ! empty( $settings['from'] ) ) {
				Utils::setYaySmtpSetting( 'fromEmail', $settings['from'] );
			}

			if ( ! empty( $settings['fromname'] ) ) {
				Utils::setYaySmtpSetting( 'fromName', $settings['fromname'] );
			}

			if ( ! empty( $settings['host'] ) ) {
				Utils::setYaySmtpSetting( 'host', $settings['host'], 'smtp' );
			}

			if ( ! empty( $settings['port'] ) ) {
				Utils::setYaySmtpSetting( 'port', $settings['port'], 'smtp' );
			}

			if ( isset( $settings['smtpauth'] ) ) {
				if ( 'yes' == $settings['smtpauth'] ) {
					Utils::setYaySmtpSetting( 'auth', 'yes', 'smtp' );
				} else {
					Utils::setYaySmtpSetting( 'auth', 'no', 'smtp' );
				}
			}

			if ( ! empty( $settings['smtpsecure'] ) ) {
				if ( 'ssl' == $settings['smtpsecure'] ) {
					Utils::setYaySmtpSetting( 'encryption', 'ssl', 'smtp' );
				} elseif ( 'tls' == $settings['smtpsecure'] ) {
					Utils::setYaySmtpSetting( 'encryption', 'tls', 'smtp' );
				} else {
					Utils::setYaySmtpSetting( 'encryption', '', 'smtp' );
				}
			}

			if ( ! empty( $settings['username'] ) ) {
				Utils::setYaySmtpSetting( 'user', $settings['username'], 'smtp' );
			}

			if ( ! empty( $settings['password'] ) ) {
				$pass   = $settings['password'];
				$passDb = Utils::encrypt( $pass, 'smtppass' );
				Utils::setYaySmtpSetting( 'pass', $passDb, 'smtp' );
			}
		}
	}

	// WP Mail Bank
	public function importSettingsOfMailBank() {
		$settings = Utils::getMailBankSettingsTable();
		if ( ! empty( $settings ) ) {

			if ( ! empty( $settings['mailer_type'] ) ) {
				if ( 'php_mail_function' == $settings['mailer_type'] ) {
					Utils::setYaySmtpSetting( 'currentMailer', 'mail' );
				} elseif ( 'smtp' == $settings['mailer_type'] ) {
					Utils::setYaySmtpSetting( 'currentMailer', 'smtp' );
				}
			}

			if ( isset( $settings['auth_type'] ) ) {
				if ( 'none' == $settings['auth_type'] ) {
					Utils::setYaySmtpSetting( 'auth', 'no', 'smtp' );
				} elseif ( 'login' == $settings['auth_type'] || 'plain' == $settings['auth_type'] ) {
					Utils::setYaySmtpSetting( 'auth', 'yes', 'smtp' );
				} elseif ( 'oauth2' == $settings['auth_type'] && ! empty( $settings['hostname'] ) ) {
					if ( 'smtp.live.com' == $settings['hostname'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'outlookms' );
					} elseif ( 'smtp.gmail.com' == $settings['hostname'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'gmail' );
					}
				}
			}

			if ( ! empty( $settings['sender_email'] ) ) {
				Utils::setYaySmtpSetting( 'fromEmail', $settings['sender_email'] );
			}

			if ( ! empty( $settings['from_email_configuration'] ) ) {
				if ( 'override' == $settings['from_email_configuration'] ) {
					Utils::setYaySmtpSetting( 'forceFromEmail', '1' );
				} else {
					Utils::setYaySmtpSetting( 'forceFromEmail', '0' );
				}
			}

			if ( ! empty( $settings['sender_name'] ) ) {
				Utils::setYaySmtpSetting( 'fromName', $settings['sender_name'] );
			}

			if ( ! empty( $settings['sender_name_configuration'] ) ) {
				if ( 'override' == $settings['sender_name_configuration'] ) {
					Utils::setYaySmtpSetting( 'forceFromName', '1' );
				} else {
					Utils::setYaySmtpSetting( 'forceFromName', '0' );
				}
			}

			if ( ! empty( $settings['hostname'] ) ) {
				Utils::setYaySmtpSetting( 'host', $settings['hostname'], 'smtp' );
			}

			if ( ! empty( $settings['port'] ) ) {
				$port = (string) $settings['port'];
				Utils::setYaySmtpSetting( 'port', $port, 'smtp' );
			}

			if ( ! empty( $settings['enc_type'] ) ) {
				if ( 'ssl' == $settings['enc_type'] ) {
					Utils::setYaySmtpSetting( 'encryption', 'ssl', 'smtp' );
				} elseif( 'tls' == $settings['enc_type'] ) {
					Utils::setYaySmtpSetting( 'encryption', 'tls', 'smtp' );
				} else {
					Utils::setYaySmtpSetting( 'encryption', '', 'smtp' );
				}
			}

			if ( ! empty( $settings['username'] ) ) {
				Utils::setYaySmtpSetting( 'user', $settings['username'], 'smtp' );
			}

			if ( ! empty( $settings['password'] ) ) {
				$pass   = base64_decode( $settings['password'] );
				$passDb = Utils::encrypt( $pass, 'smtppass' );
				Utils::setYaySmtpSetting( 'pass', $passDb, 'smtp' );
			}

			if ( ! empty( $settings['client_id'] ) && ! empty( $settings['hostname'] ) ) {
				if ( 'smtp.live.com' == $settings['hostname'] ) {
					Utils::setYaySmtpSetting( 'client_id', $settings['client_id'], 'outlookms' );
				} elseif ( 'smtp.gmail.com' == $settings['hostname'] ) {
					Utils::setYaySmtpSetting( 'client_id', $settings['client_id'], 'gmail' );
				}
			}

			if ( ! empty( $settings['client_secret'] ) && ! empty( $settings['hostname'] ) ) {
				if ( 'smtp.live.com' == $settings['hostname'] ) {
					Utils::setYaySmtpSetting( 'client_secret', $settings['client_secret'], 'outlookms' );
				} elseif ( 'smtp.gmail.com' == $settings['hostname'] ) {
					Utils::setYaySmtpSetting( 'client_secret', $settings['client_secret'], 'gmail' );
				}
			}
		}
	}

	// Post SMTP
	public function importSettingsOfPostSMTP() {
		$settings = get_option( 'postman_options', array() );
		if ( ! empty( $settings ) ) {
			if ( ! empty( $settings['transport_type'] ) ) {
				if ( 'default' == $settings['transport_type'] ) {
					Utils::setYaySmtpSetting( 'currentMailer', 'mail' );
				} elseif ( 'smtp' == $settings['transport_type'] ) {
					Utils::setYaySmtpSetting( 'currentMailer', 'smtp' );
				} elseif ( 'gmail_api' == $settings['transport_type'] ) {
					Utils::setYaySmtpSetting( 'currentMailer', 'gmail' );
				} elseif ( 'sendgrid_api' == $settings['transport_type'] ) {
					Utils::setYaySmtpSetting( 'currentMailer', 'sendgrid' );
				} elseif ( 'mailgun_api' == $settings['transport_type'] ) {
					Utils::setYaySmtpSetting( 'currentMailer', 'mailgun' );
				}
			}

			if ( ! empty( $settings['enc_type'] ) ) {
				if ( 'ssl' == $settings['enc_type'] ) {
					Utils::setYaySmtpSetting( 'encryption', 'ssl', 'smtp' );
				} elseif ( 'tls' == $settings['enc_type'] ) {
					Utils::setYaySmtpSetting( 'encryption', 'tls', 'smtp' );
				} else {
					Utils::setYaySmtpSetting( 'encryption', '', 'smtp' );
				}
			}

			if ( ! empty( $settings['hostname'] ) ) {
				Utils::setYaySmtpSetting( 'host', $settings['hostname'], 'smtp' );
			}

			if ( ! empty( $settings['port'] ) ) {
				$port = (string) $settings['port'];
				Utils::setYaySmtpSetting( 'port', $port, 'smtp' );
			}

			if ( ! empty( $settings['sender_email'] ) ) {
				Utils::setYaySmtpSetting( 'fromEmail', $settings['sender_email'] );
			}

			if ( ! empty( $settings['sender_name'] ) ) {
				Utils::setYaySmtpSetting( 'fromName', $settings['sender_name'] );
			}

			if ( ! empty( $settings['oauth_client_id'] ) && ! empty( $settings['hostname'] ) ) {
				if ( 'smtp.live.com' == $settings['hostname'] ) {
					Utils::setYaySmtpSetting( 'client_id', $settings['oauth_client_id'], 'outlookms' );
				} elseif ( 'smtp.gmail.com' == $settings['hostname'] ) {
					Utils::setYaySmtpSetting( 'client_id', $settings['oauth_client_id'], 'gmail' );
				}
			}

			if ( ! empty( $settings['oauth_client_secret'] ) && ! empty( $settings['hostname'] ) ) {
				if ( 'smtp.live.com' == $settings['hostname'] ) {
					Utils::setYaySmtpSetting( 'client_secret', $settings['oauth_client_secret'], 'outlookms' );
				} elseif ( 'smtp.gmail.com' == $settings['hostname'] ) {
					Utils::setYaySmtpSetting( 'client_secret', $settings['oauth_client_secret'], 'gmail' );
				}
			}

			if ( ! empty( $settings['basic_auth_username'] ) ) {
				Utils::setYaySmtpSetting( 'user', $settings['basic_auth_username'], 'smtp' );
			}

			if ( ! empty( $settings['basic_auth_password'] ) ) {
				$pass   = base64_decode( $settings['basic_auth_password'] );
				$passDb = Utils::encrypt( $pass, 'smtppass' );
				Utils::setYaySmtpSetting( 'pass', $passDb, 'smtp' );
			}

			if ( ! empty( $settings['prevent_sender_email_override'] ) && 'on' == $settings['prevent_sender_email_override'] ) {
				Utils::setYaySmtpSetting( 'forceFromEmail', '1' );
			} else {
				Utils::setYaySmtpSetting( 'forceFromEmail', '0' );
			}

			if ( ! empty( $settings['prevent_sender_name_override'] ) && 'on' == $settings['prevent_sender_name_override'] ) {
				Utils::setYaySmtpSetting( 'forceFromName', '1' );
			} else {
				Utils::setYaySmtpSetting( 'forceFromName', '0' );
			}

			if ( isset( $settings['auth_type'] ) ) {
				if ( 'none' == $settings['auth_type'] ) {
					Utils::setYaySmtpSetting( 'auth', 'no', 'smtp' );
				} elseif ( 'login' == $settings['auth_type'] || 'plain' == $settings['auth_type'] ) {
					Utils::setYaySmtpSetting( 'auth', 'yes', 'smtp' );
				} elseif ( 'oauth2' == $settings['auth_type'] && ! empty( $settings['hostname'] ) ) {
					if ( 'smtp.live.com' == $settings['hostname'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'outlookms' );
					} elseif ( 'smtp.gmail.com' == $settings['hostname'] ) {
						Utils::setYaySmtpSetting( 'currentMailer', 'gmail' );
					}
				}
			}

			// Sendgrid Settings
			if ( ! empty( $settings['sendgrid_api_key'] ) ) {
				$apiKey = base64_decode( $settings['sendgrid_api_key'] );
				Utils::setYaySmtpSetting( 'api_key', $apiKey, 'sendgrid' );
			}

			// Mailgun Settings
			if ( ! empty( $settings['mailgun_api_key'] ) ) {
				$apiKey = base64_decode( $settings['mailgun_api_key'] );
				Utils::setYaySmtpSetting( 'api_key', $apiKey, 'mailgun' );
			}

			if ( ! empty( $settings['mailgun_domain_name'] ) ) {
				Utils::setYaySmtpSetting( 'domain', $settings['mailgun_domain_name'], 'mailgun' );
			}

			if ( ! empty( $settings['mailgun_region'] ) && 'on' == $settings['mailgun_region'] ) {
				Utils::setYaySmtpSetting( 'region', 'EU', 'mailgun' );
			} else {
				Utils::setYaySmtpSetting( 'region', 'US', 'mailgun' );
			}
		}
	}
}
