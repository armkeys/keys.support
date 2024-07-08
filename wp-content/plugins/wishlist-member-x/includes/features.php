<?php

/**
 * Load WishList Member features
 *
 * @package WishListMember\Loaders
 */

foreach (glob(dirname(__DIR__) . '/features/*.php') as $x) {
    require_once $x;
}

foreach (glob(dirname(__DIR__) . '/features/*/main.php') as $x) {
    require_once $x;
}
