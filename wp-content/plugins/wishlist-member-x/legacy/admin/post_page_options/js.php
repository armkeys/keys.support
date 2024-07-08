<?php
/**
 * JS File for Post Page Options
 *
 * @package WishListMember/Legacy/PostPageOptions
 */

if ( ! $post ) {
	return;
}
?>
<!-- enable Post type protection -->
<script type="text/javascript">
	jQuery('body').on('click', '#wlm3_enable_custom_post_type', function(e) {
		e.preventDefault();
		var data = {
			"<?php echo esc_js( $ptype ); ?>" : 1,
			action : 'admin_actions',
			WishListMemberAction : 'enable_custom_post_types'
		}

		jQuery.post(
			ajaxurl,
			data,
			function(result) {
				result = wlm.json_parse(result);
				if(result.success) {
					jQuery('.wlm-plugin-inside').show();
					jQuery('.wlm-custom-post-type-disabled').hide();
				}
			}
		);
		return false;
	});
</script>
<!-- new js -->
<script type="text/javascript">
	var wlm_post_id   = <?php echo wp_json_encode( $post->ID ); ?>;
	var wlm_post_type = <?php echo wp_json_encode( $post->post_type ); ?>;
	var wlm_displayed_tab = <?php echo wp_json_encode( $this->get_option( 'wlm3_postpage_displayed_tab' ) ); ?>;

	var toggle_markup = '<div class="switch"><div class="switch-left">';
	toggle_markup += '<div class="form-group"><label class="switch-light switch-wlm">';
	toggle_markup += '<input name="_toggle_name_" value="1" type="checkbox" _toggle_checked_><span>';
	<?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase ?>
	toggle_markup += '<span><img src="<?php echo esc_url( $this->plugin_url3 ); ?>/ui/images/outline-check-24px.svg" alt="">';
	<?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase ?>
	toggle_markup += '</span><span><img src="<?php echo esc_url( $this->plugin_url3 ); ?>/ui/images/baseline-close-24px.svg" alt="">';
	toggle_markup += '</span><a target="_parent"></a></span></label></div></div></div>';

	var wlm3_page_templates = <?php echo wp_json_encode( $this->page_templates ); ?>

	jQuery(document).ready(function() {

		// Meta box reloading for WP 5.5.
		// Since WishList Member 3.7.
		if( typeof wp.data != 'undefined' ) {
			var editPost = wp.data.select( 'core/edit-post' ), lastIsSaving = false;
			if( editPost ) {
				wp.data.subscribe( function() {
					var isSaving = editPost.isSavingMetaBoxes();
					if ( isSaving !== lastIsSaving && !isSaving ) {
						lastIsSaving = isSaving;
							jQuery.post(
								ajaxurl,
								{
									action: 'wlm3_update_postbox',
									post_id: wlm_post_id,
								},
								function(r) {
									if( r != '' ) {
										// Parse html.
										var html = jQuery(r);
										// Update parent ID.
										var parent_sel = ':input[name="wlm_old_post_parent"]';
										if(html.find(parent_sel).length) {
											jQuery(parent_sel).val(html.find(parent_sel).val());
										}
										// Updated inherited selection.
										var inherited_sel ='#protection-settings-inherited';
										if(html.find(inherited_sel + ':checked').length) {
											jQuery(inherited_sel).click();
										}
										jQuery('#wpm-access-inherited').html(html.find('#wpm-access-inherited').html());
									}
								}
							)
					}

					lastIsSaving = isSaving;
				} );
			}
		}

		// Button handler to pass protection to existing posts.
		// Since WishList Member 3.7.
		// Show confirmation.
		jQuery('#pass-protection-to-existing').click(function(e){
			e.preventDefault();
			jQuery('#pass-content-protection .notif2').hide();
			jQuery('#pass-content-protection .notif2').hide();
			jQuery('#pass-protection-confirmation').show();
		});
		// Cancel pass protection.
		jQuery('#pass-protection-to-existing-cancel').click(function(e){
			e.preventDefault();
			jQuery('#pass-protection-confirmation').hide();
		});
		// Confirm pass protection.
		jQuery('#pass-protection-to-existing-confirm').click(function(e){
			e.preventDefault();
			jQuery('#pass-content-protection .notif1').show();
			jQuery('#pass-protection-to-existing').addClass('-disable');
			post_id = jQuery(this).data('postid');
			jQuery.post(
				ajaxurl,
				{
					action: 'wlm3_save_postpage_pass_protection',
					post_id: post_id
				},
				function() {
					jQuery('#pass-content-protection .notif1').hide();
					jQuery('#pass-content-protection .notif2').show().delay(4000).fadeOut(500);
					jQuery('#pass-protection-confirmation').fadeOut(500);
					jQuery('#pass-protection-to-existing').removeClass('-disable');
				}
			)
		});


		function wlm_editor(t_id) {
			try {
			wp.editor.remove(t_id);
			wp.editor.initialize(t_id);
			} catch( e ) {
			wp.oldEditor.remove(t_id);
			wp.oldEditor.initialize(t_id);
			}
		}
	jQuery('[name="protection_settings"]').change(function() {
		if(!jQuery(this).is(':checked')) return;
		switch(parseInt(jQuery(this).val())) {
			case 0:
				jQuery('[name="wpm_protect"]').val('N');
				jQuery('[name="wlm_inherit_protection"]').val('');
				jQuery('#wpm-access-options').hide();
				jQuery('#wpm-access-form').hide();
				jQuery('#wpm-access-inherited').hide();
				jQuery('#wpm-payperpost-protected').hide();
				jQuery('#wpm-payperpost-unprotected').show();
				break;
			case 1:
				jQuery('[name="wpm_protect"]').val('Y');
				jQuery('[name="wlm_inherit_protection"]').val('');
				jQuery('#wpm-access-options').show();
				jQuery('#wpm-access-form').show();
				jQuery('#wpm-access-inherited').hide();
				jQuery('#wpm-payperpost-protected').show();
				jQuery('#wpm-payperpost-unprotected').hide();
				break;
			case 2:
				jQuery('[name="wpm_protect"]').val('<?php echo $wpm_protect ? 'Y' : 'N'; ?>');
				jQuery('[name="wlm_inherit_protection"]').val('Y');
				jQuery('#wpm-access-options').show();
				jQuery('#wpm-access-form').hide();
				jQuery('#wpm-access-inherited').show();
				jQuery('#wpm-payperpost-protected').toggle(<?php echo wp_json_encode( (bool) $parent_protect ); ?>);
				jQuery('#wpm-payperpost-unprotected').toggle(<?php echo wp_json_encode( ! ( (bool) $parent_protect ) ); ?>);

				break;
		}
	}).change();

	jQuery('#wlm3-ppp-modal-button').click(function() {
		tb_show(jQuery(this).attr('name'), jQuery(this).attr('href'));
		jQuery('#TB_window').addClass('wlm3-thickbox');
		jQuery('#wlm3-ppp-search-button').click();
		return false;
	});

		jQuery('[name="wpm_access[]"]').change(function() {
			var $options = jQuery('[name="wpm_access[]"] option').length;
			var $options_selected = jQuery('[name="wpm_access[]"] option:selected').length;
			if($options && $options_selected == $options) {
				jQuery('#select-all-levels').hide();
				jQuery('#clear-all-levels').show();
			} else {
				jQuery('#select-all-levels').show();
				jQuery('#clear-all-levels').hide();
			}
		}).change();
	jQuery('#select-all-levels, #clear-all-levels').click(function() {
		jQuery('[name="wpm_access[]"] option').prop('selected', this.id == 'select-all-levels');
		jQuery('[name="wpm_access[]"]').change();
		return false;
	});

	jQuery('[name="wlm_payperpost"]').change(function() {
		if(!jQuery(this).is(':checked')) return;
		switch(jQuery(this).val()) {
			case 'Y':
				jQuery('#wlm_payperpost_enable').show();
				break;
			case 'N':
				jQuery('#wlm_payperpost_enable').hide();
				break;
		}
	}).change();

	jQuery('[name^="pass_content_protection"]').change(function() {
		if(!jQuery(this).is(':checked')) return;
		switch(jQuery(this).val()) {
			case 'Y':
				jQuery('#pass-content-protection').show();
				break;
			case 'N':
				jQuery('#pass-content-protection').hide();
				break;
		}
	}).change();

	jQuery('[name="wlm_payperpost_free"]').change(function() {
		if(!jQuery(this).is(':checked')) return;
		switch(jQuery(this).val()) {
			case 'Y':
				jQuery('#wlm_payperpost_free_url').show();
				break;
			case 'N':
				jQuery('#wlm_payperpost_free_url').hide();
				break;
		}
	}).change();

	jQuery('.wlm-postpage-apply').click(function() {
		var $btn = jQuery(this);
		var $saved = jQuery(this).parent().find('.wlm-saved');
		var $saving = jQuery(this).parent().find('.wlm-saving');
		$btn.addClass('-disable').prop('disabled', true);
		$saving.show();
		var data = jQuery('#wlm_postpage_metabox :input').serialize();
		data += '&post_ID=' + wlm_post_id;
		data += '&post_type=' + wlm_post_type;
		data += '&action=wlm3_save_postpage_settings';

		jQuery.post( ajaxurl, data ).always( function() {
			$btn.removeClass('-disable').prop('disabled', false);
			$saving.hide();
			$saved.show().delay(4000).fadeOut(500);
		} );
		return false;
	});

		jQuery('#wlm_user_search_input').keypress(function(event) {
			if (event.which == 13) {
				event.preventDefault();
			}
		});

		jQuery('#wlm_user_search_by').change(function() {
			jQuery('.wlm_search_types_field').hide();
			jQuery('#wlm_search_' + jQuery(this).val()).show();
		});

		jQuery('#wlm3-ppp-search-button').click(function(){
			var $search_by = jQuery('#wlm_user_search_by').val();
			var $search = 'by_user' === $search_by ? jQuery('#wlm_user_search_input').val() : jQuery('#wlm_level_search_input').val();


			var xdata = {
				action: 'wlm3_post_page_ppp_user_search',
				search: $search,
				ppp_access: jQuery('#wlm_user_access').val(),
				ppp_id: <?php echo esc_js( $post->ID ); ?>,
				page: jQuery('#wlm3-pagination-page').val(),
				number: jQuery('#wlm3-pagination-number').val(),
				search_by: $search_by,
			};

			jQuery.post(
				ajaxurl,
				xdata,
				function(result) {
					var _html;
					var $tbody = jQuery('#wlm_payperpost_table tbody');
					if(result.total_users && !result.users.length) {
						// Reset pagination.
						jQuery('#wlm3-pagination-page').val('1');
						jQuery('#wlm3-ppp-search-button').click();
						return;
					}
					if(result.users.length) {
						$tbody.empty();
						jQuery.each(result.users, function(index, user) {
							var has_access = result.contentlevels.indexOf('U-' + user.ID) > -1;
							_html = '<tr class="' + (has_access ? 'wlm3-has-ppp' : '') + '" data-userid="_id_"><td>_id_</td><td><span title="_name_">_name_</span></td><td><span title="_login_">_login_</span></td><td><span title="_email_">_email_</span></td><td style="text-align:center">_toggle_markup_</td></tr>';
							_html = _html.replace(/_id_/g, user.ID);
							_html = _html.replace(/_name_/g, user.display_name);
							_html = _html.replace(/_login_/g, user.user_login);
							_html = _html.replace(/_email_/g, user.user_email);
							_html = _html.replace(/_toggle_markup_/g, toggle_markup);
							_html = _html.replace(/_toggle_name_/g, 'payperpost_toggle');
							_html = _html.replace(/_toggle_checked_/g, (has_access ? 'checked="checked"' : ''));
							$tbody.append(_html);
						});

						hide = '';
						if(xdata.page < 2) hide = 'prev';
						if(xdata.page >= result.total_users / xdata.number) hide = 'next';
						if(result.total_users <= xdata.number) hide = 'both';

						jQuery('a[href="#_wlm3-ppp-prev"], a[href="#_wlm3-ppp-next"]').show();
						if(hide) {
							if(hide == 'prev' || hide == 'both') {
								jQuery('a[href="#_wlm3-ppp-prev"]').hide();
							}
							if(hide == 'next' || hide == 'both') {
								jQuery('a[href="#_wlm3-ppp-next"]').hide();
							}
						}

						jQuery('.wlm3-pagination-total').text(result.total_users);

						jQuery('#wlm3-pagination-from').text( xdata.number * xdata.page - (xdata.number - 1) );
						var to = xdata.page * xdata.number;
						if( to > result.total_users ) to = result.total_users;
						jQuery('#wlm3-pagination-to').text( to );

						jQuery('#wlm3-pagination').show();

					} else {
						jQuery('#wlm3-pagination').hide();
						$tbody.html('<tr><td colspan="5"><p><?php esc_html_e( 'No results found', 'wishlist-member' ); ?></p></td></tr>');
					}
				}
			);
		});

		// Add/remove pay per post.
		jQuery('body').on('change', ':checkbox[name="payperpost_toggle"]', function() {
			var $row = jQuery(this).closest('tr');
			var $btn = jQuery(this);
			var _action = this.checked ? 'wlm3_add_user_ppp' : 'wlm3_remove_user_ppp';
			if(this.checked) {
				$row.addClass('wlm3-has-ppp');
			} else {
				$row.removeClass('wlm3-has-ppp');
			}
			$row.addClass('-saving');
			$btn.closest('.switch').addClass('-saving');
			jQuery.post(
				ajaxurl,
				{
					action: _action,
					user_id: $row.data('userid'),
					content_id: wlm_post_id
				}
			).always(function() {
				$row.removeClass('-saving');
				$btn.closest('.switch').removeClass('-saving');
			});
		});

		jQuery('body').on('click', '#wlm3-pagination a', function() {
			var hash = jQuery(this).attr('href').substring(2);
			var page = Math.abs(jQuery('#wlm3-pagination-page').val());
			switch(hash) {
				case 'wlm3-ppp-prev':
					page--;
					jQuery('#wlm3-pagination-page').val(page);
					jQuery('#wlm3-ppp-search-button').click();
				break;
				case 'wlm3-ppp-next':
					page++;
					jQuery('#wlm3-pagination-page').val(page);
					jQuery('#wlm3-ppp-search-button').click();
				break;
				case '':
				break;
				default:
					jQuery('#wlm3-pagination-page').val('1');
					jQuery('#wlm3-pagination-number').val(hash);
					jQuery('#wlm3-ppp-search-button').click();
			}
			return false;
		});

		jQuery('body').click(function() {
			jQuery('.dropdown-toggle').removeClass('-open');
		});

		jQuery('body').on('click', '#wlm3-drop-page', function() {
			$('.dropdown-toggle').toggleClass('-open');
		});

		jQuery('body').on('change', '.wlm3_system_page_types', function() {
			if(!this.checked) return;
			var $modal = jQuery(this).closest('.system-pages-modal');
			$modal.find('.wlm3-system-pages').hide();
			var v = jQuery(this).val();
			var t = jQuery(this).closest('ul').data('pagetype');
			jQuery('#wlm3-' + t + '-' + v).show();
		});

		jQuery('.system-page-config').click(function() {
		tb_show(jQuery(this).attr('name'), jQuery(this).attr('href'));
		jQuery('#TB_window').addClass('wlm3-thickbox');
		var t_id = jQuery('[data-pagetype="' + jQuery(this).data('target') + '"] textarea')[0].id;
		wlm_editor(t_id);
		return false;
		});

		jQuery('.wlm3-create-systempage').click(function() {
			var $modal = jQuery(this).closest('.system-pages-modal');
			var _page_title = $modal.find('.wlm3-create-systempage-title').val();
			var $select = $modal.find('.wlm3-system-page-dropdown');
			var _page_for = $modal.data('pagetype');
			jQuery.post(
				ajaxurl,
				{
					action : 'admin_actions',
					WishListMemberAction : 'create_system_page',
					page_title: _page_title,
					page_for: _page_for
				}
			).always(function(result) {
				result = wlm.json_parse(result);
				if(result.success){
					$select.append('<option value="' + result.post_id + '">' + result.post_title + '</option>');
					$select.val(result.post_id);
					$select.change();
					$modal.find('.wlm3-create-page').toggle();
				} else {
					alert(result.msg);
				}
			});
			return false;
		});

		jQuery('.wlm3-show-add-page').click(function() {
			jQuery(this).closest('.system-pages-modal').find('.wlm3-create-page').toggle();
		});

	jQuery('.system-page-url').blur(function() {
		if (!/^(http|https):\/\//.test(this.value)) {
			this.value = "http://" + this.value;
		}
	});

		jQuery('body').on('click', '.wlm3-save-system-page', function() {
			window.tinymce.triggerSave();
			var $this = jQuery(this);
			var x = $this.closest('.system-pages-modal');
			var pagetype = x.data('pagetype');
			var fields = x.find(':input').serialize();
			fields += '&ptype=' + pagetype;
			fields += '&post_id=' + wlm_post_id;
			fields += '&action=wlm3_save_postpage_system_page';

			jQuery('.wlm3-save-icon').toggle();
			jQuery('.wlm3-save-system-page').toggleClass('-disable');
			jQuery('.wlm3-modal-loader-overlay-holder').show();

			jQuery.post(
				ajaxurl,
				fields,
				function(result) {

				}
			).always( function () {
				jQuery('.wlm3-save-icon').toggle();
				jQuery('.wlm3-save-system-page').toggleClass('-disable');
				jQuery('.wlm3-save-system-page').removeProp('disabled');
				jQuery('.wlm3-modal-loader-overlay-holder').hide();
			});

			if($this.hasClass('-close')) {
				tb_remove();
			}
		});

		jQuery('.wlm3-shortcodes').change(function() {
			var $select = jQuery(this);
			var text_to_insert = $select.val();
			if(!text_to_insert) return;
			$select.val('').change();
			var $modal = jQuery(this).closest('.system-pages-modal');
			var ptype = $modal.data('pagetype');
			var id = ptype + '_message_mce';

			var target = jQuery('#' + id)[0];
			if (document.selection) {
				//For browsers like Internet Explorer.
				target.focus();
				var sel = document.selection.createRange();
				sel.text = text_to_insert;
				target.focus();
			} else if (target.selectionStart || target.selectionStart == '0') {
				//For browsers like Firefox and Webkit based.
				var startPos = target.selectionStart;
				var endPos = target.selectionEnd;
				var scrollTop = target.scrollTop;
				target.value = target.value.substring(0, startPos) + text_to_insert + target.value.substring(endPos, target.value.length);
				target.focus();
				target.selectionStart = startPos + text_to_insert.length;
				target.selectionEnd = startPos + text_to_insert.length;
				target.scrollTop = scrollTop;
			} else {
				target.value += text_to_insert;
				target.focus();
			}
			var editor = window.tinymce.get(id);
			if(editor !== null) {
				editor.setContent(target.value);
			}
		});

		jQuery('.wlm3-reset-message').click(function() {
			var $modal = jQuery(this).closest('.system-pages-modal');
			var ptype = $modal.data('pagetype');
			var id = ptype + '_message_mce';
			var value = wlm3_page_templates[ptype + '_internal'];
			jQuery('#' + id).val(value);
			var editor = window.tinymce.get(id);
			if(editor !== null) {
				editor.setContent(value);
			}
			return false;
		});

		// Select2.
	jQuery('.wlm-select').wlmselect2({ theme: "default wlm3-select2" });
		if( wlm_displayed_tab ) {
			window.setTimeout(function() {
				jQuery('a[data-target="' + wlm_displayed_tab + '"]').click();
			}, 1000)

		}
	});

	jQuery(document).ready(function() {
		jQuery('.wlm-datetimepicker').daterangepicker({
			singleDatePicker: true,
			timePicker: true,
			showCustomRangeLabel: false,
			startDate: moment(),
			buttonClasses: "btn -default",
			applyClass: "-success",
			cancelClass: "-bare",
			autoUpdateInput: false,
			drops: 'up',
			locale: {
				format: "<?php echo esc_js( $this->php2js_date_format( get_option( 'date_format' ) ) . ' ' . $this->php2js_date_format( get_option( 'time_format' ) ) ); ?>"
			}
		});
		jQuery('.wlm-datetimepicker').on('apply.daterangepicker', function(ev, picker) {
			jQuery(this).val(picker.startDate.format("<?php echo esc_js( $this->php2js_date_format( get_option( 'date_format' ) ) . ' ' . $this->php2js_date_format( get_option( 'time_format' ) ) ); ?>"));
		});
	});
