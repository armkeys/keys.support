<?php
/**
 * Wizard JS inline data
 *
 * @package WishListMember/Wizard
 */

?>
<script>
var wlm_wizard = 
<?php
echo wp_json_encode(
	array(
		'permalink_replacements' => array(
			'%year%'     => wp_date( 'Y' ),
			'%monthnum%' => wp_date( 'm' ),
			'%day%'      => wp_date( 'd' ),
			'%hour%'     => wp_date( 'H' ),
			'%minute%'   => wp_date( 'i' ),
			'%second%'   => wp_date( 's' ),
			'%post_id%'  => 123,
			'%postname%' => 'sample-post',
			'%category%' => 'sample-category',
			'%author%'   => 'author-login',
		),

		'site_url'               => site_url(),

		// Steps.
		'steps'                  => count( $steps ),
		'step_titles'            => $step_titles,
		'current_step'           => 1,

		// Nonce.
		'noncekey'               => 'wlm-wizard-nonce',
		'nonce'                  => wp_create_nonce( 'wlm-wizard_' . site_url() ),
	)
);
?>
;
</script>
