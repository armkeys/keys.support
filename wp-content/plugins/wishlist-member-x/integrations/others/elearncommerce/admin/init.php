<?php

$active_plugins = wlm_get_active_plugins();
if (in_array('eLearnCommerce', $active_plugins, true) || isset($active_plugins['wpep/wpextplan.php'])) {
    printf('<p>eLearnCommerce plugin is installed and activated</p>');
} else {
    printf('<p>Please install and activate your eLearnCommerce plugin</p>');
    return false;
}

$data = (array) $this->get_option('elearncommerce_settings');

thirdparty_integration_data(
    $config['id'],
    [
        'elearncommerce_settings' => $data,
    ]
);
