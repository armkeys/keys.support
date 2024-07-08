<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib;

/**
 * Class OAuthErrorObject.
 *
 * @property string $error
 * @property string $error_description
 */
class OAuthErrorObject extends StripeObject
{
    /**
     * Refreshes this object using the provided values.
     *
     * @param array                                 $values
     * @param null|array|string|Util\RequestOptions $opts
     * @param boolean                               $partial defaults to false
     */
    public function refreshFrom($values, $opts, $partial = false)
    {
        // Unlike most other API resources, the API will omit attributes in.
        // Error objects when they have a null value. We manually set default.
        // Values here to facilitate generic error handling.
        $values = \array_merge([
            'error' => null,
            'error_description' => null,
        ], $values);
        parent::refreshFrom($values, $opts, $partial);
    }
}
