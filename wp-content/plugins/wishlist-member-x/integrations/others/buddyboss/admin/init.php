<?php

$data = (array) $this->get_option('buddyboss_settings');

// Default setting of profile type uses post id but we are using post name, so we update it here.
if (isset($data['type']) && count($data['type']) > 0) {
    $types = $data['type'];
    foreach ($types as $postid => $value) {
        if (is_numeric($postid)) {
            $p = get_post($postid);
            if ($p) {
                if (isset($p->post_name)) {
                    unset($data['type'][ $postid ]);
                    $data['type'][ $p->post_name ] = $value;
                }
            }
        }
    }
}

thirdparty_integration_data(
    $config['id'],
    [
        'buddyboss_settings' => $data,
    ]
);
