<?php
/**
 * Content for license page
 *
 * @package YayCommerce\View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<section class="yaycommerce-license-page">
	<header>
		<div class="yaycommerce-license__top-bar">
			<div class="yaycommerce-license__top-bar__icon">
				<svg width="60" height="59" viewBox="0 0 60 59" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect width="60" height="59" fill="#FFF9DB"/><rect x="13" y="13" width="33" height="33" fill="url(#pattern0)"/><defs><pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1"><use xlink:href="#image0_246_3113" transform="scale(0.002)"/></pattern><image id="image0_246_3113" width="500" height="500" xlink:href="data:image/png;base64,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"/></defs></svg>
			</div>
			<div class="yaycommerce-license__top-bar__title">
				<h2>Activate Licenses</h2>
			</div>
			<div class="yaycommerce-license__important-notice">
				<span><svg viewBox="64 64 896 896" focusable="false" data-icon="info-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372z" fill="#1890ff"></path><path d="M512 140c-205.4 0-372 166.6-372 372s166.6 372 372 372 372-166.6 372-372-166.6-372-372-372zm32 588c0 4.4-3.6 8-8 8h-48c-4.4 0-8-3.6-8-8V456c0-4.4 3.6-8 8-8h48c4.4 0 8 3.6 8 8v272zm-32-344a48.01 48.01 0 010-96 48.01 48.01 0 010 96z" fill="#e6f7ff"></path><path d="M464 336a48 48 0 1096 0 48 48 0 10-96 0zm72 112h-48c-4.4 0-8 3.6-8 8v272c0 4.4 3.6 8 8 8h48c4.4 0 8-3.6 8-8V456c0-4.4-3.6-8-8-8z" fill="#1890ff"></path></svg></span>
				<span>
					<strong>Important notice: </strong>
					<span>Please activate your license to start using the Pro version</span>
				</span>
			</div>
		</div>
	</header>
	<main>
		<div class="message message--success" id="message-activate-success">
			<svg viewBox="64 64 896 896" focusable="false" data-icon="check-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm193.5 301.7l-210.6 292a31.8 31.8 0 01-51.7 0L318.5 484.9c-3.8-5.3 0-12.7 6.5-12.7h46.9c10.2 0 19.9 4.9 25.9 13.3l71.2 98.8 157.2-218c6-8.3 15.6-13.3 25.9-13.3H699c6.5 0 10.3 7.4 6.5 12.7z"></path></svg>
			<p class="message__text">Activated</p>
		</div>
		<div class="message message--error" id="message-activate-error">
			<svg viewBox="64 64 896 896" focusable="false" data-icon="close-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm165.4 618.2l-66-.3L512 563.4l-99.3 118.4-66.1.3c-4.4 0-8-3.5-8-8 0-1.9.7-3.7 1.9-5.2l130.1-155L340.5 359a8.32 8.32 0 01-1.9-5.2c0-4.4 3.6-8 8-8l66.1.3L512 464.6l99.3-118.4 66-.3c4.4 0 8 3.5 8 8 0 1.9-.7 3.7-1.9 5.2L553.5 514l130 155c1.2 1.5 1.9 3.3 1.9 5.2 0 4.4-3.6 8-8 8z"></path></svg>
			<p class="message__text">Activate failed</p>
		</div>
		<div class="message message--success" id="message-update-success">
			<svg viewBox="64 64 896 896" focusable="false" data-icon="check-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm193.5 301.7l-210.6 292a31.8 31.8 0 01-51.7 0L318.5 484.9c-3.8-5.3 0-12.7 6.5-12.7h46.9c10.2 0 19.9 4.9 25.9 13.3l71.2 98.8 157.2-218c6-8.3 15.6-13.3 25.9-13.3H699c6.5 0 10.3 7.4 6.5 12.7z"></path></svg>
			<p class="message__text">Updated</p>
		</div>
		<div class="message message--error" id="message-update-error">
			<svg viewBox="64 64 896 896" focusable="false" data-icon="close-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm165.4 618.2l-66-.3L512 563.4l-99.3 118.4-66.1.3c-4.4 0-8-3.5-8-8 0-1.9.7-3.7 1.9-5.2l130.1-155L340.5 359a8.32 8.32 0 01-1.9-5.2c0-4.4 3.6-8 8-8l66.1.3L512 464.6l99.3-118.4 66-.3c4.4 0 8 3.5 8 8 0 1.9-.7 3.7-1.9 5.2L553.5 514l130 155c1.2 1.5 1.9 3.3 1.9 5.2 0 4.4-3.6 8-8 8z"></path></svg>
			<p class="message__text">Updated failed</p>
		</div>
		<div class="message message--success" id="message-remove-success">
			<svg viewBox="64 64 896 896" focusable="false" data-icon="check-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm193.5 301.7l-210.6 292a31.8 31.8 0 01-51.7 0L318.5 484.9c-3.8-5.3 0-12.7 6.5-12.7h46.9c10.2 0 19.9 4.9 25.9 13.3l71.2 98.8 157.2-218c6-8.3 15.6-13.3 25.9-13.3H699c6.5 0 10.3 7.4 6.5 12.7z"></path></svg>
			<p class="message__text">Removed</p>
		</div>
		<div class="message message--error" id="message-remove-error">
			<svg viewBox="64 64 896 896" focusable="false" data-icon="close-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm165.4 618.2l-66-.3L512 563.4l-99.3 118.4-66.1.3c-4.4 0-8-3.5-8-8 0-1.9.7-3.7 1.9-5.2l130.1-155L340.5 359a8.32 8.32 0 01-1.9-5.2c0-4.4 3.6-8 8-8l66.1.3L512 464.6l99.3-118.4 66-.3c4.4 0 8 3.5 8 8 0 1.9-.7 3.7-1.9 5.2L553.5 514l130 155c1.2 1.5 1.9 3.3 1.9 5.2 0 4.4-3.6 8-8 8z"></path></svg>
			<p class="message__text">Removed failed</p>
		</div>
		<div class="yaycommerce-license-body">
			<div class="yaycommerce-license-layout">
				<div class="yaycommerce-license-layout-main">
					<div class="yaycommerce-license-settings">
						<div class="yaycommerce-license-card yaycommerce-license-no-license">
							<div class="yaycommerce-license-card-body">
								Welcome to YayCommerce! Please visit our website to get more <a href="https://yaycommerce.com/" target="_blank">WooCommerce plugins</a> 👋
							</div>
						</div>
						<?php do_action( 'yaycommerce_licenses_page' ); ?>
					</div>
				</div>
				<div class="yaycommerce-license-sidebar">
					<div class="yaycommerce-license-sidebar-sticky">
						<div class="yaycommerce-license-card yaycommerce-license__getting-started">
							<div class="yaycommerce-license-card-header">
								<h3>Getting Started (How to find license keys)</h3>
							</div>
							<div class="yaycommerce-license-card-body">
								<p>Please follow the steps below:</p>
								<ul class="yaycommerce-license__task-list">
									<li>
										<span>
											<svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#FFF8DB"/><path d="M12.3413 17H14.2583V7.84033H12.3477L9.97998 9.48438V11.2109L12.2271 9.64941H12.3413V17Z" fill="#F1C40F"/></svg>
										</span>
										<span>Log in to <a href="https://yaycommerce.com/dashboard" target="_blank">YayCommerce Dashboard</a></span>
									</li>
									<li>
										<span>
											<svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#FFF8DB"/><path d="M9.24365 17H15.8516V15.4575H11.8145V15.3115L13.5093 13.731C15.1787 12.1885 15.7183 11.3252 15.7183 10.2842V10.2651C15.7183 8.68457 14.3979 7.60547 12.5127 7.60547C10.5068 7.60547 9.13574 8.81152 9.13574 10.5698L9.14209 10.5952H10.9131V10.5635C10.9131 9.69385 11.5288 9.09717 12.4238 9.09717C13.2998 9.09717 13.833 9.64307 13.833 10.4238V10.4429C13.833 11.084 13.4839 11.5474 12.1953 12.7979L9.24365 15.7114V17Z" fill="#F1C40F"/></svg>
										</span>
										<span>Go to <strong>License Keys tab</strong></span>
									</li>
									<li>
										<span>
											<svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#FFF8DB"/><path d="M12.5 17.2031C14.6392 17.2031 16.0674 16.0796 16.0674 14.4165V14.4038C16.0674 13.1597 15.1787 12.3726 13.814 12.2456V12.2075C14.8867 11.9854 15.7246 11.2427 15.7246 10.1001V10.0874C15.7246 8.62744 14.4551 7.63721 12.4873 7.63721C10.564 7.63721 9.27539 8.70361 9.14209 10.3413L9.13574 10.4175H10.9004L10.9067 10.3604C10.9829 9.59863 11.5859 9.10986 12.4873 9.10986C13.3887 9.10986 13.9155 9.57959 13.9155 10.3413V10.354C13.9155 11.0967 13.2935 11.6045 12.3286 11.6045H11.3066V12.9692H12.354C13.4648 12.9692 14.1187 13.4517 14.1187 14.3276V14.3403C14.1187 15.1147 13.4775 15.6606 12.5 15.6606C11.5098 15.6606 10.856 15.1528 10.7734 14.4419L10.7671 14.3721H8.93262L8.93896 14.4546C9.06592 16.0923 10.4307 17.2031 12.5 17.2031Z" fill="#F1C40F"/></svg>
										</span>
										<span>Copy your license key and paste it in this License tab</span>
									</li>
									<li>
										<span>
											<svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="13" r="12" fill="#FFF8DB"/><path d="M13.2046 17H15.0327V15.3179H16.2261V13.7817H15.0327V7.84033H12.3413C11.1226 9.71924 9.87842 11.7505 8.7168 13.8135V15.3179H13.2046V17ZM10.4434 13.8325V13.731C11.2686 12.252 12.2524 10.6587 13.1411 9.29395H13.2427V13.8325H10.4434Z" fill="#F1C40F"/></svg>
										</span>
										<span>Done!</span>
									</li>
								</ul>
							</div>
						</div>
						<div class="yaycommerce-license-card yaycommerce-license__activate-steps">
							<div class="yaycommerce-license-card-header">
								<h3>What you’ll get when activating license:</h3>
							</div>
							<div class="yaycommerce-license-card-body">
								<ul class="yaycommerce-license__task-list">
									<li>
										<span>
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.25C5.16797 1.25 1.25 5.16797 1.25 10C1.25 14.832 5.16797 18.75 10 18.75C14.832 18.75 18.75 14.832 18.75 10C18.75 5.16797 14.832 1.25 10 1.25ZM13.7793 7.14258L9.66602 12.8457C9.60853 12.9259 9.53274 12.9913 9.44493 13.0364C9.35713 13.0815 9.25984 13.1051 9.16113 13.1051C9.06242 13.1051 8.96513 13.0815 8.87733 13.0364C8.78953 12.9913 8.71374 12.9259 8.65625 12.8457L6.2207 9.4707C6.14648 9.36719 6.2207 9.22266 6.34766 9.22266H7.26367C7.46289 9.22266 7.65234 9.31836 7.76953 9.48242L9.16016 11.4121L12.2305 7.1543C12.3477 6.99219 12.5352 6.89453 12.7363 6.89453H13.6523C13.7793 6.89453 13.8535 7.03906 13.7793 7.14258Z" fill="#52C41A"/></svg>
										</span>
										<span>Start using the Pro version</span>
									</li>
									<li>
										<span>
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.25C5.16797 1.25 1.25 5.16797 1.25 10C1.25 14.832 5.16797 18.75 10 18.75C14.832 18.75 18.75 14.832 18.75 10C18.75 5.16797 14.832 1.25 10 1.25ZM13.7793 7.14258L9.66602 12.8457C9.60853 12.9259 9.53274 12.9913 9.44493 13.0364C9.35713 13.0815 9.25984 13.1051 9.16113 13.1051C9.06242 13.1051 8.96513 13.0815 8.87733 13.0364C8.78953 12.9913 8.71374 12.9259 8.65625 12.8457L6.2207 9.4707C6.14648 9.36719 6.2207 9.22266 6.34766 9.22266H7.26367C7.46289 9.22266 7.65234 9.31836 7.76953 9.48242L9.16016 11.4121L12.2305 7.1543C12.3477 6.99219 12.5352 6.89453 12.7363 6.89453H13.6523C13.7793 6.89453 13.8535 7.03906 13.7793 7.14258Z" fill="#52C41A"/></svg>
										</span>
										<span>Auto-update to the latest version</span>
									</li>
									<li>
										<span>
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.25C5.16797 1.25 1.25 5.16797 1.25 10C1.25 14.832 5.16797 18.75 10 18.75C14.832 18.75 18.75 14.832 18.75 10C18.75 5.16797 14.832 1.25 10 1.25ZM13.7793 7.14258L9.66602 12.8457C9.60853 12.9259 9.53274 12.9913 9.44493 13.0364C9.35713 13.0815 9.25984 13.1051 9.16113 13.1051C9.06242 13.1051 8.96513 13.0815 8.87733 13.0364C8.78953 12.9913 8.71374 12.9259 8.65625 12.8457L6.2207 9.4707C6.14648 9.36719 6.2207 9.22266 6.34766 9.22266H7.26367C7.46289 9.22266 7.65234 9.31836 7.76953 9.48242L9.16016 11.4121L12.2305 7.1543C12.3477 6.99219 12.5352 6.89453 12.7363 6.89453H13.6523C13.7793 6.89453 13.8535 7.03906 13.7793 7.14258Z" fill="#52C41A"/></svg>
										</span>
										<span>Get bug fixes and security updates fastest</span>
									</li>
									<li>
										<span>
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.25C5.16797 1.25 1.25 5.16797 1.25 10C1.25 14.832 5.16797 18.75 10 18.75C14.832 18.75 18.75 14.832 18.75 10C18.75 5.16797 14.832 1.25 10 1.25ZM13.7793 7.14258L9.66602 12.8457C9.60853 12.9259 9.53274 12.9913 9.44493 13.0364C9.35713 13.0815 9.25984 13.1051 9.16113 13.1051C9.06242 13.1051 8.96513 13.0815 8.87733 13.0364C8.78953 12.9913 8.71374 12.9259 8.65625 12.8457L6.2207 9.4707C6.14648 9.36719 6.2207 9.22266 6.34766 9.22266H7.26367C7.46289 9.22266 7.65234 9.31836 7.76953 9.48242L9.16016 11.4121L12.2305 7.1543C12.3477 6.99219 12.5352 6.89453 12.7363 6.89453H13.6523C13.7793 6.89453 13.8535 7.03906 13.7793 7.14258Z" fill="#52C41A"/></svg>
										</span>
										<span>Custom CSS & theme tweaks upon requests</span>
									</li>
									<li>
										<span>
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.25C5.16797 1.25 1.25 5.16797 1.25 10C1.25 14.832 5.16797 18.75 10 18.75C14.832 18.75 18.75 14.832 18.75 10C18.75 5.16797 14.832 1.25 10 1.25ZM13.7793 7.14258L9.66602 12.8457C9.60853 12.9259 9.53274 12.9913 9.44493 13.0364C9.35713 13.0815 9.25984 13.1051 9.16113 13.1051C9.06242 13.1051 8.96513 13.0815 8.87733 13.0364C8.78953 12.9913 8.71374 12.9259 8.65625 12.8457L6.2207 9.4707C6.14648 9.36719 6.2207 9.22266 6.34766 9.22266H7.26367C7.46289 9.22266 7.65234 9.31836 7.76953 9.48242L9.16016 11.4121L12.2305 7.1543C12.3477 6.99219 12.5352 6.89453 12.7363 6.89453H13.6523C13.7793 6.89453 13.8535 7.03906 13.7793 7.14258Z" fill="#52C41A"/></svg>
										</span>
										<span>Premium technical support</span>
									</li>
									<li>
										<span>
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.25C5.16797 1.25 1.25 5.16797 1.25 10C1.25 14.832 5.16797 18.75 10 18.75C14.832 18.75 18.75 14.832 18.75 10C18.75 5.16797 14.832 1.25 10 1.25ZM13.7793 7.14258L9.66602 12.8457C9.60853 12.9259 9.53274 12.9913 9.44493 13.0364C9.35713 13.0815 9.25984 13.1051 9.16113 13.1051C9.06242 13.1051 8.96513 13.0815 8.87733 13.0364C8.78953 12.9913 8.71374 12.9259 8.65625 12.8457L6.2207 9.4707C6.14648 9.36719 6.2207 9.22266 6.34766 9.22266H7.26367C7.46289 9.22266 7.65234 9.31836 7.76953 9.48242L9.16016 11.4121L12.2305 7.1543C12.3477 6.99219 12.5352 6.89453 12.7363 6.89453H13.6523C13.7793 6.89453 13.8535 7.03906 13.7793 7.14258Z" fill="#52C41A"/></svg>
										</span>
										<span>Live Chat 1-1 on Facebook for any questions</span>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
	<footer></footer>
</section>

