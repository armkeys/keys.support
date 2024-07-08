<?php

// Get settings.
$webhooks_settings = $this->get_option('webhooks_settings');
// Make sure settings is array.
if (! is_array($webhooks_settings)) {
    $webhooks_settings = [
        'outgoing' => [],
        'incoming' => [],
    ];
    $this->add_option('webhooks_settings', $webhooks_settings);
}

$webhooks_settings = array_merge(
    [
        'outgoing' => [],
        'incoming' => [],
    ],
    $webhooks_settings
);

// Add our data to js.
thirdparty_integration_data(
    $config['id'],
    [
        'webhooks_settings' => $webhooks_settings,
    ]
);
