<?php

namespace WishListMember\Autoresponders;

class SendStudio
{
    public static function user_registered($user_id, $data)
    {
        self::added_to_level($user_id, [$data['wpm_id']]);
    }

    public static function added_to_level($user_id, $level_id)
    {
        $level_id = wlm_remove_inactive_levels($user_id, $level_id);
        self::pre_process($user_id, $level_id, 'added');
    }

    public static function removed_from_level($user_id, $level_id)
    {
        self::pre_process($user_id, $level_id, 'removed');
    }

    public static function uncancelled_from_level($user_id, $levels)
    {
        self::pre_process($user_id, $levels, 'uncancelled');
    }

    public static function cancelled_from_level($user_id, $levels)
    {
        self::pre_process($user_id, $levels, 'cancelled');
    }

    public static function pre_process($email_or_id, $levels, $action)
    {
        static $ar;

        if (empty($ar)) {
            $ar = ( new \WishListMember\Autoresponder('sendstudio') )->settings;
        }

        // Get email address.
        if (is_numeric($email_or_id)) {
            $userdata = get_userdata($email_or_id);
        } elseif (filter_var($email_or_id, FILTER_VALIDATE_EMAIL)) {
            $userdata = get_user_by('email', $email_or_id);
        } else {
            return; // Email_or_id is neither a valid ID or email address.
        }
        if (! $userdata) {
            return; // Invalid user_id.
        }

        // Make sure email is not temp.
        if (! wlm_trim($userdata->user_email) || preg_match('/^temp_[0-9a-f]+/i', $userdata->user_email)) {
            return;
        }
        // Make sure levels is an array.
        if (! is_array($levels)) {
            $levels = [$levels];
        }

        foreach ($levels as $level_id) {
            $add    = wlm_or($ar['list_actions'][ $level_id ][ $action ]['add'], '');
            $remove = wlm_or($ar['list_actions'][ $level_id ][ $action ]['remove'], '');
            if ($add) {
                self::process($add, $ar, $userdata, false);
            }
            if ($remove) {
                self::process($remove, $ar, $userdata, true);
            }
        }
    }

    public static function process($list_id, $ar, $userdata, $unsubscribe = false)
    {
        if ($list_id) { // $list_id should not be empty.
            $emailAddress = $userdata->user_email;
            $ssPath       = $ar['sspath']; // Get the Interspire XML Path.
            $ssUname      = $ar['ssuname']; // Get the Interspire XML Username.
            $ssToken      = $ar['sstoken']; // Get the Interspire XML Token.
            $ssFnameId    = $ar['ssfnameid']; // Get the Interspire Custom Field First Name ID.
            $ssLnameId    = $ar['sslnameid']; // Get the Interspire Custom Field Last Name ID.

            if ($unsubscribe) { // If the Unsubscribe.
                self::ssListUnsubscribe($ssPath, $ssUname, $ssToken, $list_id, $userdata->user_email);
            } else { // Else Subscribe.
                self::ssListSubscribe($ssPath, $ssUname, $ssToken, $ssFnameId, $ssLnameId, $list_id, $userdata->user_email, $userdata->first_name, $userdata->last_name);
            }
        }
    }

    // Function for Subscribing Members.
    public static function ssListSubscribe($ssPath, $ssUname, $ssToken, $ssFnameId, $ssLnameId, $listID, $emailAddress, $fName, $lName)
    {
        // Prepare the data.
        $xml = '<xmlrequest>
			<username>' . $ssUname . '</username>
			<usertoken>' . $ssToken . '</usertoken>
			<requesttype>subscribers</requesttype>
			<requestmethod>AddSubscriberToList</requestmethod>
			<details>
				<emailaddress>' . $emailAddress . '</emailaddress>
				<mailinglist>' . $listID . '</mailinglist>
				<format>html</format>
				<confirmed>yes</confirmed>
				<customfields>
					<item>
						<fieldid>' . ( '' ? 2 === (int) $ssFnameId : $ssFnameId ) . '</fieldid>
						<value>' . $fName . '</value>
					</item>
					<item>
						<fieldid>' . ( '' ? 3 === (int) $ssLnameId : $ssLnameId ) . '</fieldid>
						<value>' . $lName . '</value>
					</item>
				</customfields>
			</details>
		</xmlrequest>';
        self::SendRequest($xml, $ssPath);
    }

    // Function for UnSubscribing Members.
    public static function ssListUnsubscribe($ssPath, $ssUname, $ssToken, $listID, $emailAddress)
    {
        // Prepare the data.
        $xml = '<xmlrequest>
			<username>' . $ssUname . '</username>
			<usertoken>' . $ssToken . '</usertoken>
			<requesttype>subscribers</requesttype>
			<requestmethod>DeleteSubscriber</requestmethod>
			<details>
				<emailaddress>' . $emailAddress . '</emailaddress>
				<list>' . $listID . '</list>
			</details>
		</xmlrequest>';
        self::SendRequest($xml, $ssPath);
    }

    // Function for Sending Request.
    public static function SendRequest($xml, $ssPath)
    {
        wp_remote_post(
            $ssPath,
            [
                'blocking'   => false,
                'user-agent' => 'WishList Member/' . WLM_PLUGIN_VERSION,
                'body'       => $xml,
            ]
        );
    }

    // End of Funtions.
}
