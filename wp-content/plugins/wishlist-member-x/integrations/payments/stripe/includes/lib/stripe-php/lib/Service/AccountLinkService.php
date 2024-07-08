<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib\Service;

class AccountLinkService extends \WishListMember\PaymentProviders\Stripe\PHPLib\Service\AbstractService
{
    /**
     * Creates an AccountLink object that includes a single-use Stripe URL that the
     * platform can redirect their user to in order to take them through the Connect
     * Onboarding flow.
     *
     * @param null|array                                                                    $params
     * @param null|array|\WishListMember\PaymentProviders\Stripe\PHPLib\Util\RequestOptions $opts
     *
     * @throws \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\ApiErrorException if the request fails
     *
     * @return \WishListMember\PaymentProviders\Stripe\PHPLib\AccountLink
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/account_links', $params, $opts);
    }
}
