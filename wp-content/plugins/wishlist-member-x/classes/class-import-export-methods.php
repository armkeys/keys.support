<?php

/**
 * Import/Export Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Import/Export Methods trait
*/
trait Import_Export_Methods
{
    /**
     * Export/import configuration settings
     *
     * @param  array|string|null $restore_data Array, serialized string or null. Data to restore.
     * @return string                         Output message
     */
    public function export_configurations($restore_data = null)
    {
        global $wpdb;
        // Set array for internal pages.
        $arr_pages = [
            'non_members_error_page_internal',
            'wrong_level_error_page_internal',
            'membership_cancelled_internal',
            'membership_forapproval_internal',
            'membership_forconfirmation_internal',
            'after_registration_internal',
            'after_login_internal',
            'after_logout_internal',
            'unsubscribe_internal',
            'duplicate_post_error_page_internal',
            'scheduler_error_page_internal',
            'archiver_error_page_internal',
        ];
        // Set array for other configuration settings.
        $arr = [
            'non_members_error_page',
            'wrong_level_error_page',
            'membership_cancelled',
            'membership_forapproval',
            'membership_forconfirmation',
            'after_registration',
            'after_login',
            'after_logout',
            'unsubscribe',
            'recaptcha_public_key',
            'recaptcha_private_key',
            'min_passlength',
            'only_show_content_for_level',
            'hide_from_search',
            'protect_after_more',
            'auto_insert_more',
            'auto_insert_more_at',
            'exclude_pages',
            'default_protect',
            'folder_protection',
            'file_protection',
            'file_protection_ignore',
            'private_tag_protect_msg',
            'login_limit',
            'login_limit_error',
            'login_limit_notify',
            'notify_admin_of_newuser',
            'PreventDuplicatePosts',
            'members_can_update_info',
            'show_linkback',
            'affiliate_id',
        ];

        // If restore data contains a value, we will restore it.
        if (null !== $restore_data) {
            $data           = wlm_maybe_unserialize($restore_data);
            $data_keys      = array_keys($data);
            $config_options = array_merge($arr_pages, $arr);
            // Restore the settings of internal pages for configuration tab.
            foreach ((array) $data_keys as $option_name) {
                if (false !== array_search($option_name, $arr_pages, true)) { // Check if the key is in the array we set above.
                    $id = $this->restore_page_data($data[ $option_name ]); // Create a page from the to be used for the option.
                    if ($id > 0 || '' === $id) { // If the page is created succesfully.
                        $this->save_option($option_name, $id);
                        if ($id > 0) {
                            $page_data = get_page($id);
                            $out      .= "<span style='color:green'>Page Created[{$id}]: </span> '<i>{$page_data->post_title}</i>' for [{$option_name}].<br />";
                        }
                    } else { // If the page was not created and it should be created.
                        $out .= "<span style='color:red'>Warning </span>[{$id}]: Cannot create post for '{$option_name}'.<br />";
                    }
                } else { // If the key is different from the option's we had.
                    if (false === array_search($option_name, $config_options, true)) {
                        $out .= "<span style='color:red'>Warning: </span>'{$option_name}' is Invalid.<br />";
                    }
                }
            }

            // Restore the rest of the options for configuration tab.
            foreach ((array) $data_keys as $option_name) {
                if (false !== array_search($option_name, $arr, true)) { // Check if the key is in the array we set above.
                    $this->save_option($option_name, $data[ $option_name ]);
                } else { // If the key is different from the option's we had.
                    if (false === array_search($option_name, $config_options, true)) {
                        $out .= "<span style='color:red'>Warning: </span>'{$option_name}' is Invalid.<br />";
                    }
                }
            }
            $out = "<span style='color:green'>Complete</span><br /><blockquote>{$out}</blockquote>";
        } else {
            // Getting the pages.
            foreach ((array) $arr_pages as $option_name) {
                $data                = $this->get_page_data($this->get_option($option_name)); // Get the page data based on the id passed.
                $out[ $option_name ] = $data;
            }
            // Getting the rest of the options.
            foreach ((array) $arr as $option_name) {
                $out[ $option_name ] = $this->get_option($option_name);
            }
        }
        return wlm_maybe_serialize($out);
    }

