<?php

/**
 * WooCommerce_Integration class file
 *
 * @package WishListMember
 */

namespace WishListMember\PaymentProviders;

/**
 * WooCommerce_Integration class
 */
class WooCommerce_Integration
{
    /**
     * Self Instance
     *
     * @var object WooCommerce_Integration object instance
     */
    private static $instance = null;

    /**
     * Loaded with $_POST on construct
     *
     * @var array
     */
    private $post = [];

    /**
     * Loaded with $_GET on construct
     *
     * @var array
     */
    private $get = [];

    /**
     * Array of member pricing descriptions with product IDs as keys
     *
     * @var array
     */
    private $pricing_descriptions = [];

    // === Start: Init methods. ===

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->post = filter_input_array(INPUT_POST);
        $this->get  = filter_input_array(INPUT_GET);

        add_action('plugins_loaded', [$this, 'set_wordpress_hooks']);
    }

    /**
     * Public function to generate a single instance
     *
     * @return object WooCommerce_Integration object instance
     */
    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set WordPress hooks
     */
    public function set_wordpress_hooks()
    {
        if (! class_exists('woocommerce')) {
            return;
        }

        add_action('save_post_product', [$this, 'save_woocommerce_product']);
        add_action('woocommerce_order_status_changed', [$this, 'order_status_changed'], 10, 3);
        add_action('woocommerce_subscription_status_changed', [$this, 'subscription_status_changed'], 10, 3);

        add_filter('woocommerce_product_data_tabs', [$this, 'product_metabox_tabs']);
        add_action('woocommerce_product_data_panels', [$this, 'product_metabox_panel'], 1000);

        add_action('wp_trash_post', [$this, 'trash_post']);
        add_action('untrashed_post', [$this, 'untrash_post'], 1000);

        add_filter('wishlistmember_level_edit_tabs', [$this, 'level_edit_tab']);
        add_action('wishlistmember_level_edit_tab_pane_woocommerce', [$this, 'level_edit_screen']);
        add_action('wishlistmember_woocommerce_get_level_pricing_item_view', [$this, 'get_level_pricing_item_view'], 10, 3);
        add_action('wp_ajax_wishlistmember_woocommerce_get_level_pricing_item_view', [$this, 'get_level_pricing_item_view']);
        add_action('wp_ajax_wishlistmember_woocommerce_save_levels_product_pricing', [$this, 'save_levels_product_pricing']);
        add_action('wp_ajax_wishlistmember_woocommerce_delete_levels_product_pricing', [$this, 'delete_levels_product_pricing']);
        add_action('wp_ajax_wishlistmember_woocommerce_save_levels_category_pricing', [$this, 'save_levels_category_pricing']);
        add_action('wp_ajax_wishlistmember_woocommerce_delete_levels_category_pricing', [$this, 'delete_levels_category_pricing']);
        add_action('wp_ajax_wishlistmember_woocommerce_save_levels_global_pricing', [$this, 'save_levels_global_pricing']);
        add_action('wp_ajax_wishlistmember_woocommerce_save_levels_access', [$this, 'save_levels_access']);

        add_action('wp_ajax_wishlistmember_woocommerce_add_level', [$this, 'add_new_level']);
        add_action('wishlistmember_woocommerce_get_pricing_item_view', [$this, 'get_pricing_item_view'], 10);
        add_action('wp_ajax_wishlistmember_woocommerce_get_pricing_item_view', [$this, 'get_pricing_item_view']);

        add_filter('woocommerce_product_get_price', [$this, 'custom_price'], 99, 2);
        add_filter('woocommerce_product_variation_get_price', [$this, 'custom_price'], 99, 2);
        add_filter('woocommerce_get_variation_price', [$this, 'custom_price'], 99, 2);
        add_filter('woocommerce_variable_price_html', [$this, 'custom_variable_price_html'], 99, 2);

        add_action('wp_ajax_wishlistmember_woocommerce_save_pricing', [$this, 'save_pricing']);

        add_action('product_cat_add_form_fields', [$this, 'add_custom_category_pricing']);
        add_action('product_cat_edit_form_fields', [$this, 'add_custom_category_pricing']);
        add_action('wishlistmember_woocommerce_get_category_pricing_item_view', [$this, 'get_category_pricing_item_view'], 10);
        add_action('wp_ajax_wishlistmember_woocommerce_get_category_pricing_item_view', [$this, 'get_category_pricing_item_view']);
        add_action('create_product_cat', [$this, 'save_category']);
        add_action('edit_product_cat', [$this, 'save_category']);
        add_action('wp_ajax_wishlistmember_woocommerce_save_category_pricing', [$this, 'save_custom_category_pricing']);

        add_filter('woocommerce_get_sections_products', [$this, 'add_global_pricing_section']);
        add_filter('woocommerce_get_settings_products', [$this, 'show_global_pricing_section'], 10, 2);
        add_filter('woocommerce_admin_field_wlm4woo-pricing-fields', [$this, 'show_global_pricing_section_fields']);
        add_action('wishlistmember_woocommerce_get_global_pricing_item_view', [$this, 'get_global_pricing_item_view']);
        add_action('wp_ajax_wishlistmember_woocommerce_get_global_pricing_item_view', [$this, 'get_global_pricing_item_view']);
        add_action('wp_ajax_wishlistmember_woocommerce_delete_global_pricing_item', [$this, 'save_global_pricing']);
        add_action('woocommerce_update_options_products_wlm4woo-pricing', [$this, 'save_global_pricing']);
    }

    // === End: Init methods. ===
    // === Start: Callback functions for actiosn and filter. ===

    /**
     * Action callback for save_post_product
     *
     * @param integer $post_id Post ID.
     */
    public function save_woocommerce_product($post_id)
    {
        if (isset($this->post['wishlist_member_woo_levels'])) {
            $wlmwoo = wishlistmember_instance()->get_option('woocommerce_products');
            if (! is_array($wlmwoo)) {
                $wlmwoo = [];
            }
            $wlmwoo[ $post_id ] = $this->post['wishlist_member_woo_levels'];
            wishlistmember_instance()->save_option('woocommerce_products', $wlmwoo);
        }
        if (isset($this->post['wishlistmember_woo_pricing'])) {
            $pricing = wishlistmember_instance()->get_option('woocommerce_product_pricing');
            if (! is_array($pricing)) {
                $pricing = [];
            }
            $pricing[ $post_id ] = $this->post['wishlistmember_woo_pricing'];
            wishlistmember_instance()->save_option('woocommerce_product_pricing', $pricing);
        }
    }

    /**
     * Action handler for woocommerce_order_status_changed
     * Map order status change to either activate, remove or deactivate
     *
     * @uses WooCommerce_Integration::status_changed
     *
     * @param integer $order_id   Order ID.
     * @param string  $old_status Old status.
     * @param string  $new_status New status.
     */
    public function order_status_changed($order_id, $old_status, $new_status)
    {
        switch ($new_status) {
            case 'completed':
            case 'processing':
                $status = 'activate';
                break;
            case 'cancelled':
            case 'refunded':
            case 'failed':
                $status = 'deactivate';
                break;
            case 'pending':
            case 'on-hold':
                $status = 'pending';
                break;
            default:
                $status = '';
        }
        if ($status) {
            $this->status_changed($order_id, $status);
        }
    }

    /**
     * Action handler for woocommerce_subscription_status_changed
     * Map subscription status to either activate, remove or deactivate
     *
     * @uses WooCommerce_Integration::status_changed
     *
     * @param integer $order_id   Order ID.
     * @param string  $old_status Old status.
     * @param string  $new_status New status.
     */
    public function subscription_status_changed($order_id, $old_status, $new_status)
    {
        switch ($new_status) {
            case 'active':
                $status = 'activate';
                break;
            case 'cancelled':
                $status = 'deactivate';
                break;
            case 'processing':
            case 'pending':
            case 'on-hold':
                $status = 'pending';
                break;
            case 'switched':
            case 'pending-cancel':
            case 'expired':
            default:
                $status = '';
        }
        if ($status) {
            $this->status_changed($order_id, $status);
        }
    }

    /**
     * Filter callback for woocommerce_product_data_tabs
     * Add WishList Member tab to the WooCommerce Product Meta box
     *
     * @param  array $tabs Array of tabs.
     * @return array
     */
    public function product_metabox_tabs($tabs)
    {
        $tabs['wishlist_member_woo'] = [
            'label'    => __('WishList Member', 'woocommerce'),
            'target'   => 'wishlist_member_woo',
            'class'    => [],
            'priority' => 71,
        ];
        return $tabs;
    }

    /**
     * Action callback for woocommerce_product_data_panels
     * Add WishList Member panel to the WooCommerce Product Meta Box
     */
    public function product_metabox_panel()
    {
        require_once WOO_INTEGRATION_DIR . '/views/products-panel.php';
        // Load product panel javascript.
        wp_enqueue_script(
            'wishlist-member-products-panel',
            plugins_url('assets/products-panel.js', WOO_HANDLER_FILE),
            [],
            WLM3_PLUGIN_VERSION,
            true
        );
        wp_localize_script(
            'wishlist-member-products-panel',
            'wlm4woo',
            [
                'nonce'              => wp_create_nonce('wlm4woo-ajax-nonce'),
                'product_id'         => wlm_arrval($GLOBALS, 'post', 'ID'),
                'decimal_separator'  => wc_get_price_decimal_separator(),
                'thousand_separator' => wc_get_price_thousand_separator(),
                'decimals'           => wc_get_price_decimals(),
                'price_format'       => get_woocommerce_price_format(),
                'currency'           => get_woocommerce_currency_symbol(),
            ]
        );
        wp_enqueue_style(
            'wishlist-member-products-panel',
            plugins_url('assets/products-panel.css', WOO_HANDLER_FILE),
            [],
            WLM3_PLUGIN_VERSION
        );
    }

    /**
     * Action callback for wp_trash_post
     * Removes levels from a member if an order is trashed
     *
     * @param integer $post_id Post ID.
     */
    public function trash_post($post_id)
    {
        if (! function_exists('wc_get_order')) {
            return;
        }
        $order = wc_get_order($post_id);
        if (! $order) {
            return;
        }
        $this->remove_levels($this->generate_transaction_id($order));
    }

    /**
     * Action callback for untrashed_post
     * Restores an order from trash and updates levels accordingly
     *
     * @param integer $post_id Post ID.
     */
    public function untrash_post($post_id)
    {
        if (! function_exists('wc_get_order')) {
            return;
        }
        $order = wc_get_order($post_id);
        if (! $order) {
            return;
        }
        $function = function_exists('wcs_is_subscription') && wcs_is_subscription($order) ? 'subscription_status_changed' : 'order_status_changed';
        call_user_func([$this, $function], $post_id, 'trash', $order->get_status());
    }

    /**
     * Filter callback for wishlistmember_level_edit_tabs
     *
     * @param  array $tabs Tabs.
     * @return array
     */
    public function level_edit_tab($tabs)
    {
        if (file_exists(WOO_INTEGRATION_DIR . '/views/levels-panel.php')) {
            return (array) $tabs + ['woocommerce' => 'WooCommerce'];
        }
    }

    /**
     * Action callback for wishlistmember_level_edit_tab_pane_woocommerce
     *
     * @param string $level_id Level ID.
     */
    public function level_edit_screen($level_id)
    {
        if (file_exists(WOO_INTEGRATION_DIR . '/views/levels-panel.php')) {
            require WOO_INTEGRATION_DIR . '/views/levels-panel.php';
            // Load product panel javascript.
            wp_enqueue_script(
                'wishlist-member-wlm-levels-panel',
                plugins_url('assets/wlm-levels-panel.js', WOO_HANDLER_FILE),
                [],
                WLM3_PLUGIN_VERSION,
                true
            );
            wp_localize_script(
                'wishlist-member-wlm-levels-panel',
                'wlm4woo',
                [
                    'level_id' => $level_id,
                    'nonce'    => wp_create_nonce('wlm4woo-ajax-nonce'),
                ]
            );
            wp_enqueue_style(
                'wishlist-member-wlm-levels-panel',
                plugins_url('assets/wlm-levels-panel.css', WOO_HANDLER_FILE),
                [],
                WLM3_PLUGIN_VERSION
            );

            // Print scripts and styles if screen is loaded via ajax.
            if (wp_doing_ajax()) {
                wp_print_scripts('wishlist-member-wlm-levels-panel');
                wp_print_styles('wishlist-member-wlm-levels-panel');
            }
        }
    }

    /**
     * Action callback for wishlistmember_woocommerce_get_level_pricing_item_view
     * Action callback for wp_ajax_wishlistmember_woocommerce_get_level_pricing_item_view
     *
     * @param array $pricing Pricing data.
     * @param array $items   Items.
     * @param array $pid     Product ID.
     */
    public function get_level_pricing_item_view($pricing = null, $items = null, $pid = null)
    {
        if (current_action() === 'wp_ajax_wishlistmember_woocommerce_get_level_pricing_item_view') {
            if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
                exit;
            }
            $pricing = wlm_arrval($this->post, 'pricing');
            $items   = wlm_arrval($this->post, 'items');
            $pid     = wlm_arrval($this->post, 'pid');
        }

        require WOO_INTEGRATION_DIR . '/views/levels-panel-pricing-item.php';
    }

    /**
     * Callback for wp_ajax_wishlistmember_woocommerce_save_levels_product_pricing
     *
     * Saves member pricing from levels edit screen
     */
    public function save_levels_product_pricing()
    {
        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['product_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid product ID', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['level_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid level ID', 'wishlist-member'),
                ]
            );
            return;
        }

        $default = [
            'pricing_type'   => 'fixed-price',
            'pricing_amount' => 0,
            'description'    => '',
        ];

        $data = array_intersect_key($this->post, $default) + $default;

        $pricing = wishlistmember_instance()->get_option('woocommerce_product_pricing');
        if (! is_array($pricing)) {
            $pricing = [];
        }
        $pricing[ $this->post['product_id'] ][ $this->post['level_id'] ] = $data;
        wishlistmember_instance()->save_option('woocommerce_product_pricing', $pricing);

        wp_send_json_success($this->post + ['product_pricing' => $pricing]);
    }

    /**
     * Callback for wp_ajax_wishlistmember_woocommerce_delete_levels_product_pricing
     *
     * Saves member pricing from levels edit screen
     */
    public function delete_levels_product_pricing()
    {
        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['product_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid product ID', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['level_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid level ID', 'wishlist-member'),
                ]
            );
            return;
        }

        $pricing = wishlistmember_instance()->get_option('woocommerce_product_pricing');
        if (! is_array($pricing)) {
            $pricing = [];
        }
        unset($pricing[ $this->post['product_id'] ][ $this->post['level_id'] ]);
        wishlistmember_instance()->save_option('woocommerce_product_pricing', $pricing);

        wp_send_json_success($this->post + ['product_pricing' => $pricing]);
    }

    /**
     * Callback for wp_ajax_wishlistmember_woocommerce_save_levels_category_pricing
     *
     * Saves member category pricing from levels edit screen
     */
    public function save_levels_category_pricing()
    {
        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['category_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid category ID', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['level_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid level ID', 'wishlist-member'),
                ]
            );
            return;
        }

        $default = [
            'pricing_type'   => 'fixed-price',
            'pricing_amount' => 0,
            'description'    => '',
        ];

        $data = array_intersect_key($this->post, $default) + $default;

        $pricing = wishlistmember_instance()->get_option('woocommerce_category_pricing');
        if (! is_array($pricing)) {
            $pricing = [];
        }
        $pricing[ $this->post['category_id'] ][ $this->post['level_id'] ] = $data;
        wishlistmember_instance()->save_option('woocommerce_category_pricing', $pricing);

        wp_send_json_success($this->post + ['category_pricing' => $pricing]);
    }

    /**
     * Callback for wp_ajax_wishlistmember_woocommerce_delete_levels_category_pricing
     *
     * Saves member category pricing from levels edit screen
     */
    public function delete_levels_category_pricing()
    {
        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['category_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid category ID', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['level_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid level ID', 'wishlist-member'),
                ]
            );
            return;
        }

        $pricing = wishlistmember_instance()->get_option('woocommerce_category_pricing');
        if (! is_array($pricing)) {
            $pricing = [];
        }
        unset($pricing[ $this->post['category_id'] ][ $this->post['level_id'] ]);
        wishlistmember_instance()->save_option('woocommerce_category_pricing', $pricing);

        wp_send_json_success($this->post + ['category_pricing' => $pricing]);
    }
    /**
     * Callback for wishlistmember_woocommerce_save_levels_global_pricing
     *
     * Saves global member pricing from levels edit screen
     */
    public function save_levels_global_pricing()
    {
        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['level_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid level ID', 'wishlist-member'),
                ]
            );
            return;
        }

        /**
         * Global Pricing Data
         *
         * @var array
         */
        $global_pricing = wishlistmember_instance()->get_option('woocommerce_global_pricing');

        // Disable global pricing.
        if (empty($this->post['woocommerce_enable_global_pricing'])) {
            unset($global_pricing[ $this->post['level_id'] ]);
            wishlistmember_instance()->save_option('woocommerce_global_pricing', $global_pricing);
            wp_send_json_success();
            return;
        }

        $global_pricing[ $this->post['level_id'] ] = [
            'pricing_type'   => wlm_or(wlm_arrval($this->post, 'woocommerce_global_pricing_type'), 'fixed-price'),
            'pricing_amount' => (float) wlm_arrval($this->post, 'woocommerce_global_pricing_amount'),
            'description'    => wlm_arrval($this->post, 'woocommerce_global_pricing_description'),
        ];

        wishlistmember_instance()->save_option('woocommerce_global_pricing', $global_pricing);

        wp_send_json_success();
    }
    /**
     * Callback for wp_ajax_wishlistmember_woocommerce_save_levels_access
     *
     * Save access from levels edit screen
     */
    public function save_levels_access()
    {
        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
            return;
        }
        if (empty($this->post['level_id'])) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid level ID', 'wishlist-member'),
                ]
            );
            return;
        }
        $products = wlm_arrval($this->post, 'woocommerce_access_products');

        $access = wishlistmember_instance()->get_option('woocommerce_products');
        foreach ($access as &$x) {
            $x = array_unique(array_diff((array) $x, [$this->post['level_id'], '', false, 0]));
        }
        unset($x);
        foreach ($products as $pid) {
            $access[ $pid ][] = $this->post['level_id'];
        }
        wishlistmember_instance()->save_option('woocommerce_products', $access);

        wp_send_json_success($access);
    }
    /**
     * Action callback for wp_ajax_wishlistmember_woocommerce_add_level
     */
    public function add_new_level()
    {
        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
            return;
        }

        $name = wlm_trim(wlm_arrval($this->post, 'level_name'));
        if (! $name) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Level name required.', 'wishlist-member'),
                ]
            );
            return;
        }
        $level_data = [
            'name' => $name,
        ];

        $level = \WishListMember\Level::create_level($level_data);
        if (false === $level) {
            wp_send_json_error(
                [
                    // Translators: 1: Level Name.
                    'message' => sprintf(esc_html__('Level name "%1$s" already exists. Please try a different one.', 'wishlist-member'), $name),
                ]
            );
        }
        wp_send_json_success(
            [
                'level_id'   => wlm_arrval($level, 'id'),
                'level_name' => wlm_arrval($level, 'name'),
            ]
        );
    }

    /**
     * Action callback for wp_ajax_wishlistmember_woocommerce_get_pricing_item_view
     * Gets the view for one pricing item
     *
     * @param array $data Optional pricing data to pass. Will use POST data if not provided.
     */
    public function get_pricing_item_view($data = [])
    {
        if (wp_doing_ajax() && ! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            exit;
        }
        if (! is_array($data) || empty($data)) {
            $data = $this->post;
        }
        // Panel open state class.
        $open = wlm_arrval($data, 'open') ? '-open' : '';

        $regular_price = empty($data['product_id']) ? '' : wc_get_product($data['product_id'])->get_regular_price();

        $default        = [
            'level_id'       => '',
            'level_name'     => '',
            'pricing_type'   => 'fixed-price',
            'pricing_amount' => $regular_price,
            'description'    => sprintf(
                // Translators: 1: Level name.
                __('%1$s pricing', 'wishlist-member'),
                wlm_arrval($data, 'level_name')
            ),
        ];
        $vars           = array_merge($default, array_intersect_key($data, $default));
        $level_id       = wlm_trim(wlm_arrval($vars, 'level_id'));
        $level_name     = wlm_trim(wlm_arrval($vars, 'level_name'));
        $pricing_type   = wlm_arrval($vars, 'pricing_type');
        $pricing_amount = wlm_arrval($vars, 'pricing_amount');
        $description    = wlm_arrval($vars, 'description');

        if ($level_id && $level_name && $pricing_type) {
            require WOO_INTEGRATION_DIR . '/views/products-panel-pricing-item.php';
        }

        if (wp_doing_ajax()) {
            exit;
        }
    }

    /**
     *  Set Custom HTML for Variable Products/Subscriptions.
     *
     * @param  float  $orig_price Price.
     * @param  object $product    WooCommerce product object.
     * @return float
     */
    public function custom_variable_price_html($orig_price, $product)
    {

        // Let's get the min/max prices for the variable product.
        $orig_min_price = $product->get_variation_regular_price('min', true);
        $orig_max_price = $product->get_variation_regular_price('max', true);

        if (is_admin()) {
            return $orig_price;
        }

        $var_type = $product->get_type();
        if ('variable-subscription' === $var_type) {
            // Let's get the adjusted mininum pricing on a variation.
            $adjusted_min_price = $this->get_variable_discount($orig_min_price, $product);
            if ($adjusted_min_price >= $orig_min_price) {
                return $orig_price;
            }
            // Translators: %1$s: Adjusted price.
            return sprintf(__('From %1$s', 'wishlist-member'), wc_price($adjusted_min_price));
        } elseif ('variable' === $var_type) {
            // We get adjusted price for both minimun and maximum variation prices.
            $adjusted_min_price = $this->get_variable_discount($orig_min_price, $product);
            $adjusted_max_price = $this->get_variable_discount($orig_max_price, $product);

            $orig_pricing_text    = wc_price($orig_min_price) . ' - ' . wc_price($orig_max_price);
            $updated_pricing_text = wc_price($adjusted_min_price) . ' - ' . wc_price($adjusted_max_price);

            // If no changes then just return the $orig_price.
            if ($adjusted_min_price >= $orig_min_price) {
                return $orig_price;
            }
            return '<span><strike><small>' . $orig_pricing_text . '</small></strike></span> ' . $updated_pricing_text;
        }
    }

    /**
     * Checks if the passed price (min/max price on variations) and $product has discounts.
     * Added this separately as variable products doesn't have regular pricing.
     *
     * @param  float  $orig_price Price.
     * @param  object $product    WooCommerce product object.
     * @return float
     */
    public function get_variable_discount($orig_price, $product)
    {

        $user_id = get_current_user_id();
        if (! $user_id) {
            return $orig_price;
        }

        // If product has no set price then just return it.
        if (empty($orig_price)) {
            return $orig_price;
        }

        $user  = new \WishListMember\User($user_id);
        $price = $orig_price;

        /**
         * Array of custom prices found
         *
         * @var array
         */
        $found_prices = [];

        // Try product pricing.
        $product_pricing = wlm_arrval(wishlistmember_instance()->get_option('woocommerce_product_pricing'), $product->get_id());
        if (is_array($product_pricing)) {
            foreach ($product_pricing as $level_id => $pricing) {
                if (in_array($level_id, $user->active_levels)) {
                    // Found one. get out of the loop.
                    $found_prices[] = $pricing;
                    break;
                }
            }
        }

        // No product price found, try category pricing.
        if (! $found_prices) {
            $category_pricings = wishlistmember_instance()->get_option('woocommerce_category_pricing');
            if (is_array($category_pricings)) {
                foreach ($product->get_category_ids() as $cat_id) {
                    $category_pricing = wlm_arrval($category_pricings, $cat_id);
                    if (is_array($category_pricing)) {
                        foreach ($category_pricing as $level_id => $pricing) {
                            if (in_array($level_id, $user->active_levels)) {
                                // Found one. break out of the loop and check next category.
                                $found_prices[] = $pricing;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // No category price found, try global level pricing.
        if (! $found_prices) {
            $global_pricing = wishlistmember_instance()->get_option('woocommerce_global_pricing');
            if (is_array($global_pricing)) {
                foreach ($global_pricing as $level_id => $pricing) {
                    if ('all' === $level_id) {
                        continue;
                    }
                    if (in_array($level_id, $user->active_levels)) {
                        // Found one. break out of the loop.
                        $found_prices[] = $pricing;
                        break;
                    }
                }
            }
        }

        // No global level pricing. Try all levels pricing.
        if (! $found_prices && ! empty($global_pricing['all'])) {
            $found_prices[] = $global_pricing['all'];
        }

        // At least one custom price found.
        if ($found_prices) {
            $regular_price = $orig_price;
            foreach ($found_prices as $pricing) {
                // Go through each custom price found and compute new price.
                $amount = wlm_arrval($pricing, 'pricing_amount');
                switch (wlm_arrval($pricing, 'pricing_type')) {
                    case 'fixed-price':
                        $xprice = $amount;
                        break;
                    case 'fixed-discount':
                        $xprice = $amount > $regular_price ? 0 : $regular_price - $amount;
                        break;
                    case 'percentage-discount':
                        $xprice = (float) $regular_price * ( 1 - $amount / 100 );
                        break;
                }
                // Use computed price if lower than $price.
                if ($xprice < $price) {
                    $price = $xprice;
                }
            }
        }
        return $price;
    }

    /**
     * Action callback for woocommerce_product_get_price
     *
     * @param  float  $orig_price Price.
     * @param  object $product    WooCommerce product object.
     * @return float
     */
    public function custom_price($orig_price, $product)
    {
        $user_id = get_current_user_id();
        if (! $user_id) {
            return $orig_price;
        }

        // If product has no set price then just return it.
        if (empty($orig_price)) {
            return $orig_price;
        }

        $user  = new \WishListMember\User($user_id);
        $price = $orig_price;

        /**
         * Array of custom prices found
         *
         * @var array
         */
        $found_prices = [];

        // ------------ TRY PRODUCT PRICING ------------
        // Let's check first if the product_id being returned is from variation.
        // If it's for variation then the parent_id shouldn't be 0.
        $wc_get_product = wc_get_product($product->get_id());
        if ($wc_get_product) {
            $product_parent = $wc_get_product->get_parent_id();
        }

        // If it's variation then we use the parent_id of the variation (the actual product's ID)
        // To check for Product Pricing as this is the ID that's saved in our settings.
        if ($product_parent) {
            $product_id = $product_parent;
        } elseif ($wc_get_product) {
                $product_id = $wc_get_product->get_id();
        }

        $product_pricing = wlm_arrval(wishlistmember_instance()->get_option('woocommerce_product_pricing'), $product_id);
        if (is_array($product_pricing)) {
            foreach ($product_pricing as $level_id => $pricing) {
                if (in_array($level_id, $user->active_levels)) {
                    // Found one. get out of the loop.
                    $found_prices[] = $pricing;
                    break;
                }
            }
        }
        // ------------ END OF PRODUCT PRICING ------------
        // No product price found, try category pricing.
        if (! $found_prices) {
            $category_pricings = wishlistmember_instance()->get_option('woocommerce_category_pricing');
            if (is_array($category_pricings)) {
                $cat_ids = $product_parent ? wc_get_product($product_parent)->get_category_ids() : $product->get_category_ids();
                foreach ($cat_ids as $cat_id) {
                    $category_pricing = wlm_arrval($category_pricings, $cat_id);
                    if (is_array($category_pricing)) {
                        foreach ($category_pricing as $level_id => $pricing) {
                            if (in_array($level_id, $user->active_levels)) {
                                // Found one. break out of the loop and check next category.
                                $found_prices[] = $pricing;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // No category price found, try global level pricing.
        if (! $found_prices) {
            $global_pricing = wishlistmember_instance()->get_option('woocommerce_global_pricing');
            if (is_array($global_pricing)) {
                foreach ($global_pricing as $level_id => $pricing) {
                    if ('all' === $level_id) {
                        continue;
                    }
                    if (in_array($level_id, $user->active_levels)) {
                        // Found one. break out of the loop.
                        $found_prices[] = $pricing;
                        break;
                    }
                }
            }
        }

        // No global level pricing. Try all levels pricing.
        if (! $found_prices && ! empty($global_pricing['all'])) {
            $found_prices[] = $global_pricing['all'];
        }

        // At least one custom price found.
        if ($found_prices) {
            $regular_price = $orig_price;
            foreach ($found_prices as $pricing) {
                // Go through each custom price found and compute new price.
                $amount = wlm_arrval($pricing, 'pricing_amount');
                switch (wlm_arrval($pricing, 'pricing_type')) {
                    case 'fixed-price':
                        $xprice = $amount;
                        break;
                    case 'fixed-discount':
                        $xprice = $amount > $regular_price ? 0 : $regular_price - $amount;
                        break;
                    case 'percentage-discount':
                        $xprice = (float) $regular_price * ( 1 - $amount / 100 );
                        break;
                }
                // Use computed price if lower than $price.
                if ($xprice < $price) {
                    $price = $xprice;
                }
            }

            // Sale price if original price is greater than our new price.
            add_filter('woocommerce_product_is_on_sale', $orig_price > $price ? '__return_true' : '__return_false');

            // Append custom price description.
            if (get_class($product) !== 'WC_Product_Variation') {
                $this->pricing_descriptions[ $product->get_id() ] = wlm_arrval($pricing, 'description');
                add_filter('woocommerce_get_price_html', [$this, 'append_pricing_description_to_wc_price'], 10, 2);
            }
        }
        return $price;
    }

    /**
     * Callback for wp_ajax_wishlistmember_woocommerce_save_pricing action
     * Saves product pricing.
     */
    public function save_pricing()
    {
        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => __('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
        }
        $pid = abs(wlm_arrval($this->post, 'product_id'));
        if (! $pid) {
            wp_send_json_error(
                [
                    'message' => __('No product_id provided.', 'wishlist-member'),
                ]
            );
        }
        $this->save_woocommerce_product($pid);
        wp_send_json_success();
    }

    /**
     * Callback for woocommerce_get_price_html filter
     *
     * @param  string $html    HTML.
     * @param  object $product WooCommerce product object.
     * @return string
     */
    public function append_pricing_description_to_wc_price($html, $product)
    {
        $desc = wlm_trim(wlm_arrval($this->pricing_descriptions, $product->get_id()));
        if ($desc && ! is_admin() && in_the_loop() && is_single()) {
            $desc = apply_filters(
                'wishlistmember_woocommerce_member_price_description_html',
                sprintf('<span class="wishlistmember-woocommerce-member-price-description">%s</span><br>', $desc)
            );
            $html = $desc . $html;
        }
        return $html;
    }

    /**
     * Add custom category pricing form
     *
     * Callback for product_cat_add_form_fields action
     * Callback for product_cat_edit_form_fields action
     *
     * @param object|string $term Term object or term name.
     */
    public function add_custom_category_pricing($term = null)
    {
        $edit = is_object($term);
        require_once WOO_INTEGRATION_DIR . '/views/category-pricing.php';
        // Load product panel javascript.
        wp_enqueue_script(
            'wishlist-member-category-pricing',
            plugins_url('assets/category-pricing.js', WOO_HANDLER_FILE),
            [],
            WLM3_PLUGIN_VERSION,
            true
        );
        wp_localize_script(
            'wishlist-member-category-pricing',
            'wlm4woo',
            [
                'term_id' => is_object($term) ? $term->term_id : 0,
                'nonce'   => wp_create_nonce('wlm4woo-ajax-nonce'),
            ]
        );
        wp_enqueue_style(
            'wishlist-member-category-pricing',
            plugins_url('assets/category-pricing.css', WOO_HANDLER_FILE),
            [],
            WLM3_PLUGIN_VERSION
        );
    }

    /**
     * Action callback for wp_ajax_wishlistmember_woocommerce_get_pricing_item_view
     * Gets the view for one pricing item
     *
     * @param array $data Optional pricing data to pass. Will use POST data if not provided.
     */
    public function get_category_pricing_item_view($data = [])
    {
        if (wp_doing_ajax() && ! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            exit;
        }
        if (! is_array($data) || empty($data)) {
            $data = $this->post;
        }
        // Panel open state class.
        $open = wlm_arrval($data, 'open') ? '-open' : '';

        $default        = [
            'level_id'       => '',
            'level_name'     => '',
            'pricing_type'   => 'fixed-price',
            'pricing_amount' => 0,
            'description'    => sprintf(
                // Translators: 1: Level name.
                __('%1$s pricing', 'wishlist-member'),
                wlm_arrval($data, 'level_name')
            ),
        ];
        $vars           = array_merge($default, array_intersect_key($data, $default));
        $level_id       = wlm_trim(wlm_arrval($vars, 'level_id'));
        $level_name     = wlm_trim(wlm_arrval($vars, 'level_name'));
        $pricing_type   = wlm_arrval($vars, 'pricing_type');
        $pricing_amount = wlm_arrval($vars, 'pricing_amount');
        $description    = wlm_arrval($vars, 'description');

        if ($level_id && $level_name && $pricing_type) {
            require WOO_INTEGRATION_DIR . '/views/category-pricing-item.php';
        }

        if (wp_doing_ajax()) {
            exit;
        }
    }

    /**
     * Save category pricing
     *
     * Callback for create_product_cat action
     * Callback for edit_product_cat action
     *
     * @param integer $term_id Term ID.
     */
    public function save_category($term_id)
    {
        $term_id = abs($term_id);
        if ($term_id) {
            $pricing = wishlistmember_instance()->get_option('woocommerce_category_pricing');
            if (! is_array($pricing)) {
                $pricing = [];
            }
            $pricing[ $term_id ] = $this->post['wishlistmember_woo_category_pricing'];
            wishlistmember_instance()->save_option('woocommerce_category_pricing', $pricing);
        }
    }

    /**
     * Ajax handler when saving custom category pricing
     *
     * Callback for wp_ajax_wishlistmember_woocommerce_save_category_pricing
     */
    public function save_custom_category_pricing()
    {
        $term_id = wlm_arrval($this->post, 'term_id');

        if (! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            wp_send_json_error(
                [
                    'message' => __('Invalid nonce. Please refresh your browser and try again.', 'wishlist-member'),
                ]
            );
        }
        if (! $term_id) {
            wp_send_json_error(
                [
                    'message' => __('No term_id provided.', 'wishlist-member'),
                ]
            );
        }

        $this->save_category($term_id);
        wp_send_json_success();
    }

    /**
     * Callback for woocommerce_get_sections_products
     *
     * Adds 'Member Pricing' section to WooCommerce > Settings > Products
     *
     * @param  array $sections Sections.
     * @return array Sections
     */
    public function add_global_pricing_section($sections)
    {
        $sections['wlm4woo-pricing'] = __('Member Pricing', 'wishlist-member');
        return $sections;
    }

    /**
     * Callback for woocommerce_get_settings_products
     *
     * Displays 'Member Pricing' section in WooCommerce > Settings > Products
     *
     * @param  array  $settings        Settings to display.
     * @param  string $current_section Current section being displayed.
     * @return array Settings
     */
    public function show_global_pricing_section($settings, $current_section)
    {
        if ('wlm4woo-pricing' !== $current_section) {
            return $settings;
        }
        return [
            [
                'title' => __('Member Pricing', 'wishlist-member'),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'member-pricing',
            ],
            [
                'type' => 'wlm4woo-pricing-fields',
            ],
        ];
    }

    /**
     * Callback for woocommerce_admin_field_wlm4woo-pricing-fields
     *
     * Loads the global pricing view
     */
    public function show_global_pricing_section_fields()
    {
        $wlmwoo_pricing = wishlistmember_instance()->get_option('woocommerce_global_pricing');
        if (! is_array($wlmwoo_pricing)) {
            $wlmwoo_pricing = [];
        }

        $pricing_levels     = [];
        $wlmwoo_level_names = ['all' => __('All Levels', 'wishlist-member')];
        // Generate options for membership level related dropdowns and grab level names as well.
        foreach (\WishListMember\Level::get_all_levels(true) as $level) {
            // Pricing options.
            $pricing_levels[ $level->ID ] = sprintf('<option value="%s">%s</option>', $level->ID, $level->name);
            $pricing_levels[ $level->ID ] = $level->name;
            // Level names.
            $wlmwoo_level_names[ $level->ID ] = $level->name;
        }
        require_once WOO_INTEGRATION_DIR . '/views/global-pricing.php';
        wp_enqueue_script(
            'wishlist-member-global-pricing',
            plugins_url('assets/global-pricing.js', WOO_HANDLER_FILE),
            [],
            WLM3_PLUGIN_VERSION,
            true
        );
        wp_localize_script(
            'wishlist-member-global-pricing',
            'wlm4woo',
            [
                'nonce' => wp_create_nonce('wlm4woo-ajax-nonce'),
            ]
        );
        wp_enqueue_style(
            'wishlist-member-global-pricing',
            plugins_url('assets/global-pricing.css', WOO_HANDLER_FILE),
            [],
            WLM3_PLUGIN_VERSION
        );
    }

    /**
     * Callback wishlistmember_woocommerce_get_global_pricing_item_view
     * Callback wp_ajax_wishlistmember_woocommerce_get_global_pricing_item_view
     *
     * Display global pricing item
     *
     * @param array $data Optional pricing data to pass. Will use POST data if not provided.
     */
    public function get_global_pricing_item_view($data = [])
    {
        if (wp_doing_ajax() && ! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            exit;
        }
        if (! is_array($data) || empty($data)) {
            $data = $this->post;
        }
        // Panel open state class.
        $open = wlm_arrval($data, 'open') ? '-open' : '';

        $default        = [
            'level_id'       => '',
            'level_name'     => '',
            'pricing_type'   => 'fixed-price',
            'pricing_amount' => 0,
            'description'    => sprintf(
                // Translators: 1: Level name.
                __('%1$s global member pricing', 'wishlist-member'),
                wlm_arrval($data, 'level_name')
            ),
        ];
        $vars           = array_merge($default, array_intersect_key($data, $default));
        $level_id       = wlm_trim(wlm_arrval($vars, 'level_id'));
        $level_name     = wlm_trim(wlm_arrval($vars, 'level_name'));
        $pricing_type   = wlm_arrval($vars, 'pricing_type');
        $pricing_amount = wlm_arrval($vars, 'pricing_amount');
        $description    = wlm_arrval($vars, 'description');

        if ($level_id && $level_name && $pricing_type) {
            require WOO_INTEGRATION_DIR . '/views/global-pricing-item.php';
        }

        if (wp_doing_ajax()) {
            exit;
        }
    }

    /**
     * Callback for woocommerce_update_options_products_wlm4woo-pricing
     *
     * Saves global pricing data
     */
    public function save_global_pricing()
    {
        if (current_action() === 'wp_ajax_wishlistmember_woocommerce_delete_global_pricing_item' && ! wp_verify_nonce(wlm_arrval($this->post, 'nonce'), 'wlm4woo-ajax-nonce')) {
            exit;
        }

        $data = wlm_arrval($this->post, 'wishlistmember_woo_pricing');
        if (! is_array($data)) {
            $data = [];
        }
        wishlistmember_instance()->save_option('woocommerce_global_pricing', $data);
    }

    // === End: Callback functions for action and filter. ===

    /**
     * Updates a member's levels or their status
     * Creates a new member if one doesn't exist yet
     * Used info is gathered from the $order_id
     *
     * @param integer $order_id Order ID.
     * @param string  $status   Status.
     */
    private function status_changed($order_id, $status)
    {
        global $wlm_no_cartintegrationterminate;
        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        if (! function_exists('wc_get_order')) {
            return;
        }
        $woocommerce_products = wishlistmember_instance()->get_option('woocommerce_products');
        $order                = wc_get_order($order_id);
        if (! $order) {
            return;
        }
        $txnid   = $this->generate_transaction_id($order);
        $user_id = wlm_or(wishlistmember_instance()->get_user_id_from_txn_id($txnid), $order->get_customer_id());

        switch ($status) {
            case 'activate':
                $user_exists = true;

                // Initialize $user variable to $user_id.
                $user = $user_id;

                // Take care adding of new customer and levels.
                if (! $user) {
                    $user = get_user_by('email', $order->get_billing_email());
                    if (! $user) {
                        $user_exists = false;
                        $user        = [
                            'first_name'       => $order->get_billing_first_name(),
                            'last_name'        => $order->get_billing_last_name(),
                            'user_email'       => $order->get_billing_email(),
                            'user_login'       => $order->get_billing_email(),
                            'user_pass'        => wlm_generate_password(),

                            // Address.
                            'company'          => $order->get_billing_company(),
                            'address1'         => $order->get_billing_address_1(),
                            'address2'         => $order->get_billing_address_2(),
                            'city'             => $order->get_billing_city(),
                            'state'            => $order->get_billing_state(),
                            'zip'              => $order->get_billing_postcode(),
                            'country'          => WC()->countries->countries[ $order->get_billing_country() ],

                            'SendMailPerLevel' => 1,
                        ];
                    } else {
                        $user = $user->ID;
                    }

                    // If the order is not a subscription and the status is "processing"- set a transient to skip updating registration date when order status changes from "processing" to "completed" later.
                    if (false === ( function_exists('wcs_is_subscription') && wcs_is_subscription($order_id) ) && 'processing' === $order->get_status()) {
                        set_transient('wlm_woo_processing_order_' . $order_id, $user, 120);
                    }
                }

                // Get level IDs attached to purchased products.
                $levels = [];
                foreach ($order->get_items() as $item) {
                    $pid       = $item->get_product()->get_id();
                    $variation = $item->get_product()->is_type('variation');

                    if ($variation) {
                        $pid = wp_get_post_parent_id($pid);
                    }

                    if (isset($woocommerce_products[ $pid ]) && is_array($woocommerce_products[ $pid ])) {
                        $levels = array_merge($levels, $woocommerce_products[ $pid ]);
                    }
                }

                // Filter member levels that are new or with matching transaction IDs only.
                if ($user_exists) {
                    $wlm_user = new \WishListMember\User($user);
                    $levels   = array_unique(
                        array_values(
                            array_filter(
                                $levels,
                                function ($level) use ($wlm_user, $txnid) {
                                    if (empty($level)) {
                                        return false;
                                    }
                                    return isset($wlm_user->levels[ $level ]) ? $wlm_user->levels[ $level ]->TxnID === $txnid : true;
                                }
                            )
                        )
                    );
                }

                if ($levels) {
                    $memlevels = [];
                    if ($user_exists) {
                        $memlevels = array_keys($wlm_user->levels);
                        foreach ((array) $levels as $level) {
                            if (wishlistmember_instance()->level_for_approval($level, $user)) {
                                wishlistmember_instance()->level_for_approval($level, $user, false);
                            }
                        }
                    }
                    foreach ($levels as &$level) {
                        if ($user_exists) {
                            $registration_date = $wlm_user->levels[ $level ]->metas['registration_date'];
                            $expired           = $wlm_user->levels[ $level ]->Expired;
                            if ($expired && isset($wpm_levels[ $level ]['registrationdatereset'])) {
                                $registration_date = null;
                            }
                            if (isset($wpm_levels[ $level ]['registrationdateresetactive'])) {
                                $registration_date = null;
                            }
                        } else {
                            $registration_date = null;
                        }

                        // Let's update registration date if registrationdateresetactive is active and the order status is "completed".
                        if (isset($wpm_levels[ $level ]['registrationdateresetactive']) && 'completed' === $order->get_status()) {
                            $level = [$level, $txnid, $registration_date];
                        } else {
                            $level = in_array($level, $memlevels) ? false : [$level, $txnid, $registration_date];
                        }
                    }
                    unset($level);
                    $levels = ['Levels' => array_diff($levels, [false])];
                    $uid    = 0;

                    // Check if the user was just created when in "processing" status by getting the transient.
                    $transient_user = get_transient('wlm_woo_processing_order_' . $order_id);

                    // If the transient exists and the status is completed, overwrite $user to skip updating membership data since it was already updated when it was in the "processing" status.
                    if (! empty($transient_user) && 'completed' === $order->get_status()) {
                        $user = $transient_user;
                    }

                    if (is_array($user)) {
                        $result = wlmapi_add_member($user + $levels);
                        if ($result['success'] && $result['member'][0]['ID']) {
                            $uid = $result['member'][0]['ID'];
                            if (! is_admin()) {
                                wishlistmember_instance()->wpm_auto_login($result['member'][0]['ID']);
                            }
                        }
                    } else {
                        // Just continue if user value is 'Registration Date Updated' because all processes has already been performed during the status change from "Pending" to "Processing".
                        if ('Registration Date Updated' === $user) {
                            break;
                        }

                        foreach ($levels['Levels'] as $purchased_level) {
                            // Let's continue if SKU is empty.
                            if (! $purchased_level[0]) {
                                continue;
                            }

                            // Let's grab the SKU of the purchased Level/PPP.
                            $p_sku   = $purchased_level[0];
                            $p_txnid = $purchased_level[1];

                            // Initialize sequential upgrade for new users created during WooCommerce checkout process.
                            wishlistmember_instance()->is_sequential($user, true);

                            /*
                             * Let's manually add the user to PPP or to levels using the User() object
                             * as doing it via wlmapi_update_member() has an issue where existing PPPs of
                             * the user are removed when they purchase new PPP/Levels.
                             */
                            $payperpost = preg_match('/^payperpost-(\d+)$/', (string) $p_sku, $match);
                            if ($payperpost) {
                                // Only add the PPP if user doesn't have access to it yet.
                                // Otherwise WLM will send "Congrats" email notification whenever the status of the order.
                                // Connected to the PPP is changed (eg. From processing to Completed).
                                if (! in_array($match[1], $wlm_user->pay_per_posts['_all_'])) {
                                    $wlm_user->add_payperposts($p_sku);
                                    $payperpost = get_post($match[1]);
                                }
                            } else {
                                // For cancelled members.
                                $cancelled = $wlm_user->levels[ $p_sku ]->Cancelled;
                                // Lets make sure that old versions without this settings still works.
                                $resetcancelled = true;
                                $uncancelled    = false;
                                if (isset($wpm_levels[ $p_sku ]['uncancelonregistration'])) {
                                    $resetcancelled = (bool) $wpm_levels[ $p_sku ]['uncancelonregistration'];
                                }
                                if ($cancelled && $resetcancelled) {
                                    wishlistmember_instance()->level_cancelled($p_sku, $user, false);
                                    // Set flag that this registration got uncancelled.
                                    $uncancelled = true;
                                }

                                // For Expired Members.
                                $expired      = $wlm_user->levels[ $p_sku ]->Expired;
                                $resetexpired = (bool) $wpm_levels[ $p_sku ]['registrationdatereset'];
                                if ($expired && $resetexpired) {
                                        wishlistmember_instance()->user_level_timestamp($user, $p_sku, time());
                                } else {
                                    // If levels has expiration and allow reregistration for active members.
                                    $levelexpires     = isset($wpm_levels[ $p_sku ]['expire']) ? (int) $wpm_levels[ $p_sku ]['expire'] : false;
                                    $levelexpires_cal = isset($wpm_levels[ $p_sku ]['calendar']) ? $wpm_levels[ $p_sku ]['calendar'] : false;
                                    $resetactive      = (bool) $wpm_levels[ $p_sku ]['registrationdateresetactive'];
                                    if ($levelexpires && $resetactive) {
                                        // Get the registration date before it gets updated because we will use it later.
                                        $levelexpire_regdate = wishlistmember_instance()->Get_UserLevelMeta($user, $p_sku, 'registration_date');

                                        $levelexpires_cal = in_array($levelexpires_cal, ['Days', 'Weeks', 'Months', 'Years'], true) ? $levelexpires_cal : false;
                                        if ($levelexpires_cal && $levelexpire_regdate) {
                                            list( $xdate, $xfraction )                                 = explode('#', (string) $levelexpire_regdate);
                                            list( $xyear, $xmonth, $xday, $xhour, $xminute, $xsecond ) = preg_split('/[- :]/', $xdate);
                                            if ('Days' === $levelexpires_cal) {
                                                $xday = $levelexpires + $xday;
                                            }
                                            if ('Weeks' === $levelexpires_cal) {
                                                $xday = ( $levelexpires * 7 ) + $xday;
                                            }
                                            if ('Months' === $levelexpires_cal) {
                                                $xmonth = $levelexpires + $xmonth;
                                            }
                                            if ('Years' === $levelexpires_cal) {
                                                $xyear = $levelexpires + $xyear;
                                            }
                                            // If the registration was just uncancelled and the level is a fixed term, use the current date as registration date instead of the calculated date.
                                            $updated_reg_date = $uncancelled ? time() : mktime($xhour, $xminute, $xsecond, $xmonth, $xday, $xyear);

                                            unset($uncancelled);

                                            wishlistmember_instance()->user_level_timestamp($user, $p_sku, $updated_reg_date);
                                        }
                                    }
                                }

                                $wlm_user->AddLevel($p_sku, $p_txnid);

                                // Create transient for this order so it won't update the registration date twice when the status changes from "Pending" to "Processing" and "Processing" to "Completed" for existing users.
                                set_transient('wlm_woo_processing_order_' . $order_id, 'Registration Date Updated', 120);
                            }

                            $email_macros['[memberlevel]'] = $payperpost ? $payperpost->post_title : wlm_trim($wpm_levels[ $p_sku ]['name']);
                            $email_macros['[password]']    = $user_pass ? $user_pass : '********';

                            // Only send email if $email_macros['[memberlevel]'] has value.
                            if (! empty($email_macros['[memberlevel]'])) {
                                wishlistmember_instance()->email_template_level = $p_sku;
                                wishlistmember_instance()->send_email_template('admin_new_member_notice', $user, $email_macros, wishlistmember_instance()->get_option('email_sender_address'));

                                wishlistmember_instance()->email_template_level = $p_sku;
                                wishlistmember_instance()->send_email_template('registration', $user, $email_macros);
                            }
                        }
                        $uid = $user;
                    }
                    if ($uid) {
                        // Link order to user.
                        wc_update_new_customer_past_orders($uid);
                        // Update billing and shipping meta.
                        $metas = get_post_meta($order_id);
                        foreach ($metas as $key => $value) {
                            if (preg_match('/^_((billing|shipping)_.+)/', (string) $key, $match)) {
                                update_user_meta($uid, $match[1], $value[0]);
                            }
                        }
                    }
                }

                $old                             = $wlm_no_cartintegrationterminate;
                $wlm_no_cartintegrationterminate = true;
                wlm_post_data()['sctxnid']       = $txnid;

                wishlistmember_instance()->shopping_cart_reactivate();
                $wlm_no_cartintegrationterminate = $old;
                break;
            case 'deactivate':
                $old                             = $wlm_no_cartintegrationterminate;
                $wlm_no_cartintegrationterminate = true;
                wlm_post_data()['sctxnid']       = $txnid;

                if (function_exists('wcs_is_subscription') && wcs_is_subscription($order_id)) {
                    $subscription   = wcs_get_subscription($order_id);
                    $related_orders = $subscription->get_related_orders('ids', 'renewal');

                    foreach ($related_orders as $order_id) {
                        $order            = wc_get_order($order_id);
                        $txnid            = $this->generate_transaction_id($order);
                        $user_level_txnid = wishlistmember_instance()->get_membership_levels_txn_ids($user_id, $txnid);
                        if (! empty($user_level_txnid)) {
                            wlm_post_data()['sctxnid'] = $txnid;
                            wishlistmember_instance()->shopping_cart_deactivate();
                        }
                    }

                    if (! $related_orders) {
                        $parent_order_id = $subscription->get_parent_id();

                        if ($parent_order_id) {
                            if (strpos($txnid, 'WooCommerce#') === false) {
                                $order = wc_get_order($parent_order_id);
                                $txnid = $this->generate_transaction_id($order);
                            }

                            $user_level_txnid = wishlistmember_instance()->get_membership_levels_txn_ids($user_id, $txnid);
                            if (! empty($user_level_txnid)) {
                                wlm_post_data()['sctxnid'] = $txnid;
                                wishlistmember_instance()->shopping_cart_deactivate();
                            }
                        }
                    }
                }

                wlm_post_data()['sctxnid'] = $txnid;
                wishlistmember_instance()->shopping_cart_deactivate();
                $wlm_no_cartintegrationterminate = $old;
                break;
            case 'pending':
                if ($user_id) {
                    $levels = [];
                    foreach ($order->get_items() as $item) {
                        $pid       = $item->get_product()->get_id();
                        $variation = $item->get_product()->is_type('variation');

                        if ($variation) {
                            $pid = wp_get_post_parent_id($pid);
                        }

                        if (isset($woocommerce_products[ $pid ]) && is_array($woocommerce_products[ $pid ])) {
                            $levels = array_merge($levels, $woocommerce_products[ $pid ]);
                        }
                    }
                    if ($levels) {
                        foreach ((array) $levels as $level) {
                            if (! empty($level)) {
                                wishlistmember_instance()->level_for_approval($level, $user_id, true);
                            }
                        }
                    }

                    if (function_exists('wcs_is_subscription') && wcs_is_subscription($order_id)) {
                        $subscription   = wcs_get_subscription($order_id);
                        $related_orders = $subscription->get_related_orders('ids', 'renewal');

                        foreach ($related_orders as $order_id) {
                            $order  = wc_get_order($order_id);
                            $txnid  = $this->generate_transaction_id($order);
                            $levels = array_intersect(
                                array_keys((array) wishlistmember_instance()->get_membership_levels_txn_ids($user_id, $txnid)),
                                wishlistmember_instance()->get_membership_levels($user_id)
                            );

                            foreach ((array) $levels as $level) {
                                wishlistmember_instance()->level_for_approval($level, $user_id, true);
                            }
                        }
                    }
                }
                break;
            case 'remove':
                if ($user_id) {
                    $levels = [];
                    foreach ($order->get_items() as $item) {
                        $pid = $item->get_product()->get_id();
                        if (isset($woocommerce_products[ $pid ]) && is_array($woocommerce_products[ $pid ])) {
                            $levels = array_merge($levels, $woocommerce_products[ $pid ]);
                        }
                    }
                    if ($levels) {
                        foreach ($levels as $level) {
                            $expired = wishlistmember_instance()->level_expired($level, $user_id);
                            if ($expired && isset($wpm_levels[ $level ]['registrationdatereset'])) {
                                wlmapi_update_member($user_id, ['RemoveLevels' => [$level]]);
                            }
                            if (isset($wpm_levels[ $level ]['registrationdateresetactive'])) {
                                wlmapi_update_member($user_id, ['RemoveLevels' => [$level]]);
                            }
                        }
                    }
                }
                break;
        }
    }

    /**
     * Removes levels from a member based on transaction ID
     *
     * @param string $txnid Transaction ID.
     */
    private function remove_levels($txnid)
    {
        $user_id = wishlistmember_instance()->get_user_id_from_txn_id($txnid);
        if ($user_id) {
            $levels = wishlistmember_instance()->get_membership_levels_txn_ids($user_id, $txnid);
            if ($levels) {
                wlmapi_update_member($user_id, ['RemoveLevels' => array_keys($levels)]);
            }
        }
    }

    /**
     * Generates transaction id from order WooCommerce object
     *
     * @param object $order WC_Order object.
     */
    private function generate_transaction_id($order)
    {
        return 'WooCommerce#' . $order->get_parent_id() . '-' . $order->get_order_number();
    }
}
