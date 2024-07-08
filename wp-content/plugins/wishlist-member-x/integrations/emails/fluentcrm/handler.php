<?php

/**
 * FluentCRM handler
 *
 * @package WishListMember/Autoresponders
 */

namespace WishListMember\Autoresponders;

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid.

/**
 * FluentCRM class
 */
class FluentCRM
{
    /**
     * __callStatic magic method
     *
     * @param  string $name Name of method to call.
     * @param  array  $args Arguments to pass.
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        $interface = self::_interface();
        if ($interface->api()) {
            return call_user_func_array([$interface, $name], $args);
        }
    }

    /**
     * Delete tag action
     */
    public static function delete_tag_action()
    {
        $fluentcrm_settings = new \WishListMember\Autoresponder('fluentcrm');
        try {
            unset($fluentcrm_settings->settings['fluentcrm_settings']['tag'][ wlm_post_data()[ 'tag_id' ] ]);
        } catch (\Exception $e) {
            null;
        }
        $fluentcrm_settings->save_settings();
        wp_send_json_success();
    }

    /**
     * Interface
     *
     * @return FluentCRM_Interface
     */
    public static function _interface()
    {
        static $interface;
        if (! $interface) {
            $interface = new FluentCRM_Interface();
        }
        return $interface;
    }
}

/**
 * FluentCRM_Interface class
 */
class FluentCRM_Interface
{
    /**
     * Settings
     *
     * @var array
     */
    private $settings = [];

    /**
     * Plugin active status
     *
     * @var boolean
     */
    public $plugin_active = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $data = wlm_or(( new \WishListMember\Autoresponder('fluentcrm') )->settings, false);
        $data = isset($data['fluentcrm_settings']) ? $data['fluentcrm_settings'] : [];

        $this->settings = is_array($data) ? $data : [];

