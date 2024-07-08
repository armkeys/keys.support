<?php

/**
 * User Class for WishList Member
 *
 * @package WishListMember
 */

namespace WishListMember;

defined('ABSPATH') || die();

require_once ABSPATH . '/wp-admin/includes/user.php';

/**
 * WishList Member User Class
 * Keeps all membership information in one place
 */
class User
{
    /**
     * User ID
     *
     * @var integer
     */
    public $ID;

    /**
     * User information
     *
     * @var WP_User object
     */
    public $user_info;
    /**
     * User information
     *
     * @deprecated
     * @var        WP_User reference to user_info property
     */
    public $UserInfo;

    /**
     * Sequential Upgrade setting
     *
     * @var boolean
     */
    public $sequential;
    /**
     * Sequential Upgrade setting
     *
     * @deprecated
     * @var        boolean reference to sequential property
     */
    public $Sequential;

    /**
     * Membership Levels
     *
     * @var array
     */
    public $levels = [];
    /**
     * Membership Levels
     *
     * @deprecated
     * @var        array reference to levels property
     */
    public $Levels = [];

    /**
     * Array of active membership level IDs
     *
     * @var array
     */
    public $active_levels = [];

    /**
     * Pay Per Posts
     *
     * @var array
     */
    public $pay_per_posts = [];
    /**
     * Pay Per Posts
     *
     * @deprecated
     * @var        array reference to pay_per_posts property
     */
    public $PayPerPosts;

    /**
     * Deprecated properties map
     *
     * @var array
     */
    private $deprecated_properties = [
        'UserInfo'    => 'user_info',
        'Sequential'  => 'sequential',
        'PayPerPosts' => 'pay_per_posts',
    ];

    /**
     * Constructor
     *
     * @param integer $user_id   User ID.
     * @param boolean $load_user True to load user data.
     */
    public function __construct($user_id, $load_user = null)
    {
        global $wpdb;

        $this->UserInfo    = &$this->user_info;
        $this->Sequential  = &$this->sequential;
        $this->PayPerPosts = &$this->pay_per_posts;
        $this->Levels           =  &$this->levels;

        /*
         * if $user_id is not numeric then it might be an email address or a username
         */
        if (! is_numeric($user_id)) {
            $x = false;
            if (filter_var($user_id, FILTER_VALIDATE_EMAIL)) {
                $x = get_user_by('email', $user_id);
            }
            if (! $x) {
                $x = get_user_by('login', $user_id);
            }
            $user_id = $x->ID ? $x->ID : 0;
        }

        // Verify User ID.
        $user_id += 0;
        $user_id  = $wpdb->get_var($wpdb->prepare("SELECT `ID` FROM `{$wpdb->users}` WHERE `ID`=%d", $user_id));
        if (! $user_id) {
            return false;
        }

        // ID verified, save it.
        $this->ID = $user_id;

        // Load user information if requested.
        if (true === $load_user) {
            $this->LoadUser();
        }

        // Sequential setting.
        $this->sequential = wishlistmember_instance()->is_sequential($this->ID);

        $this->LoadLevels();

        $this->load_payperposts();

        return true;
    }

    /**
     * Loads user information as returned by WP_User object
     */
    public function LoadUser()
    {
        $this->user_info = wishlistmember_instance()->get_user_data($this->ID);
    }

