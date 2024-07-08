<?php

namespace WishListMember\Autoresponders;

if (! class_exists('\WLM_Sendlane')) {
    require_once wishlistmember_instance()->plugin_dir . '/extlib/wlm-sendlane.php';
}

class SendLane
{
    public static function __callStatic($name, $args)
    {
        $interface = self::_interface();
        if ($interface->api()) {
            call_user_func_array([$interface, $name], $args);
        }
    }

    public static function _interface()
    {
        static $interface;
        if (! $interface) {
            $interface = new SendLane_Interface();
        }
        return $interface;
    }
}

class SendLane_Interface
{
    private $settings  = '';
    private $api_key   = null;
    private $api_hash  = null;
    private $subdomain = null;

    public function __construct()
    {
        $this->sendlane_api = false;
        // Make sure that WLM active and infusiosnsoft connection is set.
        if (class_exists('\WLM_Sendlane')) {
            $this->settings = wlm_or(( new \WishListMember\Autoresponder('sendlane') )->settings, false);
            // Initilize sendlane api connection.
            if ($this->settings && ! empty($this->settings['api_key']) && ! empty($this->settings['api_hash']) && ! empty($this->settings['subdomain'])) {
                $this->sendlane_api = new \WLM_Sendlane($this->settings['api_key'], $this->settings['api_hash'], $this->settings['subdomain']);
                $lists_ret          = $this->sendlane_api->post('lists');
                if (isset($lists_ret['error'])) {
                    $this->sendlane_api = false;
                }
            }
        }
    }

    public function api()
    {
        return $this->sendlane_api;
    }

    public function processTags($levels, $action, $data)
    {
        if (! $this->sendlane_api) {
            return [
                'errstr' => 'Unable to process tags. No API Connection.',
                'errno'  => 1,
            ];
        }
        $levels = (array) $levels;
        if (count($levels) <= 0) {
            return [
                'errstr' => 'No Levels Found',
                'errno'  => 1,
            ];// no levels, no need to continue
        }
        if (! isset($data['user_email']) || empty($data['user_email'])) {
            return [
                'errstr' => 'Email address not found',
                'errno'  => 1,
            ];
        }
        if (! in_array($action, ['add', 'cancel', 'rereg', 'remove'])) {
            return [
                'errstr' => 'Invalid action',
                'errno'  => 1,
            ];
        }

        $errors = [];
        // Add the tags for each level.
        foreach ((array) $levels as $level) {
            $error = [];

            $apply_tags = isset($this->settings[ $level ][ $action ]['apply_tag']) ? $this->settings[ $level ][ $action ]['apply_tag'] : false;
            $apply_tags = ! empty($apply_tags) ? $apply_tags : false;
            $remove_tag = isset($this->settings[ $level ][ $action ]['remove_tag']) ? $this->settings[ $level ][ $action ]['remove_tag'] : false;
            $remove_tag = ! empty($remove_tag) ? $remove_tag : false;

            $list_add    = isset($this->settings[ $level ][ $action ]['list_add']) ? $this->settings[ $level ][ $action ]['list_add'] : false;
            $list_add    = ! empty($list_add) ? $list_add : false;
            $list_remove = isset($this->settings[ $level ][ $action ]['list_remove']) ? $this->settings[ $level ][ $action ]['list_remove'] : false;
            $list_remove = ! empty($list_remove) ? $list_remove : false;

            if ($list_add) {
                $param = [
                    'email'      => $data['user_email'],
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'list_id'    => $list_add,
                ];
                $ret   = $this->sendlane_api->post('list-subscriber-add', $param);
                if (isset($ret['error'])) {
                    $error['list_add'] = $ret['error']['messages'];
                }
            }

            if ($list_remove) {
                $param = [
                    'email'   => $data['user_email'],
                    'list_id' => $list_remove,
                ];
                $ret   = $this->sendlane_api->post('subscribers-delete', $param);
                // 406 means the email does not exists on the list.
                if (isset($ret['error']) && ! isset($ret['error'][406])) {
                    $error['list_remove'] = $ret['error']['messages'];
                }
            }

            if (! $apply_tags && ! $remove_tag) {
                continue; // Skip the rest of the loop.
            }

            // Now we can add or remove tags to record.
            if ($apply_tags) {
                $param = [
                    'email'   => $data['user_email'],
                    'tag_ids' => implode(',', $apply_tags),
                ];
                $ret   = $this->sendlane_api->post('tag-subscriber-add', $param);
                if (isset($ret['error'])) {
                    $error['apply_tag'] = $ret['error']['messages'];
                }
            }

            if ($remove_tag) {
                $param = [
                    'email'   => $data['user_email'],
                    'tag_ids' => implode(',', $remove_tag),
                ];
                $ret   = $this->sendlane_api->post('tag-subscriber-remove', $param);
                if (isset($ret['error'])) {
                    $error['remove_tag'] = $ret['error']['messages'];
                }
            }

            if (count($error)) {
                $errors[ $level ] = $error;
            }
        }
        return count($errors) ? wlm_maybe_serialize($errors) : true; // Success.
    }

