<?php

/**
 * Level Class for WishList Member
 *
 * @package WishListMember
 */

namespace WishListMember;

defined('ABSPATH') || die();

/**
 * WishList Member Level Class
 *
 * @package WishListMember
 */
class Level
{
    /**
     * Level ID
     *
     * @var integer
     */
    private $ID = null;

    /**
     * Level data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param string|integer $level_id   Level ID to initialize the object with.
     * @param array|null     $wpm_levels Optional. Membership Levels data to use.
     */
    public function __construct($level_id, $wpm_levels = null)
    {

        if (! function_exists('wishlistmember_instance') || ! in_array(get_class(wishlistmember_instance()), ['WishListMember', 'WishListMember3'], true)) {
            return;
        }

        if (is_null($wpm_levels)) {
            $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        }

        if (isset($wpm_levels[ $level_id ])) {
            $this->data       = array_merge(wishlistmember_instance()->level_defaults, $wpm_levels[ $level_id ]);
            $this->ID         = $level_id;
            $this->data['ID'] = $level_id;
        }
    }

    /**
     * Return full level data
     *
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }

    // === START: MAGIC METHODS ===

    /**
     * Gets level properties
     *
     * @param  string $name Property name.
     * @return mixed
     */
    public function __get($name)
    {
        if ('id' === $name) {
            $name = 'ID';
        }
        return $this->data[ $name ] ? $this->data[ $name ] : null;
    }

    /**
     * Sets the level properties
     *
     * @param string $name  Property name.
     * @param mixed  $value Property value.
     */
    public function __set($name, $value)
    {
        if ('id' === $name) {
            $name = 'ID';
        }
        $this->data[ $name ] = $value;
    }

    /**
     * Checks if a property is set
     *
     * @param  string $name Property name.
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[ $name ]);
    }

    /**
     * Handle calls to deprecated methods by calling the new method instead
     *
     * @param  string $name Name of method that was called.
     * @param  array  $args Arguments passed to method.
     * @return mixed
     */
    public function __call($name, $args)
    {
        static $deprecated_functions = [
            'CountMembers' => 'count_members',
        ];

        $fxn = wlm_arrval($deprecated_functions, $name);
        if ($fxn) {
            wlm_deprecated_method_error_log($name, $fxn);
            return call_user_func_array([$this, $fxn], $args);
        }
    }

    /**
     * Handle calls to deprecated static methods by calling the new static method instead
     *
     * @param  string $name Name of method that was called.
     * @param  array  $args Arguments passed to method.
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        static $deprecated_functions = [
            'GetAllLevels'      => 'get_all_levels',
            'UpdateLevelsCount' => 'update_levels_count',
        ];

        $fxn = wlm_arrval($deprecated_functions, $name);
        if ($fxn) {
            wlm_deprecated_method_error_log($name, $fxn);
            return call_user_func_array([__CLASS__, $fxn], $args);
        }
    }

    // === CLOSE: MAGIC METHODS ===
    // === START: METHODS ===

    /**
     * Save the current level
     *
     * @return void
     */
    public function save()
    {
        $wpm_levels              = wishlistmember_instance()->get_option('wpm_levels');
        $wpm_levels[ $this->ID ] = $this->data;
        wishlistmember_instance()->save_option('wpm_levels', $wpm_levels);
    }

