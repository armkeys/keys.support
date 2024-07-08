<?php

/**
 * Clickbank init
 *
 * @package WishListMember/Payments
 */

if (! class_exists('WLM3_Clickbank_Hooks')) {
    /**
     * WLM3_Clickbank_Hooks class
     */
    class WLM3_Clickbank_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_save_clickbank_product', [$this, 'save_product']);
            add_action('wp_ajax_wlm3_delete_clickbank_product', [$this, 'delete_product']);
        }

        /**
         * Save Product
         */
        public function save_product()
        {
            $data = [
                'status'  => false,
                'message' => '',
                'data'    => [],
            ];

            $id         = trim(wlm_post_data()[ 'id' ]);
            $old_id     = wlm_post_data()[ 'old_id' ];
            $access     = wlm_post_data()[ 'access' ];
            $old_access = wlm_post_data()[ 'old_access' ];

            if (empty($id)) {
                $data['message'] = 'Product ID Required';
                wp_send_json($data);
            }
            $products = $this->__delete_product($old_access, $old_id);

            $products[ $access ][] = $id;
            $products[ $access ]   = array_diff(array_unique($products[ $access ]), ['', null, 0]);

            wishlistmember_instance()->save_option('cbproducts', $products);
            $data['status']             = true;
            $data['message']            = 'Product Saved';
            $data['data']['cbproducts'] = $products;
            wp_send_json($data);
        }

        /**
         * Delete product
         */
        public function delete_product()
        {
            $data = [
                'status'  => true,
                'message' => '',
                'data'    => [],
            ];

            $id     = trim(wlm_post_data()[ 'id' ]);
            $access = wlm_post_data()[ 'access' ];

            $data['data']['cbproducts'] = $this->__delete_product($access, $id);
            wp_send_json($data);
        }

        /**
         * Private delete product method
         *
         * @param  array   $access Access.
         * @param  integer $id     Product ID.
         * @return array
         */
        private function __delete_product($access, $id)
        {
            $products = wishlistmember_instance()->get_option('cbproducts');

            if (! empty($products[ $access ])) {
                $products[ $access ] = array_diff($products[ $access ], [$id]);
                wishlistmember_instance()->save_option('cbproducts', $products);
            }

            return $products;
        }
    }
    new WLM3_Clickbank_Hooks();
}
