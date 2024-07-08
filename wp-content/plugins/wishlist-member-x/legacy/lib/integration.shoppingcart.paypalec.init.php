<?php

require_once $this->plugin_dir . '/lib/integration.shoppingcart.paypalcommon.php';

class WlmpaypalecInit
{
    private $forms;
    private $wlm;
    private $products;

    public function load_popup()
    {
        global $WishListMemberInstance;
        wp_enqueue_script('wlm-jquery-fancybox');
        wp_enqueue_style('wlm-jquery-fancybox');
        wp_enqueue_script('wlm-popup-regform');
        wp_enqueue_style('wlm-popup-regform-style');
    }
    public function __construct()
    {
        add_action('admin_init', [$this, 'use_underscore']);
        add_shortcode('wlm_paypalec_btn', [$this, 'paypalecbtn']);
        add_action('wp_footer', [$this, 'footer'], 100);

        /**
         * Add PayPal Checkout shortcode inserter
         *
         * @uses   wlm_paypal_shortcode_buttons
         * @param  array   $shortcodes Integration shortcodes manifest
         * @return array              Filter shortcodes manifest
         */
        add_filter(
            'wishlistmember_integration_shortcodes',
            function ($shortcodes) {
                return wlm_paypal_shortcode_buttons(
                    $shortcodes,
                    'wlm_paypalec_btn',
                    __('PayPal Checkout Integration', 'wishlist-member'),
                    wishlistmember_instance()->get_option('paypalecproducts'),
                    wishlistmember_instance()->get_option('paypalec_spb')
                );
            }
        );

        add_action('wp_ajax_wlm_paypalec_new-product', [$this, 'new_product']);
        add_action('wp_ajax_wlm_paypalec_all-products', [$this, 'get_all_products']);
        add_action('wp_ajax_wlm_paypalec_save-product', [$this, 'save_product']);
        add_action('wp_ajax_wlm_paypalec_delete-product', [$this, 'delete_product']);
        ob_start([$this, 'fix_incorrect_t_parameter_in_link']);

        global $WishListMemberInstance;

        if (empty($WishListMemberInstance)) {
            return;
        }
        $this->wlm      = $WishListMemberInstance;
        $this->products = $WishListMemberInstance->get_option('paypalecproducts');
    }

    /**
     * Fix incorrect t parameter in payment link
     * Called as call back to ob_start()
     *
     * @param  string $content Post content
     * @return string
     */
    public function fix_incorrect_t_parameter_in_link($content)
    {
        return preg_replace('#(https?://.+?\baction=purchase-express.*?\bt=)https?://(\d+)#', '$1$2', $content);
    }

    public function footer()
    {
        foreach ((array) $this->forms as $f) {
            fwrite(WLM_STDOUT, $f);
        }
        if (! empty($this->forms) && is_array($this->forms)) :
            ?>
            <script type="text/javascript">
            jQuery(function($) {
                <?php
                $skus = array_keys($this->forms);
                foreach ($skus as $sku) {
                    printf("$('#regform-%s .regform-form').PopupRegForm();", esc_js($sku));
                }
                ?>
            });
            </script>
            <?php
        endif;
    }
    public function use_underscore()
    {
        global $WishListMemberInstance;
        if (is_admin() && isset(wlm_get_data()['page']) && wlm_get_data()['page'] == $WishListMemberInstance->menu_id && isset(wlm_get_data()['wl']) && 'integration' === wlm_get_data()['wl']) {
            wp_enqueue_script('underscore-wlm', $WishListMemberInstance->plugin_url . '/js/underscore-min.js', ['underscore'], $WishListMemberInstance->version);
        }
    }

