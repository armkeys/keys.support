<?php

$data = new \stdClass();

// Thank you url.
$data->spreedlythankyou = wlm_trim($this->get_option('spreedlythankyou'));
if (! $data->spreedlythankyou) {
    $this->save_option('spreedlythankyou', $data->spreedlythankyou = $this->make_reg_url());
}
$data->spreedlythankyou_url = $wpm_scregister . $data->spreedlythankyou;

$data->spreedlyname  = wlm_trim($this->get_option('spreedlyname'));
$data->spreedlytoken = wlm_trim($this->get_option('spreedlytoken'));

thirdparty_integration_data($config['id'], $data);
