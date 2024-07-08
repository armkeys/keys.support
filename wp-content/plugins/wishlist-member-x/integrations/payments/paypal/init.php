<?php

// Initialization.
if (! class_exists('WLM3_PayPalPS_Hooks')) {
    class WLM3_PayPalPS_Hooks
    {
        public function __construct()
        {
            add_action('wp_ajax_paypalps_delete_product', [$this, 'delete_product']);
        }

        public function delete_product()
        {
            while (isset($_SESSION[ __FUNCTION__ ])) {
                sleep(1);
            }
            $_SESSION[ __FUNCTION__ ] = 1;
            $products                 = wishlistmember_instance()->get_option('paypalpsproducts');
            unset($products[ wlm_post_data()[ 'id' ] ]);
            wishlistmember_instance()->save_option('paypalpsproducts', $products);
            unset($_SESSION[ __FUNCTION__ ]);
            wp_send_json(
                [
                    'success'  => true,
                    'products' => $products,
                ]
            );
        }
    }

    new WLM3_PayPalPS_Hooks();
}