    /**
     * Loads membership levels including their
     * - Status (Cancelled, Pending, UnConfirmed)
     * - Timestamp
     * - Transaction ID
     */
    public function LoadLevels()
    {
        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        $x          = wishlistmember_instance()->get_membership_levels($this->ID);

        $levels = [];
        $ts     = wishlistmember_instance()->user_level_timestamps($this->ID);
        foreach ($x as $lvl) {
            if (array_key_exists($lvl, $ts)) {
                $levels[ $lvl ] = $ts[ $lvl ];
            } else {
                $levels[ $lvl ] = false;
            }
        }

        arsort($levels);

        $this->Levels = [];
        foreach ($levels as $level => $timestamp) {
            $wpm_levels[ $level ] = ( isset($wpm_levels[ $level ]) ) ? $wpm_levels[ $level ] : '';
            if ($wpm_levels[ $level ]) {
                $allmetas = wlm_or(wishlistmember_instance()->Get_All_UserLevelMetas($this->ID, $level), []);

                // Fix for users who are using PHP versions as array_column only supports array objects on PHP 7.
                $option_name_array  = [];
                $option_value_array = [];
                foreach ($allmetas as $allmeta) {
                    $option_name_array[]  = $allmeta->option_name;
                    $option_value_array[] = $allmeta->option_value;
                }

                $allmetas                 = array_combine($option_name_array, $option_value_array);
                $allmetas['parent_level'] = isset($allmetas['parent_level']) ? $allmetas['parent_level'] : 0;

                $this->Levels[ $level ] = new \stdClass();

                $this->Levels[ $level ]->Level_ID = $level;
                $this->Levels[ $level ]->Name     = $wpm_levels[ $level ]['name'];

                $this->Levels[ $level ]->Timestamp = $timestamp;

                $cancelled                                = isset($allmetas['cancelled']) ? $allmetas['cancelled'] : false;
                $this->Levels[ $level ]->Cancelled        = $cancelled;
                $this->Levels[ $level ]->CancelDate       = empty($allmetas['wlm_schedule_level_cancel']) ? false : strtotime($allmetas['wlm_schedule_level_cancel']);
                $this->Levels[ $level ]->CancelDateReason = wlm_or(wlm_maybe_json_decode(wlm_arrval($allmetas, 'schedule_level_cancel_reason')), '');
                $this->Levels[ $level ]->CancelledDate    = empty($allmetas['cancelled_date']) ? false : strtotime($allmetas['cancelled_date']);

                $pending                         = isset($allmetas['forapproval']) ? $allmetas['forapproval'] : false;
                $this->Levels[ $level ]->Pending = $pending;
                $pending                         = ( $pending ) ? true : false;

                $unconfirmed                         = isset($allmetas['unconfirmed']) ? $allmetas['unconfirmed'] : false;
                $this->Levels[ $level ]->UnConfirmed = $unconfirmed;

                $expired                            = wishlistmember_instance()->level_expired($level, $this->ID, $timestamp);
                $this->Levels[ $level ]->Expired    = $expired;
                $this->Levels[ $level ]->ExpiryDate = wishlistmember_instance()->level_expire_date($level, $this->ID, $timestamp);

                $this->Levels[ $level ]->SequentialCancelled = isset($allmetas['sequential_cancelled']) ? $allmetas['sequential_cancelled'] : false;

                $scheduled                            = wishlistmember_instance()->is_level_scheduled($level, $this->ID);
                $this->Levels[ $level ]->Scheduled    = (bool) $scheduled;
                $this->Levels[ $level ]->ScheduleInfo = wlm_maybe_unserialize($scheduled);

                $this->Levels[ $level ]->ParentLevel = isset($wpm_levels[ $allmetas['parent_level'] ]) ? $allmetas['parent_level'] : false;

                $active                         = ! ( $cancelled | $pending | $unconfirmed | $expired | (bool) $scheduled );
                $this->Levels[ $level ]->Active = $active;

                if ($active) {
                    $this->Levels[ $level ]->Status = [__('Active', 'wishlist-member')];
                    $this->active_levels[]          = $level;
                } else {
                    $status_names = [];
                    if ($unconfirmed) {
                        $status_names[] = __('Unconfirmed', 'wishlist-member');
                    }
                    if ($pending) {
                        $status_names[] = __('For Approval', 'wishlist-member');
                    }
                    if ($cancelled) {
                        $status_names[] = __('Cancelled', 'wishlist-member');
                    }
                    if ($scheduled) {
                        $status_names[] = __('Scheduled', 'wishlist-member');
                    }
                    if (true === $expired) {
                        $status_names[] = __('Expired', 'wishlist-member');
                    }
                    $this->Levels[ $level ]->Status = $status_names;
                }

                $this->Levels[ $level ]->metas = $allmetas;
            }
        }

        // Transaction IDs.
        $txns = wishlistmember_instance()->get_membership_levels_txn_ids($this->ID);
        foreach ($txns as $level => $txn) {
            if (( isset($this->Levels[ $level ]) && $this->Levels[ $level ] )) {
                $this->Levels[ $level ]->TxnID = $txn;
            }
        }
    }

