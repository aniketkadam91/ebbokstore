<?php
namespace YaySMTP;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin activate/deactivate logic
 */
class Plugin {
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}

	private function doHooks() {
		$current_version = get_option( 'yay_smtp_version' );
		if ( version_compare( YAY_SMTP_VERSION, $current_version, '>' ) ) {
			self::activate();
			update_option( 'yay_smtp_version', YAY_SMTP_VERSION );
		}
		Page\Settings::getInstance();
		PluginCore::getInstance();
		Functions::getInstance();
	}

	private function __construct() {}

	/** Plugin activated hook */
	public static function activate() {
		Helper\Installer::getInstance();
	}

	/** Plugin deactivate hook */
	public static function deactivate() {}
}
