<?php

/**
 * Load WishList Member compatibility code
 *
 * @package WishListMember\Loaders
 */

/**
 * Loads the compatibility files for each integration if it exists
 */
foreach (glob(dirname(__DIR__) . '/compatibility/*.php') as $x) {
    require_once $x;
}
