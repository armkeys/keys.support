<?php

/**
 * WishList Member API Queue class file
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * WishList Member API Queue class
 */
class API_Queue
{
    /**
     * The table prefix
     *
     * @var string
     */
    public $table_prefix;

    /**
     * The table
     *
     * @var string
     */
    public $table;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix . 'wlm_';
        $this->table        = $this->table_prefix . 'api_queue';

        // Cleanup some old records with error.
        $this->remove_old_queue();
    }

    /**
     * Add item to queue
     *
     * @param  string  $name   Item name.
     * @param  mixed   $value  Item value.
     * @param  string  $notes  Item notes.
     * @param  boolean $unique True to check for uniqueness of name + value. Default false.
     * @return integer|false   Number of items added on success or false on failure.
     */
    public function add_queue($name, $value, $notes = '', $unique = false)
    {
        global $wpdb;
        if ($unique) {
            $unique = $wpdb->get_row($wpdb->prepare('SELECT `ID` FROM `' . esc_sql($this->table) . '` WHERE `name`=%s AND `value`=%s LIMIT 1', $name, $value));
            if ($unique) {
                return false;
            }
        }
        $data = [
            'name'  => $name,
            'value' => $value,
            'notes' => $notes,
            'tries' => 0,
        ];
        return $wpdb->insert($this->table, $data);
    }

    /**
     * Return the number of times a queue item is in the database.
     *
     * @param  string  $name  Item name.
     * @param  integer $tries Optional: Number of tries.
     * @return integer
     */
    public function count_queue($name, $tries = null)
    {
        global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared.
        $count = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT COUNT(ID) AS count FROM `' . esc_sql($this->table) . '` WHERE `name` LIKE %s AND `tries` <= %d',
                '%' . $wpdb->esc_like($name) . '%',
                null !== $tries ? $tries : PHP_INT_MAX
            )
        );
        $count = $count && is_array($count) && isset($count[0]) ? $count[0]->count : 0;
        return $count;
    }

    /**
     * Get queue items.
     *
     * @param  string  $name  Item name.
     * @param  integer $limit Optional: Max number of items to return. Default all.
     * @param  integer $tries Optional: Only return items where the number of tries is less than or equal to this value.
     * @param  string  $sort  Optional: Field name to sort by. Default 'ID'.
     * @param  string  $date  Optional: MySQL date. Only return items that were added on or before this value.
     * @return array
     */
    public function get_queue($name, $limit = null, $tries = null, $sort = 'ID', $date = null)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM `' . esc_sql($this->table) . '` WHERE `name` LIKE %s AND `tries` <= %d AND `date_added` <= %s ORDER BY %0s ASC LIMIT %d',
                '%' . $wpdb->esc_like($name) . '%',
                null !== $tries ? $tries : PHP_INT_MAX,
                null !== $date ? $date : '9999-01-01',
                $sort,
                (int) $limit ? $limit : PHP_INT_MAX
            )
        );
    }

    /**
     * Update queue by ID
     *
     * @param  integer $id   Queue ID.
     * @param  mixed   $data Associative array of fields to update.
     * @return integer|false Number of rows updated or false on error.
     */
    public function update_queue($id, $data)
    {
        global $wpdb;
        $where = ['ID' => $id];
        return $wpdb->update($this->table, $data, $where);
    }

    /**
     * Delete queue by ID
     *
     * @param integer $id Queue ID.
     */
    public function delete_queue($id)
    {
        global $wpdb;
        if (is_array($id)) {
            $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM `' . esc_sql($this->table) . '` WHERE `ID` IN (' . implode(', ', array_fill(0, count($id), '%d')) . ')',
                    ...array_values($id)
                )
            );
        } else {
            $wpdb->query($wpdb->prepare('DELETE FROM `' . esc_sql($this->table) . '` WHERE `ID`=%d', $id));
        }
    }

    /**
     * Remove queueitems that are more than 1 week old and have been processed at least once.
     */
    public function remove_old_queue()
    {
        global $wpdb;
        $wpdb->query('DELETE FROM `' . esc_sql($this->table) . '` WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 WEEK) AND tries > 1');
    }
}
