<?php

/**
 * Level Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Level Methods trait
 */
trait Level_Methods
{
    use Level_Methods_Hooks;
    use Level_Methods_Deprecated;

    /**
     * Sort Memership Levels according to a given field
     *
     * @param array  $wpm_levels Membership Levels. Passed by reference.
     * @param string $sortorder  'd' / 'a'.  'd' for descending and 'a' for ascending.
     * @param string $sort_field The field to be used for sorting. Accepts 'name' & 'id'.
     */
    public function sort_levels(&$wpm_levels, $sortorder, $sort_field = 'name')
    {
        // Do this so that we can use id as the sort field. We'll remove it afterwards.
        foreach ($wpm_levels as $i => &$item) {
            $item['id'] = $i;
        }
        // Make sure to do this!! look at the manual.
        unset($item);

        $sort_fn = 'asort';
        if ('d' === $sortorder) {
            $sort_fn = 'arsort';
        }

        // We'll use regular sorting except for the id which is numeric.
        $sort_type = SORT_REGULAR;
        if ('id' === $sort_field) {
            $sort_type = SORT_NUMERIC;
        }

        $sort_field_tmp = [];
        foreach ($wpm_levels as $item) {
            // Lowercase for case-insensitive sorting.
            $sort_field_tmp[] = strtolower($item[ $sort_field ]);
        }

        // Now sort.
        $sort_fn($sort_field_tmp);

        // Build the sorted array. We are performance freaks :).
        $orig_tmp   = $wpm_levels;
        $sorted_arr = [];
        foreach ($sort_field_tmp as $v) {
            foreach ($orig_tmp as $i => $item) {
                if (strtolower($item[ $sort_field ]) === $v) {
                    $sorted_arr[ $i ] = $item;
                    // Remove this item, so we have lesser loops later.
                    unset($orig_tmp[ $i ]);
                }
            }
        }
        $wpm_levels = $sorted_arr;

        // Remove the id member variable so that we are consistent.
        foreach ($wpm_levels as $i => &$item) {
            unset($item['id']);
        }
        unset($i);
    }

    /**
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
    public function validate_levels(&$level_ids, $user_id = null, $terminate_on_error = false, $include_user_posts = false, $include_pay_per_posts = false)
    {
        $wpm_levels = $this->get_option('wpm_levels', null, true);
        if (is_null($terminate_on_error)) {
            $terminate_on_error = false;
        }
        if (is_null($include_user_posts)) {
            $include_user_posts = false;
        }
        if (is_null($include_pay_per_posts)) {
            $include_pay_per_posts = false;
        }

        $level_ids = array_unique((array) $level_ids);
        foreach ((array) $level_ids as $levelkey => $level) {
            if (empty($wpm_levels[ $level ])) {
                if ($include_user_posts) {
                    if ($this->is_user_level($level, 'STRICT' === $include_user_posts)) {
                        continue;
                    }
                }
                if ($include_pay_per_posts) {
                    if ($this->is_ppp_level($level)) {
                        continue;
                    }
                }
                if ($terminate_on_error) {
                    $level_ids = [];
                    return false;
                }
                unset($level_ids[ $levelkey ]);
            }
        }

        if (! is_null($user_id)) {
            $ulevels   = $this->get_membership_levels($user_id);
            $level_ids = array_intersect($level_ids, $ulevels);
            if ($ulevels !== $level_ids && $terminate_on_error) {
                $level_ids = [];
                return false;
            }
        }

        sort($level_ids);
        return true;
    }

    /**
     * Return names of level IDs
     *
     * @param  array $level_ids Array of level IDs.
     * @return array            Array of level names with level IDs as index
     */
    public function level_ids_to_level_names($level_ids)
    {
        static $wpm_levels;
        if (is_null($wpm_levels)) {
            $wpm_levels = $this->get_option('wpm_levels');
        }
        $names = [];
        foreach ($level_ids as $level_id) {
            if (isset($wpm_levels[ $level_id ])) {
                $names[ $level_id ] = $wpm_levels[ $level_id ]['name'];
            }
        }
        return $names;
    }

    /**
     * Syncs the member count
     * Called by wp-cron
     *
     * @wp-hook wishlistmember_syncmembership_count.
     */
    public function sync_membership_count()
    {
        $this->schedule_sync_membership(true);
    }

    /**
     * Hook that adds additional levels if specified during integration.
     * Used for upsells.
     *
     * @wp-hook wishlistmember_after_registration
     */
    public function add_additional_levels()
    {
        $post_data = wlm_post_data(true);

        $user = get_user_by('login', $post_data['username']);

        $additional_levels = $this->Get_UserMeta($user->ID, 'additional_levels');

        if (! is_array($additional_levels)) { // We assume $additional_levels is in simple CSV format if it's not an array.
            $additional_levels = explode(',', $additional_levels);
            array_walk(
                $additional_levels,
                function (&$var) {
                    $var = wlm_trim($var);
                }
            );
        }

        /*
         * each additional level can be passed as a tab-delimited string
         * containing level, transaction id and timestamp so we go through
         * each additional level and check for those
         */
        $transaction_ids = [];
        $timestamps      = [];

        foreach ($additional_levels as &$additional_level) {
            list($additional_level, $transaction_id, $timestamp) = array_pad(explode("\t", $additional_level), 3, '');
            if (wlm_trim($transaction_id)) {
                $transaction_ids[ $additional_level ] = wlm_trim($transaction_id);
            }
            if (wlm_trim($timestamp)) {
                $timestamps[ $additional_level ] = wlm_trim($timestamp);
            }
        }
        unset($additional_level);

        if (! empty($additional_levels)) {
            $this->validate_levels($additional_levels, null, null, null, true);
            if (! empty($additional_levels)) {
                $levels = array_merge($additional_levels, $this->get_membership_levels($user->ID));

                $this->set_membership_levels(
                    $user->ID,
                    $levels,
                    [
                        'process_autoresponders' => true,
                        'set_transaction_id'     => false,
                        'sync'                   => false,
                        'process_webinars'       => false,
                    ]
                );

                $default_txn = $this->get_membership_levels_txn_id($user->ID, $post_data['wpm_id']);
                $default_ts  = $this->Get_UserLevelMeta($user->ID, $post_data['wpm_id'], 'timestamp');

                $txn = [];
                $ts  = [];

                foreach ($additional_levels as $level) {
                    $txn[ $level ] = empty($transaction_ids[ $level ]) ? $default_txn : $transaction_ids[ $level ];
                    $ts[ $level ]  = empty($timestamps[ $level ]) ? $default_ts : $timestamps[ $level ];
                }

                $this->set_membership_level_txn_ids($user->ID, $txn);
                $this->user_level_timestamps($user->ID, $ts);
            }
            $this->Delete_UserMeta($user->ID, 'additional_levels');
        }
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wishlistmember_after_registration', [$wlm, 'add_additional_levels']);
        add_action('wishlistmember_syncmembership_count', [$wlm, 'sync_membership_count']);
    }
);
