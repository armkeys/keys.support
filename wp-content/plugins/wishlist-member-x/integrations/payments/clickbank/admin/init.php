<?php

$data = new \stdClass();

$data->cbthankyou = $this->get_option('cbthankyou');
if (! $data->cbthankyou) {
    $this->save_option('cbthankyou', $data->cbthankyou = $this->make_reg_url());
}
$data->cbsecret = $this->get_option('cbsecret');
if (! $data->cbsecret) {
    $this->save_option('cbsecret', $data->cbsecret = strtoupper($this->pass_gen() . $this->pass_gen()));
}

$data->cbproducts = (array) $this->get_option('cbproducts');
if (! $data->cbproducts) {
    $this->save_option('cbproducts', $data->cbproducts = []);
}

$data->cb_eot_cancel = wlm_maybe_unserialize($this->get_option('cb_eot_cancel'));
if (! is_array($data->cb_eot_cancel)) {
    $this->save_option('cb_eot_cancel', $data->cb_eot_cancel = []);
}

$data->cb_scrcancel = wlm_maybe_unserialize($this->get_option('cb_scrcancel'));
if (! is_array($data->cb_scrcancel)) {
    $data->cb_scrcancel = array_combine(array_keys($wpm_levels), array_fill(0, count($wpm_levels), '1'));
    $this->save_option('cb_scrcancel', $data->cb_scrcancel = $data->cb_scrcancel);
}


$data->cbvendor = strtolower($this->get_option('cbvendor'));

$data->cbthankyou_url = $wpm_scregister . $data->cbthankyou;

thirdparty_integration_data($config['id'], $data);