    public function paypalecbtn($atts, $content)
    {
        global $WishListMemberInstance, $wlm_paypal_buttons;
        static $spb_lib_loaded = false;
        static $sku_count      = [];

        $spb     = $WishListMemberInstance->get_option('paypalec_spb');
        $funding = empty($spb['funding']) ? null : implode(',', $spb['funding']);
        $valid   = [
            'sku'     => null,
            'btn'     => 'pp_pay:s',
            'layout'  => $spb['layout'],
            'shape'   => $spb['shape'],
            'size'    => $spb['size'],
            'color'   => $spb['color'],
            'funding' => $funding,
        ];

        $atts = shortcode_atts($valid, $atts);

        $sku = $atts['sku'];
        $btn = $atts['btn'];

        if (! isset($sku_count[ $sku ])) {
            $sku_count[ $sku ] = 1;
        } else {
            ++$sku_count[ $sku ];
        }
        $sku_id = sprintf('%s-%d', $sku, $sku_count[ $sku ]);

        $this->load_popup();
        $products = $WishListMemberInstance->get_option('paypalecproducts');
        // Standard buttons.
        $wpm_levels = $WishListMemberInstance->get_option('wpm_levels');
        $product    = $products[ $sku ];
        $content    = wlm_trim($content);
        $btn        = wlm_trim($btn);

        $paypalecthankyou     = $WishListMemberInstance->get_option('paypalecthankyou');
        $paypalecthankyou_url = $WishListMemberInstance->make_thankyou_url($paypalecthankyou);
        $paypalec_settings    = $WishListMemberInstance->get_option('paypalecsettings');

        // Adding "t" to make it unique, else it will always tru in prevent duplicate registrations.
        $paypalec_purchase_url = sprintf('%s?action=purchase-express&id=%s&t=%s', $paypalecthankyou_url, $sku, time());

        if (! empty($spb['enable'])) {
            $atts['funding'] = explode(',', $atts['funding']);
            // Smart payment buttons.
            $btn = '';
            if (! $spb_lib_loaded) {
                $btn           .= wlm_get_script_markup('https://www.paypalobjects.com/api/checkout.min.js');
                $spb_lib_loaded = true;
            }

            $funding    = ['ELV', 'CREDIT', 'CARD'];
            $allowed    = array_intersect($funding, (array) $atts['funding']);
            $disallowed = array_diff($funding, (array) $atts['funding']);
            foreach ($allowed as &$a) {
                $a = 'paypal.FUNDING.' . $a;
            }
            unset($a);
            foreach ($disallowed as &$a) {
                $a = 'paypal.FUNDING.' . $a;
            }
            unset($a);

            $allowed    = implode(',', $allowed);
            $disallowed = implode(',', $disallowed);

            $env  = empty($paypalec_settings['sandbox_mode']) ? 'production' : 'sandbox';
            $btn .= <<<STRING
<span class="paypalec-smart_payment_button" id="paypal-spbutton-container-{$sku_id}"></span>
<script type="text/javascript">
paypal.Button.render({
env: '{$env}',
style: { layout: '{$atts['layout']}', size: '{$atts['size']}', shape: '{$atts['shape']}', color: '{$atts['color']}' },
funding: { allowed: [ {$allowed} ], disallowed: [ {$disallowed} ] },
commit: true,
payment: function (data, actions) {
  return new paypal.Promise(function(resolve, reject) {
		jQuery.get('{$paypalec_purchase_url}&spb=1', function(result) {
			if(result.token) {
  			return resolve(result.token);
			} else {
				reject(new Error('Error'));
			}
		});
  });
},
onAuthorize: function (data, actions) { return actions.redirect() }
}, '#paypal-spbutton-container-{$sku_id}');
</script>
STRING;
            return $btn;
        }

        // Non-smart payment buttons.
        if (! $btn) {
            $btn = $content;
        }

        if (! empty($wlm_paypal_buttons[ $btn ])) {
            $btn = $wlm_paypal_buttons[ $btn ];
        }

        $imgbtn = false;
        if ($btn) {
            if (filter_var($btn, FILTER_VALIDATE_URL)) {
                $btn    = sprintf('<img border="0" style="border:none" class="wlm-paypal-button" src="%s">', $btn);
                $imgbtn = true;
            }
        }

        $panel_button_label = 'Pay';
        if ($product['recurring']) {
            $amt  = $product['amount'];
            $amt  = $product['amount'] . ' for the first ' . $product['recur_billing_frequency'] . ' ' . $product['recur_billing_period'] . '/s';
            $amt .= ' and <br>' . $product['recur_amount'] . ' every ' . $product['recur_billing_frequency'] . ' ' . $product['recur_billing_period'] . '/s after';
        } else {
            $amt = $product['amount'];
        }

        $settings = $WishListMemberInstance->get_option('paypalecthankyou_url');
        if ($imgbtn) {
            $btn = sprintf('<a onclick="window.location=\'%s\'" id="paypal-ec-%s" class="wlm-paypal-button">%s</a>', $paypalec_purchase_url, $sku, $btn);
        } else {
            $btn = sprintf('<button onclick="window.location=\'%s\'" id="paypal-ec-%s" class="wlm-paypal-button">%s</button>', $paypalec_purchase_url, $sku, $btn);
        }
        return $btn;
    }

    // Ajax methods.
    public function delete_product()
    {
        $id = wlm_post_data()['id'];
        unset($this->products[ $id ]);
        $this->wlm->save_option('paypalecproducts', $this->products);
    }
    public function save_product()
    {

        $id                    = wlm_post_data()['id'];
        $product               = wlm_post_data(true);
        $this->products[ $id ] = $product;
        $this->wlm->save_option('paypalecproducts', $this->products);
        echo json_encode($this->products[ $id ]);
        die();
    }

    public function get_all_products()
    {
        $products = $this->products;
        echo json_encode($products);
        die();
    }

    public function new_product()
    {
        $products = $this->products;
        if (empty($products)) {
            $products = [];
        }

        // Create an id for this button.
        $id = strtoupper(substr(sha1(microtime()), 1, 10));

        $product = [
            'id'            => $id,
            'name'          => wlm_post_data()['name'] . ' Product',
            'currency'      => 'USD',
            'amount'        => 10,
            'recurring'     => 0,
            'sku'           => wlm_post_data()['sku'],
            'checkout_type' => 'express-checkout',
        ];

        $this->products[ $id ] = $product;
        $this->wlm->save_option('paypalecproducts', $this->products);

        echo json_encode($product);
        die();
    }
}


$wlm_paypalec_init = new WlmpaypalecInit();

