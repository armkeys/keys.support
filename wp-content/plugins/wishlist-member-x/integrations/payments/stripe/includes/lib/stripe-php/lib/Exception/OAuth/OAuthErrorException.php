<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }

        return \WishListMember\PaymentProviders\Stripe\PHPLib\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
