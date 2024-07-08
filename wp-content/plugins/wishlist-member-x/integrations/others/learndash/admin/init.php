<?php

$active_plugins = wlm_get_active_plugins();
if (in_array('LearnDash LMS', $active_plugins) || isset($active_plugins['sfwd-lms/sfwd_lms.php']) || is_plugin_active('sfwd-lms/sfwd_lms.php')) {
    $the_posts = new WP_Query(
        [
            'post_type' => 'sfwd-courses',
            'nopaging'  => true,
        ]
    );
    if (count($the_posts->posts) <= 0) {
        printf('<p>You need to create a LearnDash course in order proceed</p>');
        return false;
    }
    if (! function_exists('ld_update_course_access')) {
        printf('<p>LearnDash LMS is activated but the functions needed are missing. Please contact support.</p>');
        return false;
    }
} else {
    printf('<p>Please install and activate your LearnDash plugin</p>');
    return false;
}


$data = (array) $this->get_option('learndash_settings');

thirdparty_integration_data(
    $config['id'],
    [
        'learndash_settings' => $data,
    ]
);
