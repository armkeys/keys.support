<?php
/**
 * Team Accounts Customn CSS View
 *
 * @package WishListMember/Features/TeamAccounts
 */

wlm_print_script( 'wp-codemirror' );
wlm_print_style( 'wp-codemirror' );
?>
<script>
	WLM3VARS.team_default_css = <?php echo wp_json_encode( file_get_contents( __DIR__ . '/css.txt' ) ); ?>
</script>
<div class="content-wrapper">
	<div class="row">
		<template class="wlm3-form-group">
			{
				label: '<?php echo esc_js( __( 'Enable Custom CSS', 'wishlist-member' ) ); ?>',
				type: 'toggle-adjacent',
				name: 'team-accounts/custom-css-enabled',
				value: 1,
				checked: <?php echo wp_json_encode( (bool) wishlistmember_instance()->get_option( 'team-accounts/custom-css-enabled' ) ); ?>,
				column: 'col-12',
				tooltip: '<?php echo esc_js( __( 'If enabled, custom CSS can be added to control the appearance of the Team Management page. The Reset to Default button can be used to restore the original CSS at any time.', 'wishlist-member' ) ); ?>',
			}
		</template>
		<div class="col-12" style="display:none">
			<div class="form-group">
				<textarea id="custom-css" cols="30" rows="18" name="team-accounts/custom-css" class="form-control custom-css" style="height: 300px;"><?php echo esc_textarea( wishlistmember_instance()->get_option( 'team-accounts/custom-css' ) ); ?></textarea>
				<br>
				<a href="#" class="btn -default reset-btn -condensed">
					<i class="wlm-icons">cached</i>
					<span><?php esc_html_e( 'Reset to Default', 'wishlist-member' ); ?></span>
				</a>
			</div>
		</div>
	</div>
	<div class="panel-footer -content-footer d-none">
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
