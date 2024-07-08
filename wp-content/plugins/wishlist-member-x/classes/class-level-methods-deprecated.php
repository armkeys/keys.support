<?php

/**
 * Deprecated Level Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Deprecated Level Methods trait
 */
trait Level_Methods_Deprecated
{
    /**
     * Wrapper for sort_levels()
     * Sort Memership Levels according to a given field
     *
     * @param array  $wpm_levels Membership Levels. Passed by reference.
     * @param string $sortorder  'd' / 'a'.  'd' for descending and 'a' for ascending.
     * @param string $sort_field The field to be used for sorting. Accepts 'name' & 'id'.
     */
    public function SortLevels(&$wpm_levels, $sortorder, $sort_field = 'name')
    {
        wlm_deprecated_method_error_log(__METHOD__, 'sort_levels');
        return $this->sort_levels($wpm_levels, $sortorder, $sort_field);
    }

    /**
     * Wrapper for validate_levels()
     * Removes invalid Level IDs from the passed array
     *
     * @param  array   $level_ids             Passed by referece. Array of Level IDs.
     * @param  integer $user_id               Optional User ID. If specified, then validate against user's levels as well.
     * @param  boolean $terminate_on_error    Default false. TRUE to stop validation, reset $level_ids to an empty array and
     *                                       return FALSE if at least one level does not validate.
     * @param  boolean $include_user_posts    Default false. True to include user posts.
     * @param  boolean $include_pay_per_posts Default false. True to include pay per posts.
     * @return boolean
     */
    public function ValidateLevels(&$level_ids, $user_id = null, $terminate_on_error = false, $include_user_posts = false, $include_pay_per_posts = false)
    {
        wlm_deprecated_method_error_log(__METHOD__, 'validate_levels');
        return $this->validate_levels($level_ids, $user_id, $terminate_on_error, $include_user_posts, $include_pay_per_posts);
    }
}
