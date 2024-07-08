<?php

/**
 * Field Access class file
 *
 * @package WishListMember\ThriveArchitect
 */

namespace WishListMember\ThriveArchitect;

/**
 * Field has no access class
 */
class Field_Has_No_Access extends \TCB\ConditionalDisplay\Field
{
    use Field_Access;

    /**
     * Return field key
     *
     * @return string
     */
    public static function get_key()
    {
         return 'wishlist-member/has-no-access';
    }

    /**
     * Return field label
     *
     * @return string
     */
    public static function get_label()
    {
         return __('Is not a member of', 'wishlist-member');
    }

    /**
     * Return field display order
     *
     * @return integer
     */
    public static function get_display_order()
    {
        return 2;
    }
}
