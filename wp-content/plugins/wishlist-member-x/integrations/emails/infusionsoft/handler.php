<?php

namespace WishListMember\Autoresponders;

if (! class_exists('\WLM_Infusionsoft')) {
    include_once wishlistmember_instance()->plugin_dir . '/extlib/wlm-infusionsoft.php';
}

class Infusionsoft
{
    public static function subscribe($email, $level_id)
    {
        self::process($email, $level_id);
    }

    public static function unsubscribe($email, $level_id)
    {
        self::process($email, $level_id, true);
    }

    public static function process($email, $level_id, $unsub = false)
    {
        $ar    = ( new \WishListMember\Autoresponder('infusionsoft') )->settings;
        $ifsdk = false;
        // Make sure WishListMemberInstance WLM active and infusiosnsoft connection is set.
        if (class_exists('\WLM_Infusionsoft')) {
            $machine_name = wishlistmember_instance()->get_option('auto_ismachine');
            $api_key      = wishlistmember_instance()->get_option('auto_isapikey');
            $log          = wishlistmember_instance()->get_option('auto_isenable_log');
            $machine_name = $machine_name ? $machine_name : '';
            $api_key      = $api_key ? $api_key : '';

            $apilogfile = false;
            if ($log) {
                $date_now   = wlm_date('m-d-Y');
                $apilogfile = wishlistmember_instance()->plugin_dir . "/ifs_logs_{$date_now}.csv";
            }

            if ($api_key && $machine_name) {
                $ifsdk = new \WLM_Infusionsoft($machine_name, $api_key, $apilogfile);
            }
        }

        if (! $ifsdk) {
            return;
        }

        $campid  = $ar['isCID'][ $level_id ]; // Get the campaign ID of the Membership Level.
        $isUnsub = ( 1 == $ar['isUnsub'][ $level_id ] ? true : false ); // Check if we will unsubscribe or not.

        if ($campid) {
            list( $fName, $lName ) = explode(' ', wishlistmember_instance()->ar_sender['name'], 2); // Split the name into First and Last Name.
            $email                 = wishlistmember_instance()->ar_sender['email'];

            $contactid = $ifsdk->get_contactid_by_email($email);

            if ($unsub) { // If the Unsubscribe.
                // If email is found, remove it from campaign and if it will be unsubscribe once remove from level.
                if ($contactid && $isUnsub) {
                    $res = $ifsdk->remove_followup_sequence($contactid, $campid);
                }
            } else { // Else Subscribe.
                // If email is existing, assign it to the campaign.
                if ($contactid) {
                    // Optin email first.
                    $ifsdk->optin_contact_email($email);
                    $res = $ifsdk->assign_followup_sequence($contactid, $campid);
                } else {
                    // If email is new, assign it to the add it to the database.
                    $carray    = [
                        'Email'     => $email,
                        'FirstName' => $fName,
                        'LastName'  => $lName,
                    ];
                    $contactid = $ifsdk->create_contact($carray);
                    // If successfully addded, opt-in the contact.
                    if ($contactid) {
                        $ifsdk->optin_contact_email($email);
                        $res = $ifsdk->assign_followup_sequence($contactid, $campid);
                    }
                }
            }
        }
    }

    public static function __callStatic($name, $args)
    {
        static $ifs;

        if (! $ifs) {
            $ifs = new Infusionsoft_Tags_Handler();
        }

        // If the method exists then call it.
        if ($ifs->ifsdk && method_exists($ifs, $name)) {
            return call_user_func_array([$ifs, $name], $args);
        }
    }
}


class Infusionsoft_Tags_Handler
{
    private $machine_name = '';
    private $api_key      = '';
    public $ifsdk         = null;
    private $log          = false;

