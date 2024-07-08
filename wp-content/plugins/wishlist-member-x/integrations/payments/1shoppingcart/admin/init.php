<?php

$data = new \stdClass();

$data->scthankyou = $this->get_option('scthankyou');
if (! $data->scthankyou) {
    $this->save_option('scthankyou', $data->scthankyou = $this->make_reg_url());
}

$data->scthankyou_url = $wpm_scregister . $data->scthankyou . '.PHP';

$data->onescmerchantid = $this->get_option('onescmerchantid');
$data->onescapikey     = $this->get_option('onescapikey');

// Other Settings.
$data->onescgraceperiod = $this->get_option('onescgraceperiod');
if (! $data->onescgraceperiod) {
    $this->save_option('onescgraceperiod', $data->onescgraceperiod = 3);
}
$data->onesc_include_upsells = $this->get_option('onesc_include_upsells') + 0;

thirdparty_integration_data($config['id'], $data);
