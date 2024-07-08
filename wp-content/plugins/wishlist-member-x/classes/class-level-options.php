<?php

/**
 * Level Option Class file
 *
 * @package wishlistmember
 */

namespace WishListMember;

defined('ABSPATH') || die();

/**
 * Level Options class
 */
class Level_Options
{
    /**
     * Table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     *
     * @param string $table_prefix Table prefix.
     */
    public function __construct($table_prefix)
    {
        $this->table_name = $table_prefix . 'level_options';
    }

    /**
     * Save level option
     *
     * @param  string $level_id Level ID.
     * @param  string $name     Option name.
     * @param  mixed  $data     Option data.
     * @return integer|false
     */
    public function save_option($level_id, $name, $data)
    {
        global $wpdb;
        $data = [
            'level_id'     => $level_id,
            'option_name'  => $name,
            'option_value' => wlm_maybe_serialize($data),
        ];
        return $wpdb->insert($this->table_name, $data);
    }

    /**
     * Update level option by option ID
     *
     * @param  integer $id   Option ID.
     * @param  mixed   $data Option data.
     * @return integer|false
     */
    public function update_option_by_id($id, $data)
    {
        global $wpdb;
        $data = [
            'option_value' => wlm_maybe_serialize($data),
        ];
        return $wpdb->update($this->table_name, $data, ['ID' => $id]);
    }

    /**
     * Update level option or adds it if it doesn't exist.
     *
     * @param  string $level_id Level ID.
     * @param  string $name     Option name.
     * @param  mixed  $data     Option data.
     * @return integer|false
     */
    public function update_option($level_id, $name, $data = null)
    {
        global $wpdb;
        $data = [
            'option_value' => wlm_maybe_serialize($data),
        ];
        if (count(self::get_options($level_id, $name, 1))) {
            return $wpdb->update(
                $this->table_name,
                $data,
                [
                    'level_id'    => $level_id,
                    'option_name' => $name,
                ]
            );
        } else {
            return $wpdb->insert(
                $this->table_name,
                $data + [
                    'level_id'    => $level_id,
                    'option_name' => $name,
                ]
            );
        }
    }

    /**
     * Delete level option by option ID
     *
     * @param  integer $id Option ID.
     * @return integer|false
     */
    public function delete_option_by_id($id)
    {
        global $wpdb;
        return $wpdb->delete($this->table_name, ['ID' => $id]);
    }

    /**
     * Delete level option.
     *
     * @param  string $level_id Level ID.
     * @param  string $name     Level name.
     * @return integer|false
     */
    public function delete_option($level_id, $name = null)
    {
        global $wpdb;
        $wpdb->delete(
            $this->table_name,
            [
                'level_id'    => $level_id,
                'option_name' => $name,
            ]
        );
    }

    /**
     * Get multiple level options
     *
     * @param  string  $level_id level ID.
     * @param  string  $name     Option name.
     * @param  integer $limit    Number of rows to return.
     * @return array
     */
    public function get_options($level_id = null, $name = null, $limit = null)
    {
        global $wpdb;

        $limit = (int) $limit;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM `' . esc_sql($this->table_name) . '` WHERE `level_id` LIKE %s AND `option_name` LIKE %s ORDER BY ID ASC LIMIT 0,%d',
                $level_id ? $wpdb->esc_like($level_id) : '%',
                $name ? $wpdb->esc_like($name) : '%',
                $limit ? $limit : PHP_INT_MAX
            )
        );
    }

    /**
     * Get single level option by option ID.
     *
     * @param  integer $id Option ID.
     * @return object
     */
    public function get_option_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare('SELECT * FROM %0s WHERE ID=%d', $this->table_name, $id));
    }

    /**
     * Get options with matching name
     *
     * @param  string  $level_id Level ID.
     * @param  string  $name     Option name in a format accepted by MySQL LIKE.
     * @param  integer $limit    Limit.
     * @return array
     */
    public function get_matching_options($level_id, $name, $limit = null)
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT `option_name`,`option_value` FROM `' . esc_sql($this->table_name) . '` WHERE `level_id`=%s AND `option_name` LIKE %s ORDER BY `option_name` ASC LIMIT 0,%d',
                $level_id,
                $name,
                $limit ? $limit : PHP_INT_MAX
            )
        );
    }
}
