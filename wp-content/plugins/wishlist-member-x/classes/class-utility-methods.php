<?php

/**
 * Utility Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Utility Methods trait
 */
trait Utility_Methods
{
    use Utility_Methods_Deprecated;

    /**
     * Saves User ID based on Hash as an
     * 8-hour Transient option in WP
     *
     * @param string  $hash  IP Address.
     * @param integer $trans Unique identifier.
     */
    public function set_transient_hash($hash, $trans)
    {
        $name = $this->get_temp_dir() . '/wlm_th_' . $hash;
        $f    = fopen($name, 'w');
        fwrite($f, $trans);
        fclose($f);
    }

    /**
     * Retrieves User ID based on Transient Hash
     *
     * @return string
     */
    public function get_transient_hash()
    {
        $ckname = md5('wlm_transient_hash');
        $hashes = (array) wlm_getcookie($ckname);
        foreach ($hashes as $hash) {
            $name = $this->get_temp_dir() . '/wlm_th_' . $hash;
            if (file_exists($name)) {
                $trans = trim(file_get_contents($name));
                if ($trans) {
                    return $trans;
                }
            }
        }
        return '';
    }

    /**
     * Deletes the Transient Hash from WP Database
     * and clears the Transient Hash Cookie
     */
    public function delete_transient_hash()
    {
        $ckname = md5('wlm_transient_hash');
        $hashes = (array) wlm_getcookie($ckname);
        foreach ($hashes as $hash) {
            $name = $this->get_temp_dir() . '/wlm_th_' . $hash;
            if (file_exists($name)) {
                unlink($name);
            }
        }
        wlm_setcookie(md5('wlm_transient_hash'), '', time() - 3600, '/');
    }

    // Start: Form Values Functions.


    /**
     * Outputs checked if $value1 == $value2 or
     * if $value1 is in $value2
     *
     * @param string       $value1 Value to check.
     * @param string|array $value2 Value or array of values to check against.
     */
    public function checked($value1, $value2)
    {
        if (is_array($value2)) {
            if (in_array($value1, $value2)) {
                echo ' checked ';
            }
        } else {
            if ($value1 == $value2) {
                echo ' checked ';
            }
        }
    }

    /**
     * Outputs selected if $value1 == $value2 or
     * if $value1 is in $value2
     *
     * @param string       $value1 Value to check.
     * @param string|array $value2 Value or array of values to check against.
     * @param boolean      $strict TRUE if $value1 must be an exact of $value2.
     */
    public function selected($value1, $value2, $strict = false)
    {
        if (is_array($value2)) {
            if (in_array($value1, $value2, $strict)) {
                echo ' selected ';
            }
        } else {
            if ($strict) {
                if ($value1 === $value2) {
                    echo ' selected ';
                }
            } else {
                if ($value1 == $value2) {
                    echo ' selected ';
                }
            }
        }
    }

    /**
     * Outputs $value if it's not empty or $default if $value is empty
     *
     * @param string $value   Value.
     * @param string $default Default value.
     */
    public function value($value, $default)
    {
        if (! $value) {
            $value = $default;
        }
        echo esc_html($value);
    }

    // End: Form Values Functions.


    /**
     * Retrieve temp folder path
     *
     * @return string Path to temp folder
     */
    public function get_temp_dir()
    {
        if (function_exists('sys_get_temp_dir')) {
            $tmp = sys_get_temp_dir();
        } else {
            $x   = tempnam(rand(100000, 999999), 'wlm');
            $tmp = dirname($x);
            unlink($x);
        }
        return $tmp;
    }

    /**
     * Get WordPress timezone string
     *
     * @param  boolean $pretty True to returned "prettified" string.
     * @return string
     */
    public function get_wp_tzstring($pretty = false)
    {
        static $timezone_string, $pretty_timezone_string;
        if (is_null($timezone_string)) {
            $timezone_string = get_option('timezone_string');
            if (empty($timezone_string)) {
                $gmt_offset      = get_option('gmt_offset');
                $timezone_string = sprintf('UTC%+03d:%02d', $gmt_offset, abs($gmt_offset - (int) $gmt_offset) * 60);
            }
        }
        if ($pretty) {
            if (is_null($pretty_timezone_string)) {
                $pretty_timezone_string = explode('/', $timezone_string);

                $parts[] = array_shift($pretty_timezone_string);
                $parts[] = trim(implode(' - ', $pretty_timezone_string));
                $parts   = array_diff($parts, ['']);

                $pretty_timezone_string = str_replace('_', ' ', implode('/', $parts));
            }
            return $pretty_timezone_string;
        }
        return $timezone_string;
    }

