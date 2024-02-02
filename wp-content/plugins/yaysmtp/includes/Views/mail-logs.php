<?php
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$templatePart = YAY_SMTP_PLUGIN_PATH . 'includes/Views/template-part';

$yaySmtpEmailLogSetting = Utils::getYaySmtpEmailLogSetting();
$yaySmtpShowSubjectCol  = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_subject_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_subject_cl'] : 1;
$yaySmtpShowToCol       = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_to_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_to_cl'] : 1;
$yaySmtpShowStatusCol   = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_status_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_status_cl'] : 1;
$yaySmtpShowDatetimeCol = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_datetime_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_datetime_cl'] : 1;
$yaySmtpShowActionCol   = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['show_action_cl'] ) ? (int) $yaySmtpEmailLogSetting['show_action_cl'] : 1;

$yaySmtpEmailStatus      = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['status'] ) ? $yaySmtpEmailLogSetting['status'] : 'all';
$yayStatusSentChecked    = true;
$yayStatusNotsendChecked = true;
if ( 'not_send' === $yaySmtpEmailStatus ) {
	$yayStatusSentChecked = false;
} elseif ( 'sent' === $yaySmtpEmailStatus ) {
	$yayStatusNotsendChecked = false;
} elseif ( 'empty' === $yaySmtpEmailStatus ) {
	$yayStatusSentChecked    = false;
	$yayStatusNotsendChecked = false;
}

$email_opened          = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['email_opened'] ) ? $yaySmtpEmailLogSetting['email_opened'] : 'no';
$email_clicked_links   = isset( $yaySmtpEmailLogSetting ) && isset( $yaySmtpEmailLogSetting['email_clicked_links'] ) ? $yaySmtpEmailLogSetting['email_clicked_links'] : 'no';

$styleShowHidePage = 'none';
$mainTab           = Utils::getParamUrl( 'page' );
$childTab          = Utils::getParamUrl( 'tab' );
if ( 'yaysmtp' === $mainTab && 'email-log' === $childTab ) {
	$styleShowHidePage = 'block';
}
$multisiteSetting = Utils::getMainSiteMultisiteSetting();
?>

