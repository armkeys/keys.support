<?php

/**
 * Groundhogg interface class file
 *
 * @package WishListMember/Autoresponders
 */

namespace WishListMember\Autoresponders;

use Groundhogg\Contact;

use function Groundhogg\create_contact_from_user;
use function Groundhogg\get_array_var;
use function Groundhogg\get_contactdata;
use function Groundhogg\is_a_contact;

/**
 * Groundhogg interface class
 */
class Groundhogg_Interface
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

        $data = ( new \WishListMember\Autoresponder('groundhogg') )->settings;
        $data = $data ? $data : false;
        $data = isset($data['groundhogg_settings']) ? $data['groundhogg_settings'] : [];

        $this->settings = is_array($data) ? $data : [];

        // Check if Groundhogg is active.
        $active_plugins = wlm_get_active_plugins();
        if (in_array('Groundhogg', $active_plugins, true) || isset($active_plugins['groundhogg/groundhogg.php']) || is_plugin_active('groundhogg/groundhogg.php')) {
            $this->plugin_active = true;
        }
    }

    /**
     * API interface.
     * All we really need to do is check if our plugin is active.
     *
     * @return boolean
     */
    public function api()
    {
        return $this->plugin_active;
    }

    /**
     * Tag added to contact
     *
     * @param object  $contact Contact.
     * @param integer $tag_id  Tag ID.
     */
    public function tags_added_hook($contact, $tag_id)
    {

        $action = 'add';
        $user   = $contact->get_userdata();

        if (! $user) {
            return;
        }

        $settings = isset($this->settings['tag'][ $tag_id ][ $action ]) ? $this->settings['tag'][ $tag_id ][ $action ] : [];
        $this->do_hook($user->ID, $tag_id, $action, $settings, false);
    }

    /**
     * Tag added to contact
     *
     * @param object  $contact Contact.
     * @param integer $tag_id  Tag ID.
     */
    public function tags_removed_hook($contact, $tag_id)
    {

        $action = 'remove';
        $user   = $contact->get_userdata();

        if (! $user) {
            return;
        }

        $settings = isset($this->settings['tag'][ $tag_id ][ $action ]) ? $this->settings['tag'][ $tag_id ][ $action ] : [];
        $this->do_hook($user->ID, $tag_id, $action, $settings, false);
    }

    /**
     * Do hook
     *
     * @param integer $wpuser   User ID.
     * @param string  $hook_id  Hook ID.
     * @param string  $action   Action.
     * @param array   $settings Settings.
     * @param boolean $is_list  True if list.
     */
    private function do_hook($wpuser, $hook_id, $action, $settings, $is_list = true)
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
        $txnid  = "GROUNDHOGG-{$action}-{$prefix}{$hook_id}-";

        // Add to level.
        if (count($added_levels) > 0) {
            $user_mlevels  = $current_user_mlevels;
            $add_level_arr = $added_levels;
            foreach ($add_level_arr as $id => $add_level) {
                if (! isset($wpm_levels[ $add_level ])) {
                    continue;// check if valid level.
                }
                if (! in_array($add_level, $user_mlevels)) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict.
                    $user_mlevels[] = $add_level;
                    $new_levels[]   = $add_level; // Record the new level.
                    wishlistmember_instance()->set_membership_levels($wpuser, $user_mlevels);
                    wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, "{$txnid}" . time());// update txnid.
                } else {
                    // For cancelled members.
                    $cancelled      = wishlistmember_instance()->level_cancelled($add_level, $wpuser);
                    $resetcancelled = true; // Lets make sure that old versions without this settings still works.
                    if (isset($wpm_levels[ $add_level ]['uncancelonregistration'])) {
                        $resetcancelled = (bool) $wpm_levels[ $add_level ]['uncancelonregistration'];
                    }
                    if ($cancelled && $resetcancelled) {
                        $ret = wishlistmember_instance()->level_cancelled($add_level, $wpuser, false);
                        wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, $txnid . time()); // Update txnid.
                    }

                    // For Expired Members.
                    $expired      = wishlistmember_instance()->level_expired($add_level, $wpuser);
                    $resetexpired = (bool) $wpm_levels[ $add_level ]['registrationdatereset'];
                    if ($expired && $resetexpired) {
                        wishlistmember_instance()->user_level_timestamp($wpuser, $add_level, time());
                        wishlistmember_instance()->set_membership_level_txn_id($wpuser, $add_level, $txnid . time()); // Update txnid.
                    } else {
                        // If levels has expiration and allow reregistration for active members.
                        $levelexpires     = isset($wpm_levels[ $add_level ]['expire']) ? (int) $wpm_levels[ $add_level ]['expire'] : false;
                        $levelexpires_cal = isset($wpm_levels[ $add_level ]['calendar']) ? $wpm_levels[ $add_level ]['calendar'] : false;
                        $resetactive      = (bool) $wpm_levels[ $add_level ]['registrationdateresetactive'];
                        if ($levelexpires && $resetactive) {
                            // Get the registration date before it gets updated because we will use it later.
                            $levelexpire_regdate = wishlistmember_instance()->Get_UserLevelMeta($wpuser, $add_level, 'registration_date');

                            $levelexpires_cal = in_array($levelexpires_cal, ['Days', 'Weeks', 'Months', 'Years'], true) ? $levelexpires_cal : false;
                            if ($levelexpires_cal && $levelexpire_regdate) {
                                list( $xdate, $xfraction ) = explode('#', $levelexpire_regdate);

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
            foreach ($cancelled_levels as $id => $cancel_level) {
                if (! isset($wpm_levels[ $cancel_level ])) {
                    continue;// check if valid level.
                }
                if (in_array($cancel_level, $user_mlevels)) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict.
                    $ret = wishlistmember_instance()->level_cancelled($cancel_level, $wpuser, true);
                }
            }
        }

        // Remove from level.
        if (count($removed_levels) > 0) {
            $user_mlevels = $current_user_mlevels;
            foreach ($removed_levels as $id => $remove_level) {
                $arr_index = array_search($remove_level, $user_mlevels); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict.
                if (false !== $arr_index) {
                    unset($user_mlevels[ $arr_index ]);
                }
            }
            wishlistmember_instance()->set_membership_levels($wpuser, $user_mlevels);
            wishlistmember_instance()->schedule_sync_membership(true);
        }

        if (count($add_ppp) > 0) {
            foreach ($add_ppp as $key => $value) {
                $post = get_post($value, ARRAY_A);
                if ($post) {
                    wishlistmember_instance()->add_post_users($post['post_type'], $post['ID'], $wpuser);
                }
            }
        }

        if (count($remove_ppp) > 0) {
            foreach ($remove_ppp as $key => $value) {
                $post = get_post($value, ARRAY_A);
                if ($post) {
                    wishlistmember_instance()->remove_post_users($post['post_type'], $post['ID'], $wpuser);
                }
            }
        }
    }

    /**
     * Handle the tag changes
     *
     * @param object $user        User.
     * @param array  $apply_tags  Tags to apply.
     * @param array  $remove_tags Tags to remove.
     */
    private function handle_tag_removal_or_application($user, $apply_tags, $remove_tags)
    {

        $contact = get_contactdata($user->user_email);

        if (! is_a_contact($contact)) {
            $contact = create_contact_from_user($user);
        } else {
            $contact->remove_tag($this->filter_tag_values($remove_tags));
        }

        $contact->add_tag($this->filter_tag_values($apply_tags));
    }

    /**
     * New user tags hok
     *
     * @param integer $uid   User ID.
     * @param array   $udata Registration data.
     */
    public function new_user_tags_hook($uid = null, $udata = null)
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

        $settings   = isset($this->settings['level'][ $udata['wpm_id'] ]['add']) ? $this->settings['level'][ $udata['wpm_id'] ]['add'] : [];
        $apply_tag  = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
        $remove_tag = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];

        if (! $level_unconfirmed && ! $level_for_approval) {
            $this->handle_tag_removal_or_application($user, $apply_tag, $remove_tag);
        }
    }

    /**
     * Add user tags hok
     *
     * @param integer $uid       User ID.
     * @param array   $addlevels Levels.
     */
    public function add_user_tags_hook($uid, $addlevels = [])
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
         * If from registration then don't don't process if the $addlevels is
         * the same level the user registered to. This is already processed by NewUserTagsQueue func.
         */
        if ('wpm_register' === wlm_post_data()['action']) {
            if ((string) wlm_post_data()['wpm_id'] === (string) $level_added) {
                return;
            }
        }

        foreach ($addlevels as $key => $lvl) {
            $level_unconfirmed  = wishlistmember_instance()->level_unconfirmed($lvl, $uid);
            $level_for_approval = wishlistmember_instance()->level_for_approval($lvl, $uid);

            $settings   = isset($this->settings['level'][ $lvl ]['add']) ? $this->settings['level'][ $lvl ]['add'] : [];
            $apply_tag  = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
            $remove_tag = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];

            if (! $level_unconfirmed && ! $level_for_approval) {
                $this->handle_tag_removal_or_application($user, $apply_tag, $remove_tag);
            } elseif (wlm_post_data()['SendMail']) {
                $this->handle_tag_removal_or_application($user, $apply_tag, $remove_tag);
            }
        }
    }

    /**
     * Confirm approve levels user tags hok
     *
     * @param integer $uid    User ID.
     * @param array   $levels Levels.
     */
    public function confirm_approve_levels_tags_hook($uid = null, $levels = [])
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

        $settings   = isset($this->settings['level'][ $levels[0] ]['add']) ? $this->settings['level'][ $levels[0] ]['add'] : [];
        $apply_tag  = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
        $remove_tag = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];

        if (! $level_unconfirmed && ! $level_for_approval) {
            $this->handle_tag_removal_or_application($user, $apply_tag, $remove_tag);
        }
    }

    /**
     * Reregister user tags hok
     *
     * @param integer $uid    User ID.
     * @param array   $levels Levels.
     */
    public function rereg_user_tags_hook($uid, $levels = [])
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
            if (strrpos($level, 'U-') !== false) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        foreach ($levels as $level) {
            $settings   = isset($this->settings['level'][ $level ]['rereg']) ? $this->settings['level'][ $level ]['rereg'] : [];
            $apply_tag  = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
            $remove_tag = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];

            $this->handle_tag_removal_or_application($user, $apply_tag, $remove_tag);
        }
    }

    /**
     * Remove user tags hok
     *
     * @param integer $uid    User ID.
     * @param array   $levels Levels.
     */
    public function remove_user_tags_hook($uid, $levels = [])
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
            if (strrpos($level, 'U-') !== false) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        foreach ($levels as $level) {
            $settings   = isset($this->settings['level'][ $level ]['remove']) ? $this->settings['level'][ $level ]['remove'] : [];
            $apply_tag  = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
            $remove_tag = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];

            $this->handle_tag_removal_or_application($user, $apply_tag, $remove_tag);
        }
    }

    /**
     * Cancel user tags hok
     *
     * @param integer $uid    User ID.
     * @param array   $levels Levels.
     */
    public function cancel_user_tags_hook($uid, $levels = [])
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
            if (strrpos($level, 'U-') !== false) {
                unset($levels[ $key ]);
            }
        }
        if (count($levels) <= 0) {
            return;
        }

        foreach ($levels as $level) {
            $settings   = isset($this->settings['level'][ $level ]['cancel']) ? $this->settings['level'][ $level ]['cancel'] : [];
            $apply_tag  = isset($settings['apply_tag']) ? $settings['apply_tag'] : [];
            $remove_tag = isset($settings['remove_tag']) ? $settings['remove_tag'] : [];

            $this->handle_tag_removal_or_application($user, $apply_tag, $remove_tag);
        }
    }

    /**
     * Removes empty tag entries
     *
     * @param  array $tags Array of tags.
     * @return array
     */
    private function filter_tag_values($tags)
    {
        return array_values(
            array_filter(
                $tags,
                function ($val) {
                    return ! empty(wlm_trim($val));
                }
            )
        );
    }
}
