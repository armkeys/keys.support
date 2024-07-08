<?php

// Initialization.
if (! class_exists('WLM3_PayPalPro_Hooks')) {
    class WLM3_PayPalPro_Hooks
    {
        public function __construct()
        {
            add_action('wp_ajax_paypalpro_delete_product', [$this, 'delete_product']);
        }

        public function delete_product()
        {
            while (isset($_SESSION[ __FUNCTION__ ])) {
                sleep(1);
            }
            $_SESSION[ __FUNCTION__ ] = 1;
            $products                 = wishlistmember_instance()->get_option('paypalproproducts');
            unset($products[ wlm_post_data()[ 'id' ] ]);
            wishlistmember_instance()->save_option('paypalproproducts', $products);
            unset($_SESSION[ __FUNCTION__ ]);
            wp_send_json(
                [
                    'success'  => true,
                    'products' => $products,
                ]
            );
        }
    }

    new WLM3_PayPalPro_Hooks();
}
