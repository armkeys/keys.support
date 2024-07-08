<?php

require_once __DIR__ . '/../includes/constants.php';
require_once __DIR__ . '/../includes/class-stripe-connect.php';
require_once __DIR__ . '/../includes/class-authenticator-service.php';
require_once __DIR__ . '/../includes/class-auth-utils.php';
require_once __DIR__ . '/../includes/class-gateway-utils.php';

$data = new \stdClass();

$data->currencies = ['USD', 'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EEK', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'MAD', 'MDL', 'MGA', 'MKD', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SZL', 'THB', 'TJS', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW'];
foreach ($data->currencies as &$currency) {
    $currency = [
        'value' => $currency,
        'text'  => $currency,
    ];
}
unset($currency);

$data->stripethankyou = wlm_trim($this->get_option('stripethankyou'));
if (! $data->stripethankyou) {
    $this->save_option('stripethankyou', $data->stripethankyou = $this->make_reg_url());
}

$data->stripeapikey              = wlm_trim($this->get_option('stripeapikey'));
$data->stripepublishablekey      = wlm_trim($this->get_option('stripepublishablekey'));
$data->test_stripeapikey         = wlm_trim($this->get_option('test_stripeapikey'));
$data->test_stripepublishablekey = wlm_trim($this->get_option('test_stripepublishablekey'));
$data->stripetestmode            = wlm_trim($this->get_option('stripetestmode'));
$data->stripeconnections         = $this->get_option('stripeconnections');

if (! is_array($data->stripeconnections)) {
    $data->stripeconnections = [];
}

// @since 3.6 merge "plans" with "plan" array so our product dropdown shows all selected plans.
foreach ($data->stripeconnections as &$sconn) {
        $sconn['plan'] = [$sconn['plan']];
    if (! empty($sconn['plans'])) {
        $x = json_decode(stripslashes($sconn['plans']));
        if (is_array($x)) {
            $sconn['plan'] = array_merge($sconn['plan'], array_values($x));
        }
    }
}
unset($sconn);

$data->stripesettings = $this->get_option('stripesettings');
if (! is_array($data->stripesettings)) {
    $data->stripesettings = [];
}
$data->stripesettings = wp_parse_args(
    $data->stripesettings,
    [
        'endsubscriptiontiming' => 'periodend',
        'automatictax'          => 'no',
        'currency'              => 'USD',
        'formheading'           => 'Register for %level',
        'buttonlabel'           => 'Join %level',
        'panelbuttonlabel'      => 'Pay',
        'supportemail'          => get_option('admin_email'),
    ]
);

$data->stripethankyou_url = $wpm_scregister . $data->stripethankyou;

$data->pages = get_pages('exclude=' . implode(',', $this->exclude_pages([], true)));
foreach ($data->pages as &$_page) {
    $_page = [
        'value' => $_page->ID,
        'text'  => $_page->post_title,
    ];
}
unset($_page);

$data->plan_options = [];
$data->plans        = [];

thirdparty_integration_data($config['id'], $data);
