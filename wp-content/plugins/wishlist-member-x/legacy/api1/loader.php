<?php

/**
 * Deprecated API 1.0 Autoloader
 * This:
 * 1. Autoloads the Legacy WLMAPI when needed
 * 2. Processes remote requests to WLMAPI
 *
 * @package WishListMember\API1
 */

// WLMAPI autoloader.
spl_autoload_register(
    function ($class) {
        if ('WLMAPI' === $class) {
            require_once __DIR__ . '/api.php';
        }
    }
);

// Catch WLMAPI requests.
add_action(
    'init',
    function () {
        // Check for REST API Call.
        if (isset(wlm_get_data()['WLMAPI'])) {
            list($func, $key, $params) = explode('/', wlm_get_data()['WLMAPI' ], 3);

            $params = explode('/', $params);

            foreach ((array) $params as $k => $v) { // Find arrays.  arrays are specified by separating values with commas.
                if (false !== strpos($v, ',')) {
                    $params[ $k ] = explode(',', $v);
                }
            }
            fwrite(WLM_STDOUT, WLMAPI::__remoteProcess($func, $key, $params));
            exit;
        }
    }
);