    /**
     * Get WordPress timezone
     *
     * @param  boolean $utc_to_name True to Convert UTC to names.
     *                              False to convert UTC to GMT. Default false.
     * @return string               WordPress timezone
     */
    public function get_wp_timezone($utc_to_name = false)
    {
        $tzs = $this->get_wp_tzstring();
        if ('UTC' === substr($tzs, 0, 3)) {
            if ($utc_to_name) {
                $tzs = timezone_name_from_abbr('', str_replace('UTC', '', $tzs) * 3600, false);
            } else {
                $tzs = str_replace(['UTC', ':'], '', $tzs);
            }
        }
        return $tzs;
    }

    /**
     * Process _wlping_ data
     * (Terminates script when done)
     *
     * @param string $data JSON data.
     * @param string $hash Comma-separated list of hashes.
     */
    public function process_wlping($data, $hash)
    {
        // Clean up.
        $data = stripslashes($data);

        // Compute hash.
        $key    = $this->get_option('LicenseKey');
        $myhash = substr(md5($data . $key), -10);

        // Check hash.
        if (in_array($myhash, explode(',', $hash), true)) {
            // Decode data.
            $data = json_decode($data);

            // HQ: update license expiration.
            if (! empty($data->rd)) {
                $this->save_option('LicenseExpiration', $data->rd);
            }

            // Support: set flag to update support tickets.
            if (! empty($data->update_tickets_list)) {
                $this->save_option('do_update_tickets_list', 1);
            }

            // Support: set flag to update single ticket (i.e. for replies made from SD).
            if (! empty($data->update_ticket)) {
                $this->save_option('do_update_ticket_' . $data->update_ticket, 1);
            }
        }
        exit;
    }

