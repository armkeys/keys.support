<?php

$data = new \stdClass();

$data->twocothankyou = $this->get_option('twocothankyou');
if (! $data->twocothankyou) {
    $this->save_option('twocothankyou', $data->twocothankyou = $this->make_reg_url());
}
$data->twocovendorid     = $this->get_option('twocovendorid');
$data->twocosecret       = (string) $this->get_option('twocosecret');
$data->twocodemo         = $this->get_option('twocodemo') + 0;
$data->twocothankyou_url = $wpm_scregister . $data->twocothankyou;

thirdparty_integration_data($config['id'], $data);
