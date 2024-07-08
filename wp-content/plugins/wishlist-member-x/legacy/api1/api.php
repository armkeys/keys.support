<?php

/**
 * Plugin Name: WishList Member&trade; Legacy API
 * Application Programming Interface Class for WishList Member
 */

if (! class_exists('WLMAPI')) {
    define('WLMAPI_VERSION', '0.1.20200415');

    /**
     * WishList Member Legacy API Class
     */
    class WLMAPI
    {
        public static $deprecated = 'Deprecated: Call to deprecated WLMAPIv1 at %s:%d. This API is deprecated since WishList Member 3.0 and will be removed in the near future. Please migrate your code to use the newer API. More info at http://codex.wishlistproducts.com/tutorial-how-to-connect-to-the-wishlist-member-api/.';

        /**
         * Get various WishList Member Option Settings.
         *
         * Use this to get:
         *      register_email_body, register_email_subject, email_sender_name, email_sender_address,
         *      CurrentVersion
         *
         * @param  string $option Option to retrieve.
         * @return var Current setting.
         */
        public function GetOption($option, $dec = null)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $setting = $WishListMemberInstance->get_option($option, $dec);
            return $setting;
        }

        /**
         * Check to see if the license is active.
         *
         * Use this to get:
         * true if license is active false if it is not.
         *
         * @return var Current status.
         */
        public function CheckLicense()
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            if ('1' != $WishListMemberInstance->get_option('LicenseStatus')) {
                return false;
            } else {
                return true;
            }
        }

        /**
         * Get an array with a string of members
         *
         * No parameters
         *
         * @return array Key: Level SKU plus pending and nonsequential, Value: comma delimited string of member ids
         * Example return:
         *   Array
         *       (
         *           [1253923651] => 381,390,426
         *           [1255382921] =>
         *           [pending] =>
         *           [nonsequential] =>
         *       )
         */
        public function GetMembers()
        {
            // Get array of levels&members: key=level value=list of member ids.
            self::is_deprecated();
            global $WishListMemberInstance;
            $members   = $WishListMemberInstance->member_ids(null, true);
            $cancelled = $WishListMemberInstance->cancelled_member_ids(null, true);

            foreach (array_keys($members) as $sku) {
                $members[ $sku ] = implode(',', array_diff($members[ $sku ], (array) $cancelled[ $sku ]));
            }
            return $members;
        }

        /**
         * Retrieves all Membership Levels
         *
         * @return array Membership Levels
         */
        public function GetLevels()
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $levels = $WishListMemberInstance->get_option('wpm_levels');
            foreach ((array) $levels as $id => $level) {
                $level['ID']   = $id;
                $levels[ $id ] = $level;
            }
            return $levels;
        }

        /**
         * Pass list of possibly mixed skus or names, Get an array as 'sku=>name' or 'sku=>sku'.
         *
         * @param  string $level  Either 'all' or a comma delimited string of level names or skus to return.
         * @param  string $return Either 'names' or 'skus'.
         * @return array of levels arranged as 'sku=>name' or 'sku=>sku'.
         */
        public function GetLevelArray($levels = 'all', $return = 'skus')
        {
            self::is_deprecated();
            $all_levels = self::GetLevels();
            $ret        = [];
            if (is_string($levels) && 'all' === $levels) {
                foreach ($all_levels as $key => $onelevel) {
                    $ret[ $key ] = ( 'skus' === $return ) ? $key : $onelevel['name'];
                }
                return $ret;
            }

            if (is_string($levels) && false !== strpos($levels, ',')) {
                $levels = explode(',', $levels);
            } else {
                $levels = (array) $levels;
            }

            foreach ($levels as $level) {
                foreach ($all_levels as $key => $onelevel) {
                    if (wlm_trim($level) == $onelevel['name'] || wlm_trim($level) == $key) {
                        $ret[ $key ] = ( 'skus' === $return ) ? $key : $onelevel['name'];
                        break;
                    }
                }
            }
            return $ret;
        }

        /**
         * Get a list of posts and pages for a specific level
         *
         * @param string $ContentType can be categories, pages, posts, comments
         * @param string $Level       must be a single level to capture posts and pages.
         */
        public function GetContentByLevel($ContentType, $Level)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $content = $WishListMemberInstance->get_membership_content($ContentType, $Level);
            return $content;
        }

        /**
         * Get a list of members in one or more levels.
         *
         * @param  string  $levels       Either 'all' or a comma delimited string of level names or skus to return.
         * @param  boolean $strippending Optional. Default is false. True strips pending from return.
         * @return string Comma delimited string of member ids for all members in matching levels.
         */
        public function MergedMembers($levels = 'all', $strippending = 0)
        {
            self::is_deprecated();
            $members = self::GetMembers();
            return $members;
            exit;

            if ('pending' === $levels) {
                return $members['pending'];
            }

            $levels = $this->GetLevelArray($levels);
            $ret    = [];

            foreach ($levels as $k => $level) {
                if (isset($members[ $level ]) && $members[ $level ] && 'pending' !== $k) {
                    $ra = explode(',', $members[ $level ]);
                    // $ra = preg_split('/,/i', $members[$level]);
                    $ret = array_merge($ret, $ra);
                }
            }
            $ret = array_unique($ret);

            if ($strippending && $members['pending']) {
                $ret = array_diff($ret, explode(',', $members['pending']));
            }
            return implode(',', $ret);
        }

        /**
         * Get a count of members in a level or levels.
         *
         * @param  string $level Either 'all' or a comma delimited string of level names or skus to return.
         *              For nonmembers pass 'nonmembers' For pending pass 'pending'
         * @return integer Number of members in target level(s).
         */
        public function GetMemberCount($level)
        {
            self::is_deprecated();
            global $WishListMemberInstance;

            if ('nonmembers' === $level) {
                return $WishListMemberInstance->non_member_count();
            }
            /*
                elseif ('pending' === $level)
                return $WishListMemberInstance->PendingCount();
            */

            $m = $WishListMemberInstance->member_ids($level, null, true);
            return $m;
            /*
                if ($m)
                return count(explode(',', $m));
                return 0;
            */
        }

        /**
         * Make Members pending.
         *
         * @param  int or array $ids ID or array of IDs.
         * @return integer Count of IDs.
         */
        public function MakePending($ids)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $ids = (array) $ids;
            foreach ($ids as $id) {
                $levels = $WishListMemberInstance->get_membership_levels($id);
                foreach ($levels as $level) {
                    $WishListMemberInstance->level_for_approval($level, $id, true);
                }
            }
            return true;
            // Return WLMAPI::_makeit($ids, 'pending');
        }

        /**
         * Make Members Active.
         *
         * @param  int or array $ids ID or array of IDs.
         * @return integer Count of IDs.
         */
        public function MakeActive($ids)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $ids = (array) $ids;
            foreach ($ids as $id) {
                $levels = $WishListMemberInstance->get_membership_levels($id);
                foreach ($levels as $level) {
                    $WishListMemberInstance->level_for_approval($level, $id, false);
                }
            }
            return true;
            // Return WLMAPI::_makeitnot($ids, 'pending');
        }

        /**
         * Make Members Sequential.
         *
         * @param  int or array $ids ID or array of IDs.
         * @return integer Count of IDs.
         */
        public function MakeSequential($ids)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $WishListMemberInstance->is_sequential($ids, true);
            return true;
            // Return WLMAPI::_makeitnot($ids, 'nonsequential');
        }

        /**
         * Make Members Nonsequential.
         *
         * @param  int or array $ids ID or array of IDs.
         * @return integer Count of IDs.
         */
        public function MakeNonSequential($ids)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $WishListMemberInstance->is_sequential($ids, false);
            return true;
            // Return WLMAPI::_makeit($ids, 'nonsequential');
        }

        /**
         * Adds a WP User
         *
         * @param  string $username
         * @param  string $email
         * @param  string $password
         * @param  string $firstname (optional)
         * @param  string $lastname  (optional)
         * @return integer User ID on success or False on failure
         */
        public function AddUser($username, $email, $password, $firstname = '', $lastname = '')
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            require_once ABSPATH . WPINC . '/pluggable.php';
            require_once ABSPATH . WPINC . '/registration.php';
            $username  = wlm_trim($username);
            $password  = wlm_trim($password);
            $email     = wlm_trim($email);
            $firstname = wlm_trim($firstname);
            $lastname  = wlm_trim($lastname);

            $passmin = $WishListMemberInstance->get_option('min_passlength');
            if (! $passmin) {
                $passmin = 8;
            }

            if (! $username) {
                return self::__setError('Username required');
            }
            if (username_exists($username)) {
                return self::__setError('Username already in use');
            }
            if (! wlm_is_email($email)) {
                return self::__setError('Invalid email address');
            }
            if (email_exists($email)) {
                return self::__setError('Email address already in use');
            }
            if (! $password) {
                return self::__setError('Password required');
            }
            if (strlen($password) < $passmin) {
                return self::__setError('Password has to be at least ' . $passmin . ' characters long');
            }

            $userdata = [
                'user_pass'  => $password,
                'user_login' => $username,
                'user_email' => $email,
            ];

            if ($firstname) {
                $userdata['nickname']     = $firstname;
                $userdata['first_name']   = $firstname;
                $userdata['display_name'] = $firstname;
            }
            if ($lastname) {
                $userdata['last_name']     = $lastname;
                $userdata['display_name'] .= ' ' . $lastname;
            }

            $id = wp_create_user($username, $password, $email);

            add_filter('send_password_change_email', '__return_false'); // Added to prevent WP from sending the password change email (since WP 4.3)
            if ($id) {
                $userdata['ID'] = $id;
                wp_update_user($userdata);
                return $id;
            } else {
                return self::__setError('Unknown error');
            }
        }

        /**
         * Edits a WP User
         *
         * @param  integer $id          User ID
         * @param  string  $email       (optional)
         * @param  string  $password    (optional)
         * @param  string  $firstname   (optional)
         * @param  string  $lastname    (optional)
         * @param  string  $displayname (optional)
         * @param  string  $nickname    (optional)
         * @return integer User ID on success or False on failure
         */
        public function EditUser($id, $email = '', $password = '', $firstname = '', $lastname = '', $displayname = '', $nickname = '')
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            require_once ABSPATH . WPINC . '/pluggable.php';
            require_once ABSPATH . WPINC . '/registration.php';
            $id         += 0;
            $password    = wlm_trim($password);
            $email       = wlm_trim($email);
            $firstname   = wlm_trim($firstname);
            $lastname    = wlm_trim($lastname);
            $displayname = wlm_trim($displayname);
            $nickname    = wlm_trim($nickname);

            $passmin = $WishListMemberInstance->get_option('min_passlength');
            if (! $passmin) {
                $passmin = 8;
            }

            $user = $WishListMemberInstance->get_user_data($id);
            if (! $user) {
                return self::__setError('Invalid user ID');
            }
            if (! empty($email)) {
                if (! wlm_is_email($email)) {
                    return self::__setError('Invalid email address');
                }
                if ($email != $user->user_email && email_exists($email)) {
                    return self::__setError('Email address already in use');
                }
            }
            if (! empty($password) && strlen($password) < $passmin) {
                return self::__setError('Password has to be at least ' . $passmin . ' characters long');
            }

            if (! empty($email)) {
                $user->user_email = $email;
            }
            if (! empty($password)) {
                $user->user_pass = $password;
            }
            if (! empty($firstname)) {
                $user->first_name = $firstname;
            }
            if (! empty($lastname)) {
                $user->last_name = $lastname;
            }
            if (! empty($displayname)) {
                $user->display_name = $displayname;
            }
            if (! empty($nickname)) {
                $user->nickname = $nickname;
            }

            $data = (array) $user;
            $id   = wp_update_user($data);
            if ($id) {
                return $id;
            } else {
                return self::__setError('Unknown error');
            }
        }

        /**
         * Delete a WP User
         *
         * @param  integer $id       User ID
         * @param  integer $reassign (optional) Reassign posts and links to new User ID
         * @return boolean
         */
        public function DeleteUser($id, $reassign = null)
        {
            self::is_deprecated();
            require_once ABSPATH . '/wp-admin/includes/user.php';
            $id += 0;
            if (! is_null($reassign)) {
                $reassign += 0;
            }
            if ($id) {
                if (! $reassign) {
                    $ret = wp_delete_user($id);
                } else {
                    $ret = wp_delete_user($id, $reassign);
                }
            }
            if ($ret) {
                return true;
            } else {
                return self::__setError('Unknown error');
            }
        }

        /**
         * Get an array of Levels for a user.
         *
         * This enhanced version of GetUserLevels() has several advantages:
         *
         *   1. The existing WishList Member API version trigger a database read for every member checked. A list
         *      of 500 members adds 500 reads to the page.
         *   2. The existing WishList Member API version, like many of the functions, uses syntax that works
         *      in php 5 but not in php4.
         *   3. The $levels parameter allows you to restrict the return information to levels in a
         *      list. Get a list of key levels but omit special purchases.
         *   4. It will return a list of Level names OR SKUs.
         *   5. Optionally, Add Pending or Sequential status.
         *   6. Optionally, Get cancelled levels. And optionally with lineout tags.
         *
         * @param  integer $memid         Member ID
         * @param  string  $levels        Either 'all' or a comma delimited string of level names or skus to return.
         * @param  string  $return        Either 'names' or 'skus'.
         * @param  boolean $addpending    Optional. Default is false. True adds Pending status to array, if pending.
         * @param  boolean $addsequential Optional. Default is false. True adds Sequential status to array, if sequential.
         * @param  integer $cancelled     Optional. Default is no cancelled levels returned.
         *                  1=Names returned with lineout. 2=Names returned.
         * @return array Levels. Key: Level SKU. Value: Level SKU or Name.
         *
         * Overide of the memid so that when empty it looks for the current user. This only works with extentions
         * and not with remote calls.
         */
        public function GetUserLevels($memid = '', $levels = 'all', $return = 'names', $addpending = 0, $addsequential = 0, $cancelled = 0)
        {
            self::is_deprecated();
            global $WishListMemberInstance;

            if (empty($memid)) {
                $memid = wp_get_current_user();
                $memid = $memid->ID;
            }

            if (empty($memid)) {
                $ret = 'Member ID was not supplied or found';
                return $ret;
            }

            $all_levels = self::GetLevelArray($levels, 'names');
            $his_levels = $WishListMemberInstance->get_membership_levels($memid); // Array of skus.
            $ret        = [];

            if ($addpending && $WishListMemberInstance->is_pending($memid)) {
                $ret[] = 'Pending';
            }

            if ($addsequential && $WishListMemberInstance->is_sequential($memid)) {
                $ret[] = 'Sequential';
            }

            foreach ($all_levels as $key => $name) {
                if (in_array($key, $his_levels)) {
                    if ($cancelled) {
                        if (1 === (int) $cancelled && $WishListMemberInstance->level_cancelled($key, $memid)) {
                            $ret[ $key ] = ( 'names' === $return ) ? "<strike>$name</strike>" : $key;
                        } else {
                            $ret[ $key ] = ( 'names' === $return ) ? $name : $key;
                        }
                    } elseif (! $WishListMemberInstance->level_cancelled($key, $memid)) {
                        $ret[ $key ] = ( 'names' === $return ) ? $name : $key;
                    }
                }
            }
            return $ret;
        }

        /**
         * Adds the user to the specified levels
         *
         * @param  integer $user          User ID
         * @param  array   $levels        Membership Level IDs
         * @param  array   $txid          Transaction ID for integration with payment providers
         * @param  boolean $autoresponder Default FALSE. Set to TRUE if user is to be subscribed to autoresponder for the specified levels
         * @return boolean FALSE if the user ID is invalid or TRUE otherwise
         */
        public function AddUserLevels($user, $levels, $txid = '', $autoresponder = false)
        {
            self::is_deprecated();
            global $WishListMemberInstance, $log;
            // Check to see if the levels are passed as an array.
            if (! is_array($levels)) {
                $levels = explode(',', $levels);
            }

            // Retrieve levels for user.
            $ulevels = self::GetUserLevels($user, 'all', 'skus');
            if (false === $ulevels) {
                return self::__setError('Invalid User ID');
            }
            $alllevels = array_unique((array) array_merge((array) $ulevels, (array) $levels));
            $WishListMemberInstance->set_membership_levels($user, $alllevels, ['process_autoresponders' => $autoresponder]);

            // Save transaction ids.
            foreach ($levels as $level) {
                $WishListMemberInstance->set_membership_level_txn_id($user, $level, $txid);
            }
            return true;
        }

        /**
         * Removes the user from the specified levels
         *
         * @param  integer $user          User ID
         * @param  array   $levels        Membership Level IDs
         * @param  boolean $autoresponder Default TRUE. Set to FALSE to keep the user subscribed to the level's autoresponder
         * @return boolean FALSE if the user ID is invalid or TRUE otherwise
         */
        public function DeleteUserLevels($user, $levels, $autoresponder = true)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            // Retrieve levels for user.
            $ulevels = self::GetUserLevels($user, 'all', 'skus');
            if (false === $ulevels) {
                return self::__setError('Invalid User ID');
            }
            $levels = array_diff($ulevels, $levels);
            $WishListMemberInstance->set_membership_levels($user, $levels, ['process_autoresponders' => $autoresponder]);
            return true;
        }

        /**
         * Move Members To New Level.
         *
         * Can only "move" a member if they have only one level assigned,
         * because we otherwise don't know which to remove.
         *
         * @param  int or array $ids ID or array of IDs.
         * @param  string       $lev SKU or Name of Level to change Member to.
         * @return integer Count of IDs successfully changed.
         */
        public function MoveLevel($ids, $lev = '')
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $ids = (array) $ids;
            // $lev = (array)$lev;
            $lev   = self::GetLevelArray($lev, 'skus');
            $count = 0;
            foreach ($ids as $id) {
                $currlevels = self::GetUserLevels($id, 'all', 'skus');
                $newlevels  = array_unique(array_merge($currlevels, $lev));
                if (1 == count($currlevels) && 2 == count($newlevels)) {
                    $WishListMemberInstance->set_membership_levels($id, $lev, ['process_autoresponders' => true]);
                    ++$count;
                }
            }
            return $count;
        }

        /**
         * Cancel Members From a Level.
         *
         * @param  int or array $ids ID or array of IDs.
         * @param  string       $lev SKU or Name of Level to Cancel Member from.
         * @return integer Count of IDs successfully changed.
         */
        public function CancelLevel($ids, $lev = '')
        {
            self::is_deprecated();
            $ids = (array) $ids;
            // $lev = (array)$lev;
            $lev   = self::GetLevelArray($lev, 'skus');
            $count = 0;
            foreach ($lev as $one) {
                $count += self::_CancelLevel($one, $ids, true);
            }
            return $count;
        }

        /**
         * UnCancel Members From a Level.
         *
         * @param  int or array $ids ID or array of IDs.
         * @param  string       $lev SKU or Name of Level to UnCancel Member for.
         * @return integer Count of IDs successfully changed.
         */
        public function UnCancelLevel($ids, $lev = '')
        {
            self::is_deprecated();
            $ids = (array) $ids;
            // $lev = (array)$lev;
            $lev   = self::GetLevelArray($lev, 'skus');
            $count = 0;
            foreach ($lev as $one) {
                $count += self::_CancelLevel($one, $ids, false);
            }
            return $count;
        }

        /**
         * Used Internally.
         * Cancel/Uncancel Members From a Level.
         *
         * @param  string  $lev    SKU of Level to Cancel/UnCancel Member for.
         * @param  array   $uid    array of IDs.
         * @param  boolean $status True to Cancel, False to UnCancel
         * @return integer Count of IDs successfully changed.
         */
        public function _CancelLevel($level, $uid, $status)
        {
            global $WishListMemberInstance;
            $count1 = (int) $WishListMemberInstance->cancelled_member_ids($level, null, true);
            $WishListMemberInstance->level_cancelled($level, $uid, $status);
            $count2 = (int) $WishListMemberInstance->cancelled_member_ids($level, null, true);
            return abs($count1 - $count2);
        }

        /**
         * Retrieves the membership levels that have access to a page
         *
         * @param  integer $id Page ID
         * @return array
         */
        public function GetPageLevels($id)
        {
            self::is_deprecated();
            return self::__getContentLevels('pages', $id);
        }

        /**
         * Adds the page to the specified levels
         *
         * @param integer $id     Page ID
         * @param array   $levels Membership Level IDs
         */
        public function AddPageLevels($id, $levels)
        {
            self::is_deprecated();
            return self::__addContentLevels('pages', $id, (array) $levels);
        }

        /**
         * Removes the page from the specified levels
         *
         * @param integer $id     Page ID
         * @param array   $levels Membership Level IDs
         */
        public function DeletePageLevels($id, $levels)
        {
            self::is_deprecated();
            return self::__deleteContentLevels('pages', $id, (array) $levels);
        }

        /**
         * Retrieves the membership levels that have access to a post
         *
         * @param  integer $id Post ID
         * @return array
         */
        public function GetPostLevels($id)
        {
            self::is_deprecated();
            return self::__getContentLevels('posts', $id);
        }

        /**
         * Adds the post to the specified levels
         *
         * @param integer $id     Post ID
         * @param array   $levels Membership Level IDs
         */
        public function AddPostLevels($id, $levels)
        {
            self::is_deprecated();
            return self::__addContentLevels('posts', $id, (array) $levels);
        }

        /**
         * Removes the post from the specified levels
         *
         * @param integer $id     Post ID
         * @param array   $levels Membership Level IDs
         */
        public function DeletePostLevels($id, $levels)
        {
            self::is_deprecated();
            return self::__deleteContentLevels('posts', $id, (array) $levels);
        }

        /**
         * Retrieves the memebership levels that have access to a category
         *
         * @param  integer $id Category ID
         * @return array
         */
        public function GetCategoryLevels($id)
        {
            self::is_deprecated();
            return self::__getContentLevels('categories', $id);
        }

        /**
         * Adds the category to the specified levels
         *
         * @param integer $id     Category ID
         * @param array   $levels Membership Level IDs
         */
        public function AddCategoryLevels($id, $levels)
        {
            self::is_deprecated();
            return self::__addContentLevels('categories', $id, (array) $levels);
        }

        /**
         * Removes the category from the specified levels
         *
         * @param integer $id     Category ID
         * @param array   $levels Membership Level IDs
         */
        public function DeleteCategoryLevels($id, $levels)
        {
            self::is_deprecated();
            return self::__deleteContentLevels('categories', $id, (array) $levels);
        }

        /**
         * Retrieves the membership levels that have access to post/page comments
         *
         * @param  integer $id Post/Page ID
         * @return array
         */
        public function GetCommentLevels($id)
        {
            self::is_deprecated();
            return self::__getContentLevels('comments', $id);
        }

        /**
         * Adds the post/page comment to the specified levels
         *
         * @param integer $id     Post/Page ID
         * @param array   $levels Membership Level IDs
         */
        public function AddCommentLevels($id, $levels)
        {
            self::is_deprecated();
            return self::__addContentLevels('comments', $id, (array) $levels);
        }

        /**
         * Removes the post/page comment from the specified levels
         *
         * @param integer $id     Post/Page ID
         * @param array   $levels Membership Level IDs
         */
        public function DeleteCommentLevels($id, $levels)
        {
            self::is_deprecated();
            return self::__deleteContentLevels('comments', $id, (array) $levels);
        }

        // OTHER FUNCTIONS GO HERE.


        /**
         * ShowWLMWidget
         * Displays the WishList Member sidebar widget anywhere you want
         *
         * @param  array $widgetargs
         * @return none
         */
        public function ShowWLMWidget($widgetargs)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            $WishListMemberInstance->widget($args);
        }

        /**
         * Passes a string through the WishList Member Private Tags processor
         *
         * @param  string $content
         * @return string
         */
        public function PrivateTags($content)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            return $WishListMemberInstance->private_tags($content);
        }

        /**
         * Checks if the current page is a WishList Member Magic Page
         *
         * @return boolean
         */
        public function isMagicPage()
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            global $post;
            if ($post->ID == $WishListMemberInstance->magic_page(false)) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Set a post/page Protection to yes or no
         *
         * @param  int post/page id
         * @param  string "Y","N"
         * @return boolean false / true(meaning protected)
         */
        public function SetProtect($id, $value)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            return $WishListMemberInstance->protect($id, $value);
        }

        /**
         * Get a post/page Protection to yes or no
         *
         * @param  int post/page id
         * @return boolean false / true(meaning protected)
         */
        public function IsProtected($id)
        {
            self::is_deprecated();
            global $WishListMemberInstance;
            return $WishListMemberInstance->protect($id);
        }

        // INTERNAL FUNCTIONS GO HERE.


        /**
         * Internal Function - retrieves all leves
         *
         * @param  string  $type Content Type - categories | pages | posts | comments
         * @param  integer $id   Page/Post/Category ID
         * @return array Membership Levels
         */
        public function __getContentLevels($type, $id)
        {
            global $WishListMemberInstance;
            $levels = self::GetLevels();
            $ls     = $WishListMemberInstance->get_content_levels($type, $id);
            foreach ((array) $levels as $k => $level) {
                if ($level[ 'all' . $type ]) {
                    $ls[] = $k;
                }
            }
            $ls  = array_unique($ls);
            $ret = [];
            foreach ((array) $ls as $l) {
                $ret[ $l ] = $levels[ $l ]['name'];
            }
            return $ret;
        }

        /**
         * Adds content to the speicified membership levels
         *
         * @param  string  $type   Content Type - categories | pages | posts | comments
         * @param  integer $id     Content ID
         * @param  array   $levels Array of Membership Levels to add the content to
         * @return boolean Always TRUE
         */
        public function __addContentLevels($type, $id, $levels)
        {
            global $WishListMemberInstance;
            $oldlevels = $WishListMemberInstance->get_content_levels($type, $id);
            $levels    = array_unique(array_merge($oldlevels, $levels));
            $WishListMemberInstance->set_content_levels($type, $id, $levels);
            return true;
        }

        /**
         * Removes content from the speicified membership levels
         *
         * @param  string  $type   Content Type - categories | pages | posts | comments
         * @param  integer $id     Content ID
         * @param  array   $levels Array of Membership Levels to remove the content from
         * @return boolean Always TRUE
         */
        public function __deleteContentLevels($type, $id, $levels)
        {
            global $WishListMemberInstance;
            $oldlevels = $WishListMemberInstance->get_content_levels($type, $id);
            $levels    = array_diff($oldlevels, $levels);
            $WishListMemberInstance->set_content_levels($type, $id, $levels);
            return true;
        }

        /**
         * Sets the error message.  This message is used by the __remoteProcess method
         *
         * @param   string $err Error Message
         * @returns bool Always FALSE
         */
        public function __setError($err)
        {
            global $__WLM_APIError;
            $__WLM_APIError = $err;
            return false;
        }

        /**
         * Calls an API function and returns the results as serialized data
         *
         * @param  string $func   Function name to call
         * @param  string $key    API Key
         * @param  array  $params Parameter
         * @return string Serialized data
         */
        public static function __remoteProcess($func, $key, $params)
        {
            error_reporting(0);
            global $__WLM_APIError, $WishListMemberInstance;

            // Validate the key.
            $secret     = $WishListMemberInstance->GetAPIKey();
            $hashParams = [];
            foreach ($params as $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $hashParams[] = $value;
            }
            $myhash = md5($func . '__' . $secret . '__' . implode('|', $hashParams));
            if ($myhash != $key) {
                return serialize([false, 'AUTHORIZATION FAILED']);
            }

            // Check for valid function name. We don't allow functions starting with _ too.
            if ('_' == substr($func, 0, 1) || ! method_exists('WLMAPI', $func)) {
                return serialize([false, 'INVALID FUNCTION NAME']);
            }

            // Reset the Error Message.
            $__WLM_APIError = '';
            // Call the function.
            $result = call_user_func_array([new WLMAPI(), $func], (array) $params);

            if (false === $result) { // Is $result == false?  If so return the error message too.
                return serialize([false, $__WLM_APIError]);
            } else { // All is well, return the result.
                return serialize([true, $result]);
            }
        }

        /**
         * Used Internally.
         * Make members pending or nonsequential
         *
         * @param  array  $ids  array of IDs.
         * @param  string $type Which operation to perform: pending or nonsequential
         * @return integer Count of IDs.
         */
        public function _makeit($ids, $type = 'pending')
        {
            // Pending or nonsequential.
            global $WishListMemberInstance;
            $ids = (array) $ids;
            foreach ($ids as $id) {
                $get_levels = $WishListMemberInstance->get_membership_levels($id, false, true);

                foreach ($get_levels as $level) {
                    $value = $WishListMemberInstance->level_for_approval($level, $ids, true);
                }
            }

            $members = (array) $WishListMemberInstance->get_option('Members');

            if ($members[ $type ]) {
                $members[ $type ] = implode(',', array_unique(array_merge(explode(',', $members[ $type ]), $ids)));
            } else {
                $members[ $type ] = implode(',', array_unique($ids));
            }

            $WishListMemberInstance->save_option('Members', $members);
            return $value;
        }

        /**
         * Used Internally.
         * Remove pending or nonsequential designation from Mebers
         *
         * @param  array  $ids  array of IDs.
         * @param  string $type Which operation to perform: pending or nonsequential
         * @return integer Count of IDs.
         */
        public function _makeitnot($ids, $type = 'pending')
        {
            global $WishListMemberInstance;
            $ids = (array) $ids;
            foreach ($ids as $id) {
                $get_levels = $WishListMemberInstance->get_membership_levels($id, false, false);

                foreach ($get_levels as $level) {
                    $value = $WishListMemberInstance->level_for_approval($level, $ids, false);
                }
            }

            $members = (array) $WishListMemberInstance->get_option('Members');

            $m = ",{$members[$type]},";

            foreach ($ids as $key => $id) {
                $ids[ $key ] = ",{$id},";
            }
            $m                = str_replace($ids, ',', $m);
            $members[ $type ] = substr($m, 1, -1);
            $WishListMemberInstance->save_option('Members', $members);
            return $value;
        }

        private static function is_deprecated()
        {
            $debug = debug_backtrace(0, 2);
            error_log(sprintf(self::$deprecated, $debug[1]['file'], $debug[1]['line']));
        }
    }
}
