<?php
namespace YaySMTP\Helper;

defined( 'ABSPATH' ) || exit;

class Installer {
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->setupPages();
		$this->createTables();
	}

	public function setupPages() {

	}

	public function pageExit( $postTitle ) {
		$foundPost = post_exists( $postTitle );
		return $foundPost;
	}

	public function createTables() {
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$this->createYaySMTPEmailLogs();
		// Modify $wpdb->prefix . yaysmtp_email_logs table (Ex: Add colunm, ....)
		$this->modifyYaySMTPEmailLogs();
		$this->createEventEmailOpened();
		$this->createEventEmailClickedLink();
	}

	public function createYaySMTPEmailLogs() {
		global $wpdb;
		$table = $wpdb->prefix . 'yaysmtp_email_logs';
		$sql   = "CREATE TABLE $table (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `subject` varchar(1000) DEFAULT NULL,
      `email_from` varchar(300) DEFAULT NULL,
      `email_to` longtext DEFAULT NULL,
      `mailer` varchar(300) DEFAULT NULL,
      `date_time` datetime NOT NULL,
      `status` int(1) DEFAULT NULL,
      `content_type` varchar(300) DEFAULT NULL,
      `body_content` longtext DEFAULT NULL,
      `reason_error` varchar(300) DEFAULT NULL,
      `flag_delete` int(1) DEFAULT 0,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) != $table ) {
			dbDelta( $sql );
		}
	}

	public function modifyYaySMTPEmailLogs() {
		global $wpdb;
		$table = $wpdb->prefix . 'yaysmtp_email_logs';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) == $table ) {
			$check_col_1_exist = $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}yaysmtp_email_logs` LIKE 'root_name';" );
			if ( empty( $check_col_1_exist ) ) {
				$ret = $wpdb->query( "ALTER TABLE {$wpdb->prefix}yaysmtp_email_logs ADD COLUMN root_name varchar(1000) DEFAULT NULL" );
			}

			$check_col_2_exist = $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}yaysmtp_email_logs` LIKE 'extra_info';" );
			if ( empty( $check_col_2_exist ) ) {
				$ret = $wpdb->query( "ALTER TABLE {$wpdb->prefix}yaysmtp_email_logs ADD COLUMN extra_info longtext DEFAULT NULL" );
			}
		}
	}

	public function createEventEmailOpened() {
		global $wpdb;
		$table = $wpdb->prefix . 'yaysmtp_event_email_opened';
		$sql   = "CREATE TABLE $table (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`log_id` bigint(20) UNSIGNED NOT NULL,
			`count` bigint(20) DEFAULT NULL,
			`date_time` datetime NOT NULL,
			`extra_info` longtext DEFAULT NULL,
			PRIMARY KEY (`id`),
			INDEX log_indx (log_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) != $table ) {
			dbDelta( $sql );
		}
	}

	public function createEventEmailClickedLink() {
		global $wpdb;
		$table = $wpdb->prefix . 'yaysmtp_event_email_clicked_link';
		$sql   = "CREATE TABLE $table (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`log_id` bigint(20) UNSIGNED NOT NULL,
			`url` text DEFAULT NULL,
			`count` bigint(20) DEFAULT NULL,
			`date_time` datetime NOT NULL,
			`extra_info` longtext DEFAULT NULL,
			PRIMARY KEY (`id`),
			INDEX log_indx (log_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) != $table ) {
			dbDelta( $sql );
		}
	}
}
