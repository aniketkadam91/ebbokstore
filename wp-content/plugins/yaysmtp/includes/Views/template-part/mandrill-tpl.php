<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mandrillApiKey = '';
$settings       = $params['params'];
$mailer         = 'mandrill';
if ( ! empty( $params ) ) {
	if ( ! empty( $settings[ $mailer ] ) && isset( $settings[ $mailer ]['api_key'] ) ) {
		$mandrillApiKey = $settings[ $mailer ]['api_key'];
	}
}
?>

<div class="yay-smtp-card yay-smtp-mailer-settings" data-mailer="<?php echo esc_attr( $mailer ); ?>" style="display: <?php echo $currentMailer == $mailer ? 'block' : 'none'; ?>">
  <div class="yay-smtp-card-header">
	<div class="yay-smtp-card-title-wrapper">
	  <h3 class="yay-smtp-card-title yay-smtp-card-header-item">
	  	<?php echo esc_html__( 'Step 3: Config for Mandrill', 'yay-smtp' ); ?>	
		<div class="yay-tooltip doc-setting">
		  <a class="yay-smtp-button" href="https://docs.yaycommerce.com/yaysmtp/how-to-set-up-smtps/how-to-connect-mandrill" target="_blank">
		  <svg viewBox="64 64 896 896" data-icon="book" width="15" height="15" fill="currentColor" aria-hidden="true" focusable="false" class=""><path d="M832 64H192c-17.7 0-32 14.3-32 32v832c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V96c0-17.7-14.3-32-32-32zm-260 72h96v209.9L621.5 312 572 347.4V136zm220 752H232V136h280v296.9c0 3.3 1 6.6 3 9.3a15.9 15.9 0 0 0 22.3 3.7l83.8-59.9 81.4 59.4c2.7 2 6 3.1 9.4 3.1 8.8 0 16-7.2 16-16V136h64v752z"></path></svg>
		  </a>
		  <span class="yay-tooltiptext yay-tooltip-bottom"><?php echo esc_html__( 'Mandrill Documentation', 'yay-smtp' ); ?></span>
		</div>
	  </h3>
	  <h3 class="yay-smtp-card-description yay-smtp-card-header-item">
	  	<?php echo esc_html__( 'Mandrill is a transactional email API for MailChimp users. It is reliable, powerful, and ideal for sending data driven emails.', 'yay-smtp' ); ?>	
	  </h3>
	</div>
  </div>
  <div class="yay-smtp-card-body">
	<div class="setting-el">
	  <div class="setting-label">
		<label><?php echo esc_html__( 'API Key', 'yay-smtp' ); ?></label> 
	  </div>
	  <div class="setting-field">
		<input data-setting="api_key" type="password" spellcheck="false" class="yay-settings" value="<?php echo esc_attr( $mandrillApiKey ); ?>">
		<p class="setting-description">
		  <?php echo esc_html__( 'Click here to', 'yay-smtp' ); ?>
		  <a href="https://mandrillapp.com/settings" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Create API Key', 'yay-smtp' ); ?></a>
		</p>
	  </div>
	</div>
  </div>
</div>