        // Check if FluentCRM is active.
        $active_plugins = wlm_get_active_plugins();
        if (in_array('FluentCRM - Marketing Automation For WordPress', $active_plugins, true) || isset($active_plugins['fluent-crm/fluent-crm.php']) || is_plugin_active('fluent-crm/fluent-crm.php')) {
            $this->plugin_active = true;
        }
    }

    /**
     * Check if plugin is active
     *
     * @return boolean
     */
    public function api()
    {
        return $this->plugin_active;
    }

    /**
     * Fires when tags are added
     *
     * @param array  $attached_tagids Attached tag ids.
     * @param object $subscriber      Subscriber data.
     */
    public function TagsAddedHook($attached_tagids, $subscriber)
    {
        $action = 'add';
        $user   = get_user_by('email', $subscriber->email);
        if (false === $user) {
            return;
        }
        foreach ($attached_tagids as $value) {
            $settings = isset($this->settings['tag'][ $value ][ $action ]) ? $this->settings['tag'][ $value ][ $action ] : [];
            $this->DoHook($user->ID, $value, $action, $settings, false);
        }
    }

    /**
     * Fires when tags re removed
     *
     * @param array  $detached_tagids Detached tag ids.
     * @param object $subscriber      Subscriber data.
     */
    public function TagsRemovedHook($detached_tagids, $subscriber)
    {
        $action = 'remove';
        $user   = get_user_by('email', $subscriber->email);
        if (false === $user) {
            return;
        }
        foreach ($detached_tagids as $value) {
            $settings = isset($this->settings['tag'][ $value ][ $action ]) ? $this->settings['tag'][ $value ][ $action ] : [];
            $this->DoHook($user->ID, $value, $action, $settings, false);
        }
    }

    /**
     * Fires when lists are added
     *
     * @param array  $attached_listids List IDs.
     * @param object $subscriber       Subscriber data.
     */
    public function ListsAddedHook($attached_listids, $subscriber)
    {
        $action = 'add';
        $user   = get_user_by('email', $subscriber->email);
        if (false === $user) {
            return;
        }
        foreach ($attached_listids as $value) {
            $settings = isset($this->settings['list'][ $value ][ $action ]) ? $this->settings['list'][ $value ][ $action ] : [];
            $this->DoHook($user->ID, $value, $action, $settings, false);
        }
    }

    /**
     * Fires when lists are removed.
     *
     * @param array  $detached_listids List IDs.
     * @param object $subscriber       Subscriber data.
     */
    public function ListsRemovedHook($detached_listids, $subscriber)
    {
        $action = 'remove';
        $user   = get_user_by('email', $subscriber->email);
        if (false === $user) {
            return;
        }
        foreach ($detached_listids as $value) {
            $settings = isset($this->settings['list'][ $value ][ $action ]) ? $this->settings['list'][ $value ][ $action ] : [];
            $this->DoHook($user->ID, $value, $action, $settings, false);
        }
    }

    /**
     * Run hook
     *
     * @param object  $wpuser   WP User.
     * @param string  $hook_id  Hook ID.
     * @param string  $action   Action.
     * @param array   $settings Settings.
     * @param boolean $is_list  True if list.
     */
    private function DoHook($wpuser, $hook_id, $action, $settings, $is_list = true)
    {

        $added_levels     = isset($settings['add_level']) ? $settings['add_level'] : [];
        $cancelled_levels = isset($settings['cancel_level']) ? $settings['cancel_level'] : [];
        $removed_levels   = isset($settings['remove_level']) ? $settings['remove_level'] : [];

        $add_ppp    = isset($settings['add_ppp']) ? $settings['add_ppp'] : [];
        $remove_ppp = isset($settings['remove_ppp']) ? $settings['remove_ppp'] : [];

        if (count($added_levels) <= 0 && count($cancelled_levels) <= 0 && count($removed_levels) <= 0 && count($add_ppp) <= 0 && count($remove_ppp) <= 0) {
            return;
        }

        $current_user_mlevels = wishlistmember_instance()->get_membership_levels($wpuser);
        $wpm_levels           = wishlistmember_instance()->get_option('wpm_levels');

        $prefix = $is_list ? 'L' : 'T';

        $action = strtoupper(substr($action, 0, 1));
        $txnid  = "FLUENTCRM-{$action}-{$prefix}{$hook_id}-";

        // Add to level.
        if (count($added_levels) > 0) {
            $user_mlevels  = $current_user_mlevels;
            $add_level_arr = $added_levels;
            foreach ($add_level_arr as $add_level) {
                if (! isset($wpm_levels[ $add_level ])) {
                    continue;// check if valid level.
                }
                if (! in_array($add_level, $user_mlevels)) {
                    $user_mlevels[] = $add_level;
                    wishlistmember_instance()->set_membership_levels($wpuser, $user_mlevels);
                    wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid.

                    $macros['[password]']    = '********';
                    $macros['[memberlevel]'] = $wpm_levels[ $add_level ]['name'];
                    wishlistmember_instance()->send_email_template('admin_new_member_notice', $wpuser, $macros, wishlistmember_instance()->get_option('email_sender_address'));
                    wishlistmember_instance()->send_email_template('registration', $wpuser, $macros);
                } else {
                    // For cancelled members.
                    $cancelled      = wishlistmember_instance()->level_cancelled($add_level, $wpuser);
                    $resetcancelled = true; // Lets make sure that old versions without this settings still works.
                    if (isset($wpm_levels[ $add_level ]['uncancelonregistration'])) {
                        $resetcancelled = 1 === (int) $wpm_levels[ $add_level ]['uncancelonregistration'];
                    }
                    if ($cancelled && $resetcancelled) {
                        wishlistmember_instance()->level_cancelled($add_level, $wpuser, false);
                        wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid.
                    }

                    // For Expired Members.
                    $expired      = wishlistmember_instance()->level_expired($add_level, $wpuser);
                    $resetexpired = 1 === (int) $wpm_levels[ $add_level ]['registrationdatereset'];
                    if ($expired && $resetexpired) {
                            wishlistmember_instance()->user_level_timestamp($wpuser, $add_level, time());
                            wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid.
                    } else {
                        // If levels has expiration and allow reregistration for active members.
                        $levelexpires     = isset($wpm_levels[ $add_level ]['expire']) ? (int) $wpm_levels[ $add_level ]['expire'] : false;
                        $levelexpires_cal = isset($wpm_levels[ $add_level ]['calendar']) ? $wpm_levels[ $add_level ]['calendar'] : false;
                        $resetactive      = 1 === (int) $wpm_levels[ $add_level ]['registrationdateresetactive'];
                        if ($levelexpires && $resetactive) {
                            // Get the registration date before it gets updated because we will use it later.
                            $levelexpire_regdate = wishlistmember_instance()->Get_UserLevelMeta($wpuser, $add_level, 'registration_date');

                            $levelexpires_cal = in_array($levelexpires_cal, ['Days', 'Weeks', 'Months', 'Years'], true) ? $levelexpires_cal : false;
                            if ($levelexpires_cal && $levelexpire_regdate) {
                                list( $xdate ) = explode('#', $levelexpire_regdate);
                                list( $xyear, $xmonth, $xday, $xhour, $xminute, $xsecond ) = preg_split('/[- :]/', $xdate);
                                if ('Days' === $levelexpires_cal) {
                                    $xday = $levelexpires + $xday;
                                }
                                if ('Weeks' === $levelexpires_cal) {
                                    $xday = ( $levelexpires * 7 ) + $xday;
                                }
                                if ('Months' === $levelexpires_cal) {
                                    $xmonth = $levelexpires + $xmonth;
                                }
                                if ('Years' === $levelexpires_cal) {
                                    $xyear = $levelexpires + $xyear;
                                }
                                wishlistmember_instance()->user_level_timestamp($wpuser, $add_level, mktime($xhour, $xminute, $xsecond, $xmonth, $xday, $xyear));
                                wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid.
                            }
                        }
                    }
                }
            }
            // Refresh for possible new levels.
            $current_user_mlevels = wishlistmember_instance()->get_membership_levels($wpuser);
        }

        // Cancel from level.
        if (count($cancelled_levels) > 0) {
            $user_mlevels = $current_user_mlevels;
            foreach ($cancelled_levels as $cancel_level) {
                if (! isset($wpm_levels[ $cancel_level ])) {
                    continue;// check if valid level.
                }
                if (in_array($cancel_level, $user_mlevels)) {
                    wishlistmember_instance()->level_cancelled($cancel_level, $wpuser, true);
                }
            }
        }

        // Remove from level.
        if (count($removed_levels) > 0) {
            $user_mlevels = $current_user_mlevels;
            foreach ($removed_levels as $remove_level) {
                $arr_index = array_search($remove_level, $user_mlevels);
                if (false !== $arr_index) {
                    unset($user_mlevels[ $arr_index ]);
                }
            }
            wishlistmember_instance()->set_membership_levels($wpuser, $user_mlevels);
            wishlistmember_instance()->schedule_sync_membership(true);
        }

        if (count($add_ppp) > 0) {
            foreach ($add_ppp as $value) {
                $post = get_post($value, ARRAY_A);
                if ($post) {
                    wishlistmember_instance()->add_post_users($post['post_type'], $post['ID'], $wpuser);
                }
            }
        }

        if (count($remove_ppp) > 0) {
            foreach ($remove_ppp as $value) {
                $post = get_post($value, ARRAY_A);
                if ($post) {
                    wishlistmember_instance()->remove_post_users($post['post_type'], $post['ID'], $wpuser);
                }
            }
        }
    }

    /**
     * Create FluentCRM contact
     *
     * @param array $data Contact data.
     */
    private function create_fluentcrm_contact($data)
    {
        $contact_api = FluentCrmApi('contacts');
        $contact     = $contact_api->createOrUpdate($data);
        // Send a double opt-in email if the status is pending.
        if ('pending' === $contact->status) {
            $contact->sendDoubleOptinEmail();
        }
    }

    /**
     * Fired when new users are created
     *
     * @param integer $uid   User ID.
     * @param array   $udata User Data.
     */
    public function NewUserTagsHook($uid = null, $udata = null)
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 === strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($udata['wpm_id'], $uid);
        $level_for_approval = wishlistmember_instance()->level_for_approval($udata['wpm_id'], $uid);

        $settings    = isset($this->settings['level'][ $udata['wpm_id'] ]['add']) ? $this->settings['level'][ $udata['wpm_id'] ]['add'] : [];
        $apply_tag   = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
        $remove_tag  = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];
        $apply_list  = isset($settings['apply_list']) ? $settings['apply_list'] : [];
        $remove_list = isset($settings['remove_list']) ? $settings['remove_list'] : [];

        if (! $level_unconfirmed && ! $level_for_approval) {
            $contact_api = FluentCrmApi('contacts');
            $contact     = $contact_api->getContact($user->user_email);
            if (! $contact) {
                $data = [
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->user_email, // required.
                    'tags'       => $apply_tag, // tag ids as an array.
                    'lists'      => $apply_list, // list ids as an array.
                ];
                $this->create_fluentcrm_contact($data);
            } else {
                $contact->detachTags($remove_tag);
                $contact->detachLists($remove_list);
                $contact->attachTags($apply_tag);
                $contact->attachLists($apply_list);
            }
        }
    }

    /**
     * Fired when a user is added to a level.
     *
     * @param iunt     $uid       User ID.
     * @param string[] $addlevels Level IDs.
     */
    public function AddUserTagsHook($uid, $addlevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 === strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $level_added = reset($addlevels); // Get the first element.

        /*
         * If from registration then don't don't process if the $addlevels is.
         * the same level the user registered to. This is already processed by NewUserTagsQueue func.
         */
        if (null !== wlm_post_data()[ 'action' ] && 'wpm_register' === wlm_post_data()[ 'action' ]) {
            if ((string) wlm_post_data()[ 'wpm_id' ] === (string) $level_added) {
                return;
            }
        }

        foreach ($addlevels as $lvl) {
            $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($lvl, $uid);
            $level_for_approval = wishlistmember_instance()->level_for_approval($lvl, $uid);

            $settings    = isset($this->settings['level'][ $lvl ]['add']) ? $this->settings['level'][ $lvl ]['add'] : [];
            $apply_tag   = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
            $remove_tag  = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];
            $apply_list  = isset($settings['apply_list']) ? $settings['apply_list'] : [];
            $remove_list = isset($settings['remove_list']) ? $settings['remove_list'] : [];

            $contact_api = FluentCrmApi('contacts');
            if (! $level_unconfirmed && ! $level_for_approval) {
                $contact = $contact_api->getContact($user->user_email);
                if (! $contact) {
                    $data = [
                        'first_name' => $user->first_name,
                        'last_name'  => $user->last_name,
                        'email'      => $user->user_email, // required.
                        'tags'       => $apply_tag, // tag ids as an array.
                        'lists'      => $apply_list, // list ids as an array.
                    ];
                    $this->create_fluentcrm_contact($data);
                } else {
                    $contact->detachTags($remove_tag);
                    $contact->detachLists($remove_list);
                    $contact->attachTags($apply_tag);
                    $contact->attachLists($apply_list);
                }
            } elseif (null !== wlm_post_data()[ 'SendMail' ]) {
                $contact = $contact_api->getContact($user->user_email);
                if (! $contact) {
                    $data = [
                        'first_name' => $user->first_name,
                        'last_name'  => $user->last_name,
                        'email'      => $user->user_email, // required.
                        'tags'       => $apply_tag, // tag ids as an array.
                        'lists'      => $apply_list, // list ids as an array.
                    ];
                    $this->create_fluentcrm_contact($data);
                } else {
                    $contact->detachTags($remove_tag);
                    $contact->detachLists($remove_list);
                    $contact->attachTags($apply_tag);
                    $contact->attachLists($apply_list);
                }
            }
        }
    }

    /**
     * Fired when levels are approved
     *
     * @param integer  $uid    User ID.
     * @param string[] $levels Array of Level IDs.
     */
    public function ConfirmApproveLevelsTagsHook($uid = null, $levels = null)
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 === strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $levels             = is_array($levels) ? $levels : (array) $levels;
        $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($levels[0], $uid);
        $level_for_approval = wishlistmember_instance()->level_for_approval($levels[0], $uid);

        $settings    = isset($this->settings['level'][ $levels[0] ]['add']) ? $this->settings['level'][ $levels[0] ]['add'] : [];
        $apply_tag   = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
        $remove_tag  = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];
        $apply_list  = isset($settings['apply_list']) ? $settings['apply_list'] : [];
        $remove_list = isset($settings['remove_list']) ? $settings['remove_list'] : [];

        if (! $level_unconfirmed && ! $level_for_approval) {
            $contact_api = FluentCrmApi('contacts');
            $contact     = $contact_api->getContact($user->user_email);
            if (! $contact) {
                $data = [
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->user_email, // required.
                    'tags'       => $apply_tag, // tag ids as an array.
                    'lists'      => $apply_list, // list ids as an array.
                ];
                $this->create_fluentcrm_contact($data);
            } else {
                $contact->detachTags($remove_tag);
                $contact->detachLists($remove_list);
                $contact->attachTags($apply_tag);
                $contact->attachLists($apply_list);
            }
        }
    }

    /**
     * Fired when users are re-register to levels
     *
     * @param integer  $uid    User ID.
     * @param string[] $levels Array of levels.
     */
    public function ReregUserTagsHook($uid, $levels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 === strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        // Lets check for PPPosts.
        $levels = (array) $levels;
        foreach ($levels as $key => $level) {
            if (false !== strrpos($level, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        $contact_api = FluentCrmApi('contacts');

        foreach ($levels as $level) {
            $settings    = isset($this->settings['level'][ $level ]['rereg']) ? $this->settings['level'][ $level ]['rereg'] : [];
            $apply_tag   = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
            $remove_tag  = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];
            $apply_list  = isset($settings['apply_list']) ? $settings['apply_list'] : [];
            $remove_list = isset($settings['remove_list']) ? $settings['remove_list'] : [];

            $contact = $contact_api->getContact($user->user_email);
            if (! $contact) {
                $data = [
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->user_email, // required.
                    'tags'       => $apply_tag, // tag ids as an array.
                    'lists'      => $apply_list, // list ids as an array.
                ];
                $this->create_fluentcrm_contact($data);
            } else {
                $contact->detachTags($remove_tag);
                $contact->detachLists($remove_list);
                $contact->attachTags($apply_tag);
                $contact->attachLists($apply_list);
            }
        }
    }

    /**
     * Fired when a user is removed from levels
     *
     * @param integer  $uid           User ID.
     * @param string[] $removedlevels Array of level IDs.
     */
    public function RemoveUserTagsHook($uid, $removedlevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 === strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        // Lets check for PPPosts.
        $levels = (array) $removedlevels;
        foreach ($levels as $key => $level) {
            if (false !== strrpos($level, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        $contact_api = FluentCrmApi('contacts');
        foreach ($levels as $level) {
            $settings    = isset($this->settings['level'][ $level ]['remove']) ? $this->settings['level'][ $level ]['remove'] : [];
            $apply_tag   = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
            $remove_tag  = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];
            $apply_list  = isset($settings['apply_list']) ? $settings['apply_list'] : [];
            $remove_list = isset($settings['remove_list']) ? $settings['remove_list'] : [];

            $contact = $contact_api->getContact($user->user_email);
            if (! $contact) {
                $data = [
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->user_email, // required.
                    'tags'       => $apply_tag, // tag ids as an array.
                    'lists'      => $apply_list, // list ids as an array.
                ];
                $this->create_fluentcrm_contact($data);
            } else {
                $contact->detachTags($remove_tag);
                $contact->detachLists($remove_list);
                $contact->attachTags($apply_tag);
                $contact->attachLists($apply_list);
            }
        }
    }

    /**
     * Fired when a user is cancelled from levels
     *
     * @param integer  $uid          User ID.
     * @param string[] $cancellevels Array of level IDs.
     */
    public function CancelUserTagsHook($uid, $cancellevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 === strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        // Lets check for PPPosts.
        $levels = (array) $cancellevels;
        foreach ($levels as $key => $level) {
            if (false !== strrpos($level, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        $contact_api = FluentCrmApi('contacts');
        foreach ($levels as $level) {
            $settings    = isset($this->settings['level'][ $level ]['cancel']) ? $this->settings['level'][ $level ]['cancel'] : [];
            $apply_tag   = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
            $remove_tag  = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];
            $apply_list  = isset($settings['apply_list']) ? $settings['apply_list'] : [];
            $remove_list = isset($settings['remove_list']) ? $settings['remove_list'] : [];

            $contact = $contact_api->getContact($user->user_email);
            if (! $contact) {
                $data = [
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->user_email, // required.
                    'tags'       => $apply_tag, // tag ids as an array.
                    'lists'      => $apply_list, // list ids as an array.
                ];
                $this->create_fluentcrm_contact($data);
            } else {
                $contact->detachTags($remove_tag);
                $contact->detachLists($remove_list);
                $contact->attachTags($apply_tag);
                $contact->attachLists($apply_list);
            }
        }
    }
}
