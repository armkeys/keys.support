<?php

/**
 * WebHooks Integration Handler
 */

namespace WishListMember\Integrations\Others;

class CodeRedemption
{
    /**
     * Settings name as stored in wlm_options
     *
     * @var string
     */
    const SETTINGS_NAME = 'coderedemption_settings';

    /**
     * Unclaimed status
     *
     * @var integer
     */
    const STATUS_AVAILABLE = 0;
    /**
     * Claimed status
     *
     * @var integer
     */
    const STATUS_REDEEMED = 1;
    /**
     * Cancelled status
     *
     * @var integer
     */
    const STATUS_CANCELLED = 2;

    /**
     * Claim form error message
     *
     * @var string
     */
    public static $claim_form_error = '';
    public static $claim_form_ok    = '';

    /**
     * Code redemption settings
     *
     * @var null|array
     */
    private static $settings = null;

    /**
     * Update/Create campaign
     * Action: wp_ajax_wlm_coderedemption_save_campaign
     * Data expected in $_POST
     * - id (campaign ID)
     * - name
     * - description
     * - status
     * - access
     */
    public static function save_campaign()
    {
        $data = wp_parse_args(
            wlm_post_data(true),
            [
                'id'          => '',
                'name'        => '',
                'description' => '',
                'status'      => self::STATUS_AVAILABLE,
                'access'      => [],
            ]
        );

        if (empty($data['id'])) {
            wp_send_json_error(['msg' => __('Invalid campaign ID')]);
            exit;
        }
        if (empty($data['name'])) {
            wp_send_json_error(['msg' => __('No campaign name specified')]);
            exit;
        }

        // Ensure access is a clean array.
        if (! is_array($data['access'])) {
            $data['access'] = [];
        }

        array_walk(
            $data['access'],
            function (&$value) {
                if (! wlm_arrval($value, 'levels')) {
                    $value = '';
                }
            }
        );
        $data['access'] = array_diff($data['access'], ['']);

        $settings = self::get_settings();

        $campaign = [
            'id'          => $data['id'],
            'name'        => $data['name'],
            'description' => $data['description'],
            'access'      => $data['access'],
            'status'      => $data['status'],
        ];

        if (! is_numeric($campaign['id'])) {
            $campaign['id'] = time();
            while (wlm_arrval($settings, 'campaigns', $campaign['id'])) {
                ++$campaign['id'];
            }
            $campaign['codes'] = [];
        } else {
            $campaign['codes'] = wlm_arrval($settings, 'campaigns', $campaign['id'], 'codes') ? wlm_arrval('lastresult') : [];
        }

        $settings['campaigns'][ $campaign['id'] ] = $campaign;

        self::save_settings($settings);
        wp_send_json_success(
            [
                self::SETTINGS_NAME => self::populate_quantity($settings),
                'id'                => $campaign['id'],
            ]
        );
    }

    /**
     * Deletes a campaign
     * Action: wp_ajax_wlm_coderedemption_delete_campaign
     * Data expected in $_POST
     * - campaign-id
     */
    public static function delete_campaign()
    {
        global $wpdb;
        $settings = self::get_settings();
        if (is_array(wlm_arrval($settings, 'campaigns'))) {
            $cid = wlm_post_data()['campaign-id'];
            unset($settings['campaigns'][ $cid ]);
            $wpdb->delete(self::table_name(), ['campaign_id' => $cid]);
        } else {
            $settings['campaigns'] = [];
        }
        self::save_settings($settings);
        wp_send_json_success([self::SETTINGS_NAME => self::populate_quantity($settings)]);
    }