    /**
     * Generate Password
     *
     * @param  integer $password_length (optional) default=min_passlength setting.
     * @param  boolean $strong          (optional) Generate strong password. Default false.
     * @return string Random password
     */
    public function pass_gen($password_length = null, $strong = false)
    {
        if (empty($password_length)) {
            $password_length = (int) $this->get_option('min_passlength') + 0;

            if ($password_length <= 3) {
                $password_length = 4;
            }
        }
        // If $password_length is still empty then we set it to 8.
        if (empty($password_length)) {
            $password_length = 8;
        }
        if ($strong) {
            do {
                $password = wlm_generate_password($password_length, true);
            } while (! wlm_check_password_strength($password));
        } else {
            $password = implode('', array_rand(array_flip(array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9))), $password_length));
        }
        return $password;
    }

    /**
     * Determines what array members have been removed and added
     *
     * @param array $new_array       New Array.
     * @param array $old_array       Old Array.
     * @param array $removed_members This variable will contain the levels that were removed (passed by reference).
     * @param array $new_members     This variable will contain the levels that were added (passed by reference).
     */
    public function array_diff($new_array, $old_array, &$removed_members, &$new_members)
    {
        $removed_members = array_diff((array) $old_array, (array) $new_array);
        $new_members     = array_diff((array) $new_array, (array) $old_array);
    }

    /**
     * Get Site Info
     *
     * @param  array $info Array of info to return.
     * @return array Array of info
     */
    public function get_site_info($info = null)
    {
        $data = [];
        if (is_null($info)) {
            return $data;
        }

        if (isset($info['send_wlmversion'])) {
            $data['wlmversion'] = $this->version;
        } else {
            $data['wlmversion'] = null;
        }

        if (isset($info['send_phpversion'])) {
            $data['phpversion'] = phpversion();
        } else {
            $data['phpversion'] = null;
        }

        if (isset($info['send_apachemod'])) {
            $data['apachemod'] = php_sapi_name();
        } else {
            $data['apachemod'] = null;
        }

        if (isset($info['send_webserver'])) {
            $data['webserver'] = wlm_server_data()['SERVER_SOFTWARE'];
        } else {
            $data['webserver'] = null;
        }

        if (isset($info['send_language'])) {
            $data['language'] = get_bloginfo('language');
        } else {
            $data['language'] = null;
        }

        if (isset($info['send_apiused'])) {
            $api_used = $this->get_option('WLMAPIUsed');
            if ($api_used) {
                $api_used = (array) wlm_maybe_unserialize($api_used);
            } else {
                $api_used = [];
            }
            $data['apiused'] = $api_used;
        } else {
            $data['apiused'] = null;
        }

        if (isset($info['send_payment'])) {
            $shoppingcart_used = $this->get_option('WLMShoppinCartUsed');
            if ($shoppingcart_used) {
                $shoppingcart_used = (array) wlm_maybe_unserialize($shoppingcart_used);
            } else {
                $shoppingcart_used = [];
            }
            $data['payment'] = $shoppingcart_used;
        } else {
            $data['payment'] = null;
        }

        if (isset($info['send_autoresponder'])) {
            $autoresponder_used = $this->get_option('Autoresponders');

            if ($autoresponder_used && ! empty($autoresponder_used['ARProvider'])) {
                $data['autoresponder'] = $autoresponder_used['ARProvider'];
            } else {
                $data['autoresponder'] = 'None';
            }
        } else {
            $data['autoresponder'] = null;
        }

        if (isset($info['send_webinar'])) {
            $webinars = $this->get_option('webinar');
            if ($webinars) {
                $data['webinar'] = implode(',', array_keys((array) $webinars));
            } else {
                $data['webinar'] = 'None';
            }
        } else {
            $data['webinar'] = null;
        }

        if (isset($info['send_nlevels'])) {
            $wpm_levels      = (array) $this->get_option('wpm_levels');
            $data['nlevels'] = count($wpm_levels);
        } else {
            $data['nlevels'] = null;
        }

        if (isset($info['send_nmembers'])) {
            $data['nmembers'] = count($this->member_ids());
        } else {
            $data['nmembers'] = null;
        }

        if (isset($info['send_sequential'])) {
            $wpm_levels = (array) $this->get_option('wpm_levels');
            $is_seq     = false;
            foreach ($wpm_levels as $level) {
                if ($level['upgradeTo'] && strlen($level['upgradeTo']) > 3) {
                    $is_seq = true;
                    break;
                }
            }
            if ($is_seq) {
                $data['sequential'] = 1;
            } else {
                $data['sequential'] = 0;
            }
        } else {
            $data['sequential'] = null;
        }

        if (isset($info['send_customreg'])) {
            $forms             = $this->get_custom_reg_forms();
            $data['customreg'] = count($forms);
        } else {
            $data['customreg'] = null;
        }

        return $data;
    }

    /**
     * Return an array of countries
     *
     * @return array
     */
    public function countries()
    {
        return require $this->plugin_dir3 . '/helpers/countries.php';
    }

    /**
     * Format date to WordPress settings
     *
     * @param  string  $date Date.
     * @param  integer $gmt  GMT offset in seconds.
     * @return string
     */
    public function format_date($date, $gmt = null)
    {
        if (is_null($gmt)) {
            $gmt = $this->gmt;
        }
        return date_i18n($this->get_date_time_format(), strtotime($date) + $gmt);
    }

    /**
     * Retrieves the date / time format from WordPress settings
     *
     * @return string
     */
    public function get_date_time_format()
    {
        return get_option('date_format') . ' ' . get_option('time_format');
    }

    /**
     * Deletes an entire directory tree
     *
     * @param string $dir Folder Name.
     */
    public function recursive_delete($dir)
    {
        $files = glob(trailingslashit($dir) . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->recursive_delete($file);
            } elseif (file_exists($file)) {
                @unlink($file);
            }
        }
        if (file_exists($dir) && is_dir($dir)) {
            @rmdir($dir);
        }
    }

    /**
     * Copies and entire directory tree
     *
     * @param string $source Source path.
     * @param string $dest   Destination path.
     */
    public function recursive_copy($source, $dest)
    {
        if ('/' !== substr($source, -1)) {
            $source .= '/';
        }
        $files = glob($source . '*', GLOB_MARK);
        if (! file_exists($dest) || ! is_dir($dest)) {
            mkdir($dest, 0777, true);
        }
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->recursive_copy($file, $dest . '/' . basename($file));
            } else {
                copy($file, $dest . '/' . basename($file));
            }
        }
    }

    /**
     * Get WishList Member announcement
     *
     * @return string
     */
    public function get_announcement()
    {

        $announcement = $this->get_option('hq_announcement');

        $hq_announcement = get_transient('wlm_hq_announcement');
        if (false === $hq_announcement) {
            $hq_announcement = $this->ReadURL('http://hq.wishlistproducts.com/announcement/?wlm');
            set_transient('wlm_hq_announcement', $hq_announcement, 86400);
        }

        if (( empty($announcement) && ! empty($hq_announcement) ) || ( $announcement !== $hq_announcement )) {
            global $current_user;
            $user_id      = $current_user->ID;
            $announcement = $hq_announcement;
            $this->save_option('hq_announcement', $announcement);
            delete_user_meta($user_id, 'dismiss_hq_notice');
        }
        return $announcement;
    }

    /**
     * Retrieves all user saved searches
     *
     * @global object $wpdb
     * @return array
     */
    public function get_all_saved_search()
    {
        global $wpdb;
        $option_values = [];
        $results       = $wpdb->get_results('SELECT `option_name`,`option_value` FROM `' . esc_sql($this->table_names->options) . '` WHERE `option_name` LIKE "SaveSearch%" OR `option_name` LIKE "SavedSearch%"');
        if ($results) {
            foreach ($results as $result) {
                $value['name']  = $result->option_name;
                $value['value'] = wlm_maybe_unserialize($result->option_value);
                array_push($option_values, $value);
            }
        }
        return $option_values;
    }

    /**
     * Retrieve Existing Saved Search
     *
     * @param  string $name Name of saved search.
     * @return array
     */
    public function get_saved_search($name)
    {
        global $wpdb;
        $option_values = [];
        if ($name) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT `option_name`,`option_value` FROM `' . esc_sql($this->table_names->options) . '` WHERE `option_name` = %s',
                    $name
                )
            );
        }
        if ($results) {
            $value         = wlm_maybe_unserialize($results[0]->option_value);
            $value['name'] = $results[0]->option_name;
            array_push($option_values, $value);
        }
        return $option_values;
    }

    /**
     * Sanitizes a string by replacing whitespace with hyphens,
     * and removing characters that are not from A-Z, a-z, 0-9, _ and -
     *
     * Also replaces duplicate hyphens with just a single hyphen
     *
     * @param  string  $string        String to sanitize.
     * @param  boolean $to_lower_case True to transfrom string to lowercase. Default true.
     * @return string
     */
    public function sanitize_string($string, $to_lower_case = true)
    {
        $string = preg_replace('/\s/', '-', $string);

        $string = preg_replace('/[^A-Za-z0-9_-]/', '', $string);
        if ($to_lower_case) {
            $string = strtolower($string);
        }
        $string = preg_replace('/-+/', '-', $string);
        return $string;
    }

    /**
     * Get matching levels
     *
     * @param  string $thefile File.
     * @param  string $mlevel  Level.
     * @return array
     */
    public function get_matching_levels($thefile, $mlevel)
    {
        wp_raise_memory_limit('get_matching_levels');
        $auto_detect_line_endings = ini_get('auto_detect_line_endings');
        @ini_set('auto_detect_line_endings', 1);
        wlm_set_time_limit(3600);
        $wpm_levels = $this->get_option('wpm_levels');
        $f          = fopen($thefile, 'r');
        $row        = 0;

        while (false !== ( $data = fgetcsv($f, 10000) )) {
            ++$row;
            echo esc_html(str_pad(' ', 2048));
            flush();
            list($uname, $fname, $lname, $email, $password, $m_level, $txn_id, $registration_date) = $data;
            wlm_post_data()['m_level'] = $m_level;
            $m_level          = explode(',', $m_level);
            foreach ($m_level as $k => $vl) {
                if ('level' !== $vl) {
                    $all_level[] = $vl;
                }
            }
        }
        $all_level = array_unique($all_level);
        foreach ($all_level as $id => $v) {
            foreach ($wpm_levels as $k => $vl) {
                if ($v === $vl['name']) {
                    $matchingname[]    = $v;
                    $all_level_match[] = $k;
                }
            }
        }

        if (count($matchingname) > 0) {
            $nonmatching = array_diff($all_level, $matchingname);
        } else {
            $nonmatching = $all_level;
        }

        fclose($f);
        @ini_set('auto_detect_line_endings', $auto_detect_line_endings);
        if ('match' === $mlevel) {
            return $nonmatching;
        } else {
            return $all_level_match;
        }
    }
}
