<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib\Exception;

/**
 * PermissionException is thrown in cases where access was attempted on a
 * resource that wasn't allowed.
 */
class PermissionException extends ApiErrorException
{
}
