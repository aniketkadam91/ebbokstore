<?php
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templatePart = YAY_SMTP_PLUGIN_PATH . 'includes/Views/template-part';

$yaysmtp_mail_report_choose = 'no';
$yaysmtp_mail_report_type   = 'weekly';
$has_mail_fallback          = 'no';
$fallback_force_from_email  = 'yes';
$fallback_force_from_name   = 'no';
$fallback_host              = '';
$fallback_encryption        = '';
$fallback_port              = '';
$fallback_auth              = 'yes';
$fallback_user              = '';
$fallback_pass              = '';
$uninstall_flag             = 'no';
$disable_emails_delivery    = 'no';

$fallback_current_mailer            = Utils::getCurrentMailerFallback();
$fallback_is_mailer_complete        = Utils::isFullSettingsFallbackSmtp();
$fallback_service_provider_settings = [];
$fallback_allow_multisite           = Utils::getMultisiteSetting();

if ( ! empty( $params['params'] ) ) {
	$settings = $params['params'];
	if ( isset( $settings['mail_report_choose'] ) ) {
		$yaysmtp_mail_report_choose = $settings['mail_report_choose'];
	}
	if ( ! empty( $settings['mail_report_type'] ) ) {
		$yaysmtp_mail_report_type = $settings['mail_report_type'];
	}
	if ( isset( $settings['fallback_has_setting_mail'] ) ) {
		$has_mail_fallback = $settings['fallback_has_setting_mail'];
	}
	if ( isset( $settings['fallback_force_from_email'] ) ) {
		$fallback_force_from_email = $settings['fallback_force_from_email'];
	}
	if ( isset( $settings['fallback_force_from_name'] ) ) {
		$fallback_force_from_name = $settings['fallback_force_from_name'];
	}
	if ( isset( $settings['fallback_host'] ) ) {
		$fallback_host = $settings['fallback_host'];
	}
	if ( isset( $settings['fallback_auth_type'] ) ) {
		$fallback_encryption = $settings['fallback_auth_type'];
	}
	if ( isset( $settings['fallback_port'] ) ) {
		$fallback_port = $settings['fallback_port'];
	}
	if ( isset( $settings['fallback_auth'] ) ) {
		$fallback_auth = $settings['fallback_auth'];
	}
	if ( isset( $settings['fallback_smtp_user'] ) ) {
		$fallback_user = $settings['fallback_smtp_user'];
	}
	if ( isset( $settings['fallback_smtp_pass'] ) ) {
		$fallback_pass = Utils::decrypt( $settings['fallback_smtp_pass'], 'smtppass' );
	}
	if ( isset( $settings['uninstall_flag'] ) ) {
		$uninstall_flag = $settings['uninstall_flag'];
	}
	if ( isset( $settings['disable_emails_delivery'] ) ) {
		$disable_emails_delivery = $settings['disable_emails_delivery'];
	}
	if ( isset( $settings['fallback_service_provider_mailer_settings'] ) ) {
		$fallback_service_provider_settings = $settings['fallback_service_provider_mailer_settings'];
	}
}

$fallback_yaysmtper_list = Utils::getAllMailer();

$styleShowHidePage = 'none';
$mainTab           = Utils::getParamUrl( 'page' );
$childTab          = Utils::getParamUrl( 'tab' );
if ( 'yaysmtp' === $mainTab && 'additional-setting' === $childTab ) {
	$styleShowHidePage = 'block';
}
?>

