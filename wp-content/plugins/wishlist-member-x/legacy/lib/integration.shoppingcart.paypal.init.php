<?php

require_once $this->plugin_dir . '/lib/integration.shoppingcart.paypalcommon.php';

class WlmpaypalpsInit
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
        add_shortcode('wlm_paypalps_btn', [$this, 'paypalpsbtn']);
        add_action('wp_footer', [$this, 'footer'], 100);

        /**
         * Add PayPal Payments Standard (Legacy) shortcode inserter
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
                    'wlm_paypalps_btn',
                    __('PayPal Payments Standard Integration', 'wishlist-member'),
                    wishlistmember_instance()->get_option('paypalpsproducts')
                );
            }
        );

        add_action('wp_ajax_wlm_paypalps_new-product', [$this, 'new_product']);
        add_action('wp_ajax_wlm_paypalps_all-products', [$this, 'get_all_products']);
        add_action('wp_ajax_wlm_paypalps_save-product', [$this, 'save_product']);
        add_action('wp_ajax_wlm_paypalps_delete-product', [$this, 'delete_product']);
        add_action('wp_ajax_wlm_paypalps_get-product-form', [$this, 'paypal_form']);

        global $WishListMemberInstance;

        if (empty($WishListMemberInstance)) {
            return;
        }
        $this->wlm      = $WishListMemberInstance;
        $this->products = $WishListMemberInstance->get_option('paypalpsproducts');
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

    public function paypalpsbtn($atts, $content)
    {
        global $WishListMemberInstance, $wlm_paypal_buttons;

        $atts                 = extract(
            shortcode_atts(
                [
                    'sku' => null,
                    'btn' => null,
                ],
                $atts
            )
        );
        $paypalpsthankyou     = $WishListMemberInstance->get_option('ppthankyou');
        $blogurl              = get_bloginfo('url');
        $paypalpsthankyou_url = $WishListMemberInstance->make_thankyou_url($paypalpsthankyou);

        $btn = wlm_trim($btn);

        if (! empty($wlm_paypal_buttons[ $btn ])) {
            $btn = $wlm_paypal_buttons[ $btn ];
        }

        if (! $btn) {
            if ($product['recurring']) {
                $btn = 'https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_m.png';
            } else {
                $btn = 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-medium.png';
            }
        }

        $link = $paypalpsthankyou_url . '?pid=' . $sku;

        if (false === filter_var($btn, FILTER_VALIDATE_URL)) {
            $pattern = '<button onclick="window.location=\'%s\'" class="wlm-paypal-button">%s</button>';
        } else {
            $pattern = '<a href="%s"><img src="%s" border="0" style="border:none" class="wlm-paypal-button"></a>';
        }

        $btn = sprintf($pattern, $link, $btn);

        return $btn;
    }

    // Ajax methods.
    public function delete_product()
    {
        $id = wlm_post_data()['id'];
        unset($this->products[ $id ]);
        $this->wlm->save_option('paypalpsproducts', $this->products);
    }
    public function save_product()
    {

        $id                    = wlm_post_data()['id'];
        $product               = wlm_post_data(true);
        $this->products[ $id ] = $product;
        $this->wlm->save_option('paypalpsproducts', $this->products);
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
            'checkout_type' => 'payments-standard',
        ];

        $this->products[ $id ] = $product;
        $this->wlm->save_option('paypalpsproducts', $this->products);

        echo json_encode($product);
        die();
    }

    public function paypal_form()
    {
        fwrite(WLM_STDOUT, $this->paypal_link(wlm_post_data()['product_id'], true));
        exit;
    }

    public function paypal_link($product_id, $return_as_html_form = false)
    {
        global $WishListMemberInstance;

        if (empty($this->products[ $product_id ])) {
            return '';
        }

        $product = $this->products[ $product_id ];

        $sandbox              = (int) $WishListMemberInstance->get_option('ppsandbox');
        $paypalpsthankyou     = $WishListMemberInstance->get_option('ppthankyou');
        $blogurl              = get_bloginfo('url');
        $paypalpsthankyou_url = $WishListMemberInstance->make_thankyou_url($paypalpsthankyou);
        $paypalcmd            = $product['recurring'] ? '_xclick-subscriptions' : '_xclick';
        $formsubmit           = $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        $paypalemail          = $WishListMemberInstance->get_option($sandbox ? 'ppsandboxemail' : 'ppemail');

        $thefields                   = [];
        $the_fields['cmd']           = $paypalcmd;
        $the_fields['business']      = $paypalemail;
        $the_fields['item_name']     = $product['name'];
        $the_fields['item_number']   = $product['sku'];
        $the_fields['no_note']       = '1';
        $the_fields['no_shipping']   = '1';
        $the_fields['rm']            = '2';
        $the_fields['bn']            = 'WishListProducts_SP';
        $the_fields['cancel_return'] = $blogurl;
        $the_fields['notify_url']    = $paypalpsthankyou_url;
        $the_fields['return']        = $paypalpsthankyou_url;
        $the_fields['currency_code'] = $product['currency'];
        $the_fields['charset']       = 'utf-8';

        $button = '';

        if ($product['recurring']) {
            $button       = 'https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_m.png';
            $period       = strtoupper(substr($product['recur_billing_period'], 0, 1));
            $trialperiod  = strtoupper(substr($product['trial_recur_billing_period'], 0, 1));
            $trial2period = strtoupper(substr($product['trial2_recur_billing_period'], 0, 1));

            if ($product['trial']) {
                $the_fields['a1'] = $product['trial_amount'];
                $the_fields['p1'] = $product['trial_recur_billing_frequency'];
                $the_fields['t1'] = $trialperiod;


                // If 0$ trial then let's set cookie for the user's IP.
                // Which we will use to set transient when we receive the IPN for the order.
                // The reason we're doing this is due to 0$ trial subscription not sending $_GET['tx']
                // When a user is redirected from Paypal.
                if (0 == $product['trial_amount']) {
                    $ckname = md5('wlm_transient_hash');
                    $hash   = md5(wlm_server_data()['REMOTE_ADDR']);
                    wlm_setcookie("{$ckname}[{$hash}]", $hash, 0, '/');
                    $the_fields['custom'] = wlm_server_data()['REMOTE_ADDR'];
                }


                if ($product['trial2']) {
                    $the_fields['a2'] = $product['trial2_amount'];
                    $the_fields['p2'] = $product['trial2_recur_billing_frequency'];
                    $the_fields['t2'] = $trial2period;
                }
            }

            $the_fields['a3']  = $product['recur_amount'];
            $the_fields['p3']  = $product['recur_billing_frequency'];
            $the_fields['t3']  = $period;
            $the_fields['src'] = '1';

            if ($product['recur_billing_cycles'] > 1) {
                $the_fields['srt'] = $product['recur_billing_cycles'];
            }
        } else {
            $button               = 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-medium.png';
            $the_fields['amount'] = $product['amount'];
        }

        if ($return_as_html_form) {
            foreach ($the_fields as $fname => $fvalue) {
                $fvalue               = sprintf("<input type='hidden' name='%s' value='%s'>", $fname, htmlentities($fvalue, ENT_QUOTES));
                $the_fields[ $fname ] = $fvalue;
            }
            return sprintf("<form method='post' action='%s' target='_top'>\n%s\n<input type='image' src='%s' alt='Pay with PayPal'>\n</form>", $formsubmit, implode("\n", $the_fields), $button);
        } else {
            $the_fields = http_build_query($the_fields);
            return $formsubmit . '?' . $the_fields;
        }
    }
}

global $wlm_paypalps_init;
$wlm_paypalps_init = new WlmpaypalpsInit();
