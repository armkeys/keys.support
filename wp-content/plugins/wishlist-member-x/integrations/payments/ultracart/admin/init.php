<?php

$data = new \stdClass();

$data->ultracartthankyou = $this->get_option('ultracartthankyou');
if (! $data->ultracartthankyou) {
    $this->save_option('ultracartthankyou', $data->ultracartthankyou = $this->make_reg_url());
}
$data->ultracartsecret = $this->get_option('ultracartsecret');
if (! $data->ultracartsecret) {
    $this->save_option('ultracartsecret', $data->ultracartsecret = $this->pass_gen() . $this->pass_gen());
}

$data->ultracartthankyou_url = $wpm_scregister . $data->ultracartthankyou;

thirdparty_integration_data($config['id'], $data);
