<?php

$data = $ar_data[ $config['id'] ];

if (isset($data['access_tokens'])) {
    $data['connected_auth_key'] = sprintf(
        '%s|%s',
        preg_replace('/\|$/', '', $data['auth_key']),
        implode('|', $data['access_tokens'])
    );
    $data['auth_key']           = $data['connected_auth_key'];
}


$parameters = [
    'aweberapi_connect' => 1,
    'wl'                => wlm_get_data()['wl' ],
    'page'              => wlm_get_data()['page' ],
];

$callback = add_query_arg($parameters, admin_url('admin.php'));

thirdparty_integration_data($config['id'], $data);
