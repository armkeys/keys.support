<?php

$wlmapikeys = new \WishListMember\APIKey();
$wlmapikey  = wlm_or($wlmapikeys->get('payments/' . $config['id']), [$wlmapikeys, 'add'], 'payments/' . $config['id']);