    public function __construct()
    {
        // Make sure that WLM active and infusiosnsoft connection is set.
        if (class_exists('\WLM_Infusionsoft')) {
            $this->machine_name = wishlistmember_instance()->get_option('auto_ismachine');
            $this->api_key      = wishlistmember_instance()->get_option('auto_isapikey');
            $this->log          = wishlistmember_instance()->get_option('auto_isenable_log');
            $this->machine_name = $this->machine_name ? $this->machine_name : '';
            $this->api_key      = $this->api_key ? $this->api_key : '';

            $apilogfile = false;
            if ($this->log) {
                $date_now   = wlm_date('m-d-Y');
                $apilogfile = wishlistmember_instance()->plugin_dir . "/ifs_logs_{$date_now}.csv";
            }

            if ($this->api_key && $this->machine_name) {
                $this->ifsdk = new \WLM_Infusionsoft($this->machine_name, $this->api_key, $apilogfile);
            }
        }
    }

    public function add_ifs_field($custom_fields, $userid)
    {
        if (! current_user_can('manage_options')) {
            return $custom_fields;
        }

        $contactid = wishlistmember_instance()->Get_UserMeta($userid, 'wlminfusionsoft_contactid');
        if (! $contactid) {
            $contactid = get_user_meta($userid, 'wlifcon_contactid', true); // WLMIS contactid.
        }
        $custom_fields['wlminfusionsoft_contactid'] = [
            'type'       => 'text', // hidden, select, textarea, checkbox, etc
            'label'      => 'Infusionsoft Contact ID',
            // 'description' => 'Description',
            'attributes' => [
                'type'  => 'text', // hidden, select, textarea, checkbox, etc
                'name'  => 'wlminfusionsoft_contactid', // same as index above
                // 'other attributes' => 'value',
                'value' => $contactid,
                // More attributes if needed.
            ],
        ];
        return $custom_fields;
    }

    public function save_ifs_field($data)
    {
        if (! current_user_can('manage_options')) {
            return;
        }
        if (! isset($data['userid'])) {
            return;
        }

        $user_custom_fields = isset($data['customfields']) ? $data['customfields'] : [];
        if (! isset($user_custom_fields['wlminfusionsoft_contactid'])) {
            return;
        }
        $wlminfusionsoft_contactid = $user_custom_fields['wlminfusionsoft_contactid'] ? (int) wlm_trim($user_custom_fields['wlminfusionsoft_contactid']) : '';

        wishlistmember_instance()->Update_UserMeta($data['userid'], 'wlminfusionsoft_contactid', $wlminfusionsoft_contactid);
    }

    public function ProfileForm($user)
    {
        global $pagenow;
        if (! current_user_can('manage_options')) {
            return;
        }
        $user_id = $user;
        if (is_object($user)) {
            $user_id = $user->ID;
        }
        if ('profile.php' !== $pagenow && 'user-edit.php' !== $pagenow) {
            return;
        }

        $contactid = wishlistmember_instance()->Get_UserMeta($user_id, 'wlminfusionsoft_contactid');
        if (! $contactid) {
            $contactid = get_user_meta($user_id, 'wlifcon_contactid', true); // WLMIS contactid.
        }
        echo '<h3>Infusionsoft Info</h3>';
        echo '<table class="form-table">';
        echo '<tbody>';
        echo '<tr>';
        echo '<th><label for="wlminfusionsoft_contactid">Infusionsoft Contact ID</label></th>';
        echo '<td>';
        echo '<input type="text" name="wlminfusionsoft_contactid" id="wlminfusionsoft_contactid" value="' . esc_attr($contactid) . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
    }

    public function UpdateProfile($user)
    {
        if (! current_user_can('manage_options')) {
            return;
        }
        $user_id = $user;
        if (is_object($user)) {
            $user_id = $user->ID;
        }

        if (isset(wlm_post_data()['wlminfusionsoft_contactid'])) {
            wishlistmember_instance()->Update_UserMeta($user_id, 'wlminfusionsoft_contactid', (int) trim(wlm_post_data()['wlminfusionsoft_contactid']));
        }
    }