</script>

<!-- old js -->
<script type="text/javascript">
jQuery(function($) {
	$('.wpm_toggleAllLevels').on('change', function(ev) {
		$('.allLevels').not('.allindex').prop('checked', $(this).prop('checked'));
	});
	var wlm_radioshack = '';
	function wlm_save_radios() {
		wlm_radioshack = $('input[type=radio][name=wpm_protect]:checked').val();
	}
	function wlm_restore_radios() {
		$('input[type=radio][name=wpm_protect][value=' + wlm_radioshack + ']').prop('checked', 'checked');
	}
	$('.meta-box-sortables').sortable({beforeStop: wlm_save_radios, stop: wlm_restore_radios});
	$('.wlm_inherit_protection').on('change', function(ev) {
		var prop = $(this).prop('checked');
		$('.wlm_inherit_toggle input[type=radio],.wlm_inherit_toggle input[type=checkbox]').not('.allindex').prop('disabled', prop);
	});
	$('.wlm_inherit_protection').trigger('change');
	$('body').on('click', 'a.wlm-inside-toggle', function(e) {
		e.preventDefault();
		$('.wlm-plugin-sidebar li').removeClass('active');
		$('.wlm-inside').hide();
		$(this).closest('li').addClass('active');
		$($(this).data('target')).show();
		$(this).blur();

		var data = {
			action : 'wlm3_save_postpage_displayed_tab',
			target : $(this).data('target')
		}
		$.post( ajaxurl, data );

		$('#wlm_postpage_metabox h2.hndle span').html(': ' + $(this).text());
		return false;
	});
	$('a.wlm-inside-toggle').first().click();
});
</script>
