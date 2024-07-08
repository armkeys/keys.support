<?php

/**
 * ELearnCommerce init
 *
 * @package WishListMember/OtherProviders
 */

if (! class_exists('WLM3_ELearnCommerce_Hooks')) {
    /**
     * WLM3_ELearnCommerce_Hooks class
     */
    class WLM3_ELearnCommerce_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_elearncommerce_check_plugin', [$this, 'check_plugin']);
        }

        /**
         * Check if the plugin exists.
         */
        public function check_plugin()
        {
            $data = [
                'status'  => false,
                'message' => '',
                'courses' => [],
            ];
            // Connect and get info.
            try {
                $active_plugins = wlm_get_active_plugins();
                if (in_array('eLearnCommerce', $active_plugins, true) || isset($active_plugins['wpep/wpextplan.php'])) {
                    $data['status']  = true;
                    $data['message'] = 'eLearnCommerce plugin is installed and activated';
                    $the_posts       = new WP_Query(
                        [
                            'post_type' => 'courses',
                            'nopaging'  => true,
                        ]
                    );
                    $courses         = [];
                    if (count($the_posts->posts)) {
                        foreach ($the_posts->posts as $c) {
                            $courses[ $c->ID ] = $c->post_title;
                        }
                        $data['courses'] = $courses;
                    } else {
                        $data['message'] = 'You need to create a eLearnCommerce course in order proceed';
                    }
                    if (! function_exists('wpep_user_course_started_event')) {
                        $data['status']  = false;
                        $data['message'] = 'eLearnCommerce is activated but the functions needed are missing. Please contact support.';
                    }
                } else {
                    $data['message'] = 'Please install and activate your eLearnCommerce plugin';
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_ELearnCommerce_Hooks();
}
