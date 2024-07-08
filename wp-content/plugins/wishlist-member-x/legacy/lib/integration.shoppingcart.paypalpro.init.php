<?php

require_once $this->plugin_dir . '/lib/integration.shoppingcart.paypalcommon.php';

class WlmPaypalProInit
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
        add_shortcode('wlm_paypalpro_btn', [$this, 'paypalprobtn']);
        add_action('wp_footer', [$this, 'footer'], 100);
        add_filter('the_content', [$this, 'shortlink_processor']);

        /**
         * Add PayPal Pro shortcode inserter
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
                    'wlm_paypalpro_btn',
                    __('PayPal Pro Integration', 'wishlist-member'),
                    wishlistmember_instance()->get_option('paypalproproducts')
                );
            }
        );

        add_action('wp_ajax_wlm_paypalpro_new-product', [$this, 'new_product']);
        add_action('wp_ajax_wlm_paypalpro_all-products', [$this, 'get_all_products']);
        add_action('wp_ajax_wlm_paypalpro_save-product', [$this, 'save_product']);
        add_action('wp_ajax_wlm_paypalpro_delete-product', [$this, 'delete_product']);

        global $WishListMemberInstance;

        if (empty($WishListMemberInstance)) {
            return;
        }
        $this->wlm      = $WishListMemberInstance;
        $this->products = $WishListMemberInstance->get_option('paypalproproducts');
    }

    public function shortlink_processor($content)
    {
        static $called = false;
        if ($called) {
            return $content;
        }
        $called = true;
        if (! empty(wlm_get_data()['pppro'])) {
            $pppro = wlm_get_data()['pppro'];
            printf('<div style="display:none">%s</div>', do_shortcode(sprintf('[wlm_paypalpro_btn sku="%s"]', esc_attr($pppro))));
            ?>
            <script>
                jQuery(function($) {
                    window.location.hash='regform-<?php echo esc_js($pppro); ?>';
                });
            </script>
            <?php
        }
        return $content;
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

    public function paypalprobtn($atts, $content)
    {
        global $WishListMemberInstance, $wlm_paypal_buttons;
        $this->load_popup();
        $products   = $WishListMemberInstance->get_option('paypalproproducts');
        $wpm_levels = $WishListMemberInstance->get_option('wpm_levels');
        $atts       = extract(
            shortcode_atts(
                [
                    'sku' => null,
                    'btn' => null,
                ],
                $atts
            )
        );
        $product    = $products[ $sku ];
        $content    = wlm_trim($content);
        $btn        = wlm_trim($btn);

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
            $amt = nl2br(wlm_paypal_create_description($product, false));
        } else {
            $amt = sprintf('%s %0.2f', $product['currency'], $product['amount']);
        }

        $settings              = $WishListMemberInstance->get_option('paypalprothankyou_url');
        $paypalprothankyou     = $WishListMemberInstance->get_option('paypalprothankyou');
        $paypalprothankyou_url = $WishListMemberInstance->make_thankyou_url($paypalprothankyou);
        include $WishListMemberInstance->plugin_dir . '/extlib/wlm_paypal/form_new_fields.php';
        $this->forms[ $sku ] = wlm_build_payment_form($data);
        if ($imgbtn) {
            $btn = sprintf('<a id="go-regform-%s" class="wlm-paypal-button go-regform" href="#regform-%s">%s</a>', $sku, $sku, $btn);
        } else {
            $btn = sprintf('<button id="go-regform-%s" class="wlm-paypal-button go-regform" href="#regform-%s">%s</button>', $sku, $sku, $btn);
        }
        return $btn;
    }

    // Ajax methods.
    public function delete_product()
    {
        $id = wlm_post_data()['id'];
        unset($this->products[ $id ]);
        $this->wlm->save_option('paypalproproducts', $this->products);
    }
    public function save_product()
    {

        $id                    = wlm_post_data()['id'];
        $product               = wlm_post_data(true);
        $this->products[ $id ] = $product;
        $this->wlm->save_option('paypalproproducts', $this->products);
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
            'checkout_type' => 'direct-charge',
        ];

        $this->products[ $id ] = $product;
        $this->wlm->save_option('paypalproproducts', $this->products);

        echo json_encode($product);
        die();
    }
}


$wlm_paypalpro_init = new WlmPaypalProInit();

