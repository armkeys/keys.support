<?php

$data = new \stdClass();

$data->plugnpaidthankyou = wlm_trim($this->get_option('plugnpaidthankyou'));
if (! $data->plugnpaidthankyou) {
    $this->save_option('plugnpaidthankyou', $data->plugnpaidthankyou = $this->make_reg_url());
}

$data->plugnpaid_products = (array) $this->get_option('plugnpaid_products');

$data->plugnpaidapikey = wlm_trim($this->get_option('plugnpaidapikey'));

$data->plugnpaidthankyou_url = $wpm_scregister . $data->plugnpaidthankyou;

thirdparty_integration_data($config['id'], $data);
