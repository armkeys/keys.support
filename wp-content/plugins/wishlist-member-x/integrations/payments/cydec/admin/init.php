<?php

$data = new \stdClass();

$data->cydecthankyou = $this->get_option('cydecthankyou');
if (! $data->cydecthankyou) {
    $this->save_option('cydecthankyou', $data->cydecthankyou = $this->make_reg_url());
}
$data->cydecsecret = $this->get_option('cydecsecret');
if (! $data->cydecsecret) {
    $this->save_option('cydecsecret', $data->cydecsecret = $this->pass_gen() . $this->pass_gen());
}

$data->cydecthankyou_url = $wpm_scregister . $data->cydecthankyou;

thirdparty_integration_data($config['id'], $data);
