<?php

/**
 * Thrive Automator handler
 *
 * @package WishListMember/Integrations/Others
 */

namespace WishListMember\Integrations\Others;

add_action(
    'init',
    function () {
        if (class_exists('\TCB\ConditionalDisplay\Main', false)) {
            // Registration integration with Thrive Automator.
            require_once __DIR__ . '/architect/main.php';
        }
    }
);
