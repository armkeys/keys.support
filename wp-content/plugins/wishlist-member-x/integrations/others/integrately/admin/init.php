<?php

/**
 * Integrately admin interface init
 *
 * @package WishListMember\Integrations\Others\Integrately
 */

$wlmapikeys = new \WishListMember\APIKey();
$wlmapikey  = 'others/' . $config['id'];
$wlmapikey  = wlm_or($wlmapikeys->get($wlmapikey), [$wlmapikeys, 'add'], $wlmapikey);
