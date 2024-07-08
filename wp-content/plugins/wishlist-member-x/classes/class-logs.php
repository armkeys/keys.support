<?php

/**
 * WishList Member Logs class file
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * WishList Member Logs class
 */
class Logs
{
    /**
     * Add log entry
     *
     * @uses \wpdb::replace
     *
     * @param  integer             $user_id   User ID.
     * @param  string              $log_group Log Group.
     * @param  string              $log_key   Log Key.
     * @param  mixed               $log_value Log Data.
     * @param  string|integer|null $timestamp Unix timestamp or a valid date string.
     * @return integer|false                 The number of rows inserted/affected, or false on error
     */
    public static function add($user_id, $log_group, $log_key, $log_value, $timestamp = null)
    {
        global $wpdb;
        if (empty($user_id) || empty($log_group) || empty($log_key) || empty($log_value)) {
            return false;
        }

        $timestamp = self::compute_time($timestamp);

        $data = [
            'user_id'    => (int) $user_id,
            'log_group'  => (string) $log_group,
            'log_key'    => (string) $log_key,
            'date_added' => $timestamp,
            'log_value'  => wlm_maybe_serialize($log_value),
        ];
        return $wpdb->replace(wishlistmember_instance()->table_names->logs, $data);
    }

    /**
     * Retrieve log entries
     *
     * @uses \wpdb::get_results
     *
     * @param  integer             $user_id   User ID.
     * @param  string|null         $log_group Log Group.
     * @param  string|null         $log_key   Log Key.
     * @param  string|integer|null $timestamp Unix timestamp or a valid date string.
     * @return array|null                Database query results
     */
    public static function get($user_id, $log_group = null, $log_key = null, $timestamp = null)
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM `' . esc_sql(wishlistmember_instance()->table_names->logs) . '` WHERE `user_id`=%d AND `log_group` LIKE %s AND `log_key` LIKE %s AND `date_added` LIKE %s ORDER BY date_added DESC',
                $user_id,
                $log_group ? $wpdb->esc_like($log_group) : '%',
                $log_key ? $wpdb->esc_like($log_key) : '%',
                $timestamp ? self::compute_time($timestamp) : '%'
            )
        );
    }

    /**
     * Delete log entries
     *
     * @uses \wpdb::delete
     *
     * @param  integer             $user_id   User ID.
     * @param  string|null         $log_group Log Group.
     * @param  string|null         $log_key   Log Key.
     * @param  string|integer|null $timestamp Unix timestamp or a valid date string.
     * @return integer|false                 The number of rows deleted, or false on error.
     */
    public static function delete($user_id, $log_group = null, $log_key = null, $timestamp = null)
    {
        global $wpdb;

        $data = [
            'user_id' => (int) $user_id,
        ];
        if ($log_group) {
            $data['log_group'] = (string) $log_group;
        }
        if ($log_key) {
            $data['log_key'] = (string) $log_key;
        }
        if ($timestamp) {
            $data['date_added'] = self::compute_time($timestamp);
        }

        return $wpdb->delete(wishlistmember_instance()->table_names->logs, $data);
    }

    /**
     * Converts $timestamp into MySQL datatime format
     * Generates datetime from time() if passed an empty or invalid $timestamp
     *
     * @param  string|integer $timestamp Unix timestamp or a valid date string.
     * @return string               MySQL datetime.
     */
    private static function compute_time($timestamp)
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}\d{2}$/', $timestamp)) {
            if (! is_numeric($timestamp)) {
                $timestamp = strtotime($timestamp);
                $timestamp = $timestamp ? $timestamp : time();
            }
            $timestamp = gmdate('Y-m-d H:i:s', $timestamp);
        }
        return $timestamp;
    }
}
