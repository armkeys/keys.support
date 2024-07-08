<?php

namespace WishListMember\Autoresponders;

if (! class_exists('\mcsdk')) {
    include_once wishlistmember_instance()->plugin_dir . '/extlib/mailchimp/mcsdk.php';
}


class MailChimp
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
        $ar = ( new \WishListMember\Autoresponder('mailchimp') )->settings;

        $listID = $ar['mcID'][ $level_id ]; // Get the list ID of the Membership Level.
        $mcAPI  = $ar['mcapi']; // Get the MailChimp API.

        $WishlistAPIQueueInstance = new \WishListMember\API_Queue();

        if ($listID) { // $listID should not be empty.
            list( $fName, $lName ) = explode(' ', wishlistmember_instance()->ar_sender['name'], 2); // Split the name into First and Last Name.
            $emailAddress          = wishlistmember_instance()->ar_sender['email'];
            $data                  = false;
            if ($unsub) { // If the Unsubscribe.
                $mcOnRemCan = isset($ar['mcOnRemCan'][ $level_id ]) ? $ar['mcOnRemCan'][ $level_id ] : '';
                if ('unsub' === $mcOnRemCan) {
                    $data = [
                        'apikey'        => $mcAPI,
                        'action'        => 'unsubscribe',
                        'listID'        => $listID,
                        'email'         => $emailAddress,
                        'delete_member' => true,
                    ];
                } elseif ('move' === $mcOnRemCan || 'add' === $mcOnRemCan) {
                    $gp    = $ar['mcRCGp'][ $level_id ];
                    $gping = $ar['mcRCGping'][ $level_id ];

                    $interests  = ( is_array($gping) && count($gping) > 0 ) ? $gping : [];
                    $merge_vars = [
                        'FNAME' => $fName,
                        'LNAME' => $lName,
                    ];

                    $replace_interests = 'move' === $mcOnRemCan ? true : false;
                    $optin             = $ar['optin']; // Get the MailChimp API.
                    $optin             = 1 === (int) $optin ? false : true;
                    $data              = [
                        'apikey'            => $mcAPI,
                        'action'            => 'subscribe',
                        'listID'            => $listID,
                        'email'             => $emailAddress,
                        'mergevars'         => $merge_vars,
                        'optin'             => $optin,
                        'update_existing'   => true,
                        'replace_interests' => $replace_interests,
                        'interests'         => $interests,
                    ];
                }
            } else { // Else Subscribe.
                $gp    = $ar['mcGp'][ $level_id ];
                $gping = array_diff((array) $ar['mcGping'][ $level_id ], ['', false, null]);

                $interests  = ( is_array($gping) && count($gping) > 0 ) ? $gping : [];
                $merge_vars = [
                    'FNAME' => $fName,
                    'LNAME' => $lName,
                ];

                $optin = $ar['optin']; // Get the MailChimp API.
                $optin = 1 === (int) $optin ? false : true;
                $data  = [
                    'apikey'            => $mcAPI,
                    'action'            => 'subscribe',
                    'listID'            => $listID,
                    'email'             => $emailAddress,
                    'mergevars'         => $merge_vars,
                    'optin'             => $optin,
                    'update_existing'   => true,
                    'replace_interests' => false,
                    'interests'         => $interests,
                ];
            }
            if ($data) {
                $qname = 'mailchimp_' . time();
                $data  = wlm_maybe_serialize($data);
                $WishlistAPIQueueInstance->add_queue($qname, $data, 'For Queueing');
                self::_interface()->mcProcessQueue();
            }
        }
    }

    public static function __callStatic($name, $args)
    {
        $interface = self::_interface();
        call_user_func_array([$interface, $name], $args);
    }

    public static function _interface()
    {
        static $interface;
        if (! $interface) {
            $interface = new MailChimp_Interface();
        }
        return $interface;
    }
}

class MailChimp_Interface
{
    // This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above.
    private $api;

    public function api($apikey)
    {
        if (! $this->api) {
            $this->api = new \mcsdk($apikey);
        }
        return $this->api;
    }

    public function mcProcessQueue($recnum = 10, $tries = 5)
    {
        $WishlistAPIQueueInstance = new \WishListMember\API_Queue();
        $last_process             = get_option('WLM_MailchimpAPI_LastProcess');
        $current_time             = time();
        $tries                    = $tries > 1 ? (int) $tries : 5;
        $error                    = false;
        // Lets process every 10 seconds.
        if (! $last_process || ( $current_time - $last_process ) > 10) {
            $queues = $WishlistAPIQueueInstance->get_queue('mailchimp', $recnum, $tries, 'tries,name');
            foreach ($queues as $queue) {
                $data = wlm_maybe_unserialize($queue->value);
                if ('subscribe' === $data['action']) {
                    $data['interests'] = array_diff((array) $data['interests'], ['', false, null]);
                    $res               = $this->mc_list_subscribe($data);
                } elseif ('unsubscribe' === $data['action']) {
                    $res = $this->mc_list_unsubscribe($data);
                }

                if (isset($res['error'])) {
                    $res['error'] = strip_tags($res['error']);
                    $res['error'] = str_replace(["\n", "\t", "\r"], '', $res['error']);
                    $d            = [
                        'notes' => "{$res['code']}:{$res['error']}",
                        'tries' => $queue->tries + 1,
                    ];
                    $WishlistAPIQueueInstance->update_queue($queue->ID, $d);
                    $error = true;
                } else {
                    $WishlistAPIQueueInstance->delete_queue($queue->ID);
                    $error = false;
                }
            }
            // Save the last processing time when error has occured on last transaction.
            if ($error) {
                $current_time = time();
                if ($last_process) {
                    update_option('WLM_MailchimpAPI_LastProcess', $current_time);
                } else {
                    add_option('WLM_MailchimpAPI_LastProcess', $current_time);
                }
            }
        }
    }

