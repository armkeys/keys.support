<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib\Service\Sigma;

class ScheduledQueryRunService extends \WishListMember\PaymentProviders\Stripe\PHPLib\Service\AbstractService
{
    /**
     * Returns a list of scheduled query runs.
     *
     * @param null|array                                                                    $params
     * @param null|array|\WishListMember\PaymentProviders\Stripe\PHPLib\Util\RequestOptions $opts
     *
     * @throws \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\ApiErrorException if the request fails
     *
     * @return \WishListMember\PaymentProviders\Stripe\PHPLib\Collection
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/sigma/scheduled_query_runs', $params, $opts);
    }

    /**
     * Retrieves the details of an scheduled query run.
     *
     * @param string                                                                        $id
     * @param null|array                                                                    $params
     * @param null|array|\WishListMember\PaymentProviders\Stripe\PHPLib\Util\RequestOptions $opts
     *
     * @throws \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\ApiErrorException if the request fails
     *
     * @return \WishListMember\PaymentProviders\Stripe\PHPLib\Sigma\ScheduledQueryRun
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/sigma/scheduled_query_runs/%s', $id), $params, $opts);
    }
}
