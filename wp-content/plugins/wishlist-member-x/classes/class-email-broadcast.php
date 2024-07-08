<?php

/**
 * WishList Member Email Broadcast class file
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * WishList Member Email Broadcast class
 */
class Email_Broadcast
{
    /**
     * Table names
     *
     * @var array
     */
    public $table_names;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->table_names = wishlistmember_instance()->table_names;
    }

    /**
     * Save email broadcast.
     *
     * @param  string $subject      Subject.
     * @param  string $msg          Message.
     * @param  string $footer       Footer.
     * @param  string $send_to      Sed to.
     * @param  string $mlevel       Membership levels.
     * @param  string $sent_as      Sent as.
     * @param  string $otheroptions Other options.
     * @param  string $from_name    Optional sender name.
     * @param  string $from_email   Optional sender email.
     * @return integer|false Last insert ID or false on error.
     */
    public function save_broadcast($subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions, $from_name = '', $from_email = '')
    {
        global $wpdb;
        $wp_current_date = wlm_date('Y-m-d H:i:s');

        $result = $wpdb->query(
            $wpdb->prepare(
                'INSERT INTO `' . esc_sql($this->table_names->emailbroadcast) . '` ( subject, text_body, footer, send_to, mlevel, sent_as, status, otheroptions, from_name, from_email, date_added ) VALUES ( %s, %s, %s, %s, %s, %s, "Queueing", %s, %s, %s, %s )',
                $subject,
                $msg,
                $footer,
                $send_to,
                $mlevel,
                $sent_as,
                $otheroptions,
                $from_name,
                $from_email,
                $wp_current_date
            )
        );

        if ($result) {
            $ret = $wpdb->get_results('SELECT LAST_INSERT_ID( ) as LAST_INSERT_ID');
            return $ret[0]->LAST_INSERT_ID;
        } else {
            return false;
        }
    }

    /**
     * Get Email Broadcast
     *
     * @param integer $id ID.
     */
    public function get_broadcast($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . esc_sql($this->table_names->emailbroadcast) . '` WHERE id=%d', $id));
    }

    /**
     * Get all email broadcast
     *
     * @param  string $start    Starting row.
     * @param  string $per_page Rows per query.
     * @param  string $order    Order by field.
     * @return array
     */
    public function get_all_broadcast($start = '', $per_page = '', $order = '')
    {
        global $wpdb;
        $per_page = (int) $per_page;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . esc_sql($this->table_names->emailbroadcast) . ' ORDER BY %0s DESC LIMIT %d,%d',
                $order ? $order : 'date_added',
                $per_page > 0 ? $start : 0,
                $per_page > 0 ? $per_page : PHP_INT_MAX
            )
        );
    }

    /**
     * Get all Unsynced Email Broadcast (from old email broadcast)
     *
     * @return array
     */
    public function get_unsync_broadcast()
    {
        global $wpdb;
        return $wpdb->get_results('SELECT * FROM `' . esc_sql($this->table_names->emailbroadcast) . '` WHERE ( recipients != "" AND total_queued <= 0 ) OR ( failed_address IS NOT NULL AND failed_address != "" )');
    }

    /**
     * Count all email broadcast
     *
     * @return integer
     */
    public function count_broadcast()
    {
        global $wpdb;
        return $wpdb->get_var('SELECT COUNT(*) FROM `' . esc_sql($this->table_names->emailbroadcast) . '`');
    }

    /**
     * Update Email Broadcast
     *
     * @param integer $id   ID.
     * @param array   $data Data.
     */
    public function update_broadcast($id, $data)
    {
        global $wpdb;
        return $wpdb->update($this->table_names->emailbroadcast, $data, ['id' => (int) $id]);
    }

    /**
     * Delete Email Broadcasts
     *
     * @param  array $ids
     * @return boolean
     */
    public function delete_broadcast($ids)
    {
        global $wpdb;
        $ids = (array) $ids;
        return $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM ' . esc_sql($this->table_names->emailbroadcast) . ' WHERE id IN (' . implode(', ', array_fill(0, count($ids), '%d')) . ')',
                ...array_values($ids)
            )
        );
    }

    /**
     * Add To Email Queue
     *
     * @param  integer $broadcastid Broadcast ID.
     * @param  integer $userid      User ID.
     * @return boolean
     */
    public function add_email_queue($broadcastid, $userid)
    {
        global $wpdb;
        if ($wpdb->query($wpdb->prepare('INSERT INTO ' . esc_sql($this->table_names->email_queue) . ' (broadcastid,userid) VALUES(%d,%d)', $broadcastid, $userid))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Bulk Add To Email Queue
     *
     * @param  array $fields Field names.
     * @param  array $data   Data.
     * @return boolean
     */
    public function bulk_add_email_queue($fields, $data)
    {
        global $wpdb;
        if (false !== $wpdb->insert($this->table_names->email_queue, array_combine($fields, $data))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get email queue.
     *
     * @param  integer $broadcastid    Broadcast ID.
     * @param  boolean $include_fail   True to include failed broadcasts. Default false.
     * @param  boolean $include_paused True to include paused broadcasts. Default false.
     * @param  integer $limit          Max rows to return.
     * @return array
     */
    public function get_email_queue($broadcastid = null, $include_fail = false, $include_paused = false, $limit = 0)
    {
        global $wpdb;
        $limit = (int) $limit;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT q.id AS id,q.broadcastid AS broadcastid,q.userid as userid,b.subject AS subject, b.text_body AS text_body,b.footer AS footer,b.send_to AS send_to,b.sent_as AS sent_as, b.from_name, b.from_email FROM `' . esc_sql($this->table_names->email_queue) . '` AS q LEFT JOIN `' . esc_sql($this->table_names->emailbroadcast) . '` AS b ON b.id=q.broadcastid WHERE q.broadcastid LIKE %s AND q.failed LIKE %s AND b.status LIKE %s LIMIT %d',
                is_numeric($broadcastid) ? (int) $broadcastid : '%',
                ! $include_fail ? 0 : '%',
                ! $include_paused ? 'Queued' : '%',
                $limit ? $limit : PHP_INT_MAX
            )
        );
    }

    /**
     * Get Email Queue
     *
     * @param  integer $id ID.
     * @return object      Row.
     */
    public function get_email_queue_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT q.id AS id,q.broadcastid AS broadcastid,q.userid as userid,b.subject AS subject, b.text_body AS text_body,b.footer AS footer,b.send_to AS send_to,b.sent_as AS sent_as FROM `' . esc_sql($this->table_names->email_queue) . '` AS q LEFT JOIN `' . esc_sql($this->table_names->emailbroadcast) . '` AS b ON b.id=q.broadcastid WHERE q.id = %d',
                $id
            )
        );
    }

    /**
     * Count Email Queue
     *
     * @param int     $broadcastid
     * @param boolean $include_fail
     * @param boolean $count
     */
    /**
     * Count email queue.
     *
     * @param  integer $broadcastid    Broadcast ID.
     * @param  boolean $include_fail   True to include failed. Default false.
     * @param  boolean $include_paused True to include paused. Default false.
     * @return integer
     */
    public function count_email_queue($broadcastid = null, $include_fail = false, $include_paused = false)
    {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM `' . esc_sql($this->table_names->email_queue) . '` AS q LEFT JOIN `' . esc_sql($this->table_names->emailbroadcast) . '` AS b ON b.id=q.broadcastid WHERE broadcastid LIKE %s AND failed LIKE %s AND b.status LIKE %s',
                is_numeric($broadcastid) ? (int) $broadcastid : '%',
                ! $include_fail ? 0 : '%',
                ! $include_paused ? 'Queued' : '%'
            )
        );
    }

    /**
     * Get Failed Email Queue
     *
     * @param  integer $broadcastid ID.
     * @return array
     */
    public function get_failed_queue($broadcastid = null)
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT ee.id AS id, ee.broadcastid AS broadcastid, ee.userid AS userid, ee.failed AS failed, u.user_email AS user_email FROM `' . esc_sql($this->table_names->email_queue) . "` AS ee LEFT JOIN `{$wpdb->users}` u ON u.ID = ee.userid WHERE broadcastid LIKE %s AND failed > 0",
                is_numeric($broadcastid) ? (int) $broadcastid : '%'
            )
        );
    }

    /**
     * Get Failed Email Queue count.
     *
     * @param  integer $broadcastid ID.
     * @return integer
     */
    public function count_failed_queue($broadcastid = null)
    {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM `' . esc_sql($this->table_names->email_queue) . '` WHERE broadcastid LIKE %s AND failed > 0',
                is_numeric($broadcastid) ? (int) $broadcastid : '%'
            )
        );
    }

    /**
     * Delete Multiple Email Broadcast Queue items.
     *
     * @param  array $ids IDs.
     * @return boolean
     */
    public function delete_email_queue($ids)
    {
        global $wpdb;
        $ids = (array) $ids;
        return $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM `' . esc_sql($this->table_names->email_queue) . '` WHERE id IN (' . implode(', ', array_fill(0, count($ids), '%d')) . ')',
                ...array_values($ids)
            )
        );
    }

    /**
     * Purge Email Broadcast Queue filtered by failed status.
     *
     * @param  integer $broadcastid Broadcast ID.
     * @param  boolean $failed_only True to delete only failed. Default true.
     * @return boolean
     */
    public function purge_broadcast_queues($broadcastid, $failed_only = true)
    {
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM `' . esc_sql($this->table_names->email_queue) . '` WHERE broadcastid = %d AND failed > %d',
                $broadcastid,
                $failed_only ? 0 : -1
            )
        );
    }

    /**
     * Fail/Unfail Email Broadcast Queue
     *
     * @param  array $ids   IDS.
     * @param  array $value 1 to fail. 0 to unfail.
     * @return boolean
     */
    public function fail_email_queue($ids, $value = 1)
    {
        global $wpdb;
        $ids   = (array) $ids;
        $value = (int) $value;
        return $wpdb->query(
            $wpdb->prepare(
                'UPDATE ' . esc_sql($this->table_names->email_queue) . ' SET failed=%d WHERE id IN (' . implode(', ', array_fill(0, count($ids), '%d')) . ')',
                $value,
                ...array_values($ids)
            )
        );
    }

    /**
     * Request broadcast.
     *
     * @param  integer $broadcastid ID.
     * @return boolean
     */
    public function requeue_email($broadcastid)
    {
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare(
                'UPDATE ' . esc_sql($this->table_names->email_queue) . ' SET failed = 0 WHERE broadcastid = %d',
                $broadcastid
            )
        );
    }

    /**
     * Check if old stats is missing
     *
     * @return boolean;
     */
    public function check_stats_missing()
    {
        global $wpdb;
        // Check if the column exist.
        $res = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND ( COLUMN_NAME="failed_address" OR COLUMN_NAME="recipients" ) AND TABLE_NAME=%s',
                $this->table_names->emailbroadcast
            )
        );
        return count($res) > 1 ? true : false;
    }
}
