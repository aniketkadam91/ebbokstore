<?php
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templatePart = YAY_SMTP_PLUGIN_PATH . 'includes/Views/template-part';
?>

<div class="yay-sidenav yay-smtp-test-fallback-mail-drawer">
  <a href="javascript:void(0)" class="closebtn">&times;</a>
  <div class="yay-smtp-layout-activity-panel-content">
	<div class="yay-smtp-activity-panel-header">
	  <h3 class="yay-smtp-activity-panel-header-title"><?php echo esc_html__( 'Send Email', 'yay-smtp' ); ?></h3>
	</div>
	<div class="yay-smtp-activity-panel-content">
	  <div class="yay-smtp-card-body test-fallback-email">
		<div class="setting-label">
		  <label for="yaysmtp_fallback_test_mail_address"><?php echo esc_html__( 'Email Address', 'yay-smtp' ); ?></label>
		</div>
		<div class="setting-field">
		  <input type="text" id="yaysmtp_fallback_test_mail_address" class="yaysmtp-fallback-test-mail-address" value=<?php echo esc_attr( Utils::getAdminEmail() ); ?>>
		  <div class="error-message-email" style="display:none"></div>
		</div>
	  </div>
	  <div>
		<p class="setting-description">
		  <?php echo esc_html__( 'Before sending test email, please make sure to set up fallback properly and save changes.', 'yay-smtp' ); ?>
		</p>
		<button type="button" class="yaysmtp-fallback-send-mail-action" <?php echo !$fallback_is_mailer_complete ? 'disabled' : ''; ?>>Send Email</button>
	  </div>
	</div>
  </div>
  <?php //Utils::getTemplatePart($templatePart, 'debug'); ?>
</div>
