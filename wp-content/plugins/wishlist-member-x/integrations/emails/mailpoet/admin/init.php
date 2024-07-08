<?php

$data = $ar_data[ $config['id'] ];

if (empty($data)) {
    $data = [];
}
$data['mailpoet_lists'] = $wlm_mailpoet_api->getLists();

thirdparty_integration_data($config['id'], $data);
