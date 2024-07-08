<?php

$data = new \stdClass();

$data->pptoken        = $this->get_option('pptoken');
$data->ppemail        = $this->get_option('ppemail');
$data->ppsandbox      = (int) $this->get_option('ppsandbox');
$data->ppsandboxtoken = $this->get_option('ppsandboxtoken');
$data->ppsandboxemail = $this->get_option('ppsandboxemail');
$data->ppthankyou     = $this->get_option('ppthankyou');
if (! $data->ppthankyou) {
    $this->save_option('ppthankyou', $data->ppthankyou = $this->make_reg_url());
}

$data->paypalpsproducts = $this->get_option('paypalpsproducts');
if (! $data->paypalpsproducts) {
    $data->paypalpsproducts = [];
}

$data->eotcancel = $this->get_option('eotcancel');
if ($data->eotcancel) {
    $data->eotcancel = wlm_maybe_unserialize($data->eotcancel);
} else {
    $data->eotcancel = [];
}

$data->subscrcancel = (array) wlm_maybe_unserialize($this->get_option('subscrcancel')) + array_combine(array_keys($wpm_levels), array_fill(0, count($wpm_levels), 1));

$data->ppthankyou_url = $wpm_scregister . $data->ppthankyou;

thirdparty_integration_data($config['id'], $data);