    /**
     * Export/import Email Settings Tab
     *
     * @param  array|string|null $restore_data Array, serialized string or null. Data to restore.
     * @return string                         Output message
     */
    public function export_email_settings($restore_data = null)
    {
        // Set array for other configuration settings.
        $arr = [
            'email_sender_name',
            'email_sender_address',
            'email_per_minute',
            'register_email_subject',
            'register_email_body',
            'lostinfo_email_subject',
            'lostinfo_email_message',
            'newmembernotice_email_recipient',
            'newmembernotice_email_subject',
            'newmembernotice_email_message',
            'confirm_email_subject',
            'confirm_email_message',
            'requireadminapproval_email_subject',
            'requireadminapproval_email_message',
        ];
        // If restore data contains a value, we will restore it.
        if (null !== $restore_data) {
            $data      = wlm_maybe_unserialize($restore_data);
            $data_keys = array_keys($data);
            foreach ((array) $data_keys as $option_name) {
                if (false !== array_search($option_name, $arr, true)) { // Check if the key is in the array we set above.
                    $this->save_option($option_name, $data[ $option_name ]);
                } else {
                    $out .= "<span style='color:red'>Warning: </span>'{$option_name}' is Invalid.<br />";
                }
            }
            $out = "<span style='color:green'>Complete</span><br /><blockquote>{$out}</blockquote>";
        } else { // Else generate the settings array to be saved.
            foreach ((array) $arr as $option_name) {
                $out[ $option_name ] = $this->get_option($option_name);
            }
        }
        return wlm_maybe_serialize($out);
    }

    /**
     * Export/import Advance Settings Tab
     *
     * @param  array|string|null $restore_data Array, serialized string or null. Data to restore.
     * @return string                         Output message
     */
    public function export_advance_settings($restore_data = null)
    {
        // Set array for other configuration settings.
        $arr = [
            'sidebar_widget_css',
            'login_mergecode_css',
            'reg_form_css',
            'reg_instructions_new',
            'reg_instructions_new_noexisting',
            'reg_instructions_existing',
        ];
        // If restore data contains a value, we will restore it.
        if (null !== $restore_data) {
            $data      = wlm_maybe_unserialize($restore_data);
            $data_keys = array_keys($data);
            foreach ((array) $data_keys as $option_name) {
                if (false !== array_search($option_name, $arr, true)) { // Check if the key is in the array we set above.
                    $this->save_option($option_name, $data[ $option_name ]);
                } else {
                    $out .= "<span style='color:red'>Warning: </span>'{$option_name}' is Invalid.<br />";
                }
            }
            $out = "<span style='color:green'>Complete</span><br /><blockquote>{$out}</blockquote>";
        } else { // Else generate the settings array to be saved.
            foreach ((array) $arr as $option_name) {
                $out[ $option_name ] = $this->get_option($option_name);
            }
        }
        return wlm_maybe_serialize($out);
    }

    /**
     * Export/import Membership Levels
     *
     * @param  array|string|null $restore_data Array, serialized string or null. Data to restore.
     * @return string                         Output message
     */
    public function export_membership_levels($restore_data = null)
    {
        global $wpdb;
        // Set array for other configuration settings.
        $arr = [
            'wpm_levels',
            'regpage_before',
            'regpage_after',
        ];
        // If restore data contains a value, we will restore it.
        if ($restore_data) {
            $data      = wlm_maybe_unserialize($restore_data);
            $data_keys = array_keys($data);
            foreach ((array) $data_keys as $option_name) {
                if (false !== array_search($option_name, $arr, true)) { // Check if the key is in the array we set above.
                    $this->save_option($option_name, $data[ $option_name ]);
                } else {
                    $out .= "<span style='color:red'>Warning: </span>'{$option_name}' is Invalid.<br />";
                }
            }
            $out = "<span style='color:green'>Complete</span><br /><blockquote>{$out}</blockquote>";
        } else { // Else generate the settings array to be saved.
            foreach ((array) $arr as $option_name) {
                if (wlm_post_data()['export_registrationpage'] || 'wpm_levels' === $option_name) { // If including the before/after text on registration page.
                    $out[ $option_name ] = $this->get_option($option_name);
                }
            }
        }
        return wlm_maybe_serialize($out);
    }