    public function AddQueue($data, $process = true)
    {
        $WishlistAPIQueueInstance = new \WishListMember\API_Queue();
        $qname                    = 'sendlanear' . time();
        $data                     = wlm_maybe_serialize($data);
        $WishlistAPIQueueInstance->add_queue($qname, $data, 'For Queueing');
        if ($process) {
            $this->ProcessQueue();
        }
    }

    public function ProcessQueue($recnum = 10, $tries = 3)
    {
        if (! $this->sendlane_api) {
            return;
        }
        $WishlistAPIQueueInstance = new \WishListMember\API_Queue();
        $last_process             = get_option('WLM_AUTORESPONDER_SENDLANE_LastProcess');
        $current_time             = time();
        $tries                    = $tries > 1 ? (int) $tries : 3;
        $error                    = false;
        // Lets process every 10 seconds.
        if (! $last_process || ( $current_time - $last_process ) > 10) {
            $queues = $WishlistAPIQueueInstance->get_queue('sendlanear', $recnum, $tries, 'tries,name');
            foreach ($queues as $queue) {
                $data = wlm_maybe_unserialize($queue->value);
                if ('new' === $data['action']) {
                    $res = $this->NewUserTagsHook($data['uid'], $data['levels'], $data['data']);
                } elseif ('add' === $data['action']) {
                    $res = $this->AddUserTagsHook($data['uid'], $data['levels'], $data['data']);
                } elseif ('remove' === $data['action']) {
                    $res = $this->RemoveUserTagsHook($data['uid'], $data['levels'], $data['data']);
                } elseif ('cancel' === $data['action']) {
                    $res = $this->CancelUserTagsHook($data['uid'], $data['levels'], $data['data']);
                } elseif ('rereg' === $data['action']) {
                    $res = $this->ReregUserTagsHook($data['uid'], $data['levels'], $data['data']);
                } elseif ('delete' === $data['action']) {
                    $res = $this->DeleteUserTagsHook($data['uid'], $data['levels'], $data['data']);
                }

                if (true !== $res) {
                    $d = [
                        'notes' => $res,
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
                    update_option('WLM_AUTORESPONDER_SENDLANE_LastProcess', $current_time);
                } else {
                    add_option('WLM_AUTORESPONDER_SENDLANE_LastProcess', $current_time);
                }
            }
        }
    }

    // FOR NEW USERS.
    public function NewUserTagsHookQueue($uid = null, $udata = null)
    {
        // Part of the Fix for issue where Add To levels aren't being processed.
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        // Don't add the data into the queue if it's from a temp account.
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $udata['first_name'] = $user->first_name;
        $udata['last_name']  = $user->last_name;
        $udata['user_email'] = $user->user_email;
        $udata['username']   = $user->user_login;
        $data                = [
            'uid'    => $uid,
            'action' => 'new',
            'levels' => (array) $udata['wpm_id'],
            'data'   => $udata,
        ];
        $this->AddQueue($data);
    }

    public function NewUserTagsHook($uid, $levels, $data)
    {
        $tempacct = 'temp_' . md5($data['orig_email']) == $data['email'];
        if ($tempacct) {
            return; // If temp account used by sc, do not process.
        }
        return $this->processTags($levels, 'add', $data);
    }

    // WHEN ADDED TO LEVELS.
    public function AddUserTagsHookQueue($uid, $addlevels = '')
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }

