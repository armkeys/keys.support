<?php

/**
 * Deprecated Member Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Deprecated Member Methods trait
 */
trait Member_Methods_Deprecated
{
    /**
     * Deprecated Method ::SyncMembership()
     * This calls ::schedule_sync_membership() instead
     */
    public function SyncMembership()
    {
        wlm_deprecated_method_error_log(__METHOD__, 'schedule_sync_membership');
        return $this->schedule_sync_membership(...func_get_args());
    }

    /**
     * Deprecated Method ::SetMembershipLevels()
     * This calls ::set_membership_levels() instead
     *
     * @param integer $id                     User ID.
     * @param array   $levels                 Level IDs.
     * @param boolean $no_autoresponder       Set to TRUE to disable autoresponder.
     * @param boolean $timestamp_no_set       Set to TRUE to disable setting of timestamp.
     * @param boolean $transaction_id_no_set  Set to TRUE to disable setting of transaction ID.
     * @param boolean $no_sync                Set to TRUE to prevent calling SyncMembership.
     * @param boolean $no_webinar             Set to TRUE to disable webinar.
     * @param array   $pending_autoresponders Array of pending autoresponders.
     * @param boolean $keep_existing_levels   Set to TRUE to keep existing levels not passed in $levels.
     * @param boolean $registration           Set to TRUE if called from a registration .
     */
    public function SetMembershipLevels($id, $levels, $no_autoresponder = null, $timestamp_no_set = null, $transaction_id_no_set = null, $no_sync = null, $no_webinar = null, $pending_autoresponders = null, $keep_existing_levels = null, $registration = false)
    {
        wlm_deprecated_method_error_log(__METHOD__, 'set_membership_levels');

        $options = [];
        if (! is_null($no_autoresponder)) {
            $options['process_autoresponders'] = ! $no_autoresponder;
        }
        if (! is_null($timestamp_no_set)) {
            $options['set_timestamp'] = ! $timestamp_no_set;
        }
        if (! is_null($transaction_id_no_set)) {
            $options['set_transaction_id'] = ! $transaction_id_no_set;
        }
        if (! is_null($no_sync)) {
            $options['sync'] = ! $no_sync;
        }
        if (! is_null($no_webinar)) {
            $options['process_webinars'] = ! $no_webinar;
        }
        if (! is_null($pending_autoresponders)) {
            $options['pending_autoresponders'] = (array) $pending_autoresponders;
        }
        if (! is_null($keep_existing_levels)) {
            $options['keep_existing_levels'] = (bool) $keep_existing_levels;
        }
        $options['registration'] = (bool) $registration;

        return $this->set_membership_levels($id, $levels, $options);
    }

    /**
     * Deprecated Method ::ImportMembers()
     * This calls ::queue_import_members() instead
     */
    public function ImportMembers()
    {
        wlm_deprecated_method_error_log(__METHOD__, 'queue_import_members');
        return $this->queue_import_members(...func_get_args());
    }
}
