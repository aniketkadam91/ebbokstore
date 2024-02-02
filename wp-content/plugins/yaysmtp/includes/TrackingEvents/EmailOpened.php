<?php
namespace YaySMTP\TrackingEvents;
use YaySMTP\Helper\Utils;

defined( 'ABSPATH' ) || exit;

class EmailOpened {
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
	
	}

	public function is_enable() {
		$yaySmtpEmailLogSetting = Utils::getYaySmtpEmailLogSetting();
		if ( ! empty( $yaySmtpEmailLogSetting['email_opened'] ) && 'yes' === $yaySmtpEmailLogSetting['email_opened']) {
			return true;
		}
		return false;
	}

	public function modify_email_content( $mail_content, $log_id ) {
		$passphrase = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'yay_smtp123098';
		$code = Utils::encrypt_basic( http_build_query(['track_data' => $log_id, 'track_type' => 'email_opened']), $passphrase );
		$tracking_api_link = get_rest_url( null, 'yaysmtp/v1/track-event/' . $code );

		$mail_content .= sprintf( '<img src="%s" alt="track" style="display:none;"/>', $tracking_api_link );
		return $mail_content ;
	}

	/**
	 * Tracking data into DB
	 */
	public function update_database( $log_id ) {
		global $wpdb;
		$log_exist = $wpdb->get_row( $wpdb->prepare( "Select * FROM {$wpdb->prefix}yaysmtp_event_email_opened WHERE log_id = %d", $log_id ) );

		if( empty( $log_exist ) ) { // insert new log
			$data = array(
				'log_id' 	=> intval( $log_id ),
				'count'     => 1,
				'date_time' => current_time( 'mysql', true )
			);
	
			$wpdb->insert( $wpdb->prefix . 'yaysmtp_event_email_opened', $data, array( '%d', '%d', '%s' ) );
			$track_id = $wpdb->insert_id;

			return $track_id;
		} else { // update exist log
			$data = array(
				'count'     => intval( $log_exist->count ) + 1,
				'date_time' => current_time( 'mysql', true )
			);

			$wpdb->update( $wpdb->prefix . 'yaysmtp_event_email_opened', $data, array( 'id' => intval( $log_exist->id ) ) );

			return intval( $log_exist->id );
		}
	}
}
