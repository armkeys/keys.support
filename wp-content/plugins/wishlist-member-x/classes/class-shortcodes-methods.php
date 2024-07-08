<?php

/**
 * Shortcodes Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Shortcodes Methods trait
*/
trait Shortcodes_Methods
{
    /**
     * Custom fields merge codes
     *
     * @var array
     */
    public $custom_fields_merge_codes;

    public function shortcodes_init()
    {
        // Get levels.
        $wpm_levels = $this->get_option('wpm_levels');

        // Shortcodes array.
        $wlm_shortcodes = [
            'Member'  => [
                [
                    'title' => 'First Name',
                    'value' => '[wlm_firstname]',
                ],
                [
                    'title' => 'Last Name',
                    'value' => '[wlm_lastname]',
                ],
                [
                    'title' => 'Email',
                    'value' => '[wlm_email]',
                ],
                [
                    'title' => 'Username',
                    'value' => '[wlm_username]',
                ],
            ],
            'Access'  => [
                [
                    'title' => 'Membership Levels',
                    'value' => '[wlm_memberlevel]',
                ],
                [
                    'title' => 'Pay Per Posts',
                    'value' => '[wlm_userpayperpost sort="ascending"]',
                ],
                [
                    'title' => 'RSS Feed',
                    'value' => '[wlm_rss]',
                ],
                [
                    'title' => 'Content Levels',
                    'value' => '[wlm_contentlevels type="comma" link_target="_blank" class="wlm_contentlevels" show_link="1" salespage_only="1"]',
                ],
            ],
            'Login'   => [
                [
                    'title' => 'Login Form',
                    'value' => '[wlm_loginform]',
                ],
                [
                    'title' => 'Login URL',
                    'value' => '[wlm_loginurl]',
                ],
                [
                    'title' => 'Log out URL',
                    'value' => '[wlm_logouturl]',
                ],
            ],
            'Profile' => [
                [
                    'title' => 'Profile Form',
                    'value' => '[wlm_profileform hide_mailinglist=no]',
                ],
                [
                    'title' => 'Profile URL',
                    'value' => '[wlm_profileurl]',
                ],
            ],
        ];

        if ($wpm_levels) {
            $wlm_shortcodes['Join Date']       = [];
            $wlm_shortcodes['Expiration Date'] = [];
            foreach ((array) $wpm_levels as $level) {
                if (false === strpos($level['name'], '/')) {
                    $wlm_shortcodes['Join Date'][]       = [
                        'title' => "{$level['name']}",
                        'value' => "[wlm_joindate {$level['name']}]",
                    ];
                    $wlm_shortcodes['Expiration Date'][] = [
                        'title' => "{$level['name']}",
                        'value' => "[wlm_expiration {$level['name']}]",
                    ];
                }
            }
        }

        $wlm_shortcodes['Address'] = [
            [
                'title' => 'Company',
                'value' => '[wlm_company]',
            ],
            [
                'title' => 'Address',
                'value' => '[wlm_address]',
            ],
            [
                'title' => 'Address 1',
                'value' => '[wlm_address1]',
            ],
            [
                'title' => 'Address 2',
                'value' => '[wlm_address2]',
            ],
            [
                'title' => 'City',
                'value' => '[wlm_city]',
            ],
            [
                'title' => 'State',
                'value' => '[wlm_state]',
            ],
            [
                'title' => 'Zip',
                'value' => '[wlm_zip]',
            ],
            [
                'title' => 'Country',
                'value' => '[wlm_country]',
            ],
        ];

        // Custom fields shortcode.
        $custom_fields                   = $this->get_custom_fields_merge_codes();
        $this->custom_fields_merge_codes = $custom_fields ? $custom_fields : [];
        if (count($custom_fields)) {
            $wlm_shortcodes['Custom Fields'] = [];
            foreach ($custom_fields as $custom_field) {
                $wlm_shortcodes['Custom Fields'] = [
                    'title' => $custom_field,
                    'value' => $custom_field,
                ];
            }
        }

        $wlm_shortcodes['Other'] = [
            [
                'title' => 'Website',
                'value' => '[wlm_website]',
            ],
            [
                'title' => 'AOL Instant Messenger',
                'value' => '[wlm_aim]',
            ],
            [
                'title' => 'Yahoo Instant Messenger',
                'value' => '[wlm_yim]',
            ],
            [
                'title' => 'Jabber',
                'value' => '[wlm_jabber]',
            ],
            [
                'title' => 'Biography',
                'value' => '[wlm_biography]',
            ],
        ];

        if (\WishListMember\Level::any_can_autocreate_account_for_integration()) {
            $wlm_shortcodes[] = [
                'title' => 'Auto-generated Password',
                'value' => '[wlm_autogen_password]',
            ];
        }

        // Mergecodes array.
        $wlm_mergecodes[] = [
            'title' => 'Is Member',
            'value' => '[wlm_ismember]',
            'type'  => 'merge',
        ];
        $wlm_mergecodes[] = [
            'title' => 'Non-Member',
            'value' => '[wlm_nonmember]',
            'type'  => 'merge',
        ];
        $wlm_mergecodes[] = [
            'title'  => 'Private Tags',
            'value'  => '',
            'jsfunc' => 'wlmtnmcelbox_vars.show_private_tags_lightbox',
        ];

        // Reg form shortcodes.
        $wlm_mergecodes[] = [
            'title'  => 'Registration Forms',
            'value'  => '',
            'jsfunc' => 'wlmtnmcelbox_vars.show_reg_form_lightbox',
        ];

        // $wlm_mergecodes are actually called Shortcodes.
        $wlm_mergecodes    = apply_filters('wlm_mergecodes', $wlm_mergecodes);
        $this->short_codes = $wlm_mergecodes;
        // $wlm_shortcodes are actually called Mergecodes.
        $wlm_shortcodes    = apply_filters('wlm_shortcodes', $wlm_shortcodes);
        $this->merge_codes = $wlm_shortcodes;

        $wlmshortcode_role_access = $this->get_option('wlmshortcode_role_access');
        $wlmshortcode_role_access = false === $wlmshortcode_role_access ? false : $wlmshortcode_role_access;
        $wlmshortcode_role_access = is_string($wlmshortcode_role_access) ? [] : $wlmshortcode_role_access;
        if (is_array($wlmshortcode_role_access)) {
            $wlmshortcode_role_access[] = 'administrator';
            $wlmshortcode_role_access   = array_unique($wlmshortcode_role_access);
        } else {
            $wlmshortcode_role_access = false;
        }

        if (! isset(wlm_get_data()['page']) || wlm_get_data()['page'] != $this->menu_id) {
            // Don't initiate tinymce (shortcode inserter) on admin-ajax.php to avoid conflicts with profile builders.
            if ('admin-ajax.php' != basename(wlm_server_data()['PHP_SELF'])) {
                global $WLMTinyMCEPluginInstanceOnly;
                if (! isset($WLMTinyMCEPluginInstanceOnly)) { // Instantiate the class only once.
                    $WLMTinyMCEPluginInstanceOnly = new \WLMTinyMCEPluginOnly($wlmshortcode_role_access);
                    add_action('admin_init', [&$WLMTinyMCEPluginInstanceOnly, 'TNMCE_PluginJS'], 1);
                }
                $WLMTinyMCEPluginInstanceOnly->RegisterShortcodes('Mergecodes', [], [], 0, null, $wlm_shortcodes);
                $WLMTinyMCEPluginInstanceOnly->RegisterShortcodes('Shortcodes', [], [], 0, null, $wlm_mergecodes);
                if (count($this->integration_shortcodes) > 0) {
                    $WLMTinyMCEPluginInstanceOnly->RegisterShortcodes('Integrations', [], [], 0, null, $this->integration_shortcodes);
                }
            }
        }

        // $this->integration_shortcodes(); //lets try to load it above.
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('plugins_loaded', [$wlm, 'shortcodes_init']);
    }
);
