<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib;

/**
 * Class CheckoutSession
 *
 * @property string $id
 * @property string $object
 * @property bool $livemode
 *
 * @package WishListMember\PaymentProviders\Stripe\PHPLib
 */
class CheckoutSession extends ApiResource
{
    const OBJECT_NAME = 'checkout_session';

    use ApiOperations\Create;
}
