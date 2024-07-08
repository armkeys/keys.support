<?php

/**
 * Deprecated Utility Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Deprecated Utility Methods trait
 */
trait Utility_Methods_Deprecated
{
    /**
     * Determines what array members have been removed and added
     *
     * @param array $new_array       New Array.
     * @param array $old_array       Old Array.
     * @param array $removed_members This variable will contain the levels that were removed (passed by reference).
     * @param array $new_members     This variable will contain the levels that were added (passed by reference).
     */
    public function ArrayDiff($new_array, $old_array, &$removed_members, &$new_members)
    {
        wlm_deprecated_method_error_log(__METHOD__, 'array_diff');
        $this->array_diff($new_array, $old_array, $removed_members, $new_members);
    }
}
