<?php
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// style - start
$yaysmtp_report_performed_cl       = 'display: inline-flex; border: 1px solid #ccd0d4; width: 99%;';
$yaysmtp_mail_wrap                 = 'width: 30%; text-align: center; margin: 15px;';
$yaysmtp_mail_icon                 = 'margin-bottom: 15px;';
$yaysmtp_mail_number               = 'font-size: 20px; font-weight: 500; margin-bottom: 15px;';
$yaysmtp_mail_percent_down         = 'font-size: 15px;color: #d94f4f';
$yaysmtp_mail_percent_up           = 'font-size: 15px;color: #4ab866';
$yaysmtp_top_emails_wrap_cl        = 'margin-top: 35px;padding-bottom: 15px;border-bottom: 1px solid #ccd0d4;';
$yaysmtp_width                     = 'width: 99%;text-align: center;';
$yaysmtp_group_title               = 'font-size: 17px;margin-top: 25px;width: 80%;text-align: left;';
$yaysmtp_group_content_wrap        = 'display: inline-flex;width: 100%;';
$yaysmtp_group_content_item        = 'display: inline-flex; width: 20%; margin-left: 15px;margin-right: 15px;margin-top: 25px;';
$yaysmtp_group_content_item_icon   = 'margin-right: 8px;';
$yaysmtp_group_content_item_number = 'font-size: 16px;margin-top: 2px;';
$yaysmtp_group_button              = 'font-size: 17px;margin-top: 25px;';
// style - end

// last month
$yaysmtpRepStart = gmdate( 'Y-m-d', strtotime( 'first day of last month' ) );
$yaysmtpRepEnd   = gmdate( 'Y-m-d', strtotime( 'last day of last month' ) );
$mailReportData  = Utils::getMailReportData( $yaysmtpRepStart, $yaysmtpRepEnd );

// Prepare data for Subject mail group in last month
$mailReportGroupByData = Utils::getMailReportGroupByData( 'subject', $yaysmtpRepStart, $yaysmtpRepEnd, 5 );

// 2 last month
$twoLastMonthStart      = gmdate( 'Y-m-d', strtotime( 'first day of -2 month' ) );
$twoLastMonthLast       = gmdate( 'Y-m-d', strtotime( 'last day of -2 month' ) );
$twoLastMonthReportData = Utils::getMailReportData( $twoLastMonthStart, $twoLastMonthLast );

$yaysmtpMailTotalPercent  = Utils::percentClass( $mailReportData['total_mail'], $twoLastMonthReportData['total_mail'] );
$yaysmtpMailSentPercent   = Utils::percentClass( $mailReportData['sent_mail'], $twoLastMonthReportData['sent_mail'] );
$yaysmtpMailFailedPercent = Utils::percentClass( $mailReportData['failed_mail'], $twoLastMonthReportData['failed_mail'] );

?>

