<?php

/**
 * Deprecated Email Broadcast Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Deprecated Email Broadcast Methods trait
 */
trait Email_Broadcast_Methods_Deprecated
{
    /**
     * Deprecated method.
     * Call get_all_email_broadcast() instead.
     */
    public function GetALLEmailbroadcast()
    {
        wlm_deprecated_method_error_log(__METHOD__, 'get_all_email_broadcast');
        return $this->get_all_email_broadcast(...func_get_args());
    }
}
