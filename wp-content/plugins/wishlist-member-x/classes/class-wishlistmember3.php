<?php

/**
 * WishList Member 3.0 Parent Class
 *
 * @package WishListMember
 */

if (! class_exists('WishListMember')) {
    /**
     * WishList Member 3.0
     */
    class WishListMember extends WishListMember3_Hooks
    {
        // Load traits.
        use \WishListMember\Backup_Methods;
        use \WishListMember\Content_Methods;
        use \WishListMember\Core_Methods;
        use \WishListMember\Email_Broadcast_Methods;
        use \WishListMember\Email_Methods;
        use \WishListMember\Import_Export_Methods;
        use \WishListMember\Integration_Methods;
        use \WishListMember\Level_Methods;
        use \WishListMember\Marketplace_Methods;
        use \WishListMember\Member_Methods;
        use \WishListMember\Options;
        use \WishListMember\Payment_Integration_Methods;
        use \WishListMember\Payperpost_Methods;
        use \WishListMember\Plugin_Update_Methods;
        use \WishListMember\Post_Editor_Tinymce_Methods;
        use \WishListMember\Protection_Methods;
        use \WishListMember\Registration_Methods;
        use \WishListMember\Shortcodes_Methods;
        use \WishListMember\System_Pages_Methods;
        use \WishListMember\User_Methods;
        use \WishListMember\User_Level_Methods;
        use \WishListMember\Utility_Methods;
        use \WishListMember\Widget_Methods;
        use \WishListMember\Script_Translations;
        use \WishListMember\Wizard_Methods;
        use \WishListMember\Checklist_Methods;

        /**
         * Original $_GET content
         *
         * @var array
         */
        protected $get_data = [];

        /**
         * Original $_POST content
         *
         * @var array
         */
        protected $post_data = [];

        /**
         * Merged content of $get_data and $post_data
         * with $post_data overriding $get_data if key
         * exists in both.
         *
         * @var array
         */
        protected $request_data = [];

        /**
         * Original $_SERVER content
         *
         * @var array
         */
        protected $server_data = [];

        /**
         * Original $_COOKIE content
         *
         * @var array
         */
        protected $cookie_data = [];


        /**
         * GMT Offset in seconds
         *
         * @var integer
         */
        public $gmt = 0;

        /**
         * WP Upload path
         *
         * @var string
         */
        public $wp_upload_path = '';

        /**
         * Relative WP Upload path
         *
         * @var string
         */
        public $wp_upload_path_relative = '';

        /**
         * Registration cookie timeout
         *
         * @var integer
         */
        public $registration_cookie_timeout = 600;
        /**
         * Array of integration errors
         *
         * @var array
         */
        public $integration_errors = [];

        /**
         * Active integration indicators
         *
         * @var array
         */
        public $active_integration_indicators = [];

        /**
         * Content Control object
         *
         * @var \WishListMember\Features\Content_Control
         */
        public $content_control = null;

        /**
         * WishListAcl object (WishList Access Control)
         *
         * @var WishListAcl
         */
        public $access_control = null;

        /**
         * WishList Member options table.
         *
         * @var string
         */
        protected $options_table = '';

        /**
         * Constructor
         */
        public function __construct()
        {
            global $wpdb;

            $this->get_data     = (array) filter_input_array(INPUT_GET);
            $this->post_data    = (array) filter_input_array(INPUT_POST);
            $this->cookie_data  = (array) filter_input_array(INPUT_COOKIE);
            $this->server_data  = (array) filter_input_array(INPUT_SERVER);
            $this->request_data = array_merge($this->get_data, $this->post_data);

            $this->options_table = $wpdb->prefix . 'wlm_options';

            $this->gmt = get_option('gmt_offset') * 3600;

            $this->wp_upload_path          = wlm_arrval(wp_upload_dir(), 'basedir');
            $this->wp_upload_path_relative = str_replace(ABSPATH, '', $this->wp_upload_path);

            $this->registration_cookie_timeout = wlm_or($this->get_option('reg_cookie_timeout') + 0, 600);
        }

        /**
         * Constructor
         *
         * @param string $plugin_file Plugin File.
         * @param string $sku         Plugin SKU.
         * @param string $menu_id     Menu ID.
         * @param string $title       Plugin Title.
         * @param string $link        Plugin Link.
         */
        public function initialize($plugin_file, $sku, $menu_id, $title, $link)
        {
            $this->access_control = new \WishListAcl();

            $this->Constructor(__FILE__, $sku, $menu_id, $title, $link);
            $this->constructor3($plugin_file, $sku, $menu_id, $title, $link);

            // Initialize hooks.
            $this->hooks_init();

            /**
             * Action to load WishList Member hooks.
             *
             * @since 3.14
             * @param \WishListMember $this WishList Member object.
             */
            do_action('wishlistmember_register_hooks', $this);

            /**
             * Action to load WishList Member integrations
             *
             * @since 3.14
             * @param \WishListMember $this WishList Member object.
             */
            do_action('wishlistmember_load_integrations', $this);
        }

        /**
         * Return the value of the any of the following properties
         * $get_data, $post_data, $request_data, $cookie_data, $server_data
         *
         * @param  string         $property   Any of 'get_data', 'post_data', 'request_data', 'cookie_data', 'server_data'
         * @param  string|integer ...$indexes Index. More than one index can be provided for multidimensional arrays and objects.
         * @return mixed
         */
        private function get_data($property, ...$indexes)
        {
            if (! in_array($property, ['get_data', 'post_data', 'request_data', 'cookie_data', 'server_data'], true)) {
                return [];
            }
            $value = $indexes ? wlm_arrval($this->$property, ...array_values($indexes)) : wlm_arrval($this->$property);
            if ($indexes && is_null($value)) {
                if ('get_data' === $property) {
                    $value = wlm_arrval(wlm_get_data(true), ...array_values($indexes));
                }
                if ('post_data' === $property) {
                    $value = wlm_arrval(wlm_post_data(true), ...array_values($indexes));
                }
            }
            return $value;
        }
    }
}
