<?php

/**
 * WishList Member - Thrive Automator integration
 * Main file
 *
 * @package WishListMember/WLMTA
 */

use WishListMember\WLMTA\Apps;
use WishListMember\WLMTA\Triggers;
use WishListMember\WLMTA\Data_Fields;
use WishListMember\WLMTA\Data_Objects;
use WishListMember\WLMTA\Action_Fields;
use WishListMember\WLMTA\Actions;

// Autoload WishList Member Thrive Automator classes.
spl_autoload_register(
    function ($class) {
        if (0 === strpos($class, 'WishListMember\\WLMTA\\')) {
              $class = str_replace('WishListMember\\WLMTA\\', '', $class);
            $file    = __DIR__ . '/' . strtolower(
                preg_replace(
                    ['/_/', '/\\\/', '#[^/]+$#'],
                    ['-', '/', 'class-\0.php'],
                    $class
                )
            );
              file_exists($file) && require_once $file;
        }
    }
);

add_action('thrive_automator_init', __NAMESPACE__ . '\thrive_automator_init');
/**
 * Initialize Thrive Automator integration
 * Called by 'thrive_automator_init' hook.
 */
function thrive_automator_init()
{
    // App.
    thrive_automator_register_app(Apps\WishList_Member_App::class);

    // Data fields.
    thrive_automator_register_data_field(Data_Fields\Membership_Level_ID::class);
    thrive_automator_register_data_field(Data_Fields\Membership_Level_Name::class);
    thrive_automator_register_data_field(Data_Fields\User_Levels::class);
    thrive_automator_register_data_field(Data_Fields\Registration_Data::class);

    // Data objects.
    thrive_automator_register_data_object(Data_Objects\Membership_Level::class);
    thrive_automator_register_data_object(Data_Objects\User_Levels::class);
    thrive_automator_register_data_object(Data_Objects\Registration_Data::class);

    // Triggers.
    thrive_automator_register_trigger(Triggers\User_Registered::class);
    thrive_automator_register_trigger(Triggers\Member_Added::class);
    thrive_automator_register_trigger(Triggers\Member_Removed::class);
    thrive_automator_register_trigger(Triggers\Member_Cancelled::class);
    thrive_automator_register_trigger(Triggers\Member_Uncancelled::class);
    thrive_automator_register_trigger(Triggers\Member_Expired::class);
    thrive_automator_register_trigger(Triggers\Member_Unexpired::class);
    thrive_automator_register_trigger(Triggers\Member_Approved::class);
    thrive_automator_register_trigger(Triggers\Member_Confirmed::class);
    thrive_automator_register_trigger(Triggers\User_Deleted::class);

    // Action fields.
    thrive_automator_register_action_field(Action_Fields\Membership_Level::class);
    thrive_automator_register_action_field(Action_Fields\Transaction_ID::class);
    thrive_automator_register_action_field(Action_Fields\User_Login::class);
    thrive_automator_register_action_field(Action_Fields\User_Email::class);
    thrive_automator_register_action_field(Action_Fields\User_Password::class);

    // Actions.
    thrive_automator_register_action(Actions\Add_Member_To_Level::class);
    thrive_automator_register_action(Actions\Remove_Member_From_Level::class);
    thrive_automator_register_action(Actions\Cancel_Member_From_Level::class);
    thrive_automator_register_action(Actions\Uncancel_Member_From_Level::class);
    thrive_automator_register_action(Actions\Register_User::class);
    thrive_automator_register_action(Actions\Delete_User::class);
}