<div class="yay-smtp-wrap mail-logs" style="display:<?php echo esc_attr( $styleShowHidePage ); ?>">
  <?php if ( ! ( 'yes' === $multisiteSetting && ! is_network_admin() ) ) { ?>
  <div class="yay-button-first-header">
	<div class="yay-button-header-child-left">
	  <span class="dashicons dashicons-arrow-left-alt"></span>
	  <span><a href="<?php //echo YAY_SMTP_SITE_URL . '/wp-admin/admin.php?page=yaysmtp' ?>" class="mail-setting-redirect"><?php echo esc_html__( 'Back to Settings page', 'yay-smtp' ); ?></a></span>
	</div>
	<div class="yay-button-header-child-right">
	  <button class="yay-smtp-button yaysmtp-email-log-settings"><?php echo esc_html__( 'Email log settings', 'yay-smtp' ); ?></a>
	</div>
  </div>
  <?php } ?>

  <!-- Mail log settings drawer - start -->
  <?php Utils::getTemplatePart( $templatePart, 'email-log-settings', array( 'yaySmtpEmailLogSetting' => $yaySmtpEmailLogSetting ) ); ?>
  <!-- Mail log settings drawer  - end -->

  <div class="yay-smtp-card yay-smtp-mail-logs-wrap">
	<div class="yay-smtp-card-header">
	  <div class="yay-smtp-header">
		<div class="yay-smtp-title">
		  <h2><?php echo esc_html__( 'Email Log List', 'yay-smtp' ); ?></h2>
		</div>
		<div class="yay-button-wrap">
		  <!-- <div class="select-control bulk-action-control">
			<button type="button" class="yay-smtp-button delete-selected-button"> Delete Selected</button>
		  </div> -->
		  <div class="filter-daterangepicker-wrap">
			<div class="dashicons dashicons-calendar"></div>
			<input id="yaysmtp_daterangepicker_mail_logs" type="text" value=""/>
		  </div>
		  <div class="select-control search is-focused is-searchable">
			<div class="components-base-control select-control__control empty">
			  <i class="dashicons dashicons-search material-icons-outlined"></i>
			  <div class="components-base-control__field">
				<input class="select-control__control-input search-imput" type="text" placeholder="Search key as Subject field or To field">
			  </div>
			</div>
		  </div>
		  <div class="components-dropdown">
			<button type="button" title="Choose which values to display" class="components-button components-dropdown-button ellipsis-menu__toggle has-icon">
			  <span class="dashicon dashicons dashicons-ellipsis"></span>
			</button>
			<div class="components-popover components-dropdown__content">
			  <div class="components-popover__content">
				<div class="ellipsis-menu__content">
				  <div class="ellipsis-menu__title"><?php echo esc_html__( 'Columns', 'yay-smtp' ); ?>:</div>
				  <div class="components-base-control components-toggle-control">
					<label class="components-base-control__field" for="yaysmtp_logs_subject_control">
					  <div class="switch">
						<input type="checkbox" id="yaysmtp_logs_subject_control" <?php echo 1 === $yaySmtpShowSubjectCol ? 'checked' : ''; ?>>
						<span class="slider round"></span>
					  </div>
					  <div class="toggle-label">
						<span><?php echo esc_html__( 'Subject', 'yay-smtp' ); ?></span>
					  </div>
					</label>
					<label class="components-base-control__field" for="yaysmtp_logs_to_control">
					  <div class="switch" >
						<input type="checkbox" id="yaysmtp_logs_to_control" <?php echo 1 === $yaySmtpShowToCol ? 'checked' : ''; ?>>
						<span class="slider round"></span>
					  </div>
					  <div class="toggle-label">
						<span><?php echo esc_html__( 'To', 'yay-smtp' ); ?></span>
					  </div>
					</label>
					<label class="components-base-control__field" for="yaysmtp_logs_status_control">
					  <div class="switch">
						<input type="checkbox" id="yaysmtp_logs_status_control" <?php echo 1 === $yaySmtpShowStatusCol ? 'checked' : ''; ?>>
						<span class="slider round"></span>
					  </div>
					  <div class="toggle-label">
						<span><?php echo esc_html__( 'Status', 'yay-smtp' ); ?></span>
					  </div>
					</label>
					<label class="components-base-control__field" for="yaysmtp_logs_datetime_control">
					  <div class="switch">
						<input type="checkbox" id="yaysmtp_logs_datetime_control" <?php echo 1 === $yaySmtpShowDatetimeCol ? 'checked' : ''; ?>>
						<span class="slider round"></span>
					  </div>
					  <div class="toggle-label">
						<span><?php echo esc_html__( 'Time', 'yay-smtp' ); ?></span>
					  </div>
					</label>
					<label class="components-base-control__field" for="yaysmtp_logs_action_control">
					  <div class="switch">
						<input type="checkbox" id="yaysmtp_logs_action_control" <?php echo 1 === $yaySmtpShowActionCol ? 'checked' : ''; ?>>
						<span class="slider round"></span>
					  </div>
					  <div class="toggle-label">
						<span><?php echo esc_html__( 'Action', 'yay-smtp' ); ?></span>
					  </div>
					</label>
				  </div>
				  <div class="ellipsis-menu__title ellipsis-menu__title_status"><?php echo esc_html__( 'Status', 'yay-smtp' ); ?>:</div>
				  <div class="components-base-control components-toggle-control">
					<label class="components-base-control__field" for="yaysmtp_logs_status_sent">
					  <div class="switch">
						<input type="checkbox" id="yaysmtp_logs_status_sent" <?php echo $yayStatusSentChecked ? 'checked' : ''; ?>>
						<span class="slider round"></span>
					  </div>
					  <div class="toggle-label">
						<span><?php echo esc_html__( 'Success', 'yay-smtp' ); ?></span>
					  </div>
					</label>
					<label class="components-base-control__field" for="yaysmtp_logs_status_not_send">
					  <div class="switch" >
						<input type="checkbox" id="yaysmtp_logs_status_not_send" <?php echo $yayStatusNotsendChecked ? 'checked' : ''; ?>>
						<span class="slider round"></span>
					  </div>
					  <div class="toggle-label">
						<span><?php echo esc_html__( 'Fail', 'yay-smtp' ); ?></span>
					  </div>
					</label>
				  </div>
				  <div class="components-other-action-control">
					<div class="components-base-control components-toggle-control">
					  <label class="components-base-control__field">
						<div class="">
						  <span class="dashicons dashicons-trash"></span>
						</div>
						<div class="toggle-label">
						  <span class="yay-smtp-delete-all-mail-logs"><?php echo esc_html__( 'Delete All Mail Logs', 'yay-smtp' ); ?></span>
						</div>
					  </label>
					</div>
				  </div>
				</div>
			  </div>
			</div>
		  </div>
		</div>
	  </div>
	</div>
	<div class="yay-smtp-card-body">
	  <div class="yay-smtp-content">
		<div class="components-body">
		  <div class="wrap-table">
			<table>
			  <thead>
				<tr>
				  <th class="table-header is-checkbox-column">
					<div class="components-base-control">
					  <div class="components-base-control__field">
						<span class="checkbox-control-input-container">
						  <input id="input-check-all" class="checkbox-control-input-all checkbox-control-input" type="checkbox" aria-label="Select All">
						  <div class="checkbox-bulk-action-wrap">
							<svg class="gridicon gridicons-ellipsis" height="24" width="17" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
							  <g><path d="M7 12a2 2 0 11-4.001-.001A2 2 0 017 12zm12-2a2 2 0 10.001 4.001A2 2 0 0019 10zm-7 0a2 2 0 10.001 4.001A2 2 0 0012 10z"></path></g>
							</svg>
							<div class="bulk-action-wrap">
							  <ul class="select-control bulk-action-control">
								<li class="action-control-item delete-selected-button"><?php echo esc_html__( 'Delete Selected', 'yay-smtp' ); ?></li>
							  </ul>
							</div>
						  </div>
						</span>
					  </div>
					</div>
				  </th>
				  <th class="table-header is-left-aligned is-sortable subject-col <?php echo 0 === $yaySmtpShowSubjectCol ? 'hiden' : ''; ?>" data-sort-col="subject" data-sort="none"> <!-- none, descending, ascending-->
					<button type="button" class="components-button">
					  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24" role="img" aria-hidden="true" focusable="false">
						  <path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path>
					  </svg>
					  <!-- <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24" role="img" aria-hidden="true" focusable="false">
						  <path d="M6.5 12.4L12 8l5.5 4.4-.9 1.2L12 10l-4.5 3.6-1-1.2z"></path>
					  </svg> -->
					  <span><?php echo esc_html__( 'Subject', 'yay-smtp' ); ?></span>
					</button>
				  </th>
				  <th class="table-header is-left-aligned is-sortable to-col <?php echo 0 === $yaySmtpShowToCol ? 'hiden' : ''; ?>" data-sort-col="email_to" data-sort="none">
					<button type="button" class="components-button">
					  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24" role="img" aria-hidden="true" focusable="false">
						  <path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path>
					  </svg>
					  <span><?php echo esc_html__( 'To', 'yay-smtp' ); ?></span>
					</button>
				  </th>
				  <th class="table-header is-left-aligned mail-source-col">
					<span><?php echo esc_html__( 'Generated by', 'yay-smtp' ); ?></span>
				  </th>
				  <th class="table-header is-left-aligned is-sortable status-col <?php echo 0 === $yaySmtpShowStatusCol ? 'hiden' : ''; ?>" data-sort-col="status" data-sort="none">
					<button type="button" class="components-button">
					  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24" role="img" aria-hidden="true" focusable="false">
						  <path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path>
					  </svg>
					  <span><?php echo esc_html__( 'Status', 'yay-smtp' ); ?></span>
					</button>
				  </th>

				  <?php if( 'yes' === $email_opened ) { ?>
				  <th class="table-header is-left-aligned opened-tracking-col">
					<span><?php echo esc_html__( 'Opened', 'yay-smtp' ); ?></span>
				  </th>
				  <?php } ?>

				  <?php if( 'yes' === $email_clicked_links ) { ?>
				  <th class="table-header is-left-aligned clicked-link-tracking-col">
					<span><?php echo esc_html__( 'Clicked', 'yay-smtp' ); ?></span>
				  </th>
				  <?php } ?>

				  <th class="table-header is-left-aligned is-sortable datetime-col <?php echo 0 === $yaySmtpShowDatetimeCol ? 'hiden' : ''; ?>" data-sort-col="date_time" data-sort="none">
					<button type="button" class="components-button">
					  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24" role="img" aria-hidden="true" focusable="false">
						  <path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path>
					  </svg>
					  <span><?php echo esc_html__( 'Time', 'yay-smtp' ); ?></span>
					</button>
				  </th>
				  <th class="table-header action-col <?php echo 0 === $yaySmtpShowActionCol ? 'hiden' : ''; ?>">
					<span><?php echo esc_html__( 'Action', 'yay-smtp' ); ?></span>
				  </th>
				</tr>
			  </thead>
			  <tbody class="yaysmtp-body"></tbody>
			</table>
		  </div>
		</div>
		<!-- Mail log detail drawer - start -->
		<?php Utils::getTemplatePart( $templatePart, 'mail-details', array('email_opened' => $email_opened, 'email_clicked_links' => $email_clicked_links) ); ?>

		<!-- Mail log detail drawer  - end -->
		<div class="components-footer">
		  <div class="pagination">
			<div class="pagination-page-arrows">
			  <span class="pagination-page-arrows-label"></span>
			  <div class="pagination-page-arrows-buttons">
				<button type="button" class="components-button pagination-link previous-btn" aria-label="Previous Page">
				  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img" aria-hidden="true" focusable="false" style="flex:none">
					  <path d="M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"></path>
				  </svg>
				</button>
				<button type="button" class="components-button pagination-link next-btn" aria-label="Next Page">
				  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img" aria-hidden="true" focusable="false" style="flex:none">
					  <path d="M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"></path>
				  </svg>
				</button>
			  </div>
			</div>
			<div class="pagination-page-picker">
			  <label class="pagination-page-picker-label">
			  	<?php echo esc_html__( 'Go to page', 'yay-smtp' ); ?>
				<input id="" class="pagination-page-picker-input pag-page-current" aria-invalid="false" type="number" min="1" max="15" value="1">
			  </label>
			</div>
			<div class="pagination-per-page-picker">
			  <select class="components-select-control-input pag-per-page-sel">
				<option value="10">10/page</option>
				<option value="20">20/page</option>
				<option value="30">30/page</option>
				<option value="40">40/page</option>
			  </select>
			</div>
		  </div>
		</div>
	  </div>
	</div>
  </div>
</div>