    public function mc_list_subscribe($data)
    {
        $response = [
            'code'  => 418,
            'error' => 'Unknown Error.',
        ];
        $mc       = $this->api($data['apikey']);
        if ('' != $mc->get_last_error()) {
            return [
                'code'  => 418,
                'error' => $mc->get_last_error(),
            ];
        }

        $list_id = isset($data['listID']) ? $data['listID'] : '';
        $status  = $data['optin'] ? 'pending' : 'subscribed';

        $sub_id      = $mc->get_subscriber_id($data['email']);
        $sub_details = $mc->get("lists/{$list_id}/members/{$sub_id}");

        $interests = [];
        foreach ($data['interests'] as $key => $value) {
            $interests[ $value ] = true;
        }

        if ($sub_details) {
            $sub_interests = isset($sub_details['interests']) ? $sub_details['interests'] : [];
            if (isset($data['replace_interests']) && $data['replace_interests']) {
                foreach ($sub_interests as $key => $value) {
                    $sub_interests[ $key ] = false;
                }
            }
            $interests = array_merge($sub_interests, $interests);
            unset($interests[0]); // Remove from interests array the element where interest ID = 0.
            $sub_data = [
                'status'    => $status,
                'interests' => $interests,
            ];
            $ret      = $mc->put("lists/{$list_id}/members/{$sub_id}", $sub_data);
            if (! $mc->is_success()) {
                $response = [
                    'code'  => 418,
                    'error' => $mc->get_last_error(),
                ];
            } else {
                $response = true;
            }
        } else {
            $sub_data = [
                'email_address' => $data['email'],
                'status'        => $status,
                'merge_fields'  => $data['mergevars'],
                'interests'     => $interests,
            ];

            $ret = $mc->post("lists/{$list_id}/members", $sub_data);

            if (! $mc->is_success()) {
                $response = [
                    'code'  => 418,
                    'error' => $mc->get_last_error(),
                ];
            } else {
                $response = true;
            }
        }
        return $response;
    }

    public function mc_list_unsubscribe($data)
    {
        $response = [
            'code'  => 418,
            'error' => 'Unknown Error.',
        ];
        $mc       = $this->api($data['apikey']);
        if ('' != $mc->get_last_error()) {
            return [
                'code'  => 418,
                'error' => $mc->get_last_error(),
            ];
        }

        $list_id = isset($data['listID']) ? $data['listID'] : '';
        $status  = 'unsubscribed';

        $sub_id      = $mc->get_subscriber_id($data['email']);
        $sub_details = $mc->get("lists/{$list_id}/members/{$sub_id}");

        if ($sub_details) {
            $sub_data = [
                'status' => $status,
            ];
            $ret      = $mc->put("lists/{$list_id}/members/{$sub_id}", $sub_data);
            if (! $mc->is_success()) {
                $response = [
                    'code'  => 418,
                    'error' => $mc->get_last_error(),
                ];
            } else {
                $response = true;
            }
        } else {
            $response = true;
        }

        return $response;
    }

    public function mc_get_lists($api_key)
    {
        $lists = [];
        $mc    = $this->api($api_key);
        if ('' != $mc->get_last_error()) {
            return $lists;
        }

        $lists     = [];
        $lists2    = $lists;
        $rec_count = 100; // 100 is the maximum number of lists to return with each call.
        $lists     = $mc->get('lists', ['count' => $rec_count]);
        $start     = floor($lists['total_items'] / $rec_count);
        $offset    = 1;
        while ($offset <= $start) {
            $args   = [
                'count'  => $rec_count,
                'offset' => $offset * $rec_count,
            ];
            $lists2 = $mc->get('lists', $args);
            if ($lists2) {
                $lists = array_merge_recursive($lists, $lists2);
            }
            ++$offset;
        }

        if ($lists && $lists['total_items'] > 0) {
            $lists = $lists['lists'];
        }
        return $lists;
    }

    public function mc_get_lists_groups($api_key, $list_id)
    {
        $list_groups = [];
        $mc          = $this->api($api_key);
        if ('' != $mc->get_last_error()) {
            return $list_groups;
        }

        $interest_groups = $mc->get('lists/' . $list_id . '/interest-categories', ['count' => 100]);
        if ($interest_groups && $interest_groups['total_items'] > 0) {
            foreach ($interest_groups['categories'] as $group) {
                $list_groups[ $group['id'] ] = [
                    'title'     => $group['title'],
                    'interests' => [],
                ];
                $interests                   = $mc->get('lists/' . $list_id . '/interest-categories/' . $group['id'] . '/interests', ['count' => 100]);
                if ($interests && $interests['total_items'] > 0) {
                    foreach ($interests['interests'] as $interest) {
                        $list_groups[ $group['id'] ]['interests'][ $interest['id'] ] = $interest['name'];
                    }
                }
            }
        }
        return $list_groups;
    }

    public function mc_admin_init()
    {
        if (isset(wlm_post_data()['ar_action']) && 'get_list_interest_groups' === wlm_post_data()['ar_action']) {
            $list_id = wlm_post_data()['list_id'];
            $api_key = wlm_post_data()['api_key'];
            $lg      = $this->mc_get_lists_groups($api_key, $list_id);
            echo json_encode($lg);
            exit(0);
        }
    }
    // End of Functions.
}