    /**
     * Generates codes for a campaign
     * Action: wp_ajax_wlm_coderedemption_generate_codes
     * Data expected in $_POST
     * - id (campaign ID)
     * - format [uuid4, sha1, md5, random]
     * - quantity
     */
    public static function generate_codes()
    {
        global $wpdb;

        $format   = (string) wlm_post_data()['format'];
        $quantity = (int) wlm_post_data()['quantity'];
        $id       = (int) wlm_post_data()['id'];

        if (! in_array($format, ['uuid4', 'sha1', 'md5', 'random'])) {
            wp_send_json_error(['msg' => 'Invalid format']);
            exit;
        }

        if ($qty < 0) {
            wp_send_json_error(['msg' => 'Invalid quantity']);
            exit;
        }

        $settings = self::get_settings();

        if (! wlm_arrval($settings, 'campaigns', $id)) {
            wp_send_json_error(['msg' => __('Invalid campaign', 'wishlist-member')]);
            exit;
        }

        if (! is_array(wlm_arrval($settings, 'campaigns', $id, 'codes'))) {
            $settings['campaigns'][ $id ]['codes'] = [];
        }

        switch ($format) {
            case 'uuid4':
                $code_function = function () {
                    return sprintf(
                        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        wp_rand(0, 0xffff),
                        wp_rand(0, 0xffff),
                        wp_rand(0, 0xffff),
                        wp_rand(0, 0x0fff) | 0x4000,
                        wp_rand(0, 0x3fff) | 0x8000,
                        wp_rand(0, 0xffff),
                        wp_rand(0, 0xffff),
                        wp_rand(0, 0xffff)
                    );
                };
                break;
            case 'md5':
                $code_function = function () {
                    return md5(wp_rand());
                };
                break;
            case 'sha1':
                $code_function = function () {
                    return sha1(wp_rand());
                };
                break;
            case 'random':
                $code_function = function () {
                    do {
                        $code = wp_generate_password(32, false);
                    } while (! preg_match('/(?=[0-9a-zA-Z]+)(?=[^a-z]*[a-z])(?=[^A-Z]*[A-Z])(?=[^0-9]*[0-9])/', $code));
                    return $code;
                };
                break;
        }

        $codes = [];

        for ($i = 0; $i < $quantity; $i++) {
            do {
                $code = $code_function();
            } while (in_array($code, $codes, true));
            $codes[] = [$id, $code];
        }

        $codes = array_chunk($codes, 50);
        foreach ($codes as $chunk) {
            $wpdb->query(
                $wpdb->prepare(
                    'INSERT INTO `' . esc_sql(self::table_name()) . '` (`campaign_id`, `code`) VALUES (' . implode('),(', array_fill(0, count($chunk), '%d,%s')) . ')',
                    array_values(array_merge(...$chunk))
                )
            );
        }

        wp_send_json_success([self::SETTINGS_NAME => self::populate_quantity($settings)]);
    }

