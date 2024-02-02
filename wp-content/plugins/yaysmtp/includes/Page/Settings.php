<?php
namespace YaySMTP\Page;

use YaySMTP\Helper\Utils;

defined( 'ABSPATH' ) || exit;

class Settings {
	protected static $instance = null;
	private $hook_suffix;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}

	private $pageId = null;

	private function doHooks() {
		$this->hook_suffix = array( 'yay_smtp_main_page' );
		add_action( 'admin_menu', array( $this, 'settingsMenu' ), YAYSMTP_MENU_PRIORITY );
		add_action( 'network_admin_menu', array( $this, 'settingsNetWorkMenu' ), YAYSMTP_MENU_PRIORITY );
		add_filter( 'plugin_action_links_' . YAY_SMTP_PLUGIN_BASENAME, array( $this, 'pluginActionLinks' ) );

		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
		}
	}

	private function __construct() {}

	public function settingsMenu() {
		$this->hook_suffix['yay_smtp_main_page'] = add_submenu_page(
			'yaycommerce',
			__( 'YaySMTP Manager', 'yay-smtp' ),
			__( 'YaySMTP', 'yay-smtp' ),
			'manage_options',
			'yaysmtp',
			array( $this, 'settingsPage' ),
			0
		);
	}

	public function settingsNetWorkMenu() {
		$this->hook_suffix['yay_smtp_main_page'] = add_submenu_page(
			'yaycommerce',
			__( 'YaySMTP Manager', 'yay-smtp' ),
			__( 'YaySMTP', 'yay-smtp' ),
			'manage_options',
			'yaysmtp',
			array( $this, 'settingsPage' ),
			0
		);
	}

	public function pluginActionLinks( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=yaysmtp' ) . '" aria-label="' . esc_attr__( 'YaySMTP', 'yay-smtp' ) . '">' . esc_html__( 'Settings', 'yay-smtp' ) . '</a>',
		);
		return array_merge( $action_links, $links );
	}

	public function settingsPage() {
		include_once YAY_SMTP_PLUGIN_PATH . 'includes/Views/yay-smtp.php';
	}

	public function enqueueAdminScripts( $screenId ) {
		$scriptId = $this->getPageId();
		wp_enqueue_style( 'yay_smtp_style', YAY_SMTP_PLUGIN_URL . 'assets/css/yay-smtp-admin.css', array(), YAY_SMTP_VERSION );
		// if ($screenId == $this->hook_suffix['yay_smtp_main_page']) {
		$succ_sent_mail_last = 'yes';
		$yaysmtpSettings     = Utils::getYaySmtpSetting();
		if ( ! empty( $yaysmtpSettings ) && isset( $yaysmtpSettings['succ_sent_mail_last'] ) && false === $yaysmtpSettings['succ_sent_mail_last'] ) {
			$succ_sent_mail_last = 'no';
		}
		wp_enqueue_script( 'moment' );
		wp_enqueue_script( $scriptId, YAY_SMTP_PLUGIN_URL . 'assets/js/yay-smtp-admin.js', array(), YAY_SMTP_VERSION, true );
		$yaysmtp_settings  = Utils::getYaySmtpSetting();
		$email_log_setting = Utils::getYaySmtpEmailLogSetting();
		wp_localize_script(
			$scriptId,
			'yaySmtpWpData',
			array(
				'YAY_SMTP_PLUGIN_PATH' => YAY_SMTP_PLUGIN_PATH,
				'YAY_SMTP_PLUGIN_URL'  => YAY_SMTP_PLUGIN_URL,
				'YAY_SMTP_SITE_URL'    => YAY_SMTP_SITE_URL,
				'YAY_ADMIN_AJAX'       => admin_url( 'admin-ajax.php' ),
				'DASHBOARD_URL'   	   => get_dashboard_url(),
				'ajaxNonce'            => wp_create_nonce( 'ajax-nonce' ),
				'currentMailer'        => Utils::getCurrentMailer(),
				'yaysmtpSettings'      => ( ! empty( $yaysmtp_settings ) && is_array( $yaysmtp_settings ) ) ? $yaysmtp_settings : array(),
				'yaysmtpLogSettings'   => $email_log_setting,
				'succ_sent_mail_last'  => $succ_sent_mail_last,
				'is_network_admin'     => is_network_admin(),
				'is_multisite_mode'    => Utils::getMainSiteMultisiteSetting(),
			)
		);
		wp_enqueue_media();

		if ( isset( $this->hook_suffix['yay_smtp_main_page'] ) && $screenId == $this->hook_suffix['yay_smtp_main_page'] ) {
			wp_enqueue_style( 'yay_smtp_select2', YAY_SMTP_PLUGIN_URL . 'assets/css/select2.min.css', array(), YAY_SMTP_VERSION );
			wp_enqueue_script( 'yay_smtp_select2', YAY_SMTP_PLUGIN_URL . 'assets/js/select2.min.js', array(), YAY_SMTP_VERSION, true );
			wp_enqueue_script( 'yaysmtp_purify', YAY_SMTP_PLUGIN_URL . 'assets/js/purify.min.js', array(), YAY_SMTP_VERSION, true );
		}

		wp_enqueue_style( 'yay_smtp_daterangepicker', YAY_SMTP_PLUGIN_URL . 'assets/css/daterangepicker_custom.css', array(), YAY_SMTP_VERSION );
		wp_enqueue_script( 'yay_smtp_chart', YAY_SMTP_PLUGIN_URL . 'assets/js/chart.min.js', array(), YAY_SMTP_VERSION, true );
		wp_enqueue_script( 'yay_smtp_daterangepicker', YAY_SMTP_PLUGIN_URL . 'assets/js/daterangepicker_custom.min.js', array(), YAY_SMTP_VERSION, true );
		wp_enqueue_script( 'yay_smtp_other', YAY_SMTP_PLUGIN_URL . 'assets/js/other-smtp-admin.js', array(), YAY_SMTP_VERSION, true );
	}

	public function getPageId() {
		if ( null == $this->pageId ) {
			$this->pageId = YAY_SMTP_PREFIX . '-settings';
		}
		return $this->pageId;
	}
}
