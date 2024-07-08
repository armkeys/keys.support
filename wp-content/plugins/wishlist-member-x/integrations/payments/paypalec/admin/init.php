<?php

$data = new \stdClass();

$data->paypalecsettings = $this->get_option('paypalecsettings');
if (! is_array($data->paypalecsettings)) {
    $this->save_option(
        'paypalecsettings',
        $data->paypalecsettings = [
            'live'         => [],
            'sandbox'      => [],
            'sandbox_mode' => 0,
        ]
    );
}

$data->paypalec_spb           = $this->get_option('paypalec_spb');
$data->paypalec_spb['enable'] = $data->paypalec_spb['enable'] + 0;

$data->paypalec_cancel_url = $this->get_option('paypalec_cancel_url');
if (! $data->paypalec_cancel_url) {
    $this->save_option('paypalec_cancel_url', $data->paypalec_cancel_url = get_bloginfo('url'));
}

$data->paypalecthankyou = $this->get_option('paypalecthankyou');
if (! $data->paypalecthankyou) {
    $this->save_option('paypalecthankyou', $data->paypalecthankyou = $this->make_reg_url());
}

$data->paypalecproducts = $this->get_option('paypalecproducts');
if (! $data->paypalecproducts) {
    $data->paypalecproducts = [];
}

$data->paypaleceotcancel = $this->get_option('paypaleceotcancel');
if ($data->paypaleceotcancel) {
    $data->paypaleceotcancel = wlm_maybe_unserialize($data->paypaleceotcancel);
} else {
    $data->paypaleceotcancel = [];
}

$data->paypalecsubscrcancel = (array) wlm_maybe_unserialize($this->get_option('paypalecsubscrcancel')) + array_combine(array_keys($wpm_levels), array_fill(0, count($wpm_levels), 1));

$data->paypalecthankyou_url = $wpm_scregister . $data->paypalecthankyou;

$data->paypalec_ipnforwarding = $this->get_option('paypalec_ipnforwarding');

thirdparty_integration_data($config['id'], $data);
