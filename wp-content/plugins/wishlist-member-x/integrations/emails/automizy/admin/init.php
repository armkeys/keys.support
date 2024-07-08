<?php

$data          = $ar_data[ $config['id'] ];
$data['tags']  = [];
$data['lists'] = [];
thirdparty_integration_data($config['id'], $data);