<div class="yay-smtp-wrap yaysmtp-additional-settings-wrap" style="display:<?php echo esc_attr( $styleShowHidePage ); ?>">
	<div class="yay-button-first-header">
		<div class="yay-button-header-child-left">
			<span class="dashicons dashicons-arrow-left-alt"></span>
			<span><a class="mail-setting-redirect"><?php echo esc_html__( 'Back to Settings page', 'yay-smtp' ); ?></a></span>
		</div>
	</div>
	<div class="yay-smtp-card">
		<div class="yay-smtp-card-header">
			<div class="yay-smtp-card-title-wrapper">
				<h3 class="yay-smtp-card-title yay-smtp-card-header-item">
				<?php echo esc_html__( 'Additional Settings', 'yay-smtp' ); ?>
				</h3>
			</div>
		</div>
		<div class="yay-smtp-card-body">
			<div class="setting-el">
				<div class="setting-label">
					<label for="yaysmtp_addition_setts_disable_delivery"><?php echo esc_html__( 'Disable Email Delivery', 'yay-smtp' ); ?></label>
				</div>
				<div class="yaysmtp-addition-setts-report-cb">
					<div class="additional-settings-title"><input id="yaysmtp_addition_setts_disable_delivery" type="checkbox" <?php echo 'yes' === $disable_emails_delivery ? 'checked' : ''; ?>/></div>
					<div>
						<label for="yaysmtp_addition_setts_disable_delivery">
						<?php echo esc_html__( 'This feature will disable email delivery by YaySMTP. The email received on the logs page is email template in development mode.', 'yay-smtp' ); ?>
						</label>
					</div>
				</div>
			</div>
			<div class="setting-mail-report setting-el">
				<div class="setting-label">
				<label for="yaysmtp_addition_setts_report_cb"><?php echo esc_html__( 'Email Notifications', 'yay-smtp' ); ?></label>
				</div>
				<div class="yaysmtp-addition-setts-report-cb">
				<div class="additional-settings-title"><input id="yaysmtp_addition_setts_report_cb" type="checkbox" <?php echo 'yes' === $yaysmtp_mail_report_choose ? 'checked' : ''; ?>/></div>
				<div>
					<label for="yaysmtp_addition_setts_report_cb">
					<?php echo esc_html__( 'Receive SMTP email delivery summary via email.', 'yay-smtp' ); ?>
					</label>
				</div>
				</div>
				<div class="yaysmtp-addition-setts-report-detail">
				<label class="radio-setting">
					<input type="radio" id="yaysmtp_addition_setts_report_weekly" name="yaysmtp_addition_setts_mail_report"  value="weekly" <?php echo 'weekly' === $yaysmtp_mail_report_type ? 'checked' : ''; ?>>
					<?php echo esc_html__( 'Weekly', 'yay-smtp' ); ?>
				</label>
				<label class="radio-setting">
					<input type="radio" id="yaysmtp_addition_setts_report_monthly" name="yaysmtp_addition_setts_mail_report" value="monthly" <?php echo 'monthly' === $yaysmtp_mail_report_type ? 'checked' : ''; ?>>
					<?php echo esc_html__( 'Monthly', 'yay-smtp' ); ?>
				</label>
				</div>
			</div>
			<div class="setting-mail-fallback setting-el">
				<div class="setting-label">
					<label for="yaysmtp_setting_mail_fallback"><?php echo esc_html__( 'Fallback Carrier', 'yay-smtp' ); ?></label>
				</div>
				<div class="yaysmtp-setting-mail-fallback-wrap">
					<div class="mail-fallback-title"><input id="yaysmtp_setting_mail_fallback" class="yaysmtp-setting-mail-fallback" type="checkbox" <?php echo 'yes' === $has_mail_fallback ? 'checked' : ''; ?>/></div>
					<div>
						<label for="yaysmtp_setting_mail_fallback">
						<?php echo esc_html__( 'Configure a secondary email service provider to send WordPress emails. Automatically used after the first mailer has 1 failed attempts.', 'yay-smtp' ); ?>
						</label>
					</div>
				</div>

				<div class="yaysmtp-fallback-setting-detail-wrap" style="display: <?php echo 'yes' === $has_mail_fallback ? 'flex' : 'none'; ?>">
					<div class="yaysmtp-fallback-setting-opt-wrap">
						<div class="yay-smtp-card-header yaysmtp-fallback-setting-detail-header">
							<div class="title-wrap"><?php echo esc_html__( 'Fallback PHPMailer Settings', 'yay-smtp' ); ?></div>
							<div class="button-wrap">
								<button type="button" class="yay-smtp-button panel-tab-btn send-test-fallback-mail-panel">
								<svg viewBox="64 64 896 896" data-icon="mail" width="15" height="15" fill="currentColor" aria-hidden="true" focusable="false" class=""><path d="M928 160H96c-17.7 0-32 14.3-32 32v640c0 17.7 14.3 32 32 32h832c17.7 0 32-14.3 32-32V192c0-17.7-14.3-32-32-32zm-40 110.8V792H136V270.8l-27.6-21.5 39.3-50.5 42.8 33.3h643.1l42.8-33.3 39.3 50.5-27.7 21.5zM833.6 232L512 482 190.4 232l-42.8-33.3-39.3 50.5 27.6 21.5 341.6 265.6a55.99 55.99 0 0 0 68.7 0L888 270.8l27.6-21.5-39.3-50.5-42.7 33.2z"></path></svg>
								<span class="text">Send Test Fallback Email</span>
								</button>
							</div>
						</div>
						<div class="yay-smtp-card-body yay-smtp-card-body-setting-opt-wrap">
							<div class="yay-smtp-card">
								<div class="yay-smtp-card-header">
									<div class="yay-smtp-card-title-wrapper">
										<h3 class="yay-smtp-card-title yay-smtp-card-header-item"><?php echo esc_html__( 'Step 1: Enter Email From', 'yay-smtp' ); ?></h3>
									</div>
								</div>
								<div class="yay-smtp-card-body"> 
								<div class="setting-from-email">
									<div class="setting-label">
									<label for="yaysmtp_fallback_from_email"><?php echo esc_html__( 'From Email', 'yay-smtp' ); ?></label>
									</div>
									<div>
									<input type="text" id="yaysmtp_fallback_from_email" value="<?php echo esc_attr( Utils::getCurrentFromEmailFallback() ); ?>" />
									<p class="error-message-email" style="display:none"></p>
									<p class="setting-description">
										<?php echo esc_html__( 'The email displayed in the "From" field.', 'yay-smtp' ); ?>
									</p>
									<div>
										<input
										id="yaysmtp_fallback_force_from_email"
										type="checkbox"
										<?php echo 'yes' === $fallback_force_from_email ? 'checked' : ''; ?>
										/>
										<label for="yaysmtp_fallback_force_from_email"><?php echo esc_html__( 'Force From Email', 'yay-smtp' ); ?></label>
										<div class="yay-tooltip icon-tootip-wrap">
										<span class="icon-inst-tootip"></span>
										<span class="yay-tooltiptext yay-tooltip-bottom"><?php echo esc_html__( 'Always send emails with the above From Email address, overriding other plugins settings.', 'yay-smtp' ); ?></span>
										</div>
									</div>
									</div>
								</div>
								<div class="setting-from-name">
									<div class="setting-label">
									<label for="yaysmtp_fallback_from_name"><?php echo esc_html__( 'From Name', 'yay-smtp' ); ?></label>
									</div>
									<div>
									<input type="text" id="yaysmtp_fallback_from_name" value="<?php echo esc_attr( Utils::getCurrentFromNameFallback() ); ?>"/>
									<p class="setting-description">
										<?php echo esc_html__( 'The name displayed in emails', 'yay-smtp' ); ?>
									</p>
									<div>
										<input
										id="yaysmtp_fallback_force_from_name"
										type="checkbox"
										<?php echo 'yes' === $fallback_force_from_name ? 'checked' : ''; ?>
										/>
										<label for="yaysmtp_fallback_force_from_name"><?php echo esc_html__( 'Force From Name', 'yay-smtp' ); ?></label>
										<div class="yay-tooltip icon-tootip-wrap">
										<span class="icon-inst-tootip"></span>
										<span class="yay-tooltiptext yay-tooltip-bottom"><?php echo esc_html__( 'Always send emails with the above From Name, overriding other plugins settings.', 'yay-smtp' ); ?></span>
										</div>
									</div>
									</div>
								</div>
								</div> 
							</div>
						
							<div class="yay-smtp-card">
								<div class="yay-smtp-card-header yay-smtp-card-header-smtper-choose-fallback-wrap">
									<div class="yay-smtp-card-title-wrapper">
										<h3 class="yay-smtp-card-title yay-smtp-card-header-item"><?php echo esc_html__( 'Step 2: Select Mailer', 'yay-smtp' ); ?></h3>
									</div>
									<div class="yay-smtp-mailer-fallback smtper-choose-fallback-wrap">
										<select class="yay-settings-fallback smtper-choose-fallback" id="yaysmtp_smtper_choose_fallback">
										<?php
										foreach ( $fallback_yaysmtper_list as $val => $text ) {
											$selected = '';
											if ( $val === $fallback_current_mailer ) {
												$selected = 'selected';
											}
											echo '<option value="' . esc_attr( $val ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $text ) . '</option>';
										}
										?>
										</select>
									</div>
								</div>
							</div>

							<div class="mailer-settings-component-fallback">
								<div class="yay-smtp-card yay-smtp-mailer-settings" data-mailer="smtp" style="display: <?php echo 'smtp' === $fallback_current_mailer ? 'block' : 'none'; ?>">
									<div class="yay-smtp-card-header">
										<div class="yay-smtp-card-title-wrapper">
										<h3 class="yay-smtp-card-title yay-smtp-card-header-item">
											<?php echo esc_html__( 'Step 3: Config for Other SMTP', 'yay-smtp' ); ?>
											<div class="yay-tooltip doc-setting">
											<a class="yay-smtp-button" href="https://yaycommerce.gitbook.io/yaysmtp/how-to-set-up-smtps/how-to-connect-other-smtp/" target="_blank">
											<svg viewBox="64 64 896 896" data-icon="book" width="15" height="15" fill="currentColor" aria-hidden="true" focusable="false" class=""><path d="M832 64H192c-17.7 0-32 14.3-32 32v832c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V96c0-17.7-14.3-32-32-32zm-260 72h96v209.9L621.5 312 572 347.4V136zm220 752H232V136h280v296.9c0 3.3 1 6.6 3 9.3a15.9 15.9 0 0 0 22.3 3.7l83.8-59.9 81.4 59.4c2.7 2 6 3.1 9.4 3.1 8.8 0 16-7.2 16-16V136h64v752z"></path></svg>
											</a>
											<span class="yay-tooltiptext yay-tooltip-bottom"><?php echo esc_html__( 'Other SMTP Documentation', 'yay-smtp' ); ?></span>
											</div>
										</h3>
										<h3 class="yay-smtp-card-description yay-smtp-card-header-item">
											<?php echo esc_html__( 'Use SMTP from your hosting provider or email service (Gmail, Hotmail, Yahoo, etc).', 'yay-smtp' ); ?>
											
										</h3>
										</div>
									</div>
									<div class="yay-smtp-card-body"> 
										<div class="setting-el">
										<div class="setting-label">
											<label for="yaysmtp_fallback_host"><?php echo esc_html__( 'SMTP Host', 'yay-smtp' ); ?></label>
										</div>
										<div>
											<input type="text" id="yaysmtp_fallback_host" value="<?php echo esc_attr( $fallback_host ); ?>">
										</div>
										</div>
										<div class="setting-el">
										<div class="setting-label">
											<label for="yaysmtp_fallback_encryption_tls"><?php echo esc_html__( 'Encryption Type', 'yay-smtp' ); ?></label>
										</div>
										<div>
											<label class="radio-setting">
											<input type="radio" id="yaysmtp_fallback_encryption_none" name="yaysmtp-fallback-encryption" value="" <?php echo empty( $fallback_encryption ) ? 'checked' : ''; ?>>
											<?php echo esc_html__( 'None', 'yay-smtp' ); ?>
											</label>
											<label class="radio-setting">
											<input type="radio" id="yaysmtp_fallback_encryption_ssl" name="yaysmtp-fallback-encryption" value="ssl" <?php echo 'ssl' === $fallback_encryption ? 'checked' : ''; ?>>
											<?php echo esc_html__( 'SSL', 'yay-smtp' ); ?>
											</label>
											<label class="radio-setting">
											<input type="radio" id="yaysmtp_fallback_encryption_tls" name="yaysmtp-fallback-encryption" value="tls" <?php echo 'tls' === $fallback_encryption ? 'checked' : ''; ?>>
											<?php echo esc_html__( 'TLS', 'yay-smtp' ); ?>
											</label>
											<p class="setting-description">
											<?php echo esc_html__( 'TLS is the recommended option if your SMTP provider supports it.', 'yay-smtp' ); ?>
											</p>
										</div>
										</div>
										<div class="setting-el">
										<div class="setting-label">
											<label for="yaysmtp_fallback_port"><?php echo esc_html__( 'SMTP Port', 'yay-smtp' ); ?></label>
										</div>
										<div>
											<input type="number" id="yaysmtp_fallback_port" value="<?php echo esc_attr( $fallback_port ); ?>">
											<p class="setting-description">
											<?php echo esc_html__( 'Port of your mail server. Usually is 25, 465, 587', 'yay-smtp' ); ?>
											</p>
										</div>
										</div>
										<div class="setting-el">
										<div class="setting-label">
											<label for="yaysmtp_fallback_auth"><?php echo esc_html__( 'SMTP Authentication', 'yay-smtp' ); ?></label>
										</div>
										<div>
											<label class="switch">
											<input type="checkbox" id="yaysmtp_fallback_auth" <?php echo 'yes' === $fallback_auth ? 'checked' : ''; ?>>
											<span class="slider round"></span>
											</label>
											<label class="toggle-label">
											<span class="setting-toggle-fallback-checked">ON</span>
											<span class="setting-toggle-fallback-unchecked">OFF</span>
											</label>
										</div>
										</div>
										<div class="yaysmtp_fallback_auth_det" style="display: <?php echo 'yes' === $fallback_auth ? 'block' : 'none'; ?>">
										<div class="setting-el">
											<div class="setting-label">
											<label for="yaysmtp_fallback_smtp_user"><?php echo esc_html__( 'SMTP Username', 'yay-smtp' ); ?></label>
											</div>
											<div>
											<input type="text" id="yaysmtp_fallback_smtp_user" value="<?php echo esc_attr( $fallback_user ); ?>">
											</div>
										</div>
										<div class="setting-el yaysmtp-fallback-smtp-pass-wrap">
											<div class="setting-label">
											<label for="yaysmtp_fallback_smtp_pass"><?php echo esc_html__( 'SMTP Password', 'yay-smtp' ); ?></label>
											</div>
											<div>
											<input type="password" spellcheck="false" id="yaysmtp_fallback_smtp_pass" value="<?php echo esc_attr( $fallback_pass ); ?>">
											</div>
										</div>
										</div>
									</div>
								</div>

								<div class="mailer-settings-component-fallback">
									<div class="yay-smtp-card yay-smtp-mailer-settings" data-mailer="outlookms" style="display: <?php echo 'outlookms' === $fallback_current_mailer ? 'block' : 'none'; ?>">
										<div class="yay-smtp-card-header">
											<div class="yay-smtp-card-title-wrapper">
												<h3 class="yay-smtp-card-title yay-smtp-card-header-item">
													Please <span><a class="mail-setting-redirect">Back To Settings Page</a></span> to setting for Microsoft Outlook.
												</h3>
												<h3 class="yay-smtp-card-description yay-smtp-card-header-item">
													<?php echo esc_html__( 'After successfully configuring for Microsoft Outlook, you have to go back to the original settings for main SMTP.', 'yay-smtp' ); ?>
												</h3>
											</div>
										</div>
									</div>
								</div>

								<div class="mailer-settings-component-fallback">
									<div class="yay-smtp-card yay-smtp-mailer-settings" data-mailer="zoho" style="display: <?php echo 'zoho' === $fallback_current_mailer ? 'block' : 'none'; ?>">
										<div class="yay-smtp-card-header">
											<div class="yay-smtp-card-title-wrapper">
												<h3 class="yay-smtp-card-title yay-smtp-card-header-item">
													Please <span><a class="mail-setting-redirect">Back To Settings Page</a></span> to setting for Zoho.
												</h3>
												<h3 class="yay-smtp-card-description yay-smtp-card-header-item">
													<?php echo esc_html__( 'After successfully configuring for Zoho, you have to go back to the original settings for main SMTP.', 'yay-smtp' ); ?>
												</h3>
											</div>
										</div>
									</div>
								</div>

								<?php
								Utils::getTemplatePart( $templatePart, 'mail-tpl', array( 'currentMailer' => $fallback_current_mailer ) );
								Utils::getTemplatePart(
									$templatePart,
									'sendgrid-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'sendinblue-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'gmail-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								// Utils::getTemplatePart(
								// 	$templatePart,
								// 	'zoho-tpl',
								// 	array(
								// 		'isFallBack'    => TRUE,
								// 		'currentMailer' => $fallback_current_mailer,
								// 		'params'        => $fallback_service_provider_settings,
								// 	)
								// );
								Utils::getTemplatePart(
									$templatePart,
									'mailgun-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'smtp-com-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'amazonses-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'postmark-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'sparkpost-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'mailjet-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'pepipost-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								Utils::getTemplatePart(
									$templatePart,
									'sendpulse-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								// Utils::getTemplatePart(
								// 	$templatePart,
								// 	'outlook-ms-tpl',
								// 	array(
								// 		'isFallBack'    => TRUE,
								// 		'currentMailer' => $fallback_current_mailer,
								// 		'params'        => $fallback_service_provider_settings,
								// 	)
								// );
								Utils::getTemplatePart(
									$templatePart,
									'mandrill-tpl',
									array(
										'isFallBack'    => TRUE,
										'currentMailer' => $fallback_current_mailer,
										'params'        => $fallback_service_provider_settings,
									)
								);
								?>
							</div>
						</div>
					</div>

					<!-- Send test fallback mail drawer - start -->
					<?php Utils::getTemplatePart( $templatePart, 'send-test-mail-fallback', array('fallback_is_mailer_complete' => $fallback_is_mailer_complete) ); ?>
					<!-- Send test fallback mail drawer - end -->
				</div>
			</div>
		</div>
	</div>

	<div class="yay-smtp-card">
		<div class="yay-smtp-card-header">
			<div class="yay-smtp-card-title-wrapper">
				<h3 class="yay-smtp-card-title yay-smtp-card-header-item">
				<?php echo esc_html__( 'Tools', 'yay-smtp' ); ?>
				</h3>
			</div>
		</div>
		<!-- Import SMTP settings - start -->
		<?php Utils::getTemplatePart( YAY_SMTP_PLUGIN_PATH . 'includes/Views', 'yaysmtp-settings-other', array()); ?>
		<!-- Import SMTP settings - end -->

		<!-- Export email log - start -->
		<?php Utils::getTemplatePart( $templatePart, 'export-email-log', array() ); ?>
		<!-- Export email emaillog - end -->
	</div>

	<div class="yay-smtp-card">
		<div class="yay-smtp-card-header">
			<div class="yay-smtp-card-title-wrapper">
				<h3 class="yay-smtp-card-title yay-smtp-card-header-item">
				<?php echo esc_html__( 'Uninstallation', 'yay-smtp' ); ?>
				</h3>
			</div>
		</div>
		<div class="yay-smtp-card-body">
			<div class="setting-el">
				<div class="setting-label">
					<label for="yaysmtp_addition_setts_uninstall"><?php echo esc_html__( 'Remove All YaySMTP Data', 'yay-smtp' ); ?></label>
				</div>
				<div class="yaysmtp-addition-setts-report-cb">
					<div class="additional-settings-title"><input id="yaysmtp_addition_setts_uninstall" type="checkbox" <?php echo 'yes' === $uninstall_flag ? 'checked' : ''; ?>/></div>
					<div>
						<label for="yaysmtp_addition_setts_uninstall">
						<?php echo esc_html__( 'Remove ALL YaySMTP data when uninstalling plugin. All settings will be unrecoverable.', 'yay-smtp' ); ?>
						</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div>
		<button type="button" class="yay-smtp-button yaysmtp-additional-settings-btn"><?php echo esc_html__( 'Save Changes', 'yay-smtp' ); ?></button>
	</div>
</div>