    /**
     * Adds Level to user obj in RAM.
     *
     * @param integer $level_id Level ID.
     */
    public function AddLevelobj($level_id)
    {

        $this->Levels[ $level_id ]->Level_ID            = $level_id;
        $this->Levels[ $level_id ]->Name                = __('Name', 'wishlist-member');
        $this->Levels[ $level_id ]->Cancelled           = 'NULL';
        $this->Levels[ $level_id ]->CancelDate          = false;
        $this->Levels[ $level_id ]->Pending             = null;
        $this->Levels[ $level_id ]->UnConfirmed         = null;
        $this->Levels[ $level_id ]->Expired             = false;
        $this->Levels[ $level_id ]->ExpiryDate          = false;
        $this->Levels[ $level_id ]->SequentialCancelled = null;
        $this->Levels[ $level_id ]->Active              = true;
        $this->Levels[ $level_id ]->Status              = [__('Active', 'wishlist-member')];
        $this->Levels[ $level_id ]->Timestamp           = '';
        $this->Levels[ $level_id ]->TxnID               = '';
    }

    /**
     * Adds user to Level
     *
     * @param integer $level_id
     * @param string  $transaction_id
     */
    public function AddLevel($level_id, $transaction_id)
    {
        $x   = array_keys($this->Levels);
        $x[] = $level_id;
        wishlistmember_instance()->set_membership_levels($this->ID, array_unique($x));

        // Transaction id.
        wishlistmember_instance()->set_membership_level_txn_id($this->ID, $level_id, $transaction_id);

        // Reload levels.
        $this->LoadLevels();
    }

    /**
     * Removes user from Level
     *
     * @param integer $level_id Level ID.
     */
    public function RemoveLevel($level_id)
    {
        $x = array_unique(array_keys($this->Levels));

        // Remove level.
        $k = array_search($level_id, $x);
        if (false !== $k) {
            unset($x[ $k ]);
        }

        // Save it.
        wishlistmember_instance()->set_membership_levels($this->ID, $x);

        // Reload levels.
        $this->LoadLevels();
    }

    /**
     * Removes multiple levels
     *
     * @param array $level_ids Array of Membership Level IDs.
     */
    public function RemoveLevels($level_ids)
    {
        $x = array_unique(array_keys($this->Levels));
        wishlistmember_instance()->set_membership_levels($this->ID, array_diff($x, $level_ids));
        $this->LoadLevels();
    }

    /**
     * Execute sequential upgrade for user
     */
    public function RunSequentialUpgrade()
    {
        $this->do_sequential($this->ID);
    }

    /**
     * Cancel Membership Level
     *
     * @param integer $level_id Level ID.
     */
    public function CancelLevel($level_id)
    {
        $this->Levels[ $level_id ]->Cancelled = wishlistmember_instance()->level_cancelled($level_id, $this->ID, true);
    }

    /**
     * UnCancel Level
     *
     * @param integer $level_id Level ID.
     */
    public function UnCancelLevel($level_id)
    {
        $this->Levels[ $level_id ]->Cancelled = wishlistmember_instance()->level_cancelled($level_id, $this->ID, false);
    }

    /**
     * Approve Membership Level
     *
     * @param integer $level_id Level ID.
     */
    public function ApproveLevel($level_id)
    {
        $this->Levels[ $level_id ]->Pending = wishlistmember_instance()->level_for_approval($level_id, $this->ID, false);
    }

    /**
     * UnApprove Membership Level
     *
     * @param integer $level_id Level ID.
     */
    public function UnApproveLevel($level_id)
    {
        $this->Levels[ $level_id ]->Pending = wishlistmember_instance()->level_for_approval($level_id, $this->ID, true);
    }

    /**
     * Confirm Membership Level (Used in Email Confirmation)
     *
     * @param integer $level_id Level ID.
     */
    public function Confirm($level_id)
    {
        $this->Levels[ $level_id ]->UnConfirmed = wishlistmember_instance()->level_unconfirmed($level_id, $this->ID, false);
    }

    /**
     * Confirm user's membership level registration by hash
     *
     * @param  string $hash Hash Key.
     * @return string|false Level ID on success or FALSE on error
     */
    public function ConfirmByHash($hash)
    {
        $email    = $this->user_info->user_email;
        $username = $this->user_info->user_login;
        $key      = wishlistmember_instance()->GetAPIKey();
        foreach ($this->Levels as $level_id => $level) {
            $h = md5("{$email}__{$username}__{$level_id}__{$key}");
            if ($h === $hash && $level->UnConfirmed) {
                $this->Confirm($level_id);
                return $level_id;
            }
        }
        return false;
    }

