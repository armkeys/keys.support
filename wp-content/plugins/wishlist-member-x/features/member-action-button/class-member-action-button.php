<?php

/**
 * Member Action Button
 *
 * @package WishListMember\Features
 */

namespace WishListMember\Features;

/**
 * Member Action Button Class
 */
class Member_Action_Button
{
    /**
     * Membership Levels
     *
     * @var array
     */
    private $wpm_levels = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Load membership levels.
        $this->wpm_levels = (array) wishlistmember_instance()->get_option('wpm_levels');

        // Member action button.
        add_shortcode('wlm_member_action_button', [$this, 'member_action_button']);
        add_action('init', [$this, 'process_member_action_button']);

        // Shortcode manifest.
        add_filter('wishlistmember_shortcodes', [$this, 'shortcodes_manifest'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'shortcode_inserter_js']);
    }

    /**
     * Member action button shortcode handler
     *
     * @param  array $atts {
     *     Shortcode attributes.
     * @type   string $level                       Membership Level
     * @type   string $action                      Button action. Possible values are 'MOVE',
     *                                               'ADD', 'REMOVE', 'CANCEL' and 'UNCANCEL'
     * @type   string $label                       Button label. Default 'Add to %level%',
     * @type   string $disabled_button_label       Button label when action cannot be performed.
     *                                               Default '',
     * @type   string $unavailable_button_state    Hide button when action cannot be performed.
     *                                               Accepted values are 1, 0 or ''
     * @type   string $class                       Additional class names
     * @type   string $redirect                    Redirect url. Possible values are 'return',
     *                                               'url' or '' (after registration page)
     * @type   string $require_admin_approval      'level_settings', 'yes', or ''. Default ''
     * @type   string $require_email_confirmation  'level_settings', 'yes', or ''. Default ''
     * }
     * @return string Member action button markup
     */
    public function member_action_button($atts)
    {
        static $__scripts_and_styles_loaded = false;
        // No buttons for non-logged in customers.
        if (! is_user_logged_in()) {
            return '';
        }
        // Default shortcode attributes.
        $atts = shortcode_atts(
            [
                'level'                      => '',
                'action'                     => 'ADD',
                'label'                      => 'Add to %level%',
                'disabled_button_label'      => '',
                'unavailable_button_state'   => 'disabled',
                'class'                      => '',
                'redirect'                   => '',
                'require_admin_approval'     => '',
                'require_email_confirmation' => '',
            ],
            $atts,
            'wlm_member_action_button'
        );

        // Extract attributes.
        extract($atts);

        $level_name = '';
        $payperpost = wishlistmember_instance()->is_ppp_level($level);
        if ($payperpost) {
            // Get post title as $level_name.
            $level_name = get_the_title($payperpost->ID);
            // Assign $level to $level_id.
            $level_id = $level;
        } else {
            // Look for the level id and assign it to $level_id.
            foreach ($this->wpm_levels as $x => $wpm_level) {
                if (trim(strtoupper($wpm_level['name'])) === trim(strtoupper(html_entity_decode($level)))) {
                    $level_id   = $x;
                    $level_name = $wpm_level['name'];
                    break;
                }
            }
        }

        // Level not found, return empty string.
        if (empty($level_name)) {
            return '';
        }

        // Validate action.
        $action = strtoupper($action);
        if (! in_array($action, $payperpost ? ['ADD', 'REMOVE'] : ['MOVE', 'ADD', 'REMOVE', 'CANCEL', 'UNCANCEL'], true)) {
            return '';
        }

        // Get user.
        $user = new \WishListMember\User(get_current_user_id());
        if (empty($user->ID)) {
            return '';
        }

        $disabled = false;
        switch ($action) {
            case 'MOVE':
            case 'ADD':
                // Disable if user is already in the level.
                if ($payperpost) {
                    if (in_array($payperpost->ID, $user->pay_per_posts['_all_'])) {
                        $disabled = true;
                    }
                } else {
                    if (isset($user->Levels[ $level_id ])) {
                        $disabled = true;
                    }
                }
                switch ($require_email_confirmation) {
                    case 'level_settings':
                        $require_confirmation = (bool) $this->wpm_levels[ $level_id ]['requireemailconfirmation'];
                        break;
                    case 'yes':
                        $require_confirmation = true;
                        break;
                    default:
                        $require_confirmation = false;
                }
                switch ($require_admin_approval) {
                    case 'level_settings':
                        $require_approval = (bool) $this->wpm_levels[ $level_id ]['requireadminapproval'];
                        break;
                    case 'yes':
                        $require_approval = true;
                        break;
                    default:
                        $require_approval = false;
                }
                break;
            case 'REMOVE':
                // Disable if user is not in the level.
                if ($payperpost) {
                    if (! in_array($payperpost->ID, $user->pay_per_posts['_all_'])) {
                        $disabled = true;
                    }
                } else {
                    if (! isset($user->Levels[ $level_id ])) {
                        $disabled = true;
                    }
                }
                break;
            case 'CANCEL':
                // Disable if user is not in the level or is in the level but the level is Cancelled.
                if (! isset($user->Levels[ $level_id ]) || $user->Levels[ $level_id ]->Cancelled) {
                    $disabled = true;
                }
                break;
            case 'UNCANCEL':
                // Disable if user is not in the level or is in the level but the level is not Cancelled.
                if (! isset($user->Levels[ $level_id ]) || ! $user->Levels[ $level_id ]->Cancelled) {
                    $disabled = true;
                }
                break;
        }

        // Return nothing because action cannot be performed and $unavailable_button_state is truish.
        if ($disabled && 'hidden' === strtolower($unavailable_button_state)) {
            return '';
        }

        // Replace label because action cannot be performed and $disabled_button_label is set.
        if ($disabled && wlm_trim($disabled_button_label)) {
            $label = wlm_trim($disabled_button_label);
        }

        // Generate link.
        $link = wp_nonce_url(
            add_query_arg(
                [
                    'wishlistmember_member_action_button' => $action,
                    'level'                               => $level_id,
                    'redirect'                            => $redirect,
                    'require_admin_approval'              => $require_approval,
                    'require_email_confirmation'          => $require_confirmation,
                    'atts'                                => $atts,
                ],
                site_url()
            ),
            'wishlistmember_member_action_button'
        );

        // Generate button.
        $button = sprintf(
            '<button data-url="%s" class="wishlistmember-member-action-button %s %s" %s>%s</button>',
            $link,
            $class,
            $disabled ? '-disabled' : '',
            $disabled ? 'disabled="disabled"' : '',
            str_ireplace('%level%', $level_name, $label)
        );

        // Add scripts and styles on first use.
        if (! $__scripts_and_styles_loaded) {
            wp_enqueue_style('wlm-member-action-button-fe-style', plugin_dir_url(__FILE__) . '/fe-style.css', [], wishlistmember_instance()->version);
            wp_enqueue_script('wlm-member-action-button-fe-script', plugin_dir_url(__FILE__) . '/fe-script.js', [], wishlistmember_instance()->version, true);
            $__scripts_and_styles_loaded = true;
        }

        return $button;
    }

    /**
     * Processes the action requested by the member action button
     */
    public function process_member_action_button()
    {
        $get                        = wlm_get_data(true);
        $action                     = strtoupper((string) wlm_arrval($get, 'wishlistmember_member_action_button'));
        $level                      = wlm_arrval($get, 'level');
        $require_admin_approval     = wlm_arrval($get, 'require_admin_approval');
        $require_email_confirmation = wlm_arrval($get, 'require_email_confirmation');
        $nonce                      = wlm_arrval($get, '_wpnonce');

        // Check if payperpost.
        $payperpost = wishlistmember_instance()->is_ppp_level($level);
        if ($payperpost) {
            list( , $post_id ) = explode('-', $level);
        }

        if (
            // Level not set.
            ! $level
            // Invalid action.
            || ! in_array($action, $payperpost ? ['ADD', 'REMOVE'] : ['ADD', 'MOVE', 'REMOVE', 'CANCEL', 'UNCANCEL'], true)
            // Invalid nonce.
            || ! wp_verify_nonce(wlm_arrval($get, '_wpnonce'), 'wishlistmember_member_action_button')
            // User not logged in.
            || ! is_user_logged_in()
        ) {
            return;
        }

        // Get current user.
        $user = new \WishListMember\User(get_current_user_id());
        if (! $user->ID) {
            return;
        }

        $redirect = wlm_arrval($get, 'redirect');
        switch ($action) {
            case 'MOVE':
                $user->RemoveLevels(array_keys($user->Levels));
                // Continue to ADD.
            case 'ADD':
                if ($payperpost) {
                    $user->add_payperposts($level);
                } else {
                    $user->AddLevel($level, '');
                }
                if (empty($redirect)) {
                    $redirect = wishlistmember_instance()->get_after_reg_redirect($level);
                }
                $macros = [
                    '[password]'    => '********',
                    '[memberlevel]' => $this->wpm_levels[ $level ]['name'],
                ];
                if ($require_admin_approval) {
                    wishlistmember_instance()->level_for_approval($level, $user->ID, true);
                    wishlistmember_instance()->send_email_template('require_admin_approval', $user->ID, $macros); // Send to user.
                    wishlistmember_instance()->send_email_template('require_admin_approval_admin', $user->ID, $macros, wishlistmember_instance()->get_option('email_sender_address')); // Send to admin.
                }
                if ($require_email_confirmation) {
                    wishlistmember_instance()->level_unconfirmed($level, $user->ID, true);
                    add_filter('wishlistmember_per_level_template_setting_requireemailconfirmation_' . $level, '__return_true');
                    wishlistmember_instance()->email_template_level = $level;
                    $user                   = get_userdata($user->ID);
                    $macros['[confirmurl]'] = get_bloginfo('url') . '/index.php?wlmconfirm=' . $user->ID . '/' . md5($user->user_email . '__' . $user->user_login . '__' . $level . '__' . wishlistmember_instance()->GetAPIKey());
                    wishlistmember_instance()->send_email_template('email_confirmation', $user->ID, $macros);
                    remove_filter('wishlistmember_per_level_template_setting_requireemailconfirmation_' . $level, '__return_true');
                }
                break;
            case 'REMOVE':
                if ($payperpost) {
                    $user->remove_payperposts($level);
                } else {
                    $user->RemoveLevel($level);
                }
                break;
            case 'CANCEL':
                $user->CancelLevel($level);
                break;
            case 'UNCANCEL':
                $user->UnCancelLevel($level);
                break;
        }

        // Redirect back to referer if $redirect is 'RETURN'.
        $return = 'RETURN' === strtoupper($redirect);
        if ($return) {
            $redirect = wlm_server_data()['HTTP_REFERER'];
        }

        $x_ajax_request = getallheaders()['X-Ajax-Request'];
        // Check all lower cases too.
        if (empty($x_ajax_request)) {
            $x_ajax_request = getallheaders()['x-ajax-request'];
        }

        if (empty($x_ajax_request)) {
            // Redirect for non-ajax request. Note that X-Ajax-Request is set by us.
            if ($redirect && wp_safe_redirect(add_query_arg('wishlistmember_member_action_button_msg', $action, $redirect))) {
                exit;
            }
        } else {
            // Regenerate shortcode button if we're returning to the same page.
            if ($return) {
                $shortcode = ['[wlm_member_action_button'];
                foreach ($get['atts'] as $k => $v) {
                    $shortcode[] = sprintf('%s="%s"', $k, htmlentities($v));
                }
                $shortcode = implode(' ', $shortcode) . ']';
                $shortcode = do_shortcode($shortcode);
            }
            wp_send_json(
                [
                    'redirect' => ! $return,
                    'data'     => $return ? $shortcode : $redirect,
                ]
            );
        }
    }

    /**
     * Add wlm_member_action to shortcode inserter manifest
     *
     * @param  array $manifest      Shortcode manifest.
     * @param  array $level_options Level options for dropdown.
     * @return array                Updated manifest.
     */
    public function shortcodes_manifest($manifest, $level_options)
    {
        // Membership level options.
        $options['Membership Levels'] = [
            'options' => $level_options,
            'label'   => __('Membership Levels', 'wishlist-member'),
        ];

        // Member action button pay per posts.
        foreach (wishlistmember_instance()->get_pay_per_posts(['post_title']) as $wlm_post_type => $wlm_posts) {
            $ptype = get_post_type_object($wlm_post_type);
            if ($wlm_posts && $ptype) {
                $options[ $ptype->label ] = [
                    'options'    => [],
                    'label'      => $ptype->label,
                    'dependency' => '[name="action"] option:selected[value="add"],[name="action"] option:selected[value="remove"]',
                ];
                foreach ($wlm_posts as $wlm_post) {
                    $options[ $ptype->label ]['options'][ 'payperpost-' . $wlm_post->ID ] = ['label' => $wlm_post->post_title];
                }
            }
        }

        $manifest['wlm_member_action_button'] = [
            'label'      => 'Member Action Button',
            'attributes' => [
                'action'                     => [
                    'label'   => 'Action',
                    'columns' => 3,
                    'type'    => 'select',
                    'options' => [
                        'add'      => [
                            'label' => 'Add to',
                        ],
                        'move'     => [
                            'label' => 'Move to',
                        ],
                        'remove'   => [
                            'label' => 'Remove from',
                        ],
                        'cancel'   => [
                            'label' => 'Cancel from',
                        ],
                        'uncancel' => [
                            'label' => 'Uncancel from',
                        ],
                    ],
                ],
                'level'                      => [
                    'label'       => 'Access',
                    'columns'     => 9,
                    'type'        => 'select',
                    'placeholder' => 'Choose a Level / Pay Per Post',
                    'options'     => $options,
                ],
                'redirect-choice'            => [
                    'dependency' => '[name="level"] option:selected[value!=""]',
                    'label'      => 'Redirect URL',
                    'columns'    => 3,
                    'type'       => 'select',
                    'options'    => [
                        ''       => [
                            'label'      => 'After Registration Page',
                            'dependency' => '[name="action"] option:selected[value="add"],[name="action"] option:selected[value="move"]',
                        ],
                        'return' => [
                            'label' => 'Return to Same Page',
                        ],
                        'url'    => [
                            'label' => 'URL',
                        ],
                    ],
                ],
                'redirect'                   => [
                    'label'       => '&nbsp;',
                    'type'        => 'url',
                    'columns'     => 9,
                    'dependency'  => '[name="redirect-choice"] option:selected[value="url"] && [name="level"] option:selected[value!=""]',
                    'placeholder' => site_url(),
                ],
                'require_admin_approval'     => [
                    'label'      => 'Require Admin Approval',
                    'columns'    => -6,
                    'dependency' => '[name="level"] option:selected[value!=""]',
                    'type'       => 'select',
                    'options'    => [
                        ''               => [
                            'label' => 'DO NOT Require Admin Approval',
                        ],
                        'yes'            => [
                            'label' => 'Require Admin Approval',
                        ],
                        'level_settings' => [
                            'label' => 'Use Level Settings',
                        ],
                    ],
                ],
                'require_email_confirmation' => [
                    'label'      => 'Require Email Confirmation',
                    'columns'    => 6,
                    'dependency' => '[name="level"] option:selected[value!=""]',
                    'type'       => 'select',
                    'options'    => [
                        ''               => [
                            'label' => 'DO NOT Require Email Confirmation',
                        ],
                        'yes'            => [
                            'label' => 'Require Email Confirmation',
                        ],
                        'level_settings' => [
                            'label' => 'Use Level Settings',
                        ],
                    ],
                ],
                'label'                      => [
                    'dependency' => '[name="level"] option:selected[value!=""]',
                    'label'      => 'Button Label',
                    'columns'    => -4,
                ],
                'unavailable_button_state'   => [
                    'dependency'   => '[name="level"] option:selected[value!=""]',
                    'type'         => 'radio',
                    'label'        => 'When Action Can\'t be Performed',
                    'columns'      => 4,
                    'tooltip'      => 'You can select if you want to hide the button or show a disabled button with a label you set When the Action Can Not Be Performed. The action cannot be performed if the action has already been applied to the logged in user (the level has already been added, or removed, etc.) and the user visits the page again. The Member Action Button can either be hidden or disabled in that case.',
                    'tooltip_size' => 'md',
                    'options'      => [
                        'hidden'   => [
                            'label' => 'Hide Button',
                        ],
                        'disabled' => [
                            'label' => 'Disable Button',
                        ],
                    ],
                    'default'      => 'hidden',
                ],
                'disabled_button_label'      => [
                    'dependency' => '[name="level"] option:selected[value!=""]&&[name="unavailable_button_state"][value="disabled"]:checked',
                    'label'      => 'Disabled Button Label',
                    'columns'    => 4,
                ],
                'class'                      => [
                    'dependency' => '[name="level"] option:selected[value!=""]',
                    'label'      => 'Additional CSS Classes',
                    'columns'    => 12,
                ],

            ],
        ];

        return $manifest;
    }

    /**
     * Enqueue Shortcode inserter javascript
     *
     * @wp-hook admin_enqueue_scripts
     */
    public function shortcode_inserter_js()
    {
        wp_enqueue_script('wishlistmember-wlm_member_action_button-shortcode-insert-js', plugin_dir_url(__FILE__) . '/script.js', [], wishlistmember_instance()->version, true);
    }
}
