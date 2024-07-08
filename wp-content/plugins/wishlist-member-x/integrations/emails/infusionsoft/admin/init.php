<?php

$data            = $ar_data[ $config['id'] ];
$data['ismname'] = preg_replace('/\.infusionsoft\.com$/', '', $data['ismname']);
thirdparty_integration_data($config['id'], $data);
