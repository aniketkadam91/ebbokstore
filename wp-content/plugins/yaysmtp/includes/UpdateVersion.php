<?php
namespace YaySMTP;

defined( 'ABSPATH' ) || exit;

add_action( 'network_admin_notices', 'YaySMTP\\YaySmtpDeactiveNotice' );
add_action( 'admin_notices', 'YaySMTP\\YaySmtpDeactiveNotice' );

function YaySmtpDeactiveNotice() {
	if ( current_user_can( 'activate_plugins' ) ) {
		?>
	  <div class="notice notice-error is-dismissible">
	  <p>
		<strong><?php esc_html_e( 'It looks like you have another YaySMTP version installed, please delete it before activating this new version. All current settings and data are still preserved.', 'yay-smtp' ); ?>
		<a href="https://docs.yaycommerce.com/yaysmtp/how-to-update-yaysmtp"><?php esc_html_e( 'Read more details.', 'yay-smtp' ); ?></a>
		</strong>
	  </p>
	  </div>
		<?php
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}
