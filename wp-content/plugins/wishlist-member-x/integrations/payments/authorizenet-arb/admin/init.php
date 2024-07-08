<?php

$data = new \stdClass();

// Currencies.
$data->currencies = ['USD', 'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'TWD', 'THB', 'TRY'];
foreach ($data->currencies as &$c) {
    $c = [
        'value' => $c,
        'text'  => $c,
    ];
}
unset($c);

// Billing periods.
$data->billing_periods = [
    [
        'value' => 'Day',
        'text'  => 'Day(s)',
    ],
    [
        'value' => 'Month',
        'text'  => 'Month(s)',
    ],
];

// Card types.
$data->card_types = [
    'Visa'        => 'Visa',
    'MasterCard'  => 'MasterCard',
    'Discover'    => 'Discover',
    'Amex'        => 'American Express',
    'Diners Club' => 'Diners Club',
    'JCB'         => 'JCB',
];

// Thank you url.
$data->anetarbthankyou = wlm_trim($this->get_option('anetarbthankyou'));
if (! $data->anetarbthankyou) {
    $this->save_option('anetarbthankyou', $data->anetarbthankyou = $this->make_reg_url());
}
$data->anetarbthankyou_url = $wpm_scregister . $data->anetarbthankyou;

// Settings.
$data->anetarbsettings = (array) $this->get_option('anetarbsettings');

// Form settings.
$x             = $this->get_option('authnet_arb_formsettings');
$formsettings  = array_diff(is_array($x) ? $x : [], ['']);
$form_defaults = [
    'formheading'          => 'Register for %level',
    'formheadingrecur'     => 'Subscribe to %level',
    'formbuttonlabel'      => 'Pay',
    'formbuttonlabelrecur' => 'Pay',
    'supportemail'         => get_option('admin_email'),
];

$data->authnet_arb_formsettings = wp_parse_args($formsettings, $form_defaults);

// Subscriptions.
$x                          = $this->get_option('anetarbsubscriptions');
$data->anetarbsubscriptions = is_array($x) ? $x : [];

thirdparty_integration_data($config['id'], $data);
