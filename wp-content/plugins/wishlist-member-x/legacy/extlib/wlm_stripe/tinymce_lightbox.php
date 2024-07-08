<?php
// Levels.
$wpm_levels = $this->get_option( 'wpm_levels' );
?>
<div style='display: none !important;' class='wlmtnmcelbox' id="wlmtnmce-stripe-profile-lightbox">
	<div class="media-modal wp-core-ui" style="display: none !important;">
		<button type="button" class="media-modal-close" href="#" title="Close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></span></button>
		<div class="media-frame-title"><h1>Stripe Profile Page</h1></div>
		<div class="media-modal-content">
			<!-- Main Contend Starts -->
			<div class="wlmtnmcelbox-content">
				<!-- Options -->
				<div class="options-holder">
					<p class="modal-field-label">
						<input type='checkbox' value='1' class='wlmtnmcelbox-showlevels' /> Membership Levels
					</p>
					<p class="modal-field-label">Select Membership Levels that you want to display</p>
					<select class="wlmtnmcelbox-levels" multiple="multiple" data-placeholder=' ' >
					<option value="all">Select All</option>
					<?php foreach ( $wpm_levels as $sku => $level ) : ?>
						<?php if ( is_numeric( $sku ) ) : ?>
							<?php
								$levelname = $level['name'];
								$levelname = str_replace( '%', '&#37;', $levelname );
							?>
							<option value="<?php echo $sku; ?>"><?php echo wlm_trim( $levelname ); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
					</select>
					<p class="modal-field-label">&nbsp;</p>
					<p class="modal-field-label">
						<input type='checkbox' value='1' class='wlmtnmcelbox-includepost' /> Include Pay-Per-Posts
					</p>
				</div>
				<!-- Options Ends -->

				<!-- Preview -->
				<div class="wlmtnmcelbox-preview">
					<div class="wlmtnmcelbox-preview-msg" >
						<input tab="display_details" type="button" class="button button-primary wlmtnmcelbox-insertcode" value="<?php esc_html_e( 'Insert Mergecode', 'wishlist-member' ); ?>" />
						Shortcode Preview:
					</div>
					<textarea class="wlmtnmcelbox-preview-text"></textarea>
				</div>
				<!-- Preview Ends -->
			</div>
			<!-- Main Contend Ends -->
		</div>

	</div>
	<div class="media-modal-backdrop" style="display: none !important;"></div>
</div>
<?php
// Enqueue the js.
wp_enqueue_script(
	'wishlistmember-tinymce-stripe-lightbox-js', // handle
	plugins_url( 'js/tinymce_lightbox.js', __FILE__ ), // url
	array( 'wishlistmember-shortcoder-inserter-lightbox-js' ), // dependency
	wishlistmember_instance()->version // version
);
?>
