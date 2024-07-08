<?php

$data = new \stdClass();

// Thank you url.
$data->recurlythankyou = wlm_trim($this->get_option('recurlythankyou'));
if (! $data->recurlythankyou) {
    $this->save_option('recurlythankyou', $data->recurlythankyou = $this->make_reg_url());
}
$data->recurlythankyou_url = $wpm_scregister . $data->recurlythankyou;

$data->recurlyapikey      = (array) $this->get_option('recurlyapikey');
$data->recurlyconnections = (array) $this->get_option('recurlyconnections');
$data->plans              = [];

thirdparty_integration_data($config['id'], $data);
