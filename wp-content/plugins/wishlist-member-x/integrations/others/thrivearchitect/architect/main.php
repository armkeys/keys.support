<?php

/**
 * WishList Member conditional display
 *
 * Add WishList Member conditional display to Thrive Architect
 *
 * @package WishListMember\ThriveArchitect
 */

if (! class_exists('TCB\ConditionalDisplay\Field')) {
    return;
}
require_once __DIR__ . '/class-field-access.php';
require_once __DIR__ . '/class-field-has-access.php';
require_once __DIR__ . '/class-field-has-no-access.php';

// Register fields.
tve_register_condition_field(\WishListMember\ThriveArchitect\Field_Has_No_Access::class);
tve_register_condition_field(\WishListMember\ThriveArchitect\Field_Has_Access::class);
