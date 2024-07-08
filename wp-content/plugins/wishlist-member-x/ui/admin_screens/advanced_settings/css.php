<?php
	$wlm_css = $this->get_option( 'wlm_css' );
if ( false === $wlm_css ) {
	require $this->legacy_wlm_dir . '/core/InitialValues.php';

	$wlm_css = '';

	// Registration form CSS.
	$css = $this->get_option( 'reg_form_css' );
	if ( false === $css ) {
		if ( isset( $wishlist_member_initial_data['reg_form_css'] ) ) {
			$wlm_css .= $wishlist_member_initial_data['reg_form_css'] . "\n";
		}
	} else {
		$wlm_css .= $css;
	}

	// Sidebar widget CSS.
	$css = $this->get_option( 'sidebar_widget_css' );
	if ( false === $css ) {
		if ( isset( $wishlist_member_initial_data['sidebar_widget_css'] ) ) {
			$wlm_css .= $wishlist_member_initial_data['sidebar_widget_css'] . "\n";
		}
	} else {
		$wlm_css .= $css;
	}

	// Login mergecode CSS.
	$css = $this->get_option( 'login_mergecode_css' );
	if ( false === $css ) {
		if ( isset( $wishlist_member_initial_data['login_mergecode_css'] ) ) {
			$wlm_css .= $wishlist_member_initial_data['login_mergecode_css'] . "\n";
		}
	} else {
		$wlm_css .= $css;
	}
}
wlm_print_script( 'wp-codemirror' );
wlm_print_style( 'wp-codemirror' );
?>
<script>
	var editor = null;
	<?php
	if ( isset( wlm_post_data()['reset_custom_css'] ) ) {
		$msg = __( 'CSS has been reset back to Default', 'wishlist-member' );
		echo '$(".wlm-message-holder").show_message({message:' . esc_js( $msg ) . '});';
	}
	?>
</script>
<style>
  .CodeMirror { border: 1px solid #ddd; }
  .CodeMirror pre { padding-left: 8px; line-height: 1.25; }
</style>

<div class="page-header">
	<div class="row">
		<div class="col-md-9 col-sm-9 col-xs-8">
			<h2 class="page-title">
				<?php esc_html_e( 'Custom CSS', 'wishlist-member' ); ?>
			</h2>
		</div>
		<div class="col-md-3 col-sm-3 col-xs-4">
			<?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="content-wrapper">
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<textarea id="customcss" cols="30" rows="18" class="form-control custom-css" style="height: 300px;"><?php echo esc_textarea( $wlm_css ); ?></textarea>
						<br>
						<a href="#" class="btn -default reset-btn -condensed">
							<i class="wlm-icons">cached</i>
							<span><?php esc_html_e( 'Reset to Default', 'wishlist-member' ); ?></span>
						</a>
					</div>
				</div>
			</div>
			<input type="hidden" name="action" value="admin_actions" />
			<input type="hidden" name="WishListMemberAction" value="save" />
			<div class="panel-footer -content-footer">
				<div class="row">
					<div class="col-lg-12 text-right">
						<a href="#" class="btn -primary save-settings">
							<i class="wlm-icons">save</i>
							<span class="text"><?php esc_html_e( 'Save', 'wishlist-member' ); ?></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="reset-modal" data-id="reset-modal" data-label="reset_modal_label" data-title="Reset Custom CSS" data-classes="modal-lg" style="display:none">
	<div class="body">
		<h5 class="message"><?php esc_html_e( 'Do you want to reset to Default CSS?', 'wishlist-member' ); ?></h5>
	</div>
	<div class="footer">
		<button type="button" class="btn -bare cancel-button" data-dismiss="modal"><?php esc_html_e( 'Cancel', 'wishlist-member' ); ?></button>
		<button type="button" class="btn -primary save-button"><span class="text">Yes</span></button>
	</div>
</div>
