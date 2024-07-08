jQuery( document ).ready(
	function () {

		// Scheduler: Show On/After Toggle
		jQuery('.scheduler-toggle-radio-sched').click(function () {
			
			var value = jQuery(this).val();
			var holder = jQuery(this).closest('tr');
			if (value == 'ondate') {
				jQuery(holder).find(".scheduler-show-ondate-holder").show();
				jQuery(holder).find(".scheduler-show-after-holder").hide();
			} else {
				jQuery(holder).find(".scheduler-show-after-holder").show();
				jQuery(holder).find(".scheduler-show-ondate-holder").hide();
			}
		});

		jQuery('.scheddays').each(
			function(i,el){
				if ( jQuery( this ).val() <= 0 ) {
					jQuery( this ).val( "" );
					jQuery( this ).parent().parent().find( ".hidedays" ).val( "" );
					jQuery( this ).parent().parent().find( ".hidedays" ).addClass( '-disable' ).prop( "disabled", true );
				} else {
					jQuery( this ).parent().parent().find( ".hidedays" ).removeClass( '-disable' ).prop( "disabled", false );
				}
			}
		);

		jQuery( '.scheddays' ).change(
			function() {
				if ( jQuery( this ).val() <= 0 ) {
					jQuery( this ).val( "" );
					jQuery( this ).parent().parent().find( ".hidedays" ).val( "" );
					jQuery( this ).parent().parent().find( ".hidedays" ).addClass( '-disable' ).prop( "disabled", true );
				} else {
					jQuery( this ).parent().parent().find( ".hidedays" ).removeClass( '-disable' ).prop( "disabled", false );
				}
			}
		);

		jQuery( '.wlm-scheduler-save' ).click(
			function() {
				var $btn     = jQuery( this );
				var $message = jQuery( this ).parent().find( '.wlm-message' );
				$btn.addClass( '-disable' ).prop( 'disabled', true );
				$message.html( "Saving..." ).css('color', 'inherit').show();
				var data = jQuery( '.wlm-inside-scheduler :input' ).serialize();
				data    += '&contentids=' + wlm_post_id;
				data    += '&action=admin_actions';
				data    += '&WishListMemberAction=set_content_schedule';
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

						$btn.removeClass( '-disable' ).prop( 'disabled', false );
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
