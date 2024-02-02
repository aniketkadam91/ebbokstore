<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="yaysmtp-analytics-email-wrap">
  <div class="filter-wrap">
	<div class="dashicons filter-icon"></div>
	<input id="yaysmtp_daterangepicker" type="text" value=""/>
  </div>
  <div class="yaysmtp-chart-sumary">
	<div class="total-mail-wrap">
	  <div class="total-mail-icon">
		<svg width="26" height="19" x="0px" y="0px"
		  viewBox="0 0 26 19" style="enable-background:new 0 0 26 19;" xml:space="preserve">
		  <path style="fill:#FFFFFF;" d="M12.1,14.1"/>
		  <g>
			<path style="fill:#FFC900;" d="M23,19H3c-1.7,0-3-1.3-3-3V3c0-1.7,1.3-3,3-3h20c1.7,0,3,1.3,3,3v13C26,17.7,24.7,19,23,19z"/>
			<path style="fill:#FFFFFF;" d="M22.4,6c0.5-0.4,0.5-1.2,0.1-1.6c-0.4-0.4-1-0.4-1.4-0.1l-7.6,5c-0.3,0.2-0.8,0.2-1.1,0L4.9,4.2
			  C4.5,3.9,3.9,4,3.5,4.3C3,4.8,3.1,5.5,3.6,6l8.1,7c0.7,0.6,1.9,0.6,2.6,0L22.4,6z"/>
		  </g>
		</svg>
	  </div>
	  <div class="total-mail">0</div>total
	</div>
	<div class="sent-mail-wrap">
	  <div class="sent-mail-icon">
		<svg width="19" height="19" x="0px" y="0px"
		  viewBox="0 0 19 19" style="enable-background:new 0 0 19 19;" xml:space="preserve">
		  <g>
			<g>
			  <circle style="fill:#2196F3;" cx="9.5" cy="9.5" r="9.5"/>
			</g>
			<g>
			  <path style="fill:#FFFFFF;" d="M8.3,12.7c-0.3,0-0.5-0.1-0.7-0.3l-2-2c-0.4-0.4-0.4-1,0-1.4s1-0.4,1.4,0l1.3,1.3L12,6.5c0.4-0.4,1-0.4,1.4,0
				s0.4,1,0,1.4L9,12.4C8.8,12.6,8.5,12.7,8.3,12.7z"/>
			</g>
		  </g>
		</svg>
	  </div>
	  <div class="sent-mail">0</div>sent
	</div>
	<div class="failed-mail-wrap">
	  <div class="failed-mail-icon">
		<svg width="19" height="19" x="0px" y="0px"
		  viewBox="0 0 19 19" style="enable-background:new 0 0 19 19;" xml:space="preserve">
		  <g>
			<circle style="fill:#F44336;" cx="9.5" cy="9.5" r="9.5"/>
			<path style="fill:#FFFFFF;" d="M12.7,11.2l-1.8-1.8l1.8-1.8c0.4-0.4,0.4-1,0-1.4s-1-0.4-1.4,0L9.5,8.1L7.7,6.3c-0.4-0.4-1-0.4-1.4,0
			  c-0.4,0.4-0.4,1,0,1.4l1.8,1.8l-1.8,1.8c-0.4,0.4-0.4,1,0,1.4C6.5,12.9,6.8,13,7,13c0.3,0,0.5-0.1,0.7-0.3l1.8-1.8l1.8,1.8
			  c0.2,0.2,0.4,0.3,0.7,0.3c0.3,0,0.5-0.1,0.7-0.3C13.1,12.2,13.1,11.6,12.7,11.2z"/>
		  </g>
		</svg>
	  </div>
	  <div class="failed-mail">0</div>failed
	</div>
  </div>
  <div class="yaysmtp-chart-wrap"><canvas id="yaysmtpCharts" width="400" height="265"></canvas></div>
  <div class="top-mail-table-wrap">
	<div class="top-mail-title">
	  <h3>Top Emails</h3>
	</div>
	<div class="top-mail-table">
	  <table>
		<thead>
		  <tr>
			<th class="table-header" > 
			  Subject
			</th>
			<th class="table-header" > 
			  Total
			</th>
			<th class="table-header" > 
			  Sent
			</th>
			<th class="table-header" > 
			  Failed
			</th>
		  </tr>
		</thead>
		<tbody class="top-mail-body">
		</tbody>
	  </table>
	  <p class="view-detail-link"><a href="<?php echo esc_attr( YAY_SMTP_SITE_URL ) . '/wp-admin/admin.php?page=yaysmtp&tab=email-log'; ?>"><?php echo esc_html__( 'View details', 'yay-smtp' ); ?></a></p>
	</div>
	<div class="top-mail-table-empty">
	  <p>No data</p>
	</div>
	
  </div>
</div>
