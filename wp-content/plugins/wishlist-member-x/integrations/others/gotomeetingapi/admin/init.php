<?php

$data = ['webinar' => [$config['id'] => $webinar_data[ $config['id'] ]]];
thirdparty_integration_data($config['id'], $data);

require_once dirname(__DIR__) . '/handler.php';

$obj   = new \WishListMember\Webinars\GTMAPI_OAuth_En();
$oauth = new \WishListMember\Webinars\GTMAPI_OAuth($obj);