    /**
     * Used to get the page data for Exporting the settings
     *
     * @param  integer $post_id Post ID.
     * @return string
     */
    public function get_page_data($post_id)
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->posts}` WHERE ID = %d", $post_id), ARRAY_A);
        if (empty($row)) {
            return '';
        }
        unset($out['ID']); // Take out ID field.
        return wlm_maybe_serialize($out);
    }

    /**
     * Used to restore the page data for importing the settings
     *
     * @param  string|array $data Page data as array or serialized string.
     * @return integer Page ID
     */
    public function restore_page_data($data)
    {
        global $wpdb;
        $table_data = wlm_maybe_unserialize($data);
        if (! is_array($table_data)) {
            return '';
        }
        foreach ((array) $table_data as $key => $value) { // Create the post data.
            $post_data[ $key ] = $value;
        }
        return wp_insert_post($post_data); // Create the post as page and return the id.
    }

    /**
     * Used to export the settings to file.
     * Sends output directly to browser as a downloadable file.
     */
    public function export_settings_to_file()
    {
        wp_raise_memory_limit('wlm_export_settings_to_file');
        if (wlm_post_data()['export_configurations']) {
            $arr_settings['export_configurations'] = $this->export_configurations();
        }
        if (wlm_post_data()['export_emailsettings']) {
            $arr_settings['export_emailsettings'] = $this->export_email_settings();
        }
        if (wlm_post_data()['export_advancesettings']) {
            $arr_settings['export_advancesettings'] = $this->export_advance_settings();
        }
        if (wlm_post_data()['export_membershiplevels']) {
            $arr_settings['export_membershiplevels'] = $this->export_membership_levels();
        }
        if (count($arr_settings) > 0) {
            $filename         = 'settings_' . gmdate('YmdHis') . '.wlm'; // Add date to  the filename.
            $settings_str     = wlm_maybe_serialize($arr_settings); // Obfuscate the settings.
            $settings['data'] = $settings_str;
            $settings['md5']  = md5($settings_str);
            $settings_data    = base64_encode(wlm_maybe_serialize($settings));
            header('Content-type:text/plain');
            header('Content-disposition: attachment; filename=' . $filename);
            flush();
            fwrite(WLM_STDOUT, $settings_data);
            flush();
            exit;
        }
    }

    /**
     * Restores settings from file.
     * Expects file to be uploaded.
     */
    public function restore_settings_from_file()
    {
        $settings_file = isset($_FILES['Settingsfile']) ? wlm_arrval($_FILES, 'Settingsfile') : [];
        if (! isset($settings_file['size']) || $settings_file['size'] <= 0) {
            $this->err = 'No import file found.';
            return;
        }
        $size     = $settings_file['size'];
        $tmp_name = $settings_file['tmp_name'];
        $type     = $settings_file['type'];
        $handle   = fopen($tmp_name, 'rb');
        $contents = fread($handle, $size);
        fclose($handle);
        if ('WLM3EXPORTFILE' === substr($contents, 0, 14)) {
            $settings = explode('|', $contents, 6);
            $json     = json_decode(base64_decode($settings[5]), true);
            if (strlen($settings[5]) === (int) $settings[3] && md5($settings[5] === $settings[4]) && is_array($json['levels']) && is_array($json['globals'])) {
                if (count($json['levels'])) {
                    $level_id   = time();
                    $url        = get_bloginfo('url');
                    $wpm_levels = $this->get_option('wpm_levels');
                    foreach ($json['levels'] as $level) {
                        $id                = ( $url !== $settings[2] || empty($level['id']) ) ? $level_id++ : $level['id'];
                        $level['id']       = $id;
                        $wpm_levels[ $id ] = $level;
                    }
                    $this->save_option('wpm_levels', $wpm_levels);
                }
                if (count($json['globals'])) {
                    foreach ($json['globals'] as $setting) {
                        $this->save_option($setting['option_name'], $setting['option_value']);
                    }
                }
                $this->msg = __('Settings imported.', 'wishlist-member');
            } else {
                $this->err = __('Settings file is corrupted.', 'wishlist-member');
            }
        } else {
            $settings = wlm_maybe_unserialize(base64_decode(wlm_trim($contents))); // Decoding obfuscation.
            if (is_array($settings) && array_key_exists('md5', $settings) && array_key_exists('data', $settings)) {
                if (md5($settings['data']) === $settings['md5']) {
                    $arr_settings = wlm_maybe_unserialize($settings['data']);
                    if (! empty($arr_settings)) {
                        $exported_settings = [];
                        if (array_key_exists('export_configurations', $arr_settings) && $arr_settings['export_configurations']) {
                            // Restoring  the Configuration Settings.
                            $export_configurations = $arr_settings['export_configurations'];
                            $this->export_configurations($export_configurations);
                            $exported_settings[] = 'Configurations';
                        }
                        if (array_key_exists('export_emailsettings', $arr_settings) && $arr_settings['export_emailsettings']) {
                            // Restoring  the Email Settings.
                            $export_emailsettings = $arr_settings['export_emailsettings'];
                            $this->export_email_settings($export_emailsettings);
                            $exported_settings[] = 'Email Settings';
                        }
                        if (array_key_exists('export_advancesettings', $arr_settings) && $arr_settings['export_advancesettings']) {
                            // Restoring  the Advance Settings.
                            $export_advancesettings = $arr_settings['export_advancesettings'];
                            $this->export_advance_settings($export_advancesettings);
                            $exported_settings[] = 'Advance Settings';
                        }
                        if (array_key_exists('export_membershiplevels', $arr_settings) && $arr_settings['export_membershiplevels']) {
                            // Restoring  the Membership Levels.
                            $export_membershiplevels = $arr_settings['export_membershiplevels'];
                            $this->export_membership_levels($export_membershiplevels);
                            $exported_settings[] = 'Membership Levels';
                        }
                        if (array_key_exists('export_scsettings', $arr_settings) && $arr_settings['export_scsettings']) {
                            // Restoring  the Payment Provider Integration Settings.
                            $export_scsettings = $arr_settings['export_scsettings'];
                            $this->ExportSCSettings($export_scsettings);
                            $exported_settings[] = 'Payment Provider Settings';
                        }
                        if (array_key_exists('export_arsettings', $arr_settings) && $arr_settings['export_arsettings']) {
                            // Restoring  the Autoresponder Integration Settings.
                            $export_arsettings = $arr_settings['export_arsettings'];
                            $this->ExportARSettings($export_scsettings);
                            $exported_settings[] = 'Autoresponder Settings';
                        }
                        if (count($exported_settings) > 0) {
                            $this->msg = implode(',', $exported_settings) . __(' settings has been imported', 'wishlist-member');
                        } else {
                            $this->err = __('The file you uploaded has no settings to be imported', 'wishlist-member');
                        }
                    } else { // If the file is empty or no file is selected yet.
                        $this->err = __('Empty File! Please choose another one', 'wishlist-member');
                    }
                } else {
                    $this->err = __('Corrupted File! Contents of the file has been changed', 'wishlist-member');
                }
            } else {
                $this->err = __('Cannot Read File. Contents of the file has been changed', 'wishlist-member');
            }
        }
    }
}
