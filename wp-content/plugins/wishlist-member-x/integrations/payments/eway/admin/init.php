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
        'value' => '1',
        'text'  => 'Day(s)',
    ],
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
$data->ewaythankyou = wlm_trim($this->get_option('ewaythankyou'));
if (! $data->ewaythankyou) {
    $this->save_option('ewaythankyou', $data->ewaythankyou = $this->make_reg_url());
}
$data->ewaythankyou_url = $wpm_scregister . $data->ewaythankyou;

// Settings.
$data->ewaysettings = (array) $this->get_option('ewaysettings');

// Form settings.
$form_defaults = [
    'formheading'      => 'Register for %level',
    'buttonlabel'      => 'Join %level',
    'panelbuttonlabel' => 'Pay',
    'supportemail'     => get_option('admin_email'),
];

$data->ewaysettings = wp_parse_args($data->ewaysettings, $form_defaults);

thirdparty_integration_data($config['id'], $data);
