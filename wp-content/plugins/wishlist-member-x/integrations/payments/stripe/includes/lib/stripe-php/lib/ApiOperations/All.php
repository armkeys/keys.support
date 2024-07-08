<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib\ApiOperations;

/**
 * Trait for listable resources. Adds a `all()` static method to the class.
 *
 * This trait should only be applied to classes that derive from StripeObject.
 */
trait All
{
    /**
     * @param null|array        $params
     * @param null|array|string $opts
     *
     * @throws \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\ApiErrorException if the request fails
     *
     * @return \WishListMember\PaymentProviders\Stripe\PHPLib\Collection of ApiResources
     */
    public static function all($params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('get', $url, $params, $opts);
        $obj                   = \WishListMember\PaymentProviders\Stripe\PHPLib\Util\Util::convertToStripeObject($response->json, $opts);
        if (!( $obj instanceof \WishListMember\PaymentProviders\Stripe\PHPLib\Collection )) {
            throw new \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\UnexpectedValueException(
                'Expected type ' . \WishListMember\PaymentProviders\Stripe\PHPLib\Collection::class . ', got "' . \get_class($obj) . '" instead.'
            );
        }
        $obj->setLastResponse($response);
        $obj->setFilters($params);

        return $obj;
    }
}
