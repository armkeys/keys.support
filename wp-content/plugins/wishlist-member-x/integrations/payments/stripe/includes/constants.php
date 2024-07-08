<?php

/**
 * Stripe Integration defines file
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

/**
 * Auth Service Domain
 *
 * @var string
 */
if (! defined('WLM_AUTH_SERVICE_DOMAIN')) {
    define('WLM_AUTH_SERVICE_DOMAIN', 'auth.wishlistmember.com');
}
define('WLM_AUTH_SERVICE_URL', 'https://' . WLM_AUTH_SERVICE_DOMAIN);

/**
 * Stripe Service Domain
 *
 * @var string
 */
if (! defined('WLM_STRIPE_SERVICE_DOMAIN')) {
    define('WLM_STRIPE_SERVICE_DOMAIN', 'stripe.wishlistmember.com');
}
define('WLM_STRIPE_SERVICE_URL', 'https://' . WLM_STRIPE_SERVICE_DOMAIN);


define('WLM_SCRIPT_URL', site_url('/index.php?plugin=wlm'));
