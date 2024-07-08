<?php

$data = (array) $this->get_option('callloop_settings');

thirdparty_integration_data(
    $config['id'],
    ['callloop_settings' => $data]
);
