<?php

$data = new \stdClass();

$data->pwcthankyou = $this->get_option('pwcthankyou');
if (! $data->pwcthankyou) {
    $this->save_option('pwcthankyou', $data->pwcthankyou = $this->make_reg_url());
}
$data->pwcsecret = $this->get_option('pwcsecret');
if (! $data->pwcsecret) {
    $this->save_option('pwcsecret', $data->pwcsecret = $this->pass_gen() . $this->pass_gen());
}

$data->pwcapikey = $this->get_option('pwcapikey');
if (! $data->pwcapikey) {
    $this->save_option('pwcapikey', $data->pwcapikey = '');
}

$data->pwcmerchantid = $this->get_option('pwcmerchantid');
if (! $data->pwcsecret) {
    $this->save_option('pwcmerchantid', $data->pwcmerchantid = '');
}

$data->pwcthankyou_url = $wpm_scregister . $data->pwcthankyou;

thirdparty_integration_data($config['id'], $data);