<div>
  <p>Howdy,</p>
  <div>
	<p>Here’s the summary of <?php echo wp_kses_post( get_bloginfo( 'name' ) ); ?> email deliverability with YaySMTP.</p>
	<div style="<?php echo esc_attr( $yaysmtp_report_performed_cl ); ?>">
	  <div style="<?php echo esc_attr( $yaysmtp_mail_wrap ); ?>">
		<div style="<?php echo esc_attr( $yaysmtp_mail_icon ); ?>">
		  <img src="<?php echo esc_attr( YAY_SMTP_PLUGIN_URL ) . 'assets/img/mail-icon.png'; ?>" height="25" width="32">
		</div>
		<div style="<?php echo esc_attr( $yaysmtp_mail_number ); ?>"><?php echo wp_kses_post( $mailReportData['total_mail'] ); ?></div>
		<?php
		$totalMailText        = '- ';
		$yaysmtp_mail_percent = $yaysmtp_mail_percent_down;
		if ( 'up' === $yaysmtpMailTotalPercent['class'] ) {
			$totalMailText        = '+ ';
			$yaysmtp_mail_percent = $yaysmtp_mail_percent_up;
		}
		?>
		<div style="<?php echo esc_attr( $yaysmtp_mail_percent ); ?>">
		  <?php
			echo wp_kses_post( $totalMailText ) . wp_kses_post( $yaysmtpMailTotalPercent['percent'] ) . ' %';
			?>
		</div>
	  </div>
	  <div style="<?php echo esc_attr( $yaysmtp_mail_wrap ); ?>">
		<div style="<?php echo esc_attr( $yaysmtp_mail_icon ); ?>">
		  <img src="<?php echo esc_attr( YAY_SMTP_PLUGIN_URL ) . 'assets/img/sent-icon.png'; ?>" height="25" width="25">
		</div>
		<div style="<?php echo esc_attr( $yaysmtp_mail_number ); ?>"><?php echo wp_kses_post( $mailReportData['sent_mail'] ); ?></div>
		<?php
		$sentMailText              = '- ';
		$yaysmtp_sent_mail_percent = $yaysmtp_mail_percent_down;
		if ( 'up' === $yaysmtpMailSentPercent['class'] ) {
			$sentMailText              = '+ ';
			$yaysmtp_sent_mail_percent = $yaysmtp_mail_percent_up;
		}
		?>
		<div style="<?php echo esc_attr( $yaysmtp_sent_mail_percent ); ?>">
		  <?php
			echo wp_kses_post( $sentMailText ) . wp_kses_post( $yaysmtpMailSentPercent['percent'] ) . ' %';
			?>
		</div>
	  </div>
	  <div style="<?php echo esc_attr( $yaysmtp_mail_wrap ); ?>">
		<div style="<?php echo esc_attr( $yaysmtp_mail_icon ); ?>">
		  <img src="<?php echo esc_attr( YAY_SMTP_PLUGIN_URL ) . 'assets/img/failed-icon.png'; ?>" height="25" width="25">
		</div>
		<div style="<?php echo esc_attr( $yaysmtp_mail_number ); ?>"><?php echo wp_kses_post( $mailReportData['failed_mail'] ); ?></div>
		<?php
		$failedMailText              = '- ';
		$yaysmtp_failed_mail_percent = $yaysmtp_mail_percent_down;
		if ( 'up' === $yaysmtpMailFailedPercent['class'] ) {
			$failedMailText              = '+ ';
			$yaysmtp_failed_mail_percent = $yaysmtp_mail_percent_up;
		}
		?>
		<div style="<?php echo esc_attr( $yaysmtp_failed_mail_percent ); ?>">
		  <?php
			echo wp_kses_post( $failedMailText ) . wp_kses_post( $yaysmtpMailFailedPercent['percent'] ) . ' %';
			?>
		</div>
	  </div>
	</div>
	<div style="<?php echo esc_attr( $yaysmtp_width ); ?>">
	  <div style="<?php echo esc_attr( $yaysmtp_top_emails_wrap_cl ); ?>">
		<h2>Top Emails Last Month</h2>
	  </div>
	  <?php
		if ( ! empty( $mailReportGroupByData ) ) {
			foreach ( $mailReportGroupByData as $groupTitle => $mailGroup ) {
				$total_sent   = ! empty( $mailGroup['total_sent'] ) ? $mailGroup['total_sent'] : 0;
				$total_failed = ! empty( $mailGroup['total_failed'] ) ? $mailGroup['total_failed'] : 0;
				$total_mail   = $total_sent + $total_failed;
				if ( 0 < intval( $total_sent ) ) {
					?>
				<div style="<?php echo esc_attr( $yaysmtp_group_content_wrap ); ?>">
				  <div style="<?php echo esc_attr( $yaysmtp_group_title ); ?>"><?php echo wp_kses_post( $groupTitle ); ?></div>
				  <div style="<?php echo esc_attr( $yaysmtp_group_content_item ); ?>">
					<div style="<?php echo esc_attr( $yaysmtp_group_content_item_icon ); ?>">
					  <img src="<?php echo esc_attr( YAY_SMTP_PLUGIN_URL ) . 'assets/img/sent-icon.png'; ?>" height="22" width="22">
					</div>
					<div style="<?php echo esc_attr( $yaysmtp_group_content_item_number ); ?>"><?php echo wp_kses_post( $total_sent ); ?></div>
				  </div>
				</div>   
					<?php
				}
			}
		}
		?>
	  <div style="<?php echo esc_attr( $yaysmtp_group_button ); ?>"><a href="<?php echo esc_attr( YAY_SMTP_SITE_URL ) . '/wp-admin/admin.php?page=yaysmtp&tab=email-log'; ?>">View Details</a></div>
	</div>
  </div>
  <div><br>Cheers,<br>YayCommerce </div>
</div>




