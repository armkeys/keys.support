<?php

$data = new \stdClass();

$data->paypalprosettings = $this->get_option('paypalprosettings');
if (! is_array($data->paypalprosettings)) {
    $this->save_option(
        'paypalprosettings',
        $data->paypalprosettings = [
            'live'         => [],
            'sandbox'      => [],
            'sandbox_mode' => 0,
        ]
    );
}

$data->paypalprothankyou = $this->get_option('paypalprothankyou');
if (! $data->paypalprothankyou) {
    $this->save_option('paypalprothankyou', $data->paypalprothankyou = $this->make_reg_url());
}

$data->paypalproproducts = $this->get_option('paypalproproducts');
if (! $data->paypalproproducts) {
    $data->paypalproproducts = [];
}

$data->paypalprothankyou_url = $wpm_scregister . $data->paypalprothankyou;

thirdparty_integration_data($config['id'], $data);
