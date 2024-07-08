<?php

/**
 * Constant Contact admin UI init
 *
 * @package WishListMember/Autoresponders
 */

$data = $ar_data[ $config['id'] ];
if (empty($data['api_url'])) {
    $data['api_url'] = 'https://api2.getresponse.com';
}
thirdparty_integration_data($config['id'], $data);
