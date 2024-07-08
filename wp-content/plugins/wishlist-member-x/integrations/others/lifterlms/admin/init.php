<?php

$data = (array) $this->get_option('lifterlms_settings');

thirdparty_integration_data(
    $config['id'],
    [
        'lifterlms_settings' => $data,
    ]
);
