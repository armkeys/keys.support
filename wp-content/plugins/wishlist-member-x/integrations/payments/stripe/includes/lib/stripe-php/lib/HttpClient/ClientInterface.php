<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib\HttpClient;

interface ClientInterface
{
    /**
     * @param string  $method  The HTTP method being used
     * @param string  $absUrl  The URL being requested, including domain and protocol
     * @param array   $headers Headers to be used in the request (full strings, not KV pairs)
     * @param array   $params  KV pairs for parameters. Can be nested for arrays and hashes
     * @param boolean $hasFile Whether or not $params references a file (via an @ prefix or
     *                            CURLFile)
     *
     * @throws \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\ApiConnectionException
     * @throws \WishListMember\PaymentProviders\Stripe\PHPLib\Exception\UnexpectedValueException
     *
     * @return array an array whose first element is raw request body, second
     *    element is HTTP status code and third array of HTTP headers
     */
    public function request($method, $absUrl, $headers, $params, $hasFile);
}
