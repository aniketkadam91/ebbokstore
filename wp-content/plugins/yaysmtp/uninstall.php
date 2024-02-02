<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( current_user_can( 'manage_options' ) ) {
	$uninstallFlag   = 'no';
	$yaysmtpSettings = get_option( 'yaysmtp_settings' );
	if ( ! empty( $yaysmtpSettings ) && is_array( $yaysmtpSettings ) ) {
		if ( ! empty( $yaysmtpSettings['uninstall_flag'] ) ) {
			$uninstallFlag = $yaysmtpSettings['uninstall_flag'];
		}
	}

	if ( 'yes' === $uninstallFlag ) {
		global $wpdb;
		$allowMultisite = 'no';
		if ( is_multisite() ) {
			$yaysmtpMainSettings = get_blog_option( get_main_site_id(), 'yaysmtp_settings', array() );
			if ( ! empty( $yaysmtpMainSettings ) && ! empty( $yaysmtpMainSettings['allowMultisite'] ) ) {
				$allowMultisite = $yaysmtpMainSettings['allowMultisite'];
			}
		}

		if ( is_multisite() && ( 'yes' === $allowMultisite ) ) {
			$siteList = get_sites();
			foreach ( (array) $siteList as $site ) {
				switch_to_blog( $site->blog_id );

				// Delete option data
				delete_option( 'yaysmtp_settings' );
				delete_option( 'yaysmtp_settings_bk' );
				delete_option( 'yaysmtp_email_log_settings' );
				delete_option( 'yaysmtp_email_log_settings_bk' );
				delete_option( 'yaysmtp_debug' );
				delete_option( 'yaysmtp_debug_fallback' );
				delete_option( 'yay_smtp_version' );

				// Delete table
				$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaysmtp_email_logs;" );
				$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaysmtp_event_email_opened;" );
				$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaysmtp_event_email_clicked_link;" );

				restore_current_blog();
			}
		} else {
			// Delete option data
			delete_option( 'yaysmtp_settings' );
			delete_option( 'yaysmtp_settings_bk' );
			delete_option( 'yaysmtp_email_log_settings' );
			delete_option( 'yaysmtp_email_log_settings_bk' );
			delete_option( 'yaysmtp_debug' );
			delete_option( 'yaysmtp_debug_fallback' );
			delete_option( 'yay_smtp_version' );

			// Delete table
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaysmtp_email_logs;" );
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaysmtp_event_email_opened;" );
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaysmtp_event_email_clicked_link;" );
		}
	}
}
