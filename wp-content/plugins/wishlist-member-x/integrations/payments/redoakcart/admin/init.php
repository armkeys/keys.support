<?php

$data = new \stdClass();

$data->redoakcartthankyou = $this->get_option('redoakcartthankyou');
if (! $data->redoakcartthankyou) {
    $this->save_option('redoakcartthankyou', $data->redoakcartthankyou = $this->make_reg_url());
}
$data->redoakcartsecret = $this->get_option('redoakcartsecret');
if (! $data->redoakcartsecret) {
    $this->save_option('redoakcartsecret', $data->redoakcartsecret = $this->pass_gen() . $this->pass_gen());
}

$data->redoakcartthankyou_url = $wpm_scregister . $data->redoakcartthankyou;

thirdparty_integration_data($config['id'], $data);
