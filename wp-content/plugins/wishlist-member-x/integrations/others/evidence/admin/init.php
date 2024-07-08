<?php

$data = (array) $this->get_option('evidence_settings');

if (empty($data['active']) || ! is_array($data['active'])) {
    $data['active'] = [];
}

thirdparty_integration_data(
    $config['id'],
    ['evidence_settings' => $data]
);
