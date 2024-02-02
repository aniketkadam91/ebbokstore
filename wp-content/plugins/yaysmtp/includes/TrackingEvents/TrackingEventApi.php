<?php
namespace YaySMTP\TrackingEvents;
use YaySMTP\Helper\Utils;
defined( 'ABSPATH' ) || exit;

/**
 * Tracking Events Api
 */
class TrackingEventApi extends \WP_REST_Controller {
	/**
	 * Namespace of controller
	 *
	 * @var string
	 */
	protected $namespace = 'yaysmtp/v1';

	/**
	 * Router base name
	 *
	 * @var string
	 */
	protected $rest_base = 'track-event';

	protected static $instance = null;

	protected $email_opened;
	protected $email_clicked_link;

	private function __construct() {
		$this->email_opened       = EmailOpened::getInstance();
		$this->email_clicked_link = EmailClickedLink::getInstance();
	}

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}

	private function doHooks() {
		if ( $this->email_opened->is_enable() || $this->email_clicked_link->is_enable() ) {
			add_action( 'yaysmtp_send_before', array( $this, 'inject_tracking_into_mail_content' ), 40, 2 );
		}

		add_action( 'rest_api_init', array( $this, 'register_apis' ) );
	}

	public function register_apis() {
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/(?P<resources>.+)",
			array(
				'args' => array(
					'resources'              => array(
						'type'     => 'string',
						'required' => true,
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'handle_tracking_events' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	public function inject_tracking_into_mail_content( $phpmailer, $log_id ) {
		if ( 'text/html' === $phpmailer->ContentType && ! empty( $log_id ) ) { 
			$content = $phpmailer->Body;

			if ( $this->email_opened->is_enable() ) {
				$content = $this->email_opened->modify_email_content( $content, $log_id );
			}

			if ( $this->email_clicked_link->is_enable() ) {
				$content = $this->email_clicked_link->modify_email_content( $content, $log_id );
			}

			$phpmailer->Body = $content;
		}
	}

	public function handle_tracking_events( \WP_REST_Request $request ) {
		$params = $request->get_params('resources');
		if( ! empty($params) ){
			$passphrase = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'yay_smtp123098';
			$params_parse = Utils::decrypt_basic($params['resources'], $passphrase);
			parse_str( $params_parse, $data );
			
			if( ! empty( $data ) && isset($data['track_type'])) {
				// Handle Email Opened Tracking
				if ( 'email_opened' === $data['track_type'] && $this->email_opened->is_enable()) {
					if( ! empty( $data['track_data'] ) ) {
						$log_id = intval( $data['track_data'] );
						$track_id = $this->email_opened->update_database( $log_id );
					} 
				} 
				// Handle Email Clicked Links Tracking
				else if ( 'email_clicked_links' === $data['track_type'] ) {
					if( ! empty( $data['track_link_id'] ) && ! empty( $data['log_id'] ) && ! empty( $data['url'] )) {
						if ( $this->email_clicked_link->is_enable() ) { 
							$dataUpdate = array(
								'track_link_id' => intval( $data['track_link_id'] ),
								'log_id'        => intval( $data['log_id'] ),
								'url'           => urldecode( $data['url'] )
							);
							$track_id = $this->email_clicked_link->update_database( $dataUpdate );
						}

						$redirect = new \WP_REST_Response();
						$redirect->header( 'Cache-Control', 'must-revalidate, no-cache, no-store, max-age=0, no-transform' );
						$redirect->header( 'Pragma', 'no-cache' );
						$redirect->header( 'Location', urldecode( $data['url'] ) );
						return $redirect;					
					} 
				}
			}
		}
	}

}