    /**
     * UnConfirm Membership Level (Used in Email Confirmation)
     *
     * @param integer $level_id Level Id.
     */
    public function UnConfirm($level_id)
    {
        $this->Levels[ $level ]->UnConfirmed = wishlistmember_instance()->level_unconfirmed($level_id, $this->ID, true);
    }

    /**
     * Enable Sequential Upgrade for User
     */
    public function EnableSequential()
    {
        $this->sequential = wishlistmember_instance()->is_sequential($this->ID, true);
    }

    /**
     * Disable Sequential Upgrade for User
     */
    public function DisableSequential()
    {
        $this->sequential = wishlistmember_instance()->is_sequential($this->ID, false);
    }

    /**
     * Check if user's level is expired
     *
     * @param  string $level Level ID
     * @return boolean
     */
    public function IsExpired($level)
    {
        return true === $this->Levels[ $level ]->Expired;
    }

    /**
     * Get level expiration date.
     *
     * @param  string $level Level ID
     * @return boolean|string Expiration date or false if there is no expiration date.
     */
    public function ExpireDate($level)
    {
        if (false === $this->Levels[ $level ]->Expired) {
            return false;
        }
    }

    /**
     * Executes the "Remove From Level" & "Add To Level" features
     *
     * @param array  $the_levels     Levels of the action.
     * @param array  $removed_levels Levels that were removed.
     * @param string $action         Action to perform.
     */
    public function DoAddRemove($the_levels, $removed_levels, $action = '')
    {
        $current_levels = array_keys($this->Levels);
        $the_levels     = (array) $the_levels;
        $removed_levels = (array) $removed_levels;
        $wpm_levels     = wishlistmember_instance()->get_option('wpm_levels');

        $action = 'add' === $action ? '' : $action;
        $action = ! empty($action) ? $action . '_' : $action;

        $remove = [];
        $add    = [];
        foreach ($the_levels as $level) {
            if (isset($wpm_levels[ $level ])) { // Make sure that level is existing and active.
                // Get levels to remove or add.
                $to_remove = array_keys((array) $wpm_levels[ $level ][ $action . 'removeFromLevel' ]);
                $to_add    = array_keys((array) $wpm_levels[ $level ][ $action . 'addToLevel' ]);
                $to_cancel = array_keys((array) $wpm_levels[ $level ][ $action . 'cancelFromLevel' ]);
                foreach ($to_remove as $tr) {
                    /*
                     * if key is not a levelid, use the value, this fix is for 3.0 from 2.9
                     * 3.0 saves the levelid with numberic index eg. [0] => 1506100025107
                     * while 2.9 saves the levelid as the index with value of 1 eg. [1508151640] => 1
                     */
                    if (! isset($wpm_levels[ $tr ])) {
                        $tr = isset($wpm_levels[ $level ][ $action . 'removeFromLevel' ][ $tr ]) ? $wpm_levels[ $level ][ $action . 'removeFromLevel' ][ $tr ] : $tr;
                    }
                    if (! isset($wpm_levels[ $tr ])) {
                        continue; // Still no luck? continue.
                    }

                    if (in_array($tr, $current_levels)) { // Only remove levels that this user currently have.
                        $remove[ $tr ] = $level;
                    }
                }

                foreach ($to_add as $ta) {
                    /*
                     * if key is not a levelid, use the value, this fix is for 3.0 from 2.9
                     * 3.0 saves the levelid with numberic index eg. [0] => 1506100025107
                     * while 2.9 saves the levelid as the index with value of 1 eg. [1508151640] => 1
                     */
                    if (! isset($wpm_levels[ $ta ])) {
                        $ta = isset($wpm_levels[ $level ][ $action . 'addToLevel' ][ $ta ]) ? $wpm_levels[ $level ][ $action . 'addToLevel' ][ $ta ] : $ta;
                    }
                    if (! isset($wpm_levels[ $ta ])) {
                        continue; // Still no luck? continue.
                    }

                    if (! in_array($ta, $current_levels)) { // Only add levels that this user does not have.
                        if (array_key_exists($ta, $add)) { // If this level is for add already, check level priority.
                            if ($wpm_levels[ $level ]['levelOrder'] > $wpm_levels[ $add[ $ta ] ]['levelOrder']) {
                                $add[ $ta ] = $level;
                            }
                        } else {
                            $add[ $ta ] = $level;
                        }
                    }
                }

                foreach ($to_cancel as $tc) {
                    /*
                     * if key is not a levelid, use the value, this fix is for 3.0 from 2.9
                     * 3.0 saves the levelid with numberic index eg. [0] => 1506100025107
                     * while 2.9 saves the levelid as the index with value of 1 eg. [1508151640] => 1
                     */
                    if (! isset($wpm_levels[ $tc ])) {
                        $tc = isset($wpm_levels[ $level ]['removeFromLevel'][ $tc ]) ? $wpm_levels[ $level ]['removeFromLevel'][ $tc ] : $tc;
                    }
                    if (! isset($wpm_levels[ $tc ])) {
                        continue; // Still no luck? continue.
                    }

                    if (in_array($tc, $current_levels)) { // Only cancel levels that this user currently have.
                        wishlistmember_instance()->level_cancelled($tc, $this->ID, true);
                    }
                }
            }
        }

        $to_add_levels    = array_keys($add);
        $to_remove_levels = array_keys($remove);
        if (count($to_add_levels) <= 0 && count($to_remove_levels) <= 0) {
            return; // Nothing to do here.
        }

        /*
         * we merge current levels with levels to be automatically added
         * and then we remove the remainings levels that are to be automatically removed
         */
        $levels = array_unique(array_diff(array_merge($current_levels, $to_add_levels), $to_remove_levels));
        // We update the levels.
        $x_levels = [
            'Levels'            => array_unique($levels),
            'To_Removed_Levels' => array_unique($to_remove_levels),
            'Metas'             => [],
        ];

        if (! empty($action)) { // We only add parent for ADD action.
            foreach ($levels as $key => $lvl) {
                if (isset($add[ $lvl ])) { // If this level is newly added, we add parent meta.
                    $x_levels['Metas'][ $lvl ] = [['parent_level', $add[ $lvl ]]];
                }
            }
        }

        $res = wishlistmember_instance()->set_membership_levels($this->ID, (object) $xLevels);
    }

