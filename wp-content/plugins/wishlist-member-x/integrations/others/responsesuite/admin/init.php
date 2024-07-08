<?php

$wlmapikeys = new \WishListMember\APIKey();
$wlmapikey  = wlm_or($wlmapikeys->get('others/' . $config['id']), [$wlmapikeys, 'add'], 'others/' . $config['id']);
