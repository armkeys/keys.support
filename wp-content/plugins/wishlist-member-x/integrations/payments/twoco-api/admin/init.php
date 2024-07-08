<?php

$data = new \stdClass();

// Currencies.
$data->currencies = ['USD', 'AED', 'ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'HKD', 'ILS', 'INR', 'JPY', 'LTL', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'RON', 'RUB', 'SEK', 'SGD', 'TRY', 'ZAR'];
foreach ($data->currencies as &$c) {
    $c = [
        'value' => $c,
        'text'  => $c,
    ];
}
unset($c);

// Rebill interval.
$data->rebill_interval = [];
for ($i = 1; $i <= 30; $i++) {
    $data->rebill_interval[] = [
        'value' => $i,
        'text'  => $i,
    ];
}

// Billing periods.
$data->rebill_interval_type = [
    [
        'value' => '2',
        'text'  => 'Week(s)',
    ],
    [
        'value' => '3',
        'text'  => 'Month(s)',
    ],
    [
        'value' => '4',
        'text'  => 'Year(s)',
    ],
];

// Thank you url.
$data->twocothankyou = wlm_trim($this->get_option('twocothankyou'));
if (! $data->twocothankyou) {
    $this->save_option('twocothankyou', $data->twocothankyou = $this->make_reg_url());
}
$data->twocothankyou_url = $wpm_scregister . $data->twocothankyou;


// Set the Thank You URL for 2Checkout API, the thank you url above is for the 2CO legacy.
$data->twocheckoutapithankyouurl = wlm_trim($this->get_option('twocheckoutapithankyouurl'));
if (! $data->twocheckoutapithankyouurl) {
    $this->save_option('twocheckoutapithankyouurl', $data->twocheckoutapithankyouurl = $this->make_reg_url());
}
$data->twocheckoutapithankyouurl_url = $wpm_scregister . $data->twocheckoutapithankyouurl;

// Legacy 2co settings.
$data->twocovendorid = wlm_trim($this->get_option('twocovendorid'));
$data->twocosecret   = wlm_trim($this->get_option('twocosecret'));

// Settings.
$data->twocheckoutapisettings = (array) $this->get_option('twocheckoutapisettings');

// Form settings.
$form_defaults = [
    'formheading'      => 'Register for %level',
    'buttonlabel'      => 'Join %level',
    'panelbuttonlabel' => 'Pay',
    'supportemail'     => get_option('admin_email'),
];

$data->twocheckoutapisettings = wp_parse_args($data->twocheckoutapisettings, $form_defaults);

thirdparty_integration_data($config['id'], $data);