    /**
     * Count Members in Level
     *
     * @param  boolean $active_only Set to TRUE to count active members only.
     * @return integer
     */
    public function count_members($active_only = false)
    {
        global $wpdb;
        $table         = wishlistmember_instance()->table_prefix . 'userlevels';
        $table_options = wishlistmember_instance()->table_prefix . 'userlevel_options';

        $member_count = wlm_cache_get('wishlist_member_all_levels_members_count', 'wishlist-member');
        if (false === $member_count) {
            $member_count = [];
            $results      = $wpdb->get_results('SELECT `level_id`,COUNT(*) AS `cnt` FROM `' . esc_sql($table) . "` WHERE `user_id` IN (SELECT ID FROM {$wpdb->users}) GROUP BY `level_id`");
            foreach ($results as $result) {
                $member_count[ $result->level_id ] = $result->cnt;
            }
            wlm_cache_set('wishlist_member_all_levels_members_count', $member_count, 'wishlist-member');
        }

        if ($active_only) {
            $date = 1 === (int) $this->noexpire ? '1000-00-00 00:00:00' : wlm_date('Y-m-d H:i:s', strtotime("-{$this->expire} {$this->calendar}"));
            return $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT COUNT(DISTINCT `ul`.`user_id`) FROM `' . esc_sql($table) . '` AS `ul` LEFT JOIN `' . esc_sql($table_options) . "` AS `ulo` ON `ul`.`ID`=`ulo`.`userlevel_id` AND (`ulo`.`option_name` IN ('cancelled','forapproval','unconfirmed','registration_date') AND `ulo`.`option_value`<>'' AND `ulo`.`option_value`<>0 AND `ulo`.`option_value`<=%s) WHERE `user_id` IN (SELECT ID FROM {$wpdb->users}) AND `ul`.`level_id`=%s AND `ulo`.`userlevel_id` IS NULL",
                    $date,
                    $this->ID
                )
            );
        } else {
            return ( isset($member_count[ $this->ID ]) ? $member_count[ $this->ID ] : '' );
        }
    }

    // === CLOSE: METHODS ===
    // === START: STATIC METHODS ==

    /**
     * Checks all levels and returns TRUE if at least one has 'autocreate_account_enable' set to 1. Returns FALSE otherwise
     *
     * @return boolean
     */
    public static function any_can_autocreate_account_for_integration()
    {

        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        if (! is_array($wpm_levels)) {
            $wpm_levels = [];
        }
        foreach ($wpm_levels as $level) {
            if (! empty($level['autocreate_account_enable'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all Membership Levels
     *
     * @param  boolean $full_data Set to TRUE to return complete level information or FALSE to return just the IDs.
     * @return array
     */
    public static function get_all_levels($full_data = false)
    {
        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        if (! is_array($wpm_levels)) {
            return [];
        }
        $level_ids = array_keys($wpm_levels);
        if ($full_data) {
            $levels = [];
            foreach ($level_ids as $level_id) {
                $level = new \WishListMember\Level($level_id, $wpm_levels);
                if ((string) $level->ID === (string) $level_id) {
                    $levels[] = $level;
                }
            }
            return $levels;
        } else {
            return $level_ids;
        }
    }

    /**
     * Update the member count of all membership levels
     */
    public static function update_levels_count()
    {
        $levels     = self::get_all_levels(true);
        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        foreach ($levels as $level) {
            $wpm_levels[ $level->ID ]['count'] = $level->count_members();
        }
        wishlistmember_instance()->save_option('wpm_levels', $wpm_levels);
    }

    /**
     * Create a new membership level.
     *
     * @param  array $data Level Data.
     * @return object|false \WishListMember\Level object or false if the level name already exists
     */
    public static function create_level($data)
    {
        $defaults = wishlistmember_instance()->level_defaults + wishlistmember_instance()->level_email_defaults;
        $data     = array_intersect_key($data, $defaults) + $defaults;

        // Expiration...
        $data['noexpire'] = (int) empty($data['expire_option']);

        $levels = wishlistmember_instance()->get_option('wpm_levels');

        // Check if level name already exists.
        $names = array_column($levels, 'name');
        if (in_array($data['name'], $names, true)) {
            return false;
        }

        // Generate unique level ID.
        $id = time();
        while (isset($levels[ $id ])) {
            ++$id;
        }
        $data['ID'] = $id;

        // Generate unique level URL.
        $urls = array_column($levels, 'url');
        do {
            $data['url'] = wishlistmember_instance()->pass_gen(10);
        } while (in_array($data['url'], $urls, true));

        $levels[ $id ] = $data;
        wishlistmember_instance()->SaveOption('wpm_levels', $levels);
        return $data;
    }

    // === CLOSE: STATIC METHODS ===
}