    /**
     * Searches for campaign codes
     * Action: wp_ajax_wlm_coderedemption_search_codes
     * Data expected in $_POST
     * - id (campaign ID)
     * - search
     * - status ['' (all), 0 (available), 1 (redeemed), 2 (cancelled)]
     */
    public static function search_codes()
    {
        global $wpdb;
        extract(
            wp_parse_args(
                wlm_post_data(true),
                [
                    'id'     => 0,
                    'search' => '',
                    'status' => '',
                ]
            )
        );

        if (empty($id)) {
            wp_send_json_success(['results' => []]);
        }
        if ('' === $status) {
            $status = '0,1,2';
        } else {
            $status = intval($status);
        }
        $search = '%' . $wpdb->esc_like($search) . '%';
        wp_send_json_success(
            [
                'results' => wlm_or(
                    $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT `code`,`status`,`claimed`,`cancelled`,`user_id` FROM `%0s` WHERE `campaign_id`=%d AND `code` LIKE %s AND `status` IN (%0s)',
                            self::table_name(),
                            $id,
                            $search,
                            $status
                        ),
                        ARRAY_N
                    ),
                    []
                ),
            ]
        );
    }

    /**
     * Import codes to campaign
     * Action: wp_ajax_wlm_coderedemption_import_codes
     * Data expected in $_POST
     * - id (campaign ID)
     * - option ['skip', 'update', 'replace']
     * Data expected in $_FILES
     * - file
     */
    public static function import_codes()
    {
        global $wpdb;

        // Validate file type.
        if (! preg_match('/^text|excel/i', wlm_arrval($_FILES, 'file', 'type'))) {
            wp_send_json_error(__('Invalid file type', 'wishlist-member'));
            exit;
        }
        // Validate file upload.
        $file = wlm_arrval($_FILES, 'file', 'tmp_name');
        if (! is_uploaded_file($file)) {
            wp_send_json_error(__('No file uploaded', 'wishlist-member'));
            exit;
        }
        // Validate campaign id.
        $campaign_id = (int) wlm_post_data()['id'];
        if (empty($campaign_id)) {
            wp_send_json_error(__('Invalid Campaign ID', 'wishlist-member'));
            exit;
        }
        // Validate import option.
        $import_option = wlm_post_data()['option'];
        if (! in_array($import_option, ['skip', 'update', 'replace'])) {
            wp_send_json_error(__('Invalid import option', 'wishlist-member'));
            exit;
        }

        $fh       = fopen($file, 'r');
        $rows     = 0;
        $inserted = 0;
        $updated  = 0;
        $errors   = 0;

        if ('replace' === $import_option) {
            // Replace all codes.
            $wpdb->query($wpdb->prepare('DELETE FROM `%0s` WHERE `campaign_id`=%d', self::table_name(), $campaign_id));
            // Change import option to 'skip'
            $import_option = 'skip';
        }

        while ($line = fgetcsv($fh)) {
            ++$rows;
            list( $code, $status, $email ) = $line;

            // Replace status with integer value.
            switch (strtolower($status)) {
                case 'redeemed':
                    $status = self::STATUS_REDEEMED;
                    break;
                case 'cancelled':
                case 'canceled':
                    $status = self::STATUS_CANCELLED;
                    break;
                default:
                    $status = self::STATUS_AVAILABLE;
            }

            $code_status = $status;

            switch ($import_option) {
                case 'skip': // option: skip duplicates
                    // If ( $status && $email ) {
                        // Todo get user id.
                    // }
                    if (
                        $wpdb->insert(
                            self::table_name(),
                            [
                                'campaign_id' => $campaign_id,
                                'code'        => $code,
                                'status'      => $status,
                            ],
                            ['%d', '%s', '%d']
                        )
                    ) {
                        ++$inserted;
                    } else {
                        ++$errors;
                    }
                    break;
                case 'update': // option: update duplicates
                    switch ($wpdb->query($wpdb->prepare('INSERT INTO `%0s` (`campaign_id`,`code`,`status`) VALUES (%d,%s,%s) ON DUPLICATE KEY UPDATE `status`=%d', self::table_name(), $campaign_id, $code, $status, $status))) {
                        case 2:
                            ++$updated;
                            if ($code_status == 2) {
                                self::cancel_uncancel_code($campaign_id, $code, true, true);
                            } else {
                                self::cancel_uncancel_code($campaign_id, $code, false, true);
                            }
                            break;
                        case 1:
                            ++$inserted;
                            break;
                        default:
                            ++$errors;
                    }
                    break;
            }
        }

        $stats = [$inserted, $updated, $rows, $errors];
        wp_send_json_success(
            [
                'import_stats'      => $stats,
                self::SETTINGS_NAME => self::get_settings(true),
            ]
        );
    }

    /**
     * Export codes as CSV
     * Action: wp_ajax_wlm_coderedemption_export_codes
     * Data expected in $_POST
     * - id (campaign ID)
     * - status ['' (all), 0 (available), 1 (redeemed), 2 (cancelled)]
     */
    public static function export_codes()
    {
        global $wpdb;
        // Normalize arguments.
        $data   = wp_parse_args(
            wlm_post_data(true),
            [
                'id'     => 0,
                'status' => '',
            ]
        );
        $id     = $data['id'];
        $status = $data['status'];

        // Validate campaign ID.
        if (empty($id)) {
            wp_send_json_error(__('Invalid Campaign ID', 'wishlist-member'));
            exit;
        }

        // Validate status.
        if (! in_array($status, ['', self::STATUS_AVAILABLE, self::STATUS_REDEEMED, self::STATUS_CANCELLED])) {
            wp_send_json_error(__('Invalid Status', 'wishlist-member'));
            exit;
        }

        // Prepare filename.
        switch ($status) {
            case self::STATUS_AVAILABLE:
                $xstat = 'available';
                break;
            case self::STATUS_REDEEMED:
                $xstat = 'redeemed';
                break;
            case self::STATUS_CANCELLED:
                $xstat = 'cancelled';
                break;
            default:
                $xstat = 'all';
                break;
        }
        $filename = sprintf('campaign-%d-%s-%s.csv', $id, $xstat, current_time('Ymd-His'));

        // Set CSV header.
        header('Content-Disposition: attachment;filename=' . $filename);
        header('Content-Type: text/csv');

        $tmp_file = wp_tempnam();

        // Get codes.
        if (in_array($status, ['', self::STATUS_CANCELLED, self::STATUS_REDEEMED])) {
            // Get cancelled, redeemed or all codes.
            foreach (
                $wpdb->get_results(
                    $wpdb->prepare(
                        'SELECT `code`,
				CASE
					WHEN `status`=%d THEN "Cancelled"
					WHEN `status`=%d THEN "Redeemed"
					ELSE "" END AS `status`,
				`ue`.`user_email` AS `email`,
				`ufn`.`meta_value` AS `first_name`,
				`uln`.`meta_value` AS `last_name`
				FROM `' . esc_sql(self::table_name()) . '` `cr`
					LEFT JOIN `' . $wpdb->users . '` `ue` ON `cr`.`user_id`=`ue`.`ID`
					LEFT JOIN `' . $wpdb->usermeta . '` `ufn` ON `cr`.`user_id`=`ufn`.`user_id` AND `ufn`.`meta_key`="first_name"
					LEFT JOIN `' . $wpdb->usermeta . '` `uln` ON `cr`.`user_id`=`uln`.`user_id` AND `uln`.`meta_key`="last_name"
				WHERE `cr`.`campaign_id`=%d AND `cr`.`status` LIKE %s',
                        self::STATUS_CANCELLED,
                        self::STATUS_REDEEMED,
                        $id,
                        '' !== $status ? $status : '%'
                    ),
                    ARRAY_A
                ) as $row
            ) {
                self::export_codes_row($row, $tmp_file);
            }
        } else {
            // Get available codes.
            foreach (
                $wpdb->get_results(
                    $wpdb->prepare(
                        'SELECT `code` FROM `' . esc_sql(self::table_name()) . '` `cr` WHERE `cr`.`campaign_id`=%d AND `cr`.`status`=%d',
                        $id,
                        $status
                    ),
                    ARRAY_A
                ) as $row
            ) {
                self::export_codes_row($row, $tmp_file);
            }
        }

        readfile($tmp_file);
        unlink($tmp_file);
        // Done.
        exit;
    }

    private static function export_codes_row($row, $tmp_file)
    {
        $output = '';
        foreach ($row as &$field) {
            $output .= ',"' . str_replace('"', '""', $field) . '"';
        }
        unset($field);
        file_put_contents($tmp_file, preg_replace(['/^,/', '/,+$/'], '', $output) . "\n", FILE_APPEND);
    }

    /**
     * Delete single code from campaign
     * Action: wp_ajax_wlm_coderedemption_delete_code
     * Data expected in $_POST
     * - id (campaign ID)
     * - code
     */
    public static function delete_code()
    {
        global $wpdb;
        if ($wpdb->query($wpdb->prepare('DELETE FROM `%0s` WHERE `campaign_id`=%d AND `code`=%s', self::table_name(), wlm_post_data()['id'], wlm_post_data()['code']))) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Cancel single code from campaign
     * Action: wp_ajax_wlm_coderedemption_cancel_code
     * Data expected in $_POST
     * - id (campaign ID)
     * - code
     */
    public static function cancel_code()
    {
        wp_send_json(
            [
                'success' => self::cancel_uncancel_code(wlm_post_data()['id'], wlm_post_data()['code'], true),
            ]
        );
    }

    /**
     * Unancel single code from campaign
     * Action: wp_ajax_wlm_coderedemption_uncancel_code
     * Data expected in $_POST
     * - id (campaign ID)
     * - code
     */
    public static function uncancel_code()
    {
        wp_send_json(
            [
                'success' => self::cancel_uncancel_code(wlm_post_data()['id'], wlm_post_data()['code'], false),
            ]
        );
    }

    /**
     * Set code status to either cancelled (2) or redeemed (1) based on the $cancel_state
     * Also set the cancelled state of a user's levels that have transaction IDs that match the code
     *
     * @param  integer $campaign_id  Campaign ID
     * @param  string  $code         Code
     * @param  boolean $cancel_state Cancel state
     * @return boolean
     */
    private static function cancel_uncancel_code($campaign_id, $code, $cancel_state, $process_import = false)
    {
        global $wpdb;
        // Update the code's status.
        if (
            $wpdb->update(
                self::table_name(),
                ['status' => $cancel_state ? self::STATUS_CANCELLED : self::STATUS_REDEEMED],
                [
                    'campaign_id' => $campaign_id,
                    'code'        => $code,
                ]
            ) || $process_import
        ) {
            // Cancel/uncancel all levels that have transaction ids that match our code.
            $member_id = $wpdb->get_var($wpdb->prepare('SELECT `user_id` FROM %0s WHERE `campaign_id`=%d AND `code`=%s', self::table_name(), $campaign_id, $code));
            $member    = wlmapi_get_member($member_id);

            // Membership levels.
            foreach ((array) wlm_arrval($member, 'member', 0, 'Levels') as $level) {
                if (wlm_arrval($level, 'TxnID') == 'CODE*' . $code) {
                    wlmapi_update_level_member_data(
                        wlm_arrval($level, 'Level_ID'),
                        $member_id,
                        [
                            'SendMailPerLevel' => 1,
                            'Cancelled'        => (bool) $cancel_state,
                        ]
                    );
                }
            }

            // Pay per posts.
            $member = new \WishListMember\User($member_id);
            if ($member->ID) {
                if ((bool) $cancel_state) {
                    // Remove pay per posts on code cancellation.
                    $remove = $member->get_payperposts_by_transaction_ids(['CODE*' . $code]);
                    if ($remove) {
                        $remove = array_map(
                            function ($id) {
                                return 'payperpost-' . $id;
                            },
                            $remove
                        );
                        $member->remove_payperposts($remove);
                    }
                } else {
                    // Add pay per posts back on code uncancellation.
                    $result = self::claim_code($code, $campaign_id, $member_id, true);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Shortcode handler to generate code claim form
     * Shortcode: wlm_coderedemption
     *
     * @param  array $atts shortcode attributes
     * @return string      Code claim form
     */
    public static function shortcode_coderedemption($atts)
    {
        $atts = shortcode_atts(
            [
                'campaign'    => 0,
                'button_text' => __('Submit', 'wishlist-member'),
                'login_text'  => __('Login', 'wishlist-member'),
            ],
            $atts,
            'wlm_coderedemption'
        );

        if (! wlm_arrval(self::get_settings(), 'campaigns', $atts['campaign'])) {
            // Translators: 1: Campaign ID.
            return current_user_can('manage_options') ? sprintf(__('Invalid campaign ID: %s', 'wishlist-member'), $atts['campaign']) : '';
        }

        $fields = self::$claim_form_error ? '<div class="wlm3-profile-error"><p>' . self::$claim_form_error . '</p></div>' : '';
        if (1 == wlm_get_data()['wlm-code-redeemed']) {
            $fields = '<div class="wlm3-profile-ok"><p>' . esc_html__('Code successfully claimed.', 'wishlist-member') . '</p></div>';
        }

        $fields .= wp_nonce_field('wlm_coderedemption_claim', 'wlm_coderedemption_nonce', true, false);
        $fields .= wlm_form_field(
            [
                'name'  => 'campaign_id',
                'type'  => 'hidden',
                'value' => (int) $atts['campaign'],
            ]
        );

        if (! is_user_logged_in()) {
            $fields .= wlm_form_field(
                [
                    'name'  => 'first_name',
                    'value' => wlm_or(wlm_post_data()['first_name'], ''),
                    'label' => __(
                        'First Name',
                        'wishlist-member'
                    ),
                ]
            );
            $fields .= wlm_form_field(
                [
                    'name'  => 'last_name',
                    'value' => wlm_or(wlm_post_data()['last_name'], ''),
                    'label' => __(
                        'Last Name',
                        'wishlist-member'
                    ),
                ]
            );
            $fields .= wlm_form_field(
                [
                    'name'  => 'email',
                    'value' => wlm_or(wlm_post_data()['email'], ''),
                    'label' => __(
                        'Email',
                        'wishlist-member'
                    ),
                ]
            );
            $fields .= wlm_form_field(
                [
                    'name'  => 'username',
                    'value' => wlm_or(wlm_post_data()['username'], ''),
                    'label' => __(
                        'Username',
                        'wishlist-member'
                    ),
                ]
            );
            $fields .= wlm_form_field(
                [
                    'name'  => 'password',
                    'type'  => 'password_metered',
                    'label' => __(
                        'Password',
                        'wishlist-member'
                    ),
                ]
            );
        }
        $fields .= wlm_form_field(
            [
                'name'  => 'code',
                'value' => wlm_or(wlm_post_data()['code'], ''),
                'label' => __(
                    'Code',
                    'wishlist-member'
                ),
            ]
        );
        $fields .= wlm_form_field(
            [
                'type'  => 'submit',
                'name'  => 'wlm_coderedemption_submit',
                'value' => __(
                    $atts['button_text'],
                    'wishlist-member'
                ),
            ]
        );
        if (! is_user_logged_in()) {
            $fields .= wlm_form_field(
                [
                    'type' => 'paragraph',
                    'text' => sprintf('<a href="%s">%s</a>', wp_login_url('wlm_coderedemption/' . get_permalink()), $atts['login_text']),
                ]
            );
        }

        $form = '<form method="POST" action="' . remove_query_arg('wlm-code-redeemed') . '"><div class="wlm3-form">' . $fields . '</div></form>';
        return $form;
    }

    /**
     * Add Code Redemption shortcodes to manifest
     * Filter: wishlistmember_integration_shortcodes
     *
     * @param  array $manifest Shortcode manifest
     * @return array Updated Shortcode manifest
     */
    public static function add_shortcode_to_manifest($manifest)
    {
        // Generate campaign dropdown options.
        $campaigns = [];
        foreach (wlm_arrval(self::get_settings(), 'campaigns') as $campaign) {
            $campaigns[ wlm_arrval($campaign, 'id') ] = ['label' => wlm_arrval($campaign, 'name')];
        }

        // Add wlm_coderedemption to manifest.
        $manifest['wlm_coderedemption'] = [
            'label'      => 'Code Redemption',
            'attributes' => [
                'campaign'    => [
                    'columns'     => 12,
                    'label'       => __('Campaign', 'wishlist-member'),
                    'type'        => 'select',
                    'options'     => $campaigns,
                    'placeholder' => __('Choose a campaign', 'wishlist-member'),
                ],
                'button_text' => [
                    'dependency'  => '[name="campaign"] option:selected[value!=""]',
                    'columns'     => 6,
                    'label'       => __('Button Text', 'wishlist-member'),
                    'placeholder' => __('Submit', 'wishlist-member'),
                ],
                'login_text'  => [
                    'dependency'  => '[name="campaign"] option:selected[value!=""]',
                    'columns'     => 6,
                    'label'       => __('Login Text', 'wishlist-member'),
                    'placeholder' => __('Login', 'wishlist-member'),
                ],
            ],
        ];

        return $manifest;
    }

    /**
     * Login redirect handler to allow our link to redirect back
     * to the page with the code redemption form
     *
     * @param  string $url
     * @return string
     */
    public static function login_redirect($url)
    {
        $parts = explode('/', wlm_post_data()['redirect_to'], 2);
        return 'wlm_coderedemption' === $parts[0] ? $parts[1] : $url;
    }

    /**
     * Claim code submitted through the form generated by the wlm_coderedemption shortcode
     * Action: wp_loaded
     */
    public static function claim_code_from_form()
    {
        if (
            empty(wlm_post_data()['wlm_coderedemption_submit'])
            || is_admin()
            || ! isset(wlm_post_data()['wlm_coderedemption_nonce'])
            || ! wp_verify_nonce(wlm_post_data()['wlm_coderedemption_nonce'], 'wlm_coderedemption_claim')
        ) {
            return;
        }

        $code = self::get_code(wlm_post_data()['code'], wlm_post_data()['campaign_id']);
        if (! $code || $code->status) {
            self::$claim_form_error = __('Invalid code', 'wishlist-member');
            return;
        }

        $user_id = get_current_user_id();

        if ($user_id) {
            $new_user = false;
        } else {
            // Not logged-in, let's create a user.
            $new_user = true;

            // Scrutinize password.
            $password = trim(wlm_post_data()['password']);
            $x        = wlm_scrutinize_password($password);
            if (true !== $x) {
                self::$claim_form_error = $x;
                return;
            }

            // Check username.
            $username = trim(wlm_post_data()['username']);
            if (! $username) {
                self::$claim_form_error = __('Username required', 'wishlist-member');
                return;
            }
            if (username_exists($username)) {
                self::$claim_form_error = __('Username already in use', 'wishlist-member');
                return;
            }

            // Check email.
            $email = trim(wlm_post_data()['email']);
            if (! $email) {
                self::$claim_form_error = __('Email required', 'wishlist-member');
                return;
            }
            if (! is_email($email)) {
                self::$claim_form_error = __('Invalid email address', 'wishlist-member');
                return;
            }
            if (email_exists($email)) {
                self::$claim_form_error = __('Email already in use', 'wishlist-member');
                return;
            }

            // Prepare user data.
            $user_id = [
                'user_login' => wlm_post_data()['username'],
                'user_pass'  => wlm_post_data()['password'],
                'user_email' => wlm_post_data()['email'],
                'first_name' => wlm_post_data()['first_name'],
                'last_name'  => wlm_post_data()['last_name'],
            ];
        }

        // Claim code.
        $code = self::claim_code(wlm_post_data()['code'], wlm_post_data()['campaign_id'], $user_id);
        if (is_object($code)) {
            if (is_array($user_id)) {
                // Login.
                wishlistmember_instance()->wpm_auto_login($code->user_id);
            }
            // Get level to use for after reg redirect.
            $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
            usort(
                $code->access,
                function ($a, $b) use ($wpm_levels) {
                    $a = wlm_arrval($wpm_levels, $a, 'levelOrder');
                    $b = wlm_arrval($wpm_levels, $b, 'levelOrder');
                    if ($a == $b) {
                        return 0;
                    }
                    return $a < $b ? -1 : 1;
                }
            );
            // Redirect to after reg page.
            wp_redirect(add_query_arg('wlm-code-redeemed', 1, wishlistmember_instance()->get_after_reg_redirect(array_pop($code->access))));
            exit;
        } else {
            // Claim failed.
            self::$claim_form_error = $code;
            return;
        }
    }

    /**
     * Claims campaign code for user
     *
     * @param  string        $code        Code
     * @param  integer       $campaign_id Campaign ID
     * @param  integer|array $user        User ID or wp_insert_user compatible data
     * @param  boolean       $reuse       (Optional) Re-use code if it's already used
     * @return object|string Code object on success or error message on failure
     */
    private static function claim_code($code, $campaign_id, $user, $reuse = false)
    {

        // Validate code.
        $code = self::get_code($code, $campaign_id);
        if (! $code || $code->status) {
            if ($code->status && ! $reuse) {
                // Translators: 1: Redemption Code.
                return sprintf(__('Invalid code: %s', 'wishlist-member'), $code->code);
            }
        }

        // Validate campaign.
        $settings = self::get_settings();
        $campaign = wlm_arrval($settings, 'campaigns', $campaign_id);
        if (! $campaign) {
            return __('Invalid campaign', 'wishlist-member');
        }

        // Get access config for campaign.
        $access = wlm_arrval($campaign, 'access');
        if (! is_array($access)) {
            $access = [];
        }

        if (is_numeric($user) && $user) {
            // Existing user.
            // Check for claim limit.
            $claimed = self::get_claimed_codes($campaign_id, $user, self::STATUS_REDEEMED, [$code->code]);
            if (count($claimed) >= count($access)) {
                return __('Maximum number of codes already claimed for campaign', 'wishlist-member');
            }

            // Get action and access.
            $action = wlm_arrval($access, count($claimed), 'action');
            $access = wlm_arrval($access, count($claimed), 'levels');
            if (! is_array($access)) {
                return __('Invalid campaign configuration. Please contact site administrator');
            }

            // Prepare api data.
            $api_data = [
                'Levels'                       => self::add_transaction_id($access, $code->code),
                'ObeyRegistrationRequirements' => 1,
                'SendMailPerLevel'             => 1,
            ];

            if ('move' === $action) {
                // Remove existing levels if action is move.
                $api_data['RemoveLevels'] = array_keys(wlm_arrval(wlmapi_get_member($user), 'member', 0, 'Levels') ? wlm_arrval('lastresult') : []);
            }
            // Update user's levels using the WishList Member API.
            $api_result = wlmapi_update_member($user, $api_data);
        } else {
            // New user.
            // Get access info for first action.
            $access = wlm_arrval($access, 0, 'levels');
            if (! is_array($access)) {
                return __('Invalid campaign configuration. Please contact site administrator');
            }

            // Prepare api data.
            $user['Levels']                       = self::add_transaction_id($access, $code->code);
            $user['ObeyRegistrationRequirements'] = 1;
            $user['SendMailPerLevel']             = 1;

            // Add member using the WishList Member API.
            $api_result = wlmapi_add_member($user);
        }

        if (empty(wlm_arrval($api_result, 'success'))) {
            // WLM API call failed.
            return wlm_arrval($api_result, 'ERROR');
        }

        // All good, update the code's status.
        $code->user_id = wlm_arrval($api_result, 'member', 0, 'ID');
        $code->status  = self::STATUS_REDEEMED;
        $code->claimed = current_time('mysql');
        if (! self::update_code($code)) {
            // Log error just in case redemption failed.
            \WishListMember\Logs::add($code->user_id, 'coderedemption', 'update fail', $code);
        }
        $code->access = $access;
        return $code;
    }

    /**
     * Helper function to add transaction ID to each access level for use with the WishList Member API
     *
     * @param  array  $access Array of levels
     * @param  string $code   Code used to generate transaction ID
     * @return array Associative array of Level ID and Transaction ID pairs
     */
    private static function add_transaction_id($access, $code)
    {
        foreach ($access as &$a) {
            $a = [$a, sprintf('CODE*' . $code)];
        }
        unset($a);
        return $access;
    }

    /**
     * Retrieve a single campaign Code
     *
     * @param  string  $code
     * @param  integer $campaign_id
     * @return object  Code data : database result returned by $wpdb->get_row()
     */
    private static function get_code($code, $campaign_id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare('SELECT * FROM `%0s` WHERE `campaign_id`=%d AND `code`=%s', self::table_name(), $campaign_id, $code));
    }

    /**
     * Updates a single campaign code
     *
     * @param  string $code Code data to update. Must contain ID property
     * @return boolean
     */
    private static function update_code($code)
    {
        global $wpdb;
        if (empty(wlm_arrval($code, 'ID'))) {
            return false;
        }
        $code = (array) $code;
        unset($code['access']);
        return (bool) $wpdb->update(self::table_name(), $code, ['ID' => $code['ID']]);
    }

    /**
     * Get claimed campaign codes for a user
     *
     * @param  integer $campaign_id  Campaign ID
     * @param  integer $user_id      User ID
     * @param  integer $status       (optional) Status. Retrieve all codes irregardless of status by default.
     * @param  array   $exclude_code (optional) Array of codes to exclude
     * @return array   Array of objects as returned by $wpdb->get_results()
     */
    private static function get_claimed_codes($campaign_id, $user_id, $status = null, $exclude = [])
    {
        global $wpdb;

        $status = null === $status ? '0,1,2' : intval($status);
        if (empty($exclude)) {
            $exclude = [''];
        }
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM `%0s` WHERE `campaign_id`=%d AND `user_id`=%d AND `status` IN (%0s) AND `code` NOT IN (' . implode(',', array_fill(0, count($exclude), '%s')) . ')',
                self::table_name(),
                $campaign_id,
                $user_id,
                $status,
                ...array_values($exclude)
            )
        );
    }

    /**
     * Adds code quantity to campaigns
     *
     * @param  array $settings Full settings data
     * @return array           Full settings data with `code_total` added to each campaign
     */
    private static function populate_quantity($settings)
    {
        global $wpdb;
        foreach ($settings['campaigns'] as &$campaign) {
            $campaign = array_merge($campaign, self::get_stats($campaign['id']));
        }
        unset($campaign);
        return $settings;
    }

    /**
     * Returns table name for this integration
     *
     * @return string Table name
     */
    private static function table_name()
    {
        return wishlistmember_instance()->table_prefix . 'coderedemption';
    }

    /**
     * Returns the code usage stats for a campaign
     *
     * @param  integer $campaign_id Campaign ID
     * @return array   Associative array of usage stats
     */
    public static function get_stats($campaign_id)
    {
        global $wpdb;

        $quantities = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT `status`, COUNT(*) FROM `%0s` WHERE `campaign_id`=%d GROUP BY `status`',
                wishlistmember_instance()->table_prefix . 'coderedemption',
                $campaign_id
            ),
            ARRAY_N
        );

        // Pre-populate stats with 0.
        $stats = array_combine(['code_available', 'code_redeemed', 'code_cancelled'], [0, 0, 0]);
        // Map stats numeric values to associative keys.
        $x = array_combine([0, 1, 2], array_keys($stats));
        foreach ($quantities as $q) {
            $stats[ $x[ wlm_arrval($q, 0) ] ] = wlm_arrval($q, 1);
        }
        // Sum all stats to code_total.
        $stats['code_total'] = $stats['code_available'] + $stats['code_redeemed'] + $stats['code_cancelled'];

        return $stats;
    }

    /**
     * Retrieve Code Redemption Settings
     *
     * @param boolean $include_stats True to populate each campaign with code stats
     *
     * @return array Associative array containing code redemption settings
     */
    public static function get_settings($include_stats = false)
    {
        if (is_null(self::$settings)) {
            self::$settings = wishlistmember_instance()->get_option(self::SETTINGS_NAME);

            if (! is_array(self::$settings)) {
                self::$settings = [
                    'settings'  => [],
                    'campaigns' => [],
                ];
                wishlistmember_instance()->save_option(self::SETTINGS_NAME, self::$settings);
            }

            self::$settings = array_merge(
                [
                    'settings'  => [],
                    'campaigns' => [],
                ],
                self::$settings
            );
        }

        if ($include_stats) { // Get code stats for each campaign.
            self::$settings = self::populate_quantity(self::$settings);
        }

        return self::$settings;
    }

    /**
     * Save code redemption settings and reset self::$settings to null
     * so ::get_settings() queries the database again for its values
     *
     * @param array $settings Code Redemption settings
     */
    public static function save_settings($settings)
    {
        if (! is_array($settings)) {
            $settings = [];
        }
        wishlistmember_instance()->save_option(
            self::SETTINGS_NAME,
            array_merge(
                [
                    'settings'  => [],
                    'campaigns' => [],
                ],
                $settings
            )
        );
        self::$settings = null;
    }
}
