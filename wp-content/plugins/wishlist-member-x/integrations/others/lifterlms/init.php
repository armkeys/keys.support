<?php

/**
 * LifterLMS init
 *
 * @package WishListMember/OtherProviders
 */

if (! class_exists('WLM3_LifterLMS_Hooks')) {
    /**
     * WLM3_LifterLMS_Hooks class
     */
    class WLM3_LifterLMS_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_lifterlms_check_plugin', [$this, 'check_plugin']);
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
                if (in_array('LifterLMS', $active_plugins, true) || isset($active_plugins['lifterlms/lifterlms.php']) || is_plugin_active('lifterlms/lifterlms.php')) {
                    $data['status']  = true;
                    $data['message'] = 'Lifter LMS plugin is installed and activated';
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
                        $data['message'] = 'You need to create a Lifter LMS course in order proceed';
                    }

                    $the_memberships = new WP_Query(
                        [
                            'post_type' => 'llms_membership',
                            'nopaging'  => true,
                        ]
                    );
                    $memberships     = [];
                    if (count($the_memberships->posts)) {
                        foreach ($the_memberships->posts as $c) {
                            $memberships[ $c->ID ] = $c->post_title;
                        }
                        $data['memberships'] = $memberships;
                    }

                    if (! function_exists('llms_enroll_student')) {
                        $data['status']  = false;
                        $data['message'] = 'Lifter LMS is activated but the resources needed are missing. Please contact support.';
                    }
                } else {
                    $data['message'] = 'Please install and activate your LifterLMS plugin';
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_LifterLMS_Hooks();
}
