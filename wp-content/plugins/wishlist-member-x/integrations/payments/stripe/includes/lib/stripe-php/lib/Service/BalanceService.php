<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib\Service;

class BalanceService extends \WishListMember\PaymentProviders\Stripe\PHPLib\Service\AbstractService
{
    /**
     * Retrieves the current account balance, based on the authentication that was used
     * to make the request.  For a sample request, see <a
     * href="/docs/connect/account-balances#accounting-for-negative-balances">Accounting
     * for negative balances</a>.
     *
     * @param null|array                                                                    $params
     * @param null|array|\WishListMember\PaymentProviders\Stripe\PHPLib\Util\RequestOptions $opts
     *
     * @throws \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\ApiErrorException if the request fails
     *
     * @return \WishListMember\PaymentProviders\Stripe\PHPLib\Balance
     */
    public function retrieve($params = null, $opts = null)
    {
        return $this->request('get', '/v1/balance', $params, $opts);
    }
}