    /**
     * Retrieve history for User
     *
     * @param  string      $log_group Log group.
     * @param  string|null $log_key   Log key.
     * @return array|null Database query results
     */
    public function get_history($log_group, $log_key = null)
    {
        return \WishListMember\Logs::get($this->ID, $log_group, $log_key);
    }

    /**
     * Loads of all the User's Pay per Posts in the PayPerPosts property grouped by post type
     * A special post type called _all_ contains of the post ids irregardless of the post type
     */
    public function load_payperposts()
    {
        $ppps = wishlistmember_instance()->get_user_pay_per_post($this->ID, true);

        $this->pay_per_posts['_all_'] = [];
        foreach ($ppps as $ppp) {
            $this->pay_per_posts[ $ppp->type ][] = $ppp->content_id;
            $this->pay_per_posts['_all_'][]      = $ppp->content_id;
        }
    }

    /**
     * Add Pay Per Posts to User
     *
     * @param string|array $payperpost_ids A string or an array of strings in the format of payperpost-[0-9]+.
     */
    public function add_payperposts($payperpost_ids)
    {
        wishlistmember_instance()->set_pay_per_post($this->ID, (array) $payperpost_ids);
        $this->load_payperposts();
    }

    /**
     * Remove Pay Per Posts from User
     *
     * @param string|array $payperpost_ids A string or an array of strings in the format of payperpost-[0-9]+.
     */
    public function remove_payperposts($payperpost_ids)
    {
        foreach ((array) $payperpost_ids as $payperpost_id) {
            if (preg_match('/^payperpost-(\d+)$/i', $payperpost_id, $match)) {
                $post_type = get_post_type($match[1]);
                if ($post_type) {
                    wishlistmember_instance()->remove_post_users($post_type, $match[1], $this->ID);
                }
            }
        }
        $this->load_payperposts();
    }

