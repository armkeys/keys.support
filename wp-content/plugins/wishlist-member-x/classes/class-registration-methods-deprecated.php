<?php

/**
 * Deprecated Registration Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Deprecated Registration Methods trait
*/
trait Registration_Methods_Deprecated
{
    /**
     * Wrapper function for recaptcha_response();
     *
     * @return boolean
     */
    public function reCaptchaResponse()
    {
        wlm_deprecated_method_error_log(__METHOD__, 'recaptcha_response');
        return $this->recaptcha_response();
    }

    /**
     * Wrapper for wpm_register()
     *
     * @param  array   $data                User data array.
     * @param  string  $wpm_errmsg          Passed by reference, we save the error message here.
     * @param  boolean $send_welcome_email  True to send registration email or not.
     * @param  boolean $notify_admin        True to notify admin via email of this registration.
     * @param  integer $min_password_length Minimum password length. Defaults to user specified length in settings section.
     * @param  string  $pending_status      Pending status text.
     * @return integer|boolean             User ID on success or false on error
     */
    public function WPMRegister($data, &$wpm_errmsg, $send_welcome_email = true, $notify_admin = true, $min_password_length = null, $pending_status = null)
    {
        wlm_deprecated_method_error_log(__METHOD__, 'wpm_register');
        return $this->wpm_register($data, $wpm_errmsg, $send_welcome_email, $notify_admin, $min_password_length, $pending_status);
    }

    /**
     * Wrapper for wpm_register_existing()
     *
     * @param  array   $data               User data array.
     * @param  string  $wpm_errmsg         Passed by reference, we save the error message here.
     * @param  boolean $send_welcome_email True to send registration email or not, if "sendlevel", use level settings.
     * @param  boolean $notify_admin       True to notify admin via email of this registration.
     * @param  boolean $bypass_user_auth   Bypass user authentication.
     * @return integer|boolean            User ID on success or false on error
     */
    public function WPMRegisterExisting($data, &$wpm_errmsg, $send_welcome_email = true, $notify_admin = true, $bypass_user_auth = false)
    {
        wlm_deprecated_method_error_log(__METHOD__, 'wpm_register_existing');
        return $this->wpm_register_existing($data, $wpm_errmsg, $send_welcome_email, $notify_admin, $bypass_user_auth);
    }

    /**
     * Wrapper for registration_cookie()
     *
     * @param  boolean|string $set   Boolean or "manual"
     * @param  string         $hash  Passed by reference, cookie hash.
     * @param  string         $level Level ID.
     * @return boolean
     */
    public function RegistrationCookie($set = null, &$hash = null, $level = null)
    {
        wlm_deprecated_method_error_log(__METHOD__, 'registration_cookie');
        return $this->registration_cookie($set, $hash, $level);
    }

    /**
     * Wrapper for inject_for_approval_settings()
     *
     * @param array  $wpm_levels Passed by reference, $wpm_levels.
     * @param string $level_id   Level ID.
     */
    public function InjectForApprovalSettings(&$wpm_levels, $level_id)
    {
        wlm_deprecated_method_error_log(__METHOD__, 'inject_for_approval_settings');
        $this->inject_for_approval_settings($wpm_levels, $level_id);
    }

    /**
     * Wrapper for is_fallback_url()
     *
     * @param  string $reg value of wlm_get_data()['reg'].
     * @return boolean
     */
    public function IsFallBackURL($reg)
    {
        wlm_deprecated_method_error_log(__METHOD__, 'is_fallback_url');
        return $this->is_fallback_url($reg);
    }
}