        $udata               = [];
        $udata['first_name'] = $user->first_name;
        $udata['last_name']  = $user->last_name;
        $udata['user_email'] = $user->user_email;
        $udata['username']   = $user->user_login;
        $data                = [
            'uid'    => $uid,
            'action' => 'add',
            'levels' => $addlevels,
            'data'   => $udata,
        ];
        // Fix for issue where Add To levels aren't being processed.
        // If the data is from a temp account then add it to the queue API and don't process it for now.
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            $this->AddQueue($data, 0);
        } elseif (isset(wlm_post_data()['SendMail'])) {
            // This elseif condition fixes the issue where members who are added via.
            // WLM API aren't being processed by the Integration.
            $this->AddQueue($data, 0);
        } else {
            $this->AddQueue($data);
        }
    }

    public function AddUserTagsHook($uid, $levels, $data)
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        // Make sure that info are updated.
        $data['first_name'] = $user->first_name;
        $data['last_name']  = $user->last_name;
        $data['user_email'] = $user->user_email;
        $data['username']   = $user->user_login;
        $levels             = (array) $levels;
        return $this->processTags($levels, 'add', $data);
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
            'uid'    => $uid,
            'action' => 'remove',
            'levels' => $levels,
            'data'   => [],
        ];
        $this->AddQueue($data);
    }

    public function RemoveUserTagsHook($uid, $levels, $data)
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $data['first_name'] = $user->first_name;
        $data['last_name']  = $user->last_name;
        $data['user_email'] = $user->user_email;
        $data['username']   = $user->user_login;
        $levels             = (array) $levels;
        return $this->processTags($levels, 'remove', $data);
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
            'uid'    => $uid,
            'action' => 'cancel',
            'levels' => $levels,
            'data'   => [],
        ];
        $this->AddQueue($data);
    }

    public function CancelUserTagsHook($uid, $levels, $data)
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $data['first_name'] = $user->first_name;
        $data['last_name']  = $user->last_name;
        $data['user_email'] = $user->user_email;
        $data['username']   = $user->user_login;
        $levels             = (array) $levels;
        return $this->processTags($levels, 'cancel', $data);
    }

    // WHEN REREGISTERED FROM LEVELS.
    public function ReregUserTagsHookQueue($uid, $levels = '')
    {
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

        $data = [
            'uid'    => $uid,
            'action' => 'rereg',
            'levels' => $levels,
            'data'   => [],
        ];
        $this->AddQueue($data);
    }

    public function ReregUserTagsHook($uid, $levels, $data)
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $data['first_name'] = $user->first_name;
        $data['last_name']  = $user->last_name;
        $data['user_email'] = $user->user_email;
        $data['username']   = $user->user_login;
        $levels             = (array) $levels;
        return $this->processTags($levels, 'rereg', $data);
    }

    // WHEN DELETED FROM LEVELS.
    public function DeleteUserHookQueue($uid)
    {
        if (! $this->sendlane_api) {
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

        $user = get_userdata($uid);
        if (! $user) {
            return;
        }

        $udata               = [];
        $udata['first_name'] = $user->first_name;
        $udata['last_name']  = $user->last_name;
        $udata['user_email'] = $user->user_email;
        $udata['username']   = $user->user_login;
        $data                = [
            'uid'    => $uid,
            'action' => 'delete',
            'levels' => $levels,
            'data'   => $udata,
        ];
        $this->AddQueue($data);
        return;
    }

    public function DeleteUserTagsHook($uid, $levels, $data)
    {
        $user = get_userdata($uid);
        if (! $user) {
            return;
        }
        if (false !== strpos($user->user_email, 'temp_') && 37 == strlen($user->user_email) && false === strpos($user->user_email, '@')) {
            return;
        }

        $levels = (array) $levels;
        return $this->processTags($levels, 'remove', $data);
    }
}
