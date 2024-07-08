<?php

/**
 *  WooCommerce integration handler
 *
 * @package    WishListMember
 * @subpackage PaymentProviders
 */

namespace WishListMember\PaymentProviders;

const WOO_HANDLER_FILE    = __FILE__;
const WOO_INTEGRATION_DIR = __DIR__;

require_once __DIR__ . '/includes/class-woocommerce-integration.php';
WooCommerce_Integration::instance();
