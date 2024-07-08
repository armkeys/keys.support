<?php

$data = $ar_data[ $config['id'] ];
// If(empty($data['api_url'])) $data['api_url'] = 'https://api2.getresponse.com';
if (empty($data['api_url'])) {
    $data['api_url'] = 'https://api.getresponse.com/v3';
}
thirdparty_integration_data($config['id'], $data);
