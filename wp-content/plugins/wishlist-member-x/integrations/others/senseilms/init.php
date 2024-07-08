<?php

/**
 * SenseiLMS init
 *
 * @package WishListMember/OtherProviders
 */

if (! class_exists('WLM3_SenseiLMS_Hooks')) {
    /**
     * WLM3_SenseiLMS_Hooks class
     */
    class WLM3_SenseiLMS_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_senseilms_check_plugin', [$this, 'check_plugin']);
        }

        /**
         * Check for plugin existence
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
                if (in_array('Sensei LMS', $active_plugins, true) || isset($active_plugins['sensei-lms/sensei-lms.php']) || is_plugin_active('sensei-lms/sensei-lms.php')) {
                    $data['status']  = true;
                    $data['message'] = 'Sensei LMS plugin is installed and activated';
                    $the_posts       = new WP_Query(
                        [
                            'post_type' => 'course',
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
                        $data['message'] = 'You need to create a Sensei LMS course in order proceed';
                    }
                    if (! class_exists('Sensei_Utils')) {
                        $data['status']  = false;
                        $data['message'] = 'Sensei LMS is activated but the resources needed are missing. Please contact support.';
                    }
                } else {
                    $data['message'] = 'Please install and activate your SenseiLMS plugin';
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_SenseiLMS_Hooks();
}
