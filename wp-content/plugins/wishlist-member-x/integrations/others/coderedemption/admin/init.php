<?php

// Get settings.
@require_once __DIR__ . '/../handler.php';
$coderedemption_settings = \WishListMember\Integrations\Others\CodeRedemption::get_settings(true);

// Add our data to js.
thirdparty_integration_data(
    $config['id'],
    [
        'coderedemption_settings' => $coderedemption_settings,
    ]
);
