<?php

namespace WishListMember\PaymentProviders\Stripe\PHPLib;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\AccountLinkService $accountLinks
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\AccountService $accounts
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\ApplePayDomainService $applePayDomains
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\ApplicationFeeService $applicationFees
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\BalanceService $balance
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\BalanceTransactionService $balanceTransactions
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\ChargeService $charges
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\CountrySpecService $countrySpecs
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\CouponService $coupons
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\CreditNoteService $creditNotes
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\CustomerService $customers
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\DisputeService $disputes
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\EphemeralKeyService $ephemeralKeys
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\EventService $events
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\ExchangeRateService $exchangeRates
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\FileLinkService $fileLinks
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\FileService $files
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\InvoiceItemService $invoiceItems
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\InvoiceService $invoices
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\Issuing\IssuingServiceFactory $issuing
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\MandateService $mandates
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\OAuthService $oauth
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\OrderReturnService $orderReturns
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\OrderService $orders
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\PaymentIntentService $paymentIntents
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\PaymentMethodService $paymentMethods
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\PayoutService $payouts
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\PlanService $plans
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\PriceService $prices
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\ProductService $products
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\Radar\RadarServiceFactory $radar
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\RefundService $refunds
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\Reporting\ReportingServiceFactory $reporting
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\ReviewService $reviews
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\SetupIntentService $setupIntents
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\Sigma\SigmaServiceFactory $sigma
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\SkuService $skus
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\SourceService $sources
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\SubscriptionItemService $subscriptionItems
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\SubscriptionService $subscriptions
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\TaxRateService $taxRates
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\Terminal\TerminalServiceFactory $terminal
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\TokenService $tokens
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\TopupService $topups
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\TransferService $transfers
 * @property \WishListMember\PaymentProviders\Stripe\PHPLib\Service\WebhookEndpointService $webhookEndpoints
 */
class StripeClient extends BaseStripeClient
{
    /**
     * @var \WishListMember\PaymentProviders\Stripe\PHPLib\Service\CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new \WishListMember\PaymentProviders\Stripe\PHPLib\Service\CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->__get($name);
    }
}
