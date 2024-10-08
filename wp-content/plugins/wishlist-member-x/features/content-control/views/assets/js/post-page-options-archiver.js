jQuery( document ).ready(
	function() {
		jQuery( '.wlm-archiver-save' ).click(
			function() {
				var $btn     = jQuery( this );
				var $message = jQuery( this ).parent().find( '.wlm-message' );
				$btn.addClass( '-disable' ).prop( 'disabled', true );
				$message.html( "Saving..." ).css('color', 'inherit').show();
				var data = jQuery( '.wlm-inside-archiver :input' ).serialize();
				data    += '&contentids=' + wlm_post_id;
				data    += '&action=admin_actions';
				data    += '&WishListMemberAction=set_content_archive';
				data    += '&post_option=1';

				jQuery.post(
					ajaxurl,
					data,
					function( result ) {
						var result_data = "";
						if ( result != 0 || result != "" ) {
							// try parsing result
							try {
								result_data = wlm.json_parse( result );
							} catch (e) {
								result_data = result;
							}
						}

						$btn.removeClass('-disable').prop('disabled', false);
						if ( result_data.success ) {
							$message.html(result_data.msg).css('color', 'green').delay( 10000 ).fadeOut( 500 );
						} else {
							$message.html(result_data.msg).css('color', 'red').delay( 10000 ).fadeOut( 500 );
						}
					}
				);
				return false;
			}
		);
	}
);
