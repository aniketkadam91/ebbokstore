<?php
namespace YaySMTP;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

defined( 'ABSPATH' ) || exit;

class Functions {
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}

	private function doHooks() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'wp_ajax_yaysmtp_save_settings', array( $this, 'saveSettings' ) );
		add_action( 'wp_ajax_yaysmtp_save_addition_settings', array( $this, 'saveAdditionSettings' ) );
		add_action( 'wp_ajax_yaysmtp_send_mail', array( $this, 'sendTestMail' ) );
		add_action( 'wp_ajax_yaysmtp_fallback_send_mail', array( $this, 'sendTestMailFallback' ) );
		add_action( 'wp_ajax_yaysmtp_gmail_remove_auth', array( $this, 'gmailRemoveAuth' ) );
		add_action( 'wp_ajax_yaysmtp_gmail_remove_auth_fallback', array( $this, 'gmailRemoveAuthFallback' ) );
		add_action( 'wp_ajax_yaysmtp_yoho_remove_auth', array( $this, 'yohoRemoveAuth' ) );
		add_action( 'wp_ajax_yaysmtp_outlookms_remove_auth', array( $this, 'outlookmsRemoveAuth' ) );
		add_action( 'wp_ajax_yaysmtp_email_logs', array( $this, 'getListEmailLogs' ) );
		add_action( 'wp_ajax_yaysmtp_set_email_logs_setting', array( $this, 'setYaySmtpEmailLogSetting' ) );
		add_action( 'wp_ajax_yaysmtp_delete_email_logs', array( $this, 'deleteEmailLogs' ) );
		add_action( 'wp_ajax_yaysmtp_delete_all_email_logs', array( $this, 'deleteAllEmailLogs' ) );
		add_action( 'wp_ajax_yaysmtp_detail_email_logs', array( $this, 'getEmailLog' ) );
		add_action( 'wp_ajax_yaysmtp_overview_chart', array( $this, 'getEmailChart' ) );
	}

	private function __construct() {}

	public function saveSettings() {
		try {
			Utils::checkNonce();
			if ( isset( $_POST['settings'] ) ) {
				$settings          = Utils::saniValArray( $_POST['settings'] ); //phpcs:ignore
				$yaysmtpSettingsDB = Utils::getYaySmtpSetting();

				$yaysmtpSettings = array();
				if ( ! empty( $yaysmtpSettingsDB ) && is_array( $yaysmtpSettingsDB ) ) {
					$yaysmtpSettings = $yaysmtpSettingsDB;

					// Update "succ_sent_mail_last" option to SHOW/HIDE Debug Box on main page.
					if ( isset( $yaysmtpSettings['currentMailer'] ) ) {
						$currentMailerDB = $yaysmtpSettings['currentMailer'];
						if ( ! empty( $currentMailerDB ) && $currentMailerDB != $settings['mailerProvider'] ) {
							$yaysmtpSettings['succ_sent_mail_last'] = true;
						}
					}
				}

				$yaysmtpSettings['fromEmail']      = $settings['fromEmail'];
				$yaysmtpSettings['fromName']       = $settings['fromName'];
				$yaysmtpSettings['forceFromEmail'] = $settings['forceFromEmail'];
				$yaysmtpSettings['forceFromName']  = $settings['forceFromName'];

				$yaysmtpSettings['currentMailer'] = $settings['mailerProvider'];
				if ( ! empty( $settings['mailerProvider'] ) ) {
					$mailerSettings = ! empty( $settings['mailerSettings'] ) ? $settings['mailerSettings'] : array();

					if ( ! empty( $mailerSettings ) ) {
						foreach ( $mailerSettings as $key => $val ) {
							if ( 'pass' === $key ) {
								$yaysmtpSettings[ $settings['mailerProvider'] ][ $key ] = Utils::encrypt( $val, 'smtppass' );
							} else {
								$yaysmtpSettings[ $settings['mailerProvider'] ][ $key ] = $val;
							}
						}
					}
				}

				$isNetworkAdmin = (int) $settings['isNetworkAdmin'];
				unset( $settings['isNetworkAdmin'] );
				// Handle for multisite or Not
				if ( is_multisite() && ( 1 == $isNetworkAdmin ) ) {
					$allowMultisite                    = $settings['allowMultisite'];
					$yaysmtpSettings['allowMultisite'] = $allowMultisite;
					$siteList                          = get_sites();
					if ( 'yes' === $allowMultisite ) {
						foreach ( (array) $siteList as $site ) {
							switch_to_blog( $site->blog_id );

							// Backup old settings
							$yaysmtpSettingsOld = Utils::getYaySmtpSetting( true );
							update_option( 'yaysmtp_settings_bk', $yaysmtpSettingsOld );

							// Update new settings
							$mainSiteId = get_main_site_id();
							$blogId     = (int) $site->blog_id;
							if ( $mainSiteId !== $blogId ) {
								$yaysmtpSettings['allowMultisite'] = 'no';
							}
							  update_option( 'yaysmtp_settings', $yaysmtpSettings );

							  restore_current_blog();
						}
					} else {
						foreach ( (array) $siteList as $site ) {
							switch_to_blog( $site->blog_id );

							// General settings.
							if ( get_option( 'yaysmtp_settings_bk' ) ) {
								$settingsBk                   = get_option( 'yaysmtp_settings_bk' );
								$settingsBk['allowMultisite'] = 'no';
							} else {
								$settingsBk['allowMultisite'] = 'no';
							}
							update_option( 'yaysmtp_settings', $settingsBk );

							// Email log settings.
							if ( get_option( 'yaysmtp_email_log_settings_bk' ) ) {
								  $emalSettingsBk = get_option( 'yaysmtp_email_log_settings_bk' );
							} else {
								$emalSettingsBk = array();
							}
							update_option( 'yaysmtp_email_log_settings', $emalSettingsBk );

							restore_current_blog();
						}
					}
				} else {
						  update_option( 'yaysmtp_settings', $yaysmtpSettings );
				}

				wp_send_json_success( array( 'mess' => __( 'Settings saved!', 'yay-smtp' ) ) );
			}
			wp_send_json_error( array( 'mess' => __( 'Failed to save settings.', 'yay-smtp' ) ) );
		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}

	public function saveAdditionSettings() {
		try {
			Utils::checkNonce();
			if ( isset( $_POST['params'] ) ) {
				$params = Utils::saniValArray( $_POST['params'] ); // phpcs:ignore
				$mailerProvider = !empty($params['fallback_mailer_provider']) ? $params['fallback_mailer_provider'] : 'smtp';
				$isNetworkAdmin = (int) $params['isNetworkAdmin'];
				unset( $params['isNetworkAdmin'] );
				// Handle for multisite or Not
				if ( is_multisite() && ( 1 == $isNetworkAdmin ) ) {
					$allowMultisite = Utils::getMultisiteSetting();
					$siteList       = get_sites();
					if ( 'yes' === $allowMultisite ) {
						foreach ( (array) $siteList as $site ) {
							switch_to_blog( $site->blog_id );

							// Backup old settings
							$yaysmtpSettingsOld = Utils::getYaySmtpSetting( true );
							update_option( 'yaysmtp_settings_bk', $yaysmtpSettingsOld );

							// Update new settings
							foreach ( $params as $key => $val ) {
								if ( 'fallback_smtp_pass' == $key ) {
									$valPass = Utils::encrypt( $val, 'smtppass' );
									Utils::setYaySmtpSettingFallback( $key, $valPass );
								} else {
									if ( 'fallback_service_provider_mailer_settings' == $key ) {
										$mailerSettings = $val;
										if ( ! empty( $mailerSettings ) ) {
											$mailerSetDBs = [];
											foreach ( $mailerSettings as $key1 => $val1 ) {
												$mailerSetDBs[ $mailerProvider ][ $key1 ] = $val1;
											}
											if ( !empty($mailerSetDBs) ) {
												Utils::setYaySmtpSettingFallback( $key, $mailerSetDBs );
											}
										}
									} else {
										Utils::setYaySmtpSettingFallback( $key, $val );
									}
								}
							}

							restore_current_blog();
						}
					}
				} else {
					foreach ( $params as $key => $val ) {
						if ( 'fallback_smtp_pass' == $key ) {
							$valPass = Utils::encrypt( $val, 'smtppass' );
							Utils::setYaySmtpSettingFallback( $key, $valPass );
						} else {
							if ( 'fallback_service_provider_mailer_settings' == $key ) {
								$mailerSettings = $val;
								if ( ! empty( $mailerSettings ) ) {
									$mailerSetDBs = [];
									foreach ( $mailerSettings as $key1 => $val1 ) {
										$mailerSetDBs[ $mailerProvider ][ $key1 ] = $val1;
									}
									if ( !empty($mailerSetDBs) ) {
										Utils::setYaySmtpSettingFallback( $key, $mailerSetDBs );
									}
								}
							} else {
								Utils::setYaySmtpSettingFallback( $key, $val );
							}
						}
					}
				}

				wp_send_json_success(
					array(
						'mess' => __( 'Settings saved!', 'yay-smtp' ),
					)
				);
			}
			wp_send_json_error( array( 'mess' => __( 'Failed to save settings.', 'yay-smtp' ) ) );
		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}

	public function sendTestMail() {
		try {
			Utils::checkNonce();
			if ( isset( $_POST['emailAddress'] ) ) {
				$emailAddress = sanitize_email( $_POST['emailAddress'] );
				// check email
				if ( ! is_email( $emailAddress ) ) {
					wp_send_json_error( array( 'mess' => __( 'Invalid email format.', 'yay-smtp' ) ) );
				}

				$headers      = "Content-Type: text/html\r\n";
				$subjectEmail = __( 'YaySMTP - Test email sent successfully!', 'yay-smtp' );
				$html         = Utils::getTemplateHtml(
					'test-mail',
					YAY_SMTP_PLUGIN_PATH . 'includes/Views/template-mail'
				);

				if ( ! empty( $emailAddress ) ) {
					$sendMailSucc = wp_mail( $emailAddress, $subjectEmail, $html, $headers );
					if ( $sendMailSucc ) {
						Utils::setYaySmtpSetting( 'succ_sent_mail_last', true );
						wp_send_json_success( array( 'mess' => __( 'Email has been sent.', 'yay-smtp' ) ) );
					} else {
						Utils::setYaySmtpSetting( 'succ_sent_mail_last', false );
						if ( Utils::getCurrentMailer() == 'smtp' ) {
							LogErrors::clearErr();
							LogErrors::setErr( 'This error may be caused by: Incorrect From email, SMTP Host, Post, Username or Password.' );
							$debugText = implode( '<br>', LogErrors::getErr() );
						} else {
							$debugText = implode( '<br>', LogErrors::getErr() );
						}
						wp_send_json_error(
							array(
								'debugText' => $debugText,
								'mess'      => __(
									'Email sent failed.',
									'yay-smtp'
								),
							)
						);
					}
				}
			} else {
				wp_send_json_error( array( 'mess' => __( 'Email Address is not empty.', 'yay-smtp' ) ) );
			}
			wp_send_json_error( array( 'mess' => __( 'Error send mail.', 'yay-smtp' ) ) );
		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}

	public function sendTestMailFallback() {
		Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
		try {
			Utils::checkNonce();
			if ( isset( $_POST['emailAddress'] ) ) {
				$emailAddress = sanitize_email( $_POST['emailAddress'] );
				// check email
				if ( ! is_email( $emailAddress ) ) {
					Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
					wp_send_json_error( array( 'mess' => __( 'Invalid email format.', 'yay-smtp' ) ) );
				}

				$headers      = "Content-Type: text/html\r\n";
				$subjectEmail = __( 'YaySMTP - Fallback test email sent successfully!', 'yay-smtp' );
				$html         = Utils::getTemplateHtml(
					'test-mail',
					YAY_SMTP_PLUGIN_PATH . 'includes/Views/template-mail'
				);

				if ( ! empty( $emailAddress ) ) {
					Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'yes' );
					$sendMailSucc = wp_mail( $emailAddress, $subjectEmail, $html, $headers );
					if ( $sendMailSucc ) {
						Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
						wp_send_json_success( array( 'mess' => __( 'Email has been sent.', 'yay-smtp' ) ) );
					} else {
						Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
						if ( Utils::getCurrentMailerFallback() !== 'smtp' ) {
							$message = implode( '<br>', LogErrors::getErrFallback() );
						} else {
							$message =  __( 'Email sent failed. This error may be caused by: Incorrect From email, SMTP Host, Post, Username or Password.', 'yay-smtp' );
						}
						wp_send_json_error(
							array('mess' => $message)
						);
					}
				}
				Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
			} else {
				Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
				wp_send_json_error( array( 'mess' => __( 'Email Address is not empty.', 'yay-smtp' ) ) );
			}
			Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
			wp_send_json_error( array( 'mess' => __( 'Error send mail.', 'yay-smtp' ) ) );
		} catch ( \Exception $ex ) {
			Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
		} catch ( \Error $ex ) {
			Utils::setYaySmtpSetting( 'flag_test_mail_fallback', 'no' );
		}
	}

	public function gmailRemoveAuth() {
		Utils::checkNonce();
		$setting = Utils::getYaySmtpSetting();

		if ( ! empty( $setting ) && ! empty( $setting['gmail'] ) ) {
			$oldGmailSetting = $setting['gmail'];
			foreach ( $oldGmailSetting as $key => $val ) {
				if ( ! in_array( $key, array( 'client_id', 'client_secret' ), true ) ) {
					unset( $oldGmailSetting[ $key ] );
				}
			}

			Utils::setYaySmtpSetting( 'gmail', $oldGmailSetting );
		}

	}

	public function gmailRemoveAuthFallback() {
		Utils::checkNonce();
		$setting = Utils::getYaySmtpSetting();

		if ( ! empty( $setting ) && 
			! empty( $setting['fallback_service_provider_mailer_settings'] ) && 
			! empty( $setting['fallback_service_provider_mailer_settings']['gmail'] ) 
		) {
			$oldGmailSetting = $setting['fallback_service_provider_mailer_settings']['gmail'];
			
			foreach ( $oldGmailSetting as $key => $val ) {
				if ( ! in_array( $key, array( 'client_id', 'client_secret' ), true ) ) {
					unset( $setting['fallback_service_provider_mailer_settings']['gmail'][ $key ] );
				}
			}

			update_option( 'yaysmtp_settings', $setting );
		}
	}

	public function outlookmsRemoveAuth() {
		Utils::checkNonce();
		$setting = Utils::getYaySmtpSetting();

		if ( ! empty( $setting ) && ! empty( $setting['outlookms'] ) ) {
			$oldSetting = $setting['outlookms'];
			foreach ( $oldSetting as $key => $val ) {
				if ( ! in_array( $key, array( 'client_id', 'client_secret' ), true ) ) {
					unset( $oldSetting[ $key ] );
				}
			}

			Utils::setYaySmtpSetting( 'outlookms', $oldSetting );
		}

	}

	public function yohoRemoveAuth() {
		Utils::checkNonce();
		$setting = Utils::getYaySmtpSetting();

		if ( ! empty( $setting ) && ! empty( $setting['zoho'] ) ) {
			$oldSetting = $setting['zoho'];

			foreach ( $oldSetting as $key => $val ) {
				// Unset everything except Client ID and Client Secret.
				if ( ! in_array( $key, array( 'client_id', 'client_secret' ), true ) ) {
					unset( $oldSetting[ $key ] );
				}
			}

			Utils::setYaySmtpSetting( 'zoho', $oldSetting );

		}
	}

	public function getListEmailLogs() {
		try {
			Utils::checkNonce();
			if ( isset( $_POST['params'] ) ) {
				$params = Utils::saniValArray( $_POST['params'] ); // phpcs:ignore
				global $wpdb;

				$yaySmtpEmailLogSetting = Utils::getYaySmtpEmailLogSetting();
				$showSubjectColumn      = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_subject_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_subject_cl'] : 1;
				$showToColumn           = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_to_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_to_cl'] : 1;
				$showStatusColumn       = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_status_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_status_cl'] : 1;
				$showDatetimeColumn     = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_datetime_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_datetime_cl'] : 1;
				$showActionColumn       = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_action_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_action_cl'] : 1;
				$showStatus             = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['status'] ) ? $yaySmtpEmailLogSetting['status'] : 'all';

				$showColSettings = array(
					'showSubjectCol'  => $showSubjectColumn,
					'showToCol'       => $showToColumn,
					'showStatusCol'   => $showStatusColumn,
					'showDatetimeCol' => $showDatetimeColumn,
					'showActionCol'   => $showActionColumn,
				);

				$limit  = ! empty( $params['limit'] ) && is_numeric( $params['limit'] ) ? (int) $params['limit'] : 10;
				$page   = ! empty( $params['page'] ) && is_numeric( $params['page'] ) ? (int) $params['page'] : 1;
				$offset = ( $page - 1 ) * $limit;

				$valSearch = ! empty( $params['valSearch'] ) ? $params['valSearch'] : '';
				$sortField = ! empty( $params['sortField'] ) ? $params['sortField'] : 'date_time';
				$sortVal   = 'DESC';
				if ( ! empty( $params['sortVal'] ) && 'ascending' === $params['sortVal'] ) {
					$sortVal = 'ASC';
				}

				$status = ! empty( $params['status'] ) ? $params['status'] : $showStatus;
				if ( 'sent' === $status ) {
					$statusWhere = 'status = 1';
				} elseif ( 'not_send' === $status ) {
					$statusWhere = 'status = 0 OR status =2';
				} elseif ( 'empty' === $status ) {
					$statusWhere = 'status <> 1 AND status <> 0 and status <> 2';
				} else {
					$statusWhere = 'status = 1 OR status = 0 OR status = 2';
				}

				// Seach base on From date and To date
				$dateWhere = 'TRUE';
				if ( ! empty( $params['from'] ) && ! empty( $params['to'] ) ) {
					$startDateObj  = new \DateTime( $params['from'] );
					$endDateOrgObj = new \DateTime( $params['to'] );
					$startDate     = $startDateObj->format( 'Y-m-d' );
					$endDate       = $endDateOrgObj->format( 'Y-m-d' );

					$dateWhere = "DATE(date_time) >= '$startDate' AND DATE(date_time) <= '$endDate'";
				}

				// Result ALL
				if ( ! empty( $valSearch ) ) {
					$subjectWhere = 'subject LIKE "%%' . $valSearch . '%%"';
					$toEmailWhere = 'email_to LIKE "%%' . $valSearch . '%%"';
					$whereQuery   = "{$subjectWhere} OR {$toEmailWhere}";
					$whereQuery   = '(' . $whereQuery . ') AND (' . $statusWhere . ')';

					if ( ! empty( $dateWhere ) ) {
						$whereQuery = '(' . $whereQuery . ') AND (' . $dateWhere . ')';
					}

					$sqlRepareAll = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}yaysmtp_email_logs WHERE $whereQuery" );
					$sqlRepare    = $wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}yaysmtp_email_logs WHERE $whereQuery ORDER BY $sortField $sortVal LIMIT %d OFFSET %d",
						$limit,
						$offset
					);
				} else {
					$whereQuery = "{$statusWhere}";
					if ( ! empty( $dateWhere ) ) {
						$whereQuery = '(' . $statusWhere . ') AND (' . $dateWhere . ')';
					}

					$sqlRepareAll = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}yaysmtp_email_logs WHERE $whereQuery" );
					$sqlRepare    = $wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}yaysmtp_email_logs WHERE $whereQuery ORDER BY $sortField $sortVal LIMIT %d OFFSET %d",
						$limit,
						$offset
					);
				}

				$resultQueryAll = $wpdb->get_results( $sqlRepareAll ); // phpcs:ignore
				$totalItems     = ! empty( $resultQueryAll ) ? count( $resultQueryAll ) : 0;

				// Result Custom
				$results = $wpdb->get_results( $sqlRepare ); // phpcs:ignore

				$emailLogsList = array();
				$dateTimeFormat = get_option( 'date_format' ) . " \\a\\t " . get_option( 'time_format' );
				foreach ( $results as $result ) {
					$emailTo = maybe_unserialize( $result->email_to );

					// $subject = $result->subject;
					// $subject_out =  Utils::stringThreeDot($subject, 50);

					$emailEl         = array(
						'id'          		  => $result->id,
						'subject'     		  => wp_kses_post( $result->subject ),
						'email_from'  		  => $result->email_from,
						'email_to'    		  => $emailTo,
						'mailer'      		  => $result->mailer,
						'date_time'   		  => get_date_from_gmt( $result->date_time, "$dateTimeFormat" ),
						'status'      		  => $result->status,
						'mail_source' 		  => ! empty( $result->root_name ) ? $result->root_name : __( 'Unknown', 'yay-smtp' ),
						'email_opened' 		  => __( 'No', 'yay-smtp' ),
						'email_clicked_links' => __( 'No', 'yay-smtp' ),
					);

					$email_opened = Utils::getTrackingEmailOpenedByLogId( intval( $result->id ));
					if( ! empty( $email_opened )) {
						$email_opened_count = intval( $email_opened->count );
						if ( $email_opened_count > 0 ) {
							$emailEl['email_opened'] = __( 'Yes', 'yay-smtp' );
						}
					}

					$email_clicked_links = Utils::getTrackingEmailClickedLinkByLogId( intval( $result->id ));
					if( ! empty( $email_clicked_links )) {
						foreach( $email_clicked_links as $email_clicked_link) {
							$email_clicked_links_count = intval( $email_clicked_link->count );
							if ( $email_clicked_links_count > 0 ) {
								$emailEl['email_clicked_links'] = __( 'Yes', 'yay-smtp' );
								break;
							}
						}
					}

					$emailLogsList[] = $emailEl;
				}

				wp_send_json_success(
					array(
						'data'            => $emailLogsList,
						'totalItem'       => $totalItems,
						'totalPage'       => $limit < 0 ? 1 : ceil( $totalItems / $limit ),
						'currentPage'     => $page,
						'limit'           => $limit,
						'showColSettings' => $showColSettings,
						'mess'            => __( 'Successful.', 'yay-smtp' ),
					)
				);
			}
			wp_send_json_error( array( 'mess' => __( 'Failed.', 'yay-smtp' ) ) );
		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}

	public function setYaySmtpEmailLogSetting() {
		try {
			Utils::checkNonce();
			if ( isset( $_POST['params'] ) ) {
				$params = Utils::saniValArray( $_POST['params'] );// phpcs:ignore

				// Handle for multisite or Not
				if ( is_multisite() && isset( $params['isNetworkAdmin'] ) && ( 1 == (int) $params['isNetworkAdmin'] ) ) {
					unset( $params['isNetworkAdmin'] );
					$allowMultisite = Utils::getMultisiteSetting();
					$siteList       = get_sites();
					if ( 'yes' === $allowMultisite ) {
						foreach ( (array) $siteList as $site ) {
							switch_to_blog( $site->blog_id );

							// Backup old settings
							$yaySmtpEmailLogSettingOld = Utils::getYaySmtpEmailLogSetting();
							update_option( 'yaysmtp_email_log_settings_bk', $yaySmtpEmailLogSettingOld );

							// Update new settings
							foreach ( $params as $key => $val ) {
								  // Add wp schedule event if delete time change - start.
								if ( 'email_log_delete_time' === $key ) {
									$dayTimes              = (int) $val;
									$deleteDatetimeSetting = Utils::getDeleteDatetimeSetting();
									if ( 0 === $dayTimes ) {
											  wp_clear_scheduled_hook( 'yaysmtp_delete_email_log_schedule_hook' );
									} elseif ( 0 !== $dayTimes && $dayTimes !== $deleteDatetimeSetting ) {
										wp_clear_scheduled_hook( 'yaysmtp_delete_email_log_schedule_hook' );

										add_action( 'yaysmtp_delete_email_log_schedule_hook', array( $this, 'deleteEmailLogSchedule' ) );
										if ( ! wp_next_scheduled( 'yaysmtp_delete_email_log_schedule_hook' ) ) {
											  wp_schedule_event( time(), 'yaysmtp_specific_delete_time', 'yaysmtp_delete_email_log_schedule_hook' );
										}
									}
								}
								  // Add wp schedule event if delete time change - end.

								  Utils::setYaySmtpEmailLogSetting( $key, $val );
							}

							restore_current_blog();
						}
					}
				} else {
					unset( $params['isNetworkAdmin'] );
					foreach ( $params as $key => $val ) {
						// Add wp schedule event if delete time change - start.
						if ( 'email_log_delete_time' === $key ) {
								$dayTimes              = (int) $val;
								$deleteDatetimeSetting = Utils::getDeleteDatetimeSetting();
							if ( 0 === $dayTimes ) {
								wp_clear_scheduled_hook( 'yaysmtp_delete_email_log_schedule_hook' );
							} elseif ( 0 !== $dayTimes && $dayTimes !== $deleteDatetimeSetting ) {
								wp_clear_scheduled_hook( 'yaysmtp_delete_email_log_schedule_hook' );

								add_action( 'yaysmtp_delete_email_log_schedule_hook', array( $this, 'deleteEmailLogSchedule' ) );
								if ( ! wp_next_scheduled( 'yaysmtp_delete_email_log_schedule_hook' ) ) {
												wp_schedule_event( time(), 'yaysmtp_specific_delete_time', 'yaysmtp_delete_email_log_schedule_hook' );
								}
							}
						}
						// Add wp schedule event if delete time change - end.

						Utils::setYaySmtpEmailLogSetting( $key, $val );
					}
				}

				wp_send_json_success(
					array(
						'mess' => __( 'Settings saved!', 'yay-smtp' ),
					)
				);
			}
			wp_send_json_error( array( 'mess' => __( 'Failed to save settings.', 'yay-smtp' ) ) );
		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}

	public function deleteEmailLogSchedule() {
		Utils::deleteAllEmailLogs();
	}

	public function deleteEmailLogs() {
		try {
			Utils::checkNonce();
			if ( isset( $_POST['params'] ) ) {
				global $wpdb;
				$params = Utils::saniValArray( $_POST['params'] );// phpcs:ignore
				$ids    = isset( $params['ids'] ) ? $params['ids'] : ''; // '1,2,3'

				if ( empty( $ids ) ) {
					wp_send_json_error( array( 'mess' => __( 'No email log id found.', 'yay-smtp' ) ) );
				}

				$deletedEmailLogs 		 = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}yaysmtp_email_logs WHERE ID IN( $ids )" ) );
				$deletedEmailClickedLink = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}yaysmtp_event_email_clicked_link WHERE log_id IN( $ids )" ) );
				$deletedEmailOpened 	 = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}yaysmtp_event_email_opened WHERE log_id IN( $ids )" ) );

				if ( '' !== $wpdb->last_error ) {
					wp_send_json_error( array( 'mess' => $wpdb->last_error ) );
				}

				wp_send_json_success(
					array(
						'mess' => __( 'Delete successful.', 'yay-smtp' ),
					)
				);
			}
			wp_send_json_error( array( 'mess' => __( 'No email log id found.', 'yay-smtp' ) ) );

		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}

	public function deleteAllEmailLogs() {
		try {
			Utils::checkNonce();
			Utils::deleteAllEmailLogs();

			wp_send_json_success(
				array(
					'mess' => __( 'Delete successful.', 'yay-smtp' ),
				)
			);

		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}

	public function getEmailLog() {
		try {
			Utils::checkNonce();
			if ( isset( $_POST['params'] ) ) {
				global $wpdb;
				$params = Utils::saniValArray( $_POST['params'] );// phpcs:ignore
				$id     = isset( $params['id'] ) ? (int) $params['id'] : '';

				if ( empty( $id ) ) {
					wp_send_json_error( array( 'mess' => __( 'No email log id found.', 'yay-smtp' ) ) );
				}

				// $table       = $wpdb->prefix . 'yaysmtp_email_logs';
				$resultQuery = $wpdb->get_row( $wpdb->prepare( "Select * FROM {$wpdb->prefix}yaysmtp_email_logs WHERE id = %d", $id ) );

				if ( '' !== $wpdb->last_error ) {
					wp_send_json_error( array( 'mess' => $wpdb->last_error ) );
				}

				if ( ! empty( $resultQuery ) ) {
					$dateTimeFormat = get_option( 'date_format' ) . " \\a\\t " . get_option( 'time_format' );
					$emailTo   = maybe_unserialize( $resultQuery->email_to );
					$resultArr = array(
						'id'          		  => $resultQuery->id,
						'subject'     		  => wp_kses_post( $resultQuery->subject ),
						'email_from'  		  => $resultQuery->email_from,
						'email_to'    		  => $emailTo,
						'mailer'      		  => $resultQuery->mailer,
						'date_time'   		  => get_date_from_gmt( $resultQuery->date_time, "$dateTimeFormat" ),
						'status'      		  => $resultQuery->status,
						'mail_source' 		  => ! empty( $resultQuery->root_name ) ? $resultQuery->root_name : __( 'Unknown', 'yay-smtp' ),
						'email_opened' 		  => 'No',
						'email_clicked_links' => 'No',
					);

					if ( ! empty( $resultQuery->content_type ) ) {
						$resultArr['content_type'] = $resultQuery->content_type;
						$resultArr['body_content'] = maybe_serialize( $resultQuery->body_content );
					}

					if ( ! empty( $resultQuery->reason_error ) ) {
						$resultArr['reason_error'] = $resultQuery->reason_error;
					}

					$email_opened = Utils::getTrackingEmailOpenedByLogId( intval( $resultQuery->id ));
					if( ! empty( $email_opened )) {
						$email_opened_count = intval( $email_opened->count );
						if ( $email_opened_count > 0 ) {
							$resultArr['email_opened'] = __( 'Yes', 'yay-smtp' );
						}
					}

					$email_clicked_links = Utils::getTrackingEmailClickedLinkByLogId( intval( $resultQuery->id ));
					if( ! empty( $email_clicked_links )) {
						foreach( $email_clicked_links as $email_clicked_link) {
							$email_clicked_links_count = intval( $email_clicked_link->count );
							if ( $email_clicked_links_count > 0 ) {
								$resultArr['email_clicked_links'] = __( 'Yes', 'yay-smtp' );
								break;
							}
						}
					}

					wp_send_json_success(
						array(
							'mess' => 'Get email log #' . $id . ' successful.',
							'data' => $resultArr,
						)
					);
				} else {
					wp_send_json_error( array( 'mess' => __( 'No email log found.', 'yay-smtp' ) ) );
				}
			}
			wp_send_json_error( array( 'mess' => __( 'No email log id found.', 'yay-smtp' ) ) );

		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}

	public function getEmailChart() {
		try {
			Utils::checkNonce();

			$reqs = Utils::saniValArray( $_POST['params'] ); //phpcs:ignore
			$from = isset( $reqs['from'] ) ? $reqs['from'] : 'first day of this month';
			$to   = isset( $reqs['to'] ) ? $reqs['to'] : '';

			$startDateOrg = new \DateTime( $from );
			$endDateOrg   = new \DateTime( $to );

			$startDate = new \DateTime( $from );
			// $startDate = new \DateTime('15 days ago');
			$endDate = new \DateTime( $to );

			$dateModifier = $startDate->diff( $endDate )->m >= 11 ? '+1 month' : '+1 day';
			$groupBy      = ( '+1 month' === $dateModifier ) ? 'month' : 'day';

			$data = Utils::getChartData( $groupBy, '', $startDate->format( 'Y-m-d' ), $endDate->format( 'Y-m-d' ) );

			$labels       = array();
			$successData  = array();
			$failData     = array();
			$successTotal = 0;
			$failTotal    = 0;

			// initialize data
			for ( $i = $startDate; $i <= $endDate; $i->modify( $dateModifier ) ) {
				$date                 = $i->format( 'Y-m-d' );
				$labels[ $date ]      = $date;
				$successData[ $date ] = 0;
				$failData[ $date ]    = 0;
			}

			// fillup real data
			if ( ! empty( $data['successData'] ) ) {
				foreach ( $data['successData'] as $row ) {
					if ( 'month' == $groupBy ) {
						$date = new \DateTime( $row->date_time );
						$date->modify( 'first day of this month' );
						$date = $date->format( 'Y-m-d' );
					} else {
						$date = gmdate( 'Y-m-d', strtotime( $row->date_time ) );
					}

					$successData[ $date ] = (int) $row->total_emails;
					$successTotal        += (int) $row->total_emails;
				}
			}

			if ( ! empty( $data['failData'] ) ) {
				foreach ( $data['failData'] as $row ) {
					if ( 'month' == $groupBy ) {
						$date = new \DateTime( $row->date_time );
						$date->modify( 'first day of this month' );
						$date = $date->format( 'Y-m-d' );
					} else {
						$date = gmdate( 'Y-m-d', strtotime( $row->date_time ) );
					}

					$failData[ $date ] = (int) $row->total_emails;
					$failTotal        += (int) $row->total_emails;
				}
			}

			$topMailList       = Utils::getMailReportGroupByData( 'subject', $startDateOrg->format( 'Y-m-d' ), $endDateOrg->format( 'Y-m-d' ) );
			$topMailListOutput = array();
			if ( ! empty( $topMailList ) ) {
				foreach ( $topMailList as $title => $mail ) {
					$el = array(
						'title'  => $title,
						'sent'   => ! empty( $mail['total_sent'] ) ? $mail['total_sent'] : 0,
						'failed' => ! empty( $mail['total_failed'] ) ? $mail['total_failed'] : 0,
					);
					array_push( $topMailListOutput, $el );
				}
			}

			// Total Sales
			$response = array(
				'labels'       => array_values( $labels ),
				'datasets'     => array(
					array(
						'label'           => __( 'Email Sent', 'yay-smtp' ),
						'borderColor'     => '#2A8CE7',
						'backgroundColor' => '#2A8CE7',
						'order'           => 1,
						'data'            => array_values( $successData ),
					),
					array(
						'label'           => __( 'Email Fail', 'yay-smtp' ),
						'borderColor'     => '#d94f4f',
						'backgroundColor' => '#d94f4f',
						'order'           => 0,
						// 'type' => 'line',
						'data'            => array_values( $failData ),
					),
				),
				'successTotal' => $successTotal,
				'failTotal'    => $failTotal,
				'topMailList'  => $topMailListOutput,
			);

			wp_send_json_success(
				array(
					'mess' => __( 'Successful.', 'yay-smtp' ),
					'data' => $response,
				)
			);
		} catch ( \Exception $ex ) {
			LogErrors::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogErrors::getMessageException( $ex, true );
		}
	}
}
