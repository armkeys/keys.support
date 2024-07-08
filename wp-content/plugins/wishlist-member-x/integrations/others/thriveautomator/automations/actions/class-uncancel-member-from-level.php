<?php

/**
 * WishList Member - Thrive Automator Integration
 * Unancel member from level action
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Actions;

use Thrive\Automator\Items\User_Data;
use Thrive\Automator\Items\Action_Field;
use WishListMember\WLMTA\Action_Fields;

/**
 * Unancel member from level action class
 */
class Uncancel_Member_From_Level extends \Thrive\Automator\Items\Action
{
    use Level_Actions_Trait;

    /**
     * Return action ID
     *
     * @return string Action ID.
     */
    public static function get_id(): string
    {
        return 'wlmta/a/uncancel-member-from-level';
    }

    /**
     * Return action name
     *
     * @return string Action name.
     */
    public static function get_name(): string
    {
        return __('Uncancel member from membership level', 'wishlist-member');
    }

    /**
     * Return action description
     *
     * @return string Action description.
     */
    public static function get_description(): string
    {
        return __('Uncancels a member from a membership level', 'wishlist-member');
    }

    /**
     * Return action required fields
     *
     * @return array Array of action field IDs
     */
    public static function get_required_action_fields()
    {
        return [
            Action_Fields\Membership_Level::get_id(),
        ];
    }

    /**
     * Execute the requested action
     *
     * @param \Thrive\Automator\Items\Automation_Data Automation data object.
     */
    public function do_action($data)
    {
        $this->do_level_action($data);
    }
}