    /**
     * Get a list of pay per post IDs matching a set of transaction ids
     *
     * @param  array $transaction_ids Array of transaction IDs.
     * @return array Array of Pay Per Post IDs
     */
    public function get_payperposts_by_transaction_ids($transaction_ids)
    {
        global $wpdb;
        if (! is_array($transaction_ids) || empty($transaction_ids)) {
            return [];
        }

        return $wpdb->get_col(
            $wpdb->prepare(
                'SELECT `content_id` FROM `' . esc_sql(wishlistmember_instance()->table_names->contentlevels) . '` WHERE `level_id`=%s AND `ID` IN (SELECT `contentlevel_id` FROM `' . esc_sql(wishlistmember_instance()->table_names->contentlevel_options) . '` WHERE `option_name`="transaction_id" AND `option_value` IN ( ' . implode(', ', array_fill(0, count($transaction_ids), '%s')) . ' ) )',
                'U-' . $this->ID,
                ...array_values($transaction_ids)
            )
        );
    }

    /**
     * Generates a one-time login link for $user_id
     *
     * @uses wlm_generate_password
     * @uses add_user_meta
     * @uses add_query_arg
     *
     * @param  integer $user_id User ID.
     * @return string One-Time login link.
     */
    public static function generate_onetime_login_link($user_id)
    {
        // Generate user's unique public and private key for one-time login link.
        do {
            // Generate random public key.
            $public_key = wlm_generate_password();
            $public_key = sha1($public_key) . md5($public_key);

            // Generate private key from public key.
            $private_key = md5($public_key) . sha1($public_key);
        } while (! add_user_meta($user_id, "otl-$private_key", time(), true));

        // Generate the link and return it.
        return add_query_arg(
            [
                'wlmotl' => $public_key,
                'uid'    => $user_id,
            ],
            site_url()
        );
    }

    /**
     * Perform one-time login
     *
     * @uses $WishListMemberInstance::wpm_auto_login
     * @uses $WishListMemberInstance::Login
     *
     * @uses get_user_meta
     * @uses delete_user_meta
     *
     * @param integer $user_id    User ID.
     * @param string  $public_key Public Key.
     */
    public static function do_onetime_login($user_id, $public_key)
    {
        // Check 'otlchk' cookie against 'chk' query argument.
        if (wlm_getcookie('otlchk') !== wlm_get_data()['chk']) {
            exit;
        }

        // Generate private key from public key.
        $private_key = md5($public_key) . sha1($public_key);

        // Login if private key is found for user.
        if (get_user_meta($user_id, "otl-$private_key")) {
            // Delete the private key so it cannot be used again (thus one-time).
            delete_user_meta($user_id, "otl-$private_key");

            // Get the user and login if user is valid.
            $user = get_userdata($user_id);
            if ($user) {
                // Auto login.
                wishlistmember_instance()->wpm_auto_login($user_id);
                // Redirect to WishList Member after login page.
                wlm_post_data()['log']             = $user->user_login;
                wlm_post_data()['wlm_redirect_to'] = wlm_or(wlm_trim(wlm_get_data()['redirect']), 'wishlistmember');
                $_COOKIE['wlmotl']                 = 1;
                wishlistmember_instance()->login($user->user_login, $userinfo);
                exit;
            }
        }

        // Redirect to login URL if we're still here.
        wp_safe_redirect(wp_login_url());
        exit;
    }

    /**
     * Get the URL of the user's profile photo
     *
     * @param  integer $user_id User ID. $this->ID or current logged-in user's ID if not set.
     * @return string|false     URL or false if no profile photo is set
     */
    public function get_profile_photo($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = $this->ID ? $this->ID : get_current_user_id();
        }
        return wlm_arrval(get_user_meta($user_id, 'profile_photo', true), 'url') ? wlm_arrval('lastresult') : false;
    }

    /**
     * Getter
     *
     * @param  string $property Property name.
     * @return mixed
     */
    public function __get($property)
    {
        if (isset($this->deprecated_properties[ $property ])) {
            $new_property = $this->deprecated_properties[ $property ];
            wlm_deprecated_property_error_log($property, $new_property);
            return $this->$new_property;
        }
    }

    /**
     * Setter
     *
     * @param  string $property Property name.
     * @param  mixed  $value    Property value.
     * @return mixed
     */
    public function __set($property, $value)
    {
        if (isset($this->deprecated_properties[ $property ])) {
            $new_property = $this->deprecated_properties[ $property ];
            wlm_deprecated_property_error_log($property, $new_property);
            $this->$new_property = $value;
        }
    }
}
