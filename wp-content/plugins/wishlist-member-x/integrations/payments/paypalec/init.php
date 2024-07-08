<?php

// Initialization.
if (! class_exists('WLM3_PayPalEC_Hooks')) {
    class WLM3_PayPalEC_Hooks
    {
        public function __construct()
        {
            add_action('wp_ajax_paypalec_delete_product', [$this, 'delete_product']);
        }

        public function delete_product()
        {
            while (isset($_SESSION[ __FUNCTION__ ])) {
                sleep(1);
            }
            $_SESSION[ __FUNCTION__ ] = 1;
            $products                 = wishlistmember_instance()->get_option('paypalecproducts');
            unset($products[ wlm_post_data()[ 'id' ] ]);
            wishlistmember_instance()->save_option('paypalecproducts', $products);
            unset($_SESSION[ __FUNCTION__ ]);
            wp_send_json(
                [
                    'success'  => true,
                    'products' => $products,
                ]
            );
        }
    }

    new WLM3_PayPalEC_Hooks();
}
