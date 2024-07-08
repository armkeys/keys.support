<?php

/**
 * TutorLMS init
 *
 * @package WishListMember/OtherProviders
 */

if (! class_exists('WLM3_TutorLMS_Hooks')) {
    /**
     * WLM3_TutorLMS_Hooks class
     */
    class WLM3_TutorLMS_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_tutorlms_check_plugin', [$this, 'check_plugin']);
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
                if (in_array('TutorLMS', $active_plugins, true) || isset($active_plugins['tutor/tutor.php'])) {
                    $data['status']  = true;
                    $data['message'] = 'Tutor LMS plugin is installed and activated';
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
                        $data['status']  = false;
                        $data['message'] = 'You need to create a Tutor LMS course in order proceed';
                    }
                    if (! function_exists('tutor_utils')) {
                        $data['status']  = false;
                        $data['message'] = 'Tutor LMS is activated but the functions needed are missing. Please contact support.';
                    }
                } else {
                    $data['message'] = 'Please install and activate your Tutor LMS plugin';
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_TutorLMS_Hooks();
}
