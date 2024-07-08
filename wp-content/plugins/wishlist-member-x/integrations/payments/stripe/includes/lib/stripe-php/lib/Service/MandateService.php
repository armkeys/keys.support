<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib\Service;

class MandateService extends \WishListMember\PaymentProviders\Stripe\PHPLib\Service\AbstractService
{
    /**
     * Retrieves a Mandate object.
     *
     * @param string                                                                        $id
     * @param null|array                                                                    $params
     * @param null|array|\WishListMember\PaymentProviders\Stripe\PHPLib\Util\RequestOptions $opts
     *
     * @throws \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\ApiErrorException if the request fails
     *
     * @return \WishListMember\PaymentProviders\Stripe\PHPLib\Mandate
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/mandates/%s', $id), $params, $opts);
    }
}
