<?php

$data = new \stdClass();

$data->anloginid   = $this->get_option('anloginid');
$data->antransid   = $this->get_option('antransid');
$data->anmd5hash   = $this->get_option('anmd5hash');
$data->anetsandbox = (int) $this->get_option('anetsandbox');
$data->anthankyou  = $this->get_option('anthankyou');
if (! $data->anthankyou) {
    $this->save_option('anthankyou', $data->anthankyou = $this->make_reg_url());
}

$data->anthankyou_url = $wpm_scregister . $data->anthankyou;

thirdparty_integration_data($config['id'], $data);
