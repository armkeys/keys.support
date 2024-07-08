(function($) {
	/**
	 * Email validation function.
	 * @param {string} email
	 * @returns {boolean}
	 */
	function validate_email(email) {
		// contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
		return  /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(email);
	}
	/**
	 * Check if validati
	 * on ius required.
	 * @param {string} value
	 * @returns {boolean}
	 */
	function validate_required(value) {
		return $.trim(value).length > 0
	}

	$.fn.extend({
		forms: null,
		options: null,
		PopupRegForm: function(options) {
			var defaults = {
				skip_all_validations: false,
				validate_cvc: true,
				validate_exp: true,
				validate_ccnumber: true,
				validate_email: true,
				validate_first_name: true,
				validate_last_name: true,
				on_validate_success: function(form, fields, ui) { return true; },
				on_validate_error: function(form, fields, ui) { return true; },
			};
			var options  =  $.extend(defaults, options);
			var elements = $(this);
			var buttons  = elements.find('button');
			var self     = this;
			this.forms   = elements;
			this.options = options;

			buttons.click(function(ev) {
				$(this).prop('disabled', true);

				var i          = buttons.index($(this));
				var submit_frm = self.forms.eq(i);
				var ui = self.forms.eq(i).parents('.regform');


				fields = {
					card_number: submit_frm.find('.regform-cardnumber'),
					cvc: submit_frm.find('.regform-cvc'),
					exp_month: submit_frm.find('.regform-expmonth'),
					exp_year: submit_frm.find('.regform-expyear'),
					email: submit_frm.find('.regform-email'),
					first_name: submit_frm.find('.regform-first_name'),
					last_name: submit_frm.find('.regform-last_name'),
					address: submit_frm.find('.regform-address'),
					city: submit_frm.find('.regform-city'),
					zip: submit_frm.find('.regform-zip'),
					payment_plan: submit_frm.find('.regform-payment_plan'),
				};

				var status = options['skip_all_validations']? true : self.validate_fields(fields);

				if(status == true) {
					status = options['on_validate_success'](self.forms.eq(i), fields, ui);
				} else {
					options['on_validate_error'](self.forms.eq(i), fields, ui);
				}

				if(status === true) {
					//prevent multiple
					submit_frm.find('.regform-waiting').show();
					submit_frm.submit();
				} else {
					submit_frm.find('.regform-button').prop('disabled', false);
				}
				return false;
			});
		},
		validate_fields: function(fields) {
			var all_status = true;
			if(this.options.validate_first_name) {
				var status = validate_required(fields.first_name.val());
				all_status = status && all_status;
				if(status === true) {
					fields.first_name.removeClass("error_input");
				} else {
					fields.first_name.addClass("error_input");
				}
			}

			if(this.options.validate_last_name) {
				var status = validate_required(fields.last_name.val());
				all_status = status && all_status;
				if(status === true) {
					fields.last_name.removeClass("error_input");
				} else {
					fields.last_name.addClass("error_input");
				}
			}

			if(this.options.validate_first_name) {
				status = validate_email(fields.email.val());
				all_status = status && all_status;
				if(status === true) {
					fields.email.removeClass("error_input");
				} else {
					fields.email.addClass("error_input");
				}
			}

			if ( fields.payment_plan ) {
				var status = fields.payment_plan.val() != "";
				all_status = status && all_status;
				if(status === true) {
					fields.payment_plan.removeClass("error_input");
				} else {
					fields.payment_plan.addClass("error_input");
				}
			}

			if ( fields.address ) {
				var status = fields.address.val() != "";
				all_status = status && all_status;
				if(status === true) {
					fields.address.removeClass("error_input");
				} else {
					fields.address.addClass("error_input");
				}
			}

			if ( fields.city ) {
				var status = fields.city.val() != "";
				all_status = status && all_status;
				if(status === true) {
					fields.city.removeClass("error_input");
				} else {
					fields.city.addClass("error_input");
				}
			}

			if ( fields.state ) {
				var status = fields.state.val() != "";
				all_status = status && all_status;
				if(status === true) {
					fields.state.removeClass("error_input");
				} else {
					fields.state.addClass("error_input");
				}
			}

			if ( fields.zip ) {
				var status = fields.zip.val() != "";
				all_status = status && all_status;
				if(status === true) {
					fields.zip.removeClass("error_input");
				} else {
					fields.zip.addClass("error_input");
				}
			}

			if(this.options.validate_ccnumber) {
				status = Stripe.card.validateCardNumber(fields.card_number.val());
				all_status = status && all_status;
				if(status === true) {
					fields.card_number.removeClass("error_input");
				} else {
					fields.card_number.addClass("error_input");
				}
			}

			if(this.options.validate_exp) {
				status = Stripe.card.validateExpiry(fields.exp_month.val(), "20" + fields.exp_year.val());
				all_status = status && all_status;
				if(status === true) {
					fields.exp_month.removeClass("error_input");
					fields.exp_year.removeClass("error_input");
				} else {
					fields.exp_month.addClass("error_input");
					fields.exp_year.addClass("error_input");
				}
			}

			if(this.options.validate_cvc) {
				status = Stripe.card.validateCVC(fields.cvc.val());
				all_status = status && all_status;
				if(status === true) {
					fields.cvc.removeClass("error_input");
				} else {
					fields.cvc.addClass("error_input");
				}
			}



			return all_status;
		}

	});


})(jQuery);


