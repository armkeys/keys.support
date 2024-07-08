<?php

$data = (array) $this->get_option('slack_settings');

if (empty($data['added']) || ! is_array($data['added'])) {
    $data['added'] = ['active' => []];
}
if (empty($data['removed']) || ! is_array($data['removed'])) {
    $data['removed'] = ['active' => []];
}
if (empty($data['cancelled']) || ! is_array($data['cancelled'])) {
    $data['cancelled'] = ['active' => []];
}

thirdparty_integration_data(
    $config['id'],
    ['slack_settings' => $data]
);
