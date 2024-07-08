<?php

/**
 * Deprecated Payperpost Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Deprecated Payperpost Methods trait
 */
trait Payperpost_Methods_Deprecated
{
    /**
     * Wrapper for get_pay_per_posts()
     *
     * @param  boolean|array $data               Data to retrieve
     *                                          - true to retrieve all
     *                                          - false (default) to retrieve content_id only
     *                                          - array of column names to retrieve specific column names.
     * @param  boolean       $group_by_post_type True to group results by post type. Processed only if $data is not false.
     * @param  string        $search             Search for post title. Default is '%'. Processed only if $data is not false.
     * @param  string        $search_limit       Search limit as per MySQL LIMIT syntax.
     * @param  integer       $total_rows         Total rows found.
     * @param  array         $exclude_ids        Array of IDs to exclude.
     * @return array
     */
    public function GetPayPerPosts($data = false, $group_by_post_type = true, $search = null, $search_limit = null, &$total_rows = null, $exclude_ids = [])
    {
        wlm_deprecated_method_error_log(__METHOD__, 'get_pay_per_posts');
        return $this->get_pay_per_posts($data, $group_by_post_type, $search, $search_limit, $total_rows, $exclude_ids);
    }

    /**
     * Wrapper for inject_ppp_settings()
     *
     * @param array  $wpm_levels Passed by reference, wpm_levels.
     * @param string $level_id   Optional level ID. Default 'payperpost'.
     */
    public function InjectPPPSettings(&$wpm_levels, $level_id = 'payperpost')
    {
        wlm_deprecated_method_error_log(__METHOD__, 'inject_ppp_settings');
        $this->inject_ppp_settings($wpm_levels, $level_id);
    }
}