//application
jQuery(function($) {
	/**
	 * Update form adjustments.
	 *
	 * @param {object} form Form object.
	 * @param {string} type Adjusment type.
	 * @param {float} amount Amount to adjust.
	 * @param {string} description Description of the adjustment.
	 */
	function wlm_stripe_price_adjustments( form, type, amount, description ) {
		if( typeof form != 'object' || typeof type != 'string') {
			return;
		}
		if( typeof amount != 'number' || typeof description != 'string') {
			return;
		}

		form.data('show_summary', form.find('.wlm-regform-order-summary').length );
		form.data('adjustments') || form.data('adjustments',{});
		var adjustments = form.data('adjustments') || {};
		if( !amount ) {
			delete adjustments[type];
		} else {
			adjustments[type] = {
				type: type,
				amount: amount,
				description: description
			}
		}
		form.data('adjustments', adjustments);

		var adjustment = 0;
		var discount = 0;
		var groups = {};
		var summary = form.find('.wlm-regform-order-summary').first();
		var summary_container = form.find('.wlm-regform-order-summary-container').first();
		var due = parseFloat(summary_container.data('amount'));
		var btn = form.find('button.regform-button');

		Object.keys(adjustments).sort().forEach(function(key) {
			if(key.match(/coupon_percent_off$/)) {
				if('0-prorate-new' in adjustments) {
					discount = parseFloat(adjustments[key].amount) * (parseFloat(summary_container.data('amount')) + parseFloat(adjustments['0-prorate-new'].amount) );
				} else {
					discount = parseFloat(adjustments[key].amount) * parseFloat(summary_container.data('amount'));
				}
				if(discount > 0) {
					discount = 0;
				}
				adjustment += discount;
			} else {
				discount = parseFloat(adjustments[key].amount);
				adjustment += discount;
			}

			// Group by label and sum up the amounts.
			if('number' != typeof groups[adjustments[key].description]) {
				groups[adjustments[key].description] = 0;
			}
			groups[adjustments[key].description] += discount;
		});

		due += adjustment;
		due = due < 0 ? 0 : due;

		if(form.data('show_summary')) {
			summary.find('p.wlm-regform-order-summary-description').remove();
			Object.keys(groups).forEach(function(key) {
				var html = '<p class="wlm-regform-order-summary-description">' + key + '<span style="float:right;"><span class="wlm-stripe-discount-amount">'+groups[key].toFixed(2)+'</span></span></p>';
				summary.find('.wlm-regform-order-summary-due-now').before(html);
			});
			summary.find('.wlm-regform-order-summary-due-now-amount').text(due.toFixed(2));
		} else {
			if(adjustment){
				btn.html(btn.data('text') + ' <span class="btn-price">' + summary_container.data('currency') + ' ' + due.toFixed(2) + '</span>');
			} else {
				btn.html(btn.data('text'));
			}
		}

		btn.removeProp('disabled');
		btn.prop('disabled', false);
	}

	$(".go-regform").fancybox({
		closeBtn    : false,
		fitToView   : true,
		padding     : [0, 0, 0, 0],
		margin      : [0, 0, 0, 0],
		scrolling   : 'visible',
		baseClass   : 'wlm3-fancybox'
	}).addClass('et_smooth_scroll_disabled');
	$('.regform-close').on('click', function(ev) {
		$.fancybox.close();
	});

	hash = $(location).attr('hash');
	if(hash.length > 0) {
		if($(hash).length > 0) {
			hash = hash.replace('#', '');
			$('#go-'+hash).click();
		}
	}

	$('.regform-open-login').on('click', function(ev) {
		ev.preventDefault();

		$('.regform-login').show();
		$('.regform-new').hide()
		$(this).closest('.wlm-regform').addClass('wlm-regform-login');
	});

	$('.regform-close-login').on('click', function(ev) {
		ev.preventDefault();
		$('.regform-login').hide();
		$('.regform-new').show();
		$(this).closest('.wlm-regform').removeClass('wlm-regform-login');
	});

	var json_parse = function(json_string){
		return ('string' == typeof json_string ? JSON.parse(json_string) : json_string);
	}

	var check_coupon = function(coupon, callback) {
		var stripe_vars = get_stripe_vars();
		var promo_code = $('input[name=promo_code]').val();
		var product = $('input[name=product]').val();
		$.post(stripe_vars.stripethankyouurl, {stripe_action: 'check_coupon', promo_code: promo_code, coupon: coupon, product: product, nonce: stripe_vars.noncecoupon}, function(res) {
			callback(json_parse(res));
		});
	}

	var get_coupon = function(coupon, callback) {
		var stripe_vars = get_stripe_vars();
		var promo_code = $('input[name=promo_code]').val();
		$.post(stripe_vars.stripethankyouurl, {stripe_action: 'get_coupon', promo_code: promo_code, coupon: coupon, nonce: stripe_vars.noncecoupondetail}, function(result) {
			callback(json_parse(result));
		});
	}

	$( '[name=go_regform]' ).on('click', function() {
		var product_price = $(this).val();
		var btn_label = $('#amount').text();
		if(btn_label) {
			$( 'button[name="regform-button"]' ).html(btn_label + ' <span class="btn-price"></span>');
		}
		$( 'button[name="product_price"]' ).val(product_price);
	});

	$( '[name=href_go_regform]' ).on('click', function() {
		var product_price =  $(this).siblings('.go-regform-hidden').val()
		var btn_label = $('#amount').text();
		if(btn_label) {
			$( 'button[name="regform-button"]' ).html(btn_label + ' <span class="btn-price"></span>');
		}
		$( 'button[name="product_price"]' ).val(product_price);
	});

	$('select[name="stripe_plan"]').on('change', function() {
		$(this).closest('.regform-container').find('button[name="regform-button"]').data('price', $(this).find('option:selected').text());
		$('.stripe-coupon').data('oldvalue', '');
		$('.stripe-coupon').trigger('keyup');
	});

	$(document).on('stripe-prorate-change.wlm', 'form', function( event, sku ) {
		var form = $(event.currentTarget);

		wlm_stripe_price_adjustments(form, '0-prorate-old', 0, '');
		wlm_stripe_price_adjustments(form, '0-prorate-new', 0, '');

		var radio = $('.' + sku + '-stripe_radio_prorate:checked');
		var select = $('#' + sku + '-proration-levels');
		var txn_id  = form.find('input[name="txn_id"]').length ? form.find('input[name="txn_id"]').val().trim() : '';
		var form_timestamp  = form.find('input[name="form_timestamp"]').length ? form.find('input[name="form_timestamp"]').val().trim() : 0;
		var upgrade_to = form.find('input[name="upgrade_to_level"]').length ? form.find('input[name="upgrade_to_level"]').val().trim() : '';

		if(!txn_id || !upgrade_to || 'prorate_level' !== radio.val() || 'select' === select.val()) {
			return; // seems like we're not prorating. bail.
		}

		var stripe_vars = get_stripe_vars();
		form.find('.regform-button').prop('disabled', true);
		$.post(
			stripe_vars.stripethankyouurl,
			{
				stripe_action: 'get_prorated_amount',
				prorate_level: select.val(),
				txn_id: txn_id,
				upgrade_to: upgrade_to,
				nonce: stripe_vars.nonce_prorate,
				form_timestamp: form_timestamp
			},
			function(res) {
				if(!res.success || !res.data.lines.data[0].proration) {
					form.find('.regform-button').prop('disabled', false).removeProp( 'disabled' );
					return;
				}
				wlm_stripe_price_adjustments(
					form,
					'0-prorate-old',
					res.data.lines.data[0].amount/100,
					wp.i18n.__( 'Proration', 'wishlist-member' )
				);
				wlm_stripe_price_adjustments(
					form,
					'0-prorate-new',
					(res.data.lines.data[1].amount - res.data.lines.data[1].plan.amount)/100,
					wp.i18n.__( 'Proration', 'wishlist-member' )
				);
			}
		);
	} )

	$('.stripe-coupon').on('keyup', function() {
		var el = $(this);
		var c_code = el.val().trim();
		if(el.data('oldvalue') == c_code) {
			return;
		}
		el.data('oldvalue', c_code);
		var onpage_form = !!el.closest('.wlm3-onpage-paymentform').length;
		var container = el.closest('.regform-container');

		wlm_stripe_price_adjustments( container.find('form').first(), '9-coupon_amount_off', 0, '' );
		wlm_stripe_price_adjustments( container.find('form').first(), '9-coupon_percent_off', 0, '' );

		var $btn = container.find('button[name="regform-button"]');
		var product_price = $btn.data('price').match(/^(.+) ([^ ]+)/);

		if(!Array.isArray(product_price)) {
			return;
		}
		if($btn.data('price').match(/Every/)) {
			// Update the price for popup form.
			product_price = product_price[2];
		} else {
			// Update the price for on-page form.
			product_price = product_price[1].match(/[0-9].+$/)[0];
		}
		var btn_label = $btn.data('text');
		if(!onpage_form) {
			$btn.html(btn_label + ' <span class="btn-price"></span>');
		}
		if(c_code.length == 0) {
			el.removeClass('error_input good_input checking_input');
			return true;
		}

		el.removeClass('error_input good_input checking_input');
		$btn.prop('disabled', true);

		try {
			clearTimeout(window.wlm_stripe_coupon_timeout);
			el.removeClass('error_input good_input').addClass('checking_input');
			window.wlm_stripe_coupon_timeout = setTimeout(function(el, c_code, product_price, container, button) {
				check_coupon( el.val(), function(res) {
					if(res.success == true) {
						var c_type   = res.coupon.amount_off ? 'amount_off' : 'percent_off';
						var c_amount = res.coupon.amount_off ? res.coupon.amount_off : res.coupon.percent_off;
						product_price = parseFloat(product_price);
						wlm_stripe_price_adjustments(
							container.find('form').first(),
							'9-coupon_' + c_type,
							-c_amount/100,
							wp.i18n.__( 'Coupon', 'wishlist-member' )
						);
						el.removeClass('error_input checking_input').addClass('good_input');
						button.removeAttr('disabled').prop('disabled', false);
					} else {
						el.removeClass('good_input checking_input').addClass('error_input');
						button.removeAttr('disabled').prop('disabled', true);
					}
				});
			}, 500, el, c_code, product_price, container, $btn);
		} catch(e) {
			$btn.removeAttr('disabled').prop('disabled', false);
		}
});

	// check coupon validity by trigger keyup on input.stripe-coupon field if it has value.
	$('.stripe-coupon').val().trim() && $('.stripe-coupon').trigger('keyup');
});