    public function generateContactId($uid)
    {
        if (! $this->ifsdk || ! $this->ifsdk->is_api_connected()) {
            return null;
        }

        $contactid = get_user_meta($uid, 'wlifcon_contactid', true); // WLMIS contactid.

        if (! $contactid) {
            $user_info = get_userdata($uid);
            if (! $user_info) {
                return null;
            }

            $email = $user_info->user_email;
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $contactid = $this->ifsdk->get_contactid_by_email($email);
                if (! $contactid) {
                    $user      = [
                        'Email'     => $email,
                        'FirstName' => $user_info->user_firstname,
                        'LastName'  => $user_info->user_lastname,
                    ];
                    $contactid = $this->ifsdk->create_contact($user, 'Added Via WLM INF AR Integration API.');
                }
            } else {
                return null;
            }
        }

        if ($contactid) {
            wishlistmember_instance()->Update_UserMeta($uid, 'wlminfusionsoft_contactid', $contactid);
        } else {
            $contactid = false; // Make sure that contactid is false.
        }

        return $contactid;
    }

    public function processTags($levels, $action, $contactid = null, $uid = null)
    {
        if (! $this->ifsdk || ! $this->ifsdk->is_api_connected()) {
            return [
                'errstr' => 'Unable to process tags. No API Connection.',
                'errno'  => 1,
            ];
        }

                    $ar = wishlistmember_instance()->get_option('Autoresponders');
        $this->settings = isset($ar['infusionsoft']) ? $ar['infusionsoft'] : false;
        $levels         = (array) $levels;
        if (count($levels) <= 0) {
            return [
                'errstr' => 'No Levels Found',
                'errno'  => 1,
            ];// no levels, no need to continue
        }

        if (! $contactid) {
            $contactid = wishlistmember_instance()->Get_UserMeta($uid, 'wlminfusionsoft_contactid');
            // Get the contactid if not set.
            if (! $contactid) {
                $contactid = $this->generateContactId($uid);
                if (null === $contactid) {
                     return [
                         'errstr' => 'Theres a problem with userid, email or api connection.',
                         'errno'  => 1,
                     ];
                }
            }
        }

        if ($contactid) {
            if ('new' === $action || 'add' === $action) {
                $istags_add_app = $this->settings['istags_add_app'];
                $istags_add_rem = $this->settings['istags_add_rem'];
            } elseif ('remove' === $action) {
                $istags_add_app = $this->settings['istags_remove_app'];
                $istags_add_rem = $this->settings['istags_remove_rem'];
            } elseif ('cancel' === $action) {
                $istags_add_app = $this->settings['istags_cancelled_app'];
                $istags_add_rem = $this->settings['istags_cancelled_rem'];
            } elseif ('uncancel' === $action) {
                $istags_add_app = $this->settings['istags_uncancelled_app'];
                $istags_add_rem = $this->settings['istags_uncancelled_rem'];
            } elseif ('expire' === $action) {
                $istags_add_app = $this->settings['istags_expired_app'];
                $istags_add_rem = $this->settings['istags_expired_rem'];
            } elseif ('unexpire' === $action) {
                $istags_add_app = $this->settings['istags_unexpired_app'];
                $istags_add_rem = $this->settings['istags_unexpired_rem'];
            } elseif ('delete' === $action) {
                $istags_add_app = $this->settings['istags_remove_app'];
                $istags_add_rem = $this->settings['istags_remove_rem'];
            }

            if ($istags_add_app) {
                $istags_add_app = wlm_maybe_unserialize($istags_add_app);
            } else {
                $istags_add_app = [];
            }

            if ($istags_add_rem) {
                $istags_add_rem = wlm_maybe_unserialize($istags_add_rem);
            } else {
                $istags_add_rem = [];
            }

            // Add the tags for each level.
            foreach ((array) $levels as $level) {
                // Add the contact to a tag/group.
                if (isset($istags_add_app[ $level ])) {
                    foreach ($istags_add_app[ $level ] as $k => $val) {
                        $ret = $this->ifsdk->tag_contact($contactid, $val);
                        if (isset($ret['errno'])) {
                            return $ret;
                        }
                    }
                }

                // Remove the contact from tag/group.
                if (isset($istags_add_rem[ $level ])) {
                    foreach ($istags_add_rem[ $level ] as $k => $val) {
                        $ret = $this->ifsdk->untag_contact($contactid, $val);
                        if (isset($ret['errno'])) {
                            return $ret;
                        }
                    }
                }
            }
        } else {
            return [
                'errstr' => 'No Contact ID',
                'errno'  => 1,
            ];
        }

        return true; // Success.
    }

    public function ifarAddQueue($data, $process = true)
    {
        $WishlistAPIQueueInstance = new \WishListMember\API_Queue();
        $qname                    = 'infusionsoftar_' . time();
        $data                     = wlm_maybe_serialize($data);
        $WishlistAPIQueueInstance->add_queue($qname, $data, 'For Queueing');
        if ($process) {
            $this->ifarProcessQueue();
        }
    }

    public function ifarProcessQueue($recnum = 10, $tries = 5)
    {
        if (! $this->ifsdk || ! $this->ifsdk->is_api_connected()) {
            return;
        }
        $WishlistAPIQueueInstance = new \WishListMember\API_Queue();
        $last_process             = get_option('WLM_InfusionsoftARAPI_LastProcess');
        $current_time             = time();
        $tries                    = $tries > 1 ? (int) $tries : 5;
        $error                    = false;
        // Lets process every 10 seconds.
        if (! $last_process || ( $current_time - $last_process ) > 10) {
            $queues = $WishlistAPIQueueInstance->get_queue('infusionsoftar', $recnum, $tries, 'tries,name');
            foreach ($queues as $queue) {
                $data = wlm_maybe_unserialize($queue->value);
                if ('new' === $data['action']) {
                    $res = $this->NewUserTagsHook($data['uid'], $data['data']);
                } elseif ('add' === $data['action']) {
                    $res = $this->AddUserTagsHook($data['uid'], $data['addlevels']);
                } elseif ('remove' === $data['action']) {
                    $res = $this->RemoveUserTagsHook($data['uid'], $data['removedlevels']);
                } elseif ('cancel' === $data['action']) {
                    $res = $this->CancelUserTagsHook($data['uid'], $data['cancellevels']);
                } elseif ('uncancel' === $data['action']) {
                    $res = $this->UnCancelUserTagsHook($data['uid'], $data['uncancellevels']);
                } elseif ('expire' === $data['action']) {
                    $res = $this->ExpireUserTagsHook($data['uid'], $data['expirelevels']);
                } elseif ('unexpire' === $data['action']) {
                    $res = $this->UnexpireUserTagsHook($data['uid'], $data['unexpirelevels']);
                } elseif ('delete' === $data['action']) {
                    $res = $this->DeleteUserTagsHook($data['contactid'], $data['levels']);
                }

                if (isset($res['errstr'])) {
                    $res['error'] = strip_tags($res['errstr']);
                    $res['error'] = str_replace(["\n", "\t", "\r"], '', $res['error']);
                    $d            = [
                        'notes' => "{$res['errno']}:{$res['error']}",
                        'tries' => $queue->tries + 1,
                    ];
                    $WishlistAPIQueueInstance->update_queue($queue->ID, $d);
                    $error = true;
                } else {
                    $WishlistAPIQueueInstance->delete_queue($queue->ID);
                    $error = false;
                }
            }
            // Save the last processing time.
            if ($error) {
                $current_time = time();
                if ($last_process) {
                    update_option('WLM_InfusionsoftARAPI_LastProcess', $current_time);
                } else {
                    add_option('WLM_InfusionsoftARAPI_LastProcess', $current_time);
                }
            }
        }
    }

    public function ConfirmApproveLevelsTagsHook($uid = null, $level = null)
    {

        $user = get_userdata($uid);

        $udata = [
            'username'  => $user->user_login,
            'firstname' => $user->user_firstname,
            'lastname'  => $user->user_lastname,
            'email'     => $user->user_email,
            'wpm_id'    => $level[0],
        ];

        $level_unconfirmed = false;
        if (wishlistmember_instance()->level_unconfirmed($level[0], $uid)) {
            $level_unconfirmed = true;
        }

        $level_for_approval = false;
        if (wishlistmember_instance()->level_for_approval($level[0], $uid)) {
            $level_for_approval = true;
        }

        $data = [
            'uid'    => $uid,
            'action' => 'new',
            'data'   => $udata,
        ];

        if (! $level_unconfirmed && ! $level_for_approval) {
            $this->ifarAddQueue($data);
        }
    }

    // FOR NEW USERS.
    public function NewUserTagsHookQueue($uid = null, $udata = null)
    {

        $level_unconfirmed = false;
        if (wishlistmember_instance()->level_unconfirmed($udata['wpm_id'], $uid)) {
            $level_unconfirmed = true;
        }

        $level_for_approval = false;
        if (wishlistmember_instance()->level_for_approval($udata['wpm_id'], $uid)) {
            $level_for_approval = true;
        }

        $data = [
            'uid'    => $uid,
            'action' => 'new',
            'data'   => $udata,
        ];

        // Part of the Fix for issue where Add To levels aren't being processed.
        $user = get_userdata($uid);
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            // Don't add the data into the queue if it's from a temp account.
            null;
        } elseif ($level_unconfirmed || $level_for_approval) {
            // Don't add the data into the queue if the level's status is not active.
            null;
        } else {
            $this->ifarAddQueue($data);
        }
    }

    public function NewUserTagsHook($uid = null, $data = null)
    {
        $tempacct = 'temp_' . md5($data['orig_email']) == $data['email'];
        if ($tempacct) {
            return; // If temp account used by sc, do not process.
        }
        $levels = (array) $data['wpm_id'];

        return $this->processTags($levels, 'new', null, $uid);
    }

    // WHEN ADDED TO LEVELS.
    public function AddUserTagsHookQueue($uid, $addlevels = '')
    {

        $level_unconfirmed = false;
        if (wishlistmember_instance()->level_unconfirmed($addlevels[0], $uid)) {
            $level_unconfirmed = true;
        }

        $level_for_approval = false;
        if (wishlistmember_instance()->level_for_approval($addlevels[0], $uid)) {
            $level_for_approval = true;
        }

        $data = [
            'uid'       => $uid,
            'action'    => 'add',
            'addlevels' => $addlevels,
        ];

        // If from registration then don't don't process if the $addlevels is.
        // The same level the user registered to. This is already processed by NewUserTagsQueue func.
        if (isset(wlm_post_data()['action']) && 'wpm_register' === wlm_post_data()['action']) {
            if (wlm_post_data()['wpm_id'] == $addlevels[0]) {
                return;
            }
        }

        // Fix for issue where Add To levels aren't being processed.
        // If the data is from a temp account then add it to the queue API and don't process it for now.
        $user = get_userdata($uid);
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            $this->ifarAddQueue($data, 0);
        } elseif (isset(wlm_post_data()['SendMail'])) {
            // This elseif condition fixes the issue where members who are added via.
            // WLM API aren't being processed by the Infusionsoft Autoresponder Integration.
            $this->ifarAddQueue($data, 0);
        } elseif ($level_unconfirmed || $level_for_approval) {
            // Don't add the data into the queue if the level's status is not active.
            null;
        } else {
            $this->ifarAddQueue($data);
        }
    }

    public function AddUserTagsHook($uid, $newlevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return [
                'errstr' => 'Invalid User ID.',
                'errno'  => 1,
            ];
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $levels = (array) $newlevels;
        return $this->processTags($levels, 'add', null, $uid);
    }

    // WHEN REMOVED FROM LEVELS.
    public function RemoveUserTagsHookQueue($uid, $removedlevels = '')
    {
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

        $data = [
            'uid'           => $uid,
            'action'        => 'remove',
            'removedlevels' => $removedlevels,
        ];
        $this->ifarAddQueue($data);
    }

    public function RemoveUserTagsHook($uid, $removedlevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return [
                'errstr' => 'Invalid User ID.',
                'errno'  => 1,
            ];
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $levels = (array) $removedlevels;
        return $this->processTags($levels, 'remove', null, $uid);
    }

    // WHEN CANCELLED FROM LEVELS.
    public function CancelUserTagsHookQueue($uid, $cancellevels = '')
    {
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

        $data = [
            'uid'          => $uid,
            'action'       => 'cancel',
            'cancellevels' => $levels,
        ];
        $this->ifarAddQueue($data);
    }

    public function CancelUserTagsHook($uid, $removedlevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return [
                'errstr' => 'Invalid User ID.',
                'errno'  => 1,
            ];
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $levels = (array) $removedlevels;
        return $this->processTags($levels, 'cancel', null, $uid);
    }

    // WHEN UNCANCELLED FROM LEVELS.
    public function UnCancelUserTagsHookQueue($uid, $uncancellevels = '')
    {
        // Lets check for PPPosts.
        $levels = (array) $uncancellevels;
        foreach ($levels as $key => $level) {
            if (false !== strrpos($level, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        $data = [
            'uid'            => $uid,
            'action'         => 'uncancel',
            'uncancellevels' => $levels,
        ];
        $this->ifarAddQueue($data);
    }

    public function UnCancelUserTagsHook($uid, $uncancellevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return [
                'errstr' => 'Invalid User ID.',
                'errno'  => 1,
            ];
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $levels = (array) $uncancellevels;
        return $this->processTags($levels, 'uncancel', null, $uid);
    }

    // WHEN EXPIRED FROM LEVELS.
    public function ExpireUserTagsHookQueue($uid, $expirelevels = '')
    {
        // Lets check for PPPosts.
        $levels = (array) $expirelevels;
        foreach ($levels as $key => $level) {
            if (false !== strrpos($level, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        $data = [
            'uid'            => $uid,
            'action'         => 'expire',
            'expirelevels' => $levels,
        ];
        $this->ifarAddQueue($data);
    }

    public function ExpireUserTagsHook($uid, $expirelevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return [
                'errstr' => 'Invalid User ID.',
                'errno'  => 1,
            ];
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $levels = (array) $expirelevels;
        return $this->processTags($levels, 'expire', null, $uid);
    }

    // WHEN UNEXPIRED FROM LEVELS.
    public function UnexpireUserTagsHookQueue($uid, $unexpirelevels = '')
    {
        // Lets check for PPPosts.
        $levels = (array) $unexpirelevels;
        foreach ($levels as $key => $level) {
            if (false !== strrpos($level, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        $data = [
            'uid'            => $uid,
            'action'         => 'unexpire',
            'unexpirelevels' => $levels,
        ];
        $this->ifarAddQueue($data);
    }

    public function UnexpireUserTagsHook($uid, $unexpirelevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return [
                'errstr' => 'Invalid User ID.',
                'errno'  => 1,
            ];
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $levels = (array) $unexpirelevels;
        return $this->processTags($levels, 'unexpire', null, $uid);
    }

    // WHEN DELETED FROM LEVELS.
    public function DeleteUserHookQueue($uid)
    {
        if (! $this->ifsdk || ! $this->ifsdk->is_api_connected()) {
            return;
        }

        $levels = wishlistmember_instance()->get_membership_levels($uid);
        foreach ($levels as $key => $lvl) {
            if (false !== strpos($lvl, 'U-')) {
                unset($levels[ $key ]);
            }
        }
        if (! is_array($levels) || count($levels) <= 0) {
            return; // Lets return if no level was found.
        }

        $contactid = wishlistmember_instance()->Get_UserMeta($uid, 'wlminfusionsoft_contactid');

        if (! $contactid) { // If no contactid.
            $user_info = get_userdata($uid);
            if (! $user_info) {
                return; // Invalid user.
            }
            $email = $user_info->user_email;

            if (! $contactid) {
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $contactid = $this->ifsdk->get_contactid_by_email($email);
                    // Since we are deleting the user, we wont be adding it on IFS also.
                }
            }
            if (! $contactid) {
                $contactid = get_user_meta($uid, 'wlifcon_contactid', true); // WLMIS contactid.
            }
        }
        if (! $contactid) {
            return; // Lets return if no level was found.
        }

        $data = [
            'uid'       => $uid,
            'contactid' => $contactid,
            'action'    => 'delete',
            'levels'    => $levels,
        ];

        $this->ifarAddQueue($data);

        return;
    }

    public function DeleteUserTagsHook($contactid, $levels = [])
    {
        $levels = (array) $levels;
        return $this->processTags($levels, 'remove', $contactid, null);
    }
}
