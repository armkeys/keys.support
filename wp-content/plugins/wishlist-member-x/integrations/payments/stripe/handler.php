<?php

/**
 * Stripe Payment integration initialization
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

namespace WishListMember\PaymentProviders\Stripe;

if (! ( extension_loaded('curl') && function_exists('mb_detect_encoding') && ! class_exists('Stripe', false) )) {
    return;
}

require_once __DIR__ . '/includes/lib/stripe-php/init.php';
require_once __DIR__ . '/includes/constants.php';
require_once __DIR__ . '/includes/class-stripe-shortcodes.php';
require_once __DIR__ . '/includes/class-stripe-forms.php';
require_once __DIR__ . '/includes/class-stripe-integration.php';
require_once __DIR__ . '/includes/class-stripe-connect.php';
require_once __DIR__ . '/includes/class-authenticator-service.php';
require_once __DIR__ . '/includes/class-auth-utils.php';
require_once __DIR__ . '/includes/class-gateway-utils.php';
require_once __DIR__ . '/includes/hooks.php';

Stripe_Shortcodes::instance();
Stripe_Integration::instance();
