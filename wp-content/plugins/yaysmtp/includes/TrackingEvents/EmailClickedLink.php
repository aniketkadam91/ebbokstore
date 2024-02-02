<?php
namespace YaySMTP\TrackingEvents;
use YaySMTP\Helper\Utils;

defined( 'ABSPATH' ) || exit;

class EmailClickedLink {
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	public function is_enable() {
		$yaySmtpEmailLogSetting = Utils::getYaySmtpEmailLogSetting();
		if ( ! empty( $yaySmtpEmailLogSetting['email_clicked_links'] ) && 'yes' === $yaySmtpEmailLogSetting['email_clicked_links']) {
			return true;
		}
		return false;
	}

	public function modify_email_content( $mail_content, $log_id ) {
		global $wpdb;
		$domDoc              = new \DOMDocument();
		$old_internal_errors = libxml_use_internal_errors( true );
		$html_content 		 = make_clickable( $mail_content );

		if ( mb_detect_encoding($html_content, "UTF-8", true) ) {
			$html_content = mb_convert_encoding($html_content, "HTML-ENTITIES", "UTF-8");
		}

		$domDoc->loadHTML( $html_content );
		$org_hrefs = $domDoc->getElementsByTagName( 'a' );

		$custom_hrefs = [];
		foreach ( $org_hrefs as $org_href ) {
			$href = $org_href->getAttribute( 'href' );
			if ( 0 !== strlen( trim( $href ) ) && 
				'#' !== substr( trim( $href ), 0, 1 ) && 
				'tel' !== substr( trim( $href ), 0, 3 ) && 
				'javascript' !== substr( trim( $href ), 0, 10 ) && 
				'mailto' !== substr( trim( $href ), 0, 6 )) 
			{
				if ( ! isset( $custom_hrefs[ $href ] ) ) {
					$data = array(
						'log_id'    => intval( $log_id ),
						'url'       => esc_url_raw( $href ),
						'count'     => 0,
						'date_time' => current_time( 'mysql', true )
					);
					$add_link = $wpdb->insert( $wpdb->prefix . 'yaysmtp_event_email_clicked_link', $data, [ '%d', '%s', '%d', '%s' ] );
					$track_link_id = $add_link ? $wpdb->insert_id : false;

					if ( $track_link_id ) {
						$custom_hrefs[ $href ] = $track_link_id;
					}
				} else {
					$track_link_id = $custom_hrefs[ $href ];
				}
	
				$passphrase 	   = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'yay_smtp123098';
				$http_query_arg    = array( 'track_type' 	=> 'email_clicked_links', 
											'track_link_id' => $track_link_id, 
											'log_id' 		=> $log_id, 
											'url' 			=> rawurlencode( $href )); 
				$code 			   = Utils::encrypt_basic( http_build_query($http_query_arg), $passphrase );
				$tracking_api_link = get_rest_url( null, 'yaysmtp/v1/track-event/' . $code );

				$org_href->setAttribute( 'href', $tracking_api_link );
			}

		}

		$content_custom = $domDoc->saveHTML();

		libxml_clear_errors();
		libxml_use_internal_errors( $old_internal_errors );

		if ( ! empty( $content_custom ) ) {
			return $content_custom;
		}

		return $mail_content;
	}

	/**
	 * Tracking data into DB
	 */
	public function update_database( $data ) {
		if( empty( $data['track_link_id'] ) || empty( $data['log_id'] ) || empty( $data['url'] )) {
			return false;
		}

		global $wpdb;
		$log_exist = $wpdb->get_row( $wpdb->prepare( "Select * FROM {$wpdb->prefix}yaysmtp_event_email_clicked_link WHERE id = %d AND log_id = %d AND url = %s", $data['track_link_id'], $data['log_id'], $data['url'] ) );

		if( ! empty( $log_exist ) ) { 
			$data_update = array(
				'count'     => intval( $log_exist->count ) + 1,
				'date_time' => current_time( 'mysql', true )
			);

			$data_condition = array( 
				'id' 	 => intval( $log_exist->id ),
				'log_id' => intval( $log_exist->log_id ),
				'url'    => $log_exist->url,
			);
			$update_rst = $wpdb->update( $wpdb->prefix . 'yaysmtp_event_email_clicked_link', $data_update, $data_condition );
	
			if( $update_rst ) {
				return $log_exist->id;
			}
		} 

		return false;
	}
}
