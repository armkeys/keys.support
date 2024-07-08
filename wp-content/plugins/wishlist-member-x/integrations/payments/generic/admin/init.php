<?php

$data = new \stdClass();

$data->genericthankyou = $this->get_option('genericthankyou');
if (! $data->genericthankyou) {
    $this->save_option('genericthankyou', $data->genericthankyou = $this->make_reg_url());
}
$data->genericsecret = $this->get_option('genericsecret');
if (! $data->genericsecret) {
    $this->save_option('genericsecret', $data->genericsecret = $this->pass_gen() . $this->pass_gen());
}

$data->genericthankyou_url = $wpm_scregister . $data->genericthankyou;

thirdparty_integration_data($config['id'], $data);
