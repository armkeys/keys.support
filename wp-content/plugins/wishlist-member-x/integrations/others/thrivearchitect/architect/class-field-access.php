<?php

/**
 * Field Access trait file
 *
 * @package WishListMember\ThriveArchitect
 */

namespace WishListMember\ThriveArchitect;

/**
 * Field access trait
 */
trait Field_Access
{
    /**
     * Return supported conditions
     *
     * @return array
     */
    public static function get_conditions()
    {
        return ['autocomplete'];
    }

    /**
     * Get parent entity
     *
     * @return string
     */
    public static function get_entity()
    {
        return 'user_data';
    }

    /**
     * Get field access value for user
     *
     * @param  false|\WP_User $user_data WP_User object or false.
     * @return array|string
     */
    public function get_value($user_data)
    {
        if (! $user_data) {
            return '';
        }
        $user_levels = wlmapi_get_member(wlm_arrval($user_data, 'ID'))['member'][0]['active_levels'];
        if ('wishlist-member/has-no-access' === self::get_key()) {
            $user_levels = array_diff(\WishListMember\Level::get_all_levels(), $user_levels);
        }
        return empty($user_levels) ? '' : $user_levels;
    }

    /**
     * Get level options
     *
     * @param  array  $selected_values  Selected values.
     * @param  string $searched_keyword Search keyword.
     * @return array
     */
    public static function get_options($selected_values = [], $searched_keyword = '')
    {
        $levels = [];

        foreach (self::get_level_options() as $level) {
            if (static::filter_options($level['value'], $level['value'], $selected_values, $searched_keyword)) {
                   $levels[] = [
                       'value' => (string) $level['value'],
                       'label' => $level['label'],
                   ];
            }
        }
        return $levels;
    }

    /**
     * Generate level dropdown options
     *
     * @return array
     */
    private static function get_level_options()
    {
        static $levels;
        if (is_null($levels)) {
            $levels = wishlistmember_instance()->get_option('wpm_levels');
            array_walk(
                $levels,
                function (&$level, $key) {
                    $level = [
                        'value' => $key,
                        'label' => $level['name'],
                    ];
                }
            );
            $levels = array_values($levels);
        }
        return $levels;
    }
}
