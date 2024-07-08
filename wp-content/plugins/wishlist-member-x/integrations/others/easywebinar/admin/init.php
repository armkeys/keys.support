<?php

if (! class_exists('webinar_db_interaction')) {
    printf('<p>This integration requires the <a href="%s" target="_blank">EasyWebinar</a> plugin.</p>', esc_url($config['link']));
    return false;
}

$data = ['webinar' => [$config['id'] => $webinar_data[ $config['id'] ]]];

$wdb          = new webinar_db_interaction();
$webinar_list = $wdb->get_all_webinar();

// Empty option to allow blank fields.
$data['webinar'][ $config['id'] ]['webinar_options'][] = [
    'id'    => '',
    'value' => '',
    'text'  => '',
    'name'  => '',
];
foreach ($webinar_list as $w) {
    $data['webinar'][ $config['id'] ]['webinar_options'][] = [
        'id'    => $w->webinar_id_pk,
        'value' => $w->webinar_id_pk,
        'text'  => $w->webinar_event_name,
        'name'  => $w->webinar_event_name,
    ];
}

thirdparty_integration_data($config['id'], $data);
