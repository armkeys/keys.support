<div class="row mb-1">
	<template class="wlm3-form-group">
		{
			type : 'textarea',
			name : 'login_styling_custom_css',
			id : 'login_styling_custom_css_field',
			value : <?php echo json_encode( wp_kses($this->get_option( 'login_styling_custom_css' ),[]) ); ?> || WLM3VARS.custom_login_form_custom_css,
			column : 'col-12',
			group_class : 'mb-3'
		}
	</template>
</div>

<!-- Code Mirror Scripts and styles -->
<?php
wlm_print_script( 'wp-codemirror' );
wlm_print_style( 'wp-codemirror' );
?>
<style type="text/css">
  .CodeMirror { border: 1px solid #ddd; min-height: 380px; }
  .CodeMirror pre { padding-left: 8px; line-height: 1.25; }
</style>
<script type="text/javascript">
var wlm3_cm_editor_login_custom_css;
jQuery(function($) {
	$('#custom-login-styling-nav-tabs a').on('shown.bs.tab', function(e) {
		if(e.target.hash == '#css') {
			if(!wlm3_cm_editor_login_custom_css) {
				wlm3_cm_editor_login_custom_css = wp.CodeMirror.fromTextArea(document.getElementById("login_styling_custom_css_field"), {
					lineNumbers: true,
					mode: "text/css",
					matchBrackets: true
				});
			}
		}
	})
});
</script>

