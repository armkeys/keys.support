<?php

$data = (array) $this->get_option('zapier_settings');

$key = wlm_trim($data['key']);
if (empty($key)) {
    $data['key'] = sha1(microtime() . time() . rand());
    $this->save_option('zapier_settings', $data);
}

thirdparty_integration_data(
    $config['id'],
    [
        'zapier_settings' => $data,
    ]
);
