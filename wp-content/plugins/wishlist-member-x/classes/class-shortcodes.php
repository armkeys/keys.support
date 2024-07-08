<?php

/**
 * WishList Member Shortcodes file.
 *
 * @package WishListMember
 */

namespace WishListMember;

defined('ABSPATH') || die();

/**
 * WishList Member Shortcodes class
 */
class Shortcodes
{
    /**
     * Array of shortcodes
     *
     * @var array
     */
    public $shortcodes = [
        ['wlm_firstname', 'wlmfirstname', 'firstname'],
        'First Name',
        'userinfo',
        ['wlm_lastname', 'wlmlastname', 'lastname'],
        'Last Name',
        'userinfo',
        ['wlm_email', 'wlmemail', 'email'],
        'Email Address',
        'userinfo',
        ['wlm_memberlevel', 'wlmmemberlevel', 'memberlevel'],
        'Membership Levels',
        'userinfo',
        ['wlm_username', 'wlmusername', 'username'],
        'Username',
        'userinfo',
        ['wlm_profileurl', 'wlmprofileurl', 'profileurl'],
        'Profile URL',
        'userinfo',
        ['wlm_password', 'wlmpassword', 'password'],
        'Password',
        'userinfo',
        ['wlm_autogen_password'],
        'Auto Generated Password',
        'userinfo',
        ['wlm_website', 'wlmwebsite', 'website'],
        'URL',
        'userinfo',
        ['wlm_aim', 'wlmaim', 'aim'],
        'AIM ID',
        'userinfo',
        ['wlm_yim', 'wlmyim', 'yim'],
        'Yahoo ID',
        'userinfo',
        ['wlm_jabber', 'wlmjabber', 'jabber'],
        'Jabber ID',
        'userinfo',
        ['wlm_biography', 'wlmbiography', 'biography'],
        'Biography',
        'userinfo',
        ['wlm_company', 'wlmcompany', 'company'],
        'Company',
        'userinfo',
        ['wlm_address', 'wlmaddress', 'address'],
        'Address',
        'userinfo',
        ['wlm_address1', 'wlmaddress1', 'address1'],
        'Address 1',
        'userinfo',
        ['wlm_address2', 'wlmaddress2', 'address2'],
        'Address 2',
        'userinfo',
        ['wlm_city', 'wlmcity', 'city'],
        'City',
        'userinfo',
        ['wlm_state', 'wlmstate', 'state'],
        'State',
        'userinfo',
        ['wlm_zip', 'wlmzip', 'zip'],
        'Zip',
        'userinfo',
        ['wlm_country', 'wlmcountry', 'country'],
        'Country',
        'userinfo',
        ['wlm_loginurl', 'wlm_loginurl', 'loginurl'],
        'Login URL',
        'userinfo',
        ['wlm_logouturl', 'wlm_logouturl', 'logouturl'],
        'Log out URL',
        'userinfo',
        ['wlm_rss', 'wlmrss'],
        'RSS Feed URL',
        'rss',
        ['wlm_expiration', 'wlm_expiry', 'wlmexpiry'],
        'Level Expiry Date',
        'levelinfo',
        ['wlm_joindate', 'wlmjoindate'],
        'Level Join Date',
        'levelinfo',
        ['wlm_payperpost'],
        'Registered Pay Per Post',
        'registered_payperpost',
    ];

    /**
     * Shortcodes manifest
     * Defines shortcode groups, labels, parameters and parameter field types.
     *
     * @var array
     */
    public $manifest = [
        'Mergecodes'   => [
            'Member'        => [
                'wlm_firstname'  => [
                    'label' => 'First Name',
                ],
                'wlm_lastname'   => [
                    'label' => 'Last Name',
                ],
                'wlm_email'      => [
                    'label' => 'Email',
                ],
                'wlm_username'   => [
                    'label' => 'Username',
                ],
                'wlm_joindate'   => [
                    'label'      => 'Join Date',
                    'attributes' => [
                        'level'  => [
                            'label'   => 'Membership Level',
                            'type'    => 'select',
                            'options' => [],
                            'columns' => '9',
                        ],
                        'format' => [
                            'label'       => 'Date Format',
                            'type'        => 'text',
                            'placeholder' => '',
                            'columns'     => '3',
                        ],
                    ],
                ],
                'wlm_expiration' => [
                    'label'      => 'Expiration Date',
                    'attributes' => [
                        'level'  => [
                            'label'   => 'Membership Level',
                            'type'    => 'select',
                            'options' => [],
                            'columns' => '9',
                        ],
                        'format' => [
                            'label'       => 'Date Format',
                            'type'        => 'text',
                            'placeholder' => '',
                            'columns'     => '3',
                        ],
                    ],
                ],
            ],
            'Access'        => [
                'wlm_memberlevel'    => [
                    'label' => 'Membership Levels',
                ],
                'wlm_userpayperpost' => [
                    'label'      => 'Pay Per Posts',
                    'attributes' => [
                        'sortby' => [
                            'columns' => 6,
                            'label'   => 'Sort By',
                            'type'    => 'select',
                            'options' => [
                                'date-assigned'  => [
                                    'label' => 'Date Assigned',
                                ],
                                'date-published' => [
                                    'label' => 'Date Published',
                                ],
                                'post-title'     => [
                                    'label' => 'Post Title',
                                ],
                            ],
                            'default' => 'date-assigned',
                        ],
                        'sort'   => [
                            'columns' => 6,
                            'label'   => 'Sort Order',
                            'type'    => 'radio',
                            'options' => [
                                'ascending'  => [
                                    'label' => 'Ascending',
                                ],
                                'descending' => [
                                    'label' => 'Descending',
                                ],
                            ],
                            'default' => 'ascending',
                        ],
                    ],
                ],
                'wlm_rss'            => [
                    'label' => 'RSS Feed',
                ],
                'wlm_contentlevels'  => [
                    'label'      => 'Content Levels',
                    'attributes' => [
                        'type'           => [
                            'columns' => 3,
                            'label'   => 'List Type',
                            'type'    => 'select',
                            'options' => [
                                'comma' => [
                                    'label' => 'Comma',
                                ],
                                'ol'    => [
                                    'label' => 'Numbered List',
                                ],
                                'ul'    => [
                                    'label' => 'Bullet List',
                                ],
                            ],
                            'default' => 'comma',
                        ],
                        'link_target'    => [
                            'columns'     => 3,
                            'label'       => 'Link Target',
                            'type'        => 'text',
                            'placeholder' => '_blank',
                        ],
                        'class'          => [
                            'columns'     => 6,
                            'label'       => 'CSS Class',
                            'type'        => 'text',
                            'placeholder' => 'wlm_contentlevels',
                        ],
                        'salespage_only' => [
                            'columns' => 6,
                            'type'    => 'checkbox',
                            'options' => [
                                '1' => [
                                    'label'     => 'Only display Levels with a Sales Page URL configured',
                                    'unchecked' => 0,
                                ],
                            ],
                            'default' => 1,
                        ],
                        'show_link'      => [
                            'columns'    => 6,
                            'type'       => 'checkbox',
                            'dependency' => '[name="salespage_only"]:checked',
                            'options'    => [
                                '1' => [
                                    'label'     => 'Link to Sales Page URL',
                                    'unchecked' => 0,
                                ],
                            ],
                            'default'    => 1,
                        ],
                    ],
                ],
            ],
            'Login'         => [
                'wlm_loginform'  => [
                    'label'      => 'Login Form',
                    'attributes' => [
                        'ajax_login'     => [
                            'columns'          => '12',
                            'type'             => 'checkbox',
                            'options'          => [
                                '1' => [
                                    'label'        => 'AJAX Login',
                                    'tooltip'      => '<p>If enabled, the Login Form Mergecode with Ajax error message will be inserted. Users will remain on the same page if their login attempt fails and will be shown the error message above the form.</p><p>If not enabled, the standard Login Form Mergecode will be inserted. Users will be redirected to the WordPress login page with displayed error message if their login attempt fails. </p><p>Note: Either option will display the same login form on the site. The difference between the two options is how the error message appears if there is a failed login attempt.</p>',
                                    'tooltip_size' => 'lg',
                                    'unchecked'    => '',
                                ],
                            ],
                        ],

                    ],
                ],
                'wlm_loginurl'  => [
                    'label' => 'Login URL',
                ],
                'wlm_logouturl' => [
                    'label' => 'Log out URL',
                ],
            ],
            'Profile'       => [
                'wlm_profileform'  => [
                    'label'      => 'Profile Form',
                    'attributes' => [
                        'profile_photo'     => [
                            'columns'          => '3',
                            'type'             => 'checkbox',
                            'options'          => [
                                '' => [
                                    'label'     => 'Profile Photo',
                                    'unchecked' => 'hide',
                                ],
                            ],
                            'default'          => '',
                            'form_group_class' => 'mb-0',
                        ],
                        'first_name'        => [
                            'columns'          => '3',
                            'type'             => 'checkbox',
                            'options'          => [
                                '' => [
                                    'label'     => 'First Name',
                                    'unchecked' => 'hide',
                                ],
                            ],
                            'default'          => '',
                            'form_group_class' => 'mb-0',
                        ],
                        'last_name'         => [
                            'columns'          => '3',
                            'type'             => 'checkbox',
                            'options'          => [
                                '' => [
                                    'label'     => 'Last Name',
                                    'unchecked' => 'hide',
                                ],
                            ],
                            'default'          => '',
                            'form_group_class' => 'mb-0',
                        ],
                        'nickname'          => [
                            'columns'          => '3',
                            'type'             => 'checkbox',
                            'options'          => [
                                '' => [
                                    'label'     => 'Nickname',
                                    'unchecked' => 'hide',
                                ],
                            ],
                            'default'          => '',
                            'form_group_class' => 'mb-0',
                        ],
                        'display_name'      => [
                            'columns'          => '3',
                            'type'             => 'checkbox',
                            'options'          => [
                                '' => [
                                    'label'     => 'Display Name',
                                    'unchecked' => 'hide',
                                ],
                            ],
                            'default'          => '',
                            'form_group_class' => 'mb-0',
                        ],
                        'email'             => [
                            'columns'          => '3',
                            'type'             => 'checkbox',
                            'options'          => [
                                '' => [
                                    'label'     => 'Email',
                                    'unchecked' => 'hide',
                                ],
                            ],
                            'default'          => '',
                            'form_group_class' => 'mb-0',
                        ],
                        'list_subscription' => [
                            'columns'          => '3',
                            'type'             => 'checkbox',
                            'options'          => [
                                '' => [
                                    'label'     => 'Mailing List Subscription',
                                    'unchecked' => 'hide',
                                ],
                            ],
                            'default'          => '',
                            'form_group_class' => 'mb-0',
                        ],
                        'user_password'     => [
                            'columns'          => '3',
                            'type'             => 'checkbox',
                            'options'          => [
                                '' => [
                                    'label'     => 'Password',
                                    'unchecked' => 'hide',
                                ],
                            ],
                            'default'          => '',
                            'form_group_class' => 'mb-0',
                        ],
                        'address'           => [
                            'label'            => 'Address Fields',
                            'columns'          => '12',
                            'type'             => 'checkbox',
                            'inline'           => true,
                            'label_class'      => 'col-12',
                            'form_group_class' => 'row',
                            'form_check_class' => 'col-3 px-3 mx-0 mb-3',
                            'options'          => [
                                'company'  => [
                                    'label' => 'Company',
                                ],
                                'address1' => [
                                    'label' => 'Address (First Line)',
                                ],
                                'address2' => [
                                    'label' => 'Address (Second Line)',
                                ],
                                'city'     => [
                                    'label' => 'City/Town',
                                ],
                                'state'    => [
                                    'label' => 'State/Province',
                                ],
                                'zip'      => [
                                    'label' => 'Zip/Postal Code',
                                ],
                                'country'  => [
                                    'label' => 'Country',
                                ],
                            ],
                        ],
                    ],
                ],
                'wlm_profileurl'   => [
                    'label' => 'Profile URL',
                ],
                'wlm_profilephoto' => [
                    'label'      => 'Profile Photo',
                    'attributes' => [
                        'url_only' => [
                            'label'   => 'Return Format',
                            'type'    => 'select',
                            'options' => [
                                ''  => [
                                    'label' => 'HTML Image',
                                ],
                                '1' => [
                                    'label' => 'URL Only',
                                ],
                            ],
                        ],
                        'cropping' => [
                            'dependency' => '[name="url_only"] option:selected[value=""]',
                            'type'       => 'select',
                            'label'      => 'Cropping',
                            'options'    => [
                                ''       => [
                                    'label' => 'No Cropping',
                                ],
                                'circle' => [
                                    'label' => 'Circle',
                                ],
                                'square' => [
                                    'label' => 'Square',
                                ],
                            ],
                            'columns'    => 3,
                        ],
                        'size'     => [
                            'dependency'  => '[name="url_only"] option:selected[value=""]&&[name="cropping"] option:selected:not([value=""])',
                            'type'        => 'number',
                            'label'       => 'Size',
                            'placeholder' => 200,
                            'columns'     => 3,
                        ],
                        'height'   => [
                            'dependency'  => '[name="url_only"] option:selected[value=""]&&[name="cropping"] option:selected[value=""]',
                            'type'        => 'number',
                            'label'       => 'Height',
                            'placeholder' => 200,
                            'columns'     => 3,
                        ],
                        'width'    => [
                            'dependency'  => '[name="url_only"] option:selected[value=""]&&[name="cropping"] option:selected[value=""]',
                            'type'        => 'number',
                            'label'       => 'Width',
                            'placeholder' => 200,
                            'columns'     => 3,
                        ],
                        'class'    => [
                            'dependency' => '[name="url_only"] option:selected[value=""]',
                            'type'       => 'text',
                            'label'      => 'CSS Classes',
                            'columns'    => 3,
                        ],
                    ],
                ],
            ],
            'Address'       => [
                'wlm_company'  => [
                    'label' => 'Company',
                ],
                'wlm_address'  => [
                    'label' => 'Address',
                ],
                'wlm_address1' => [
                    'label' => 'Address 1',
                ],
                'wlm_address2' => [
                    'label' => 'Address 2',
                ],
                'wlm_city'     => [
                    'label' => 'City',
                ],
                'wlm_state'    => [
                    'label' => 'State',
                ],
                'wlm_zip'      => [
                    'label' => 'Zip',
                ],
                'wlm_country'  => [
                    'label' => 'Country',
                ],
            ],
            'Custom Fields' => [],
            'Other'         => [
                'wlm_website'   => [
                    'label' => 'Website',
                ],
                'wlm_aim'       => [
                    'label' => 'AOL Instant Messenger',
                ],
                'wlm_yim'       => [
                    'label' => 'Yahoo Instant Messenger',
                ],
                'wlm_jabber'    => [
                    'label' => 'Jabber',
                ],
                'wlm_biography' => [
                    'label' => 'Biography',
                ],
            ],
        ],
        'Shortcodes'   => [
            'wlm_ismember'  => [
                'label'     => 'Is Member',
                'enclosing' => 'Enter content to show to members',
            ],
            'wlm_nonmember' => [
                'label'     => 'Non-Member',
                'enclosing' => 'Enter content to show to non-members',
            ],
            'wlm_private'   => [
                'label'      => 'Private Tags',
                'enclosing'  => 'Enter content',
                'attributes' => [
                    'levels'  => [
                        'label'   => 'Membership Levels',
                        'type'    => 'select-multiple',
                        'columns' => 9,
                        'options' => [],
                    ],
                    'reverse' => [
                        'type'    => 'checkbox',
                        'label'   => '&nbsp;',
                        'columns' => 3,
                        'options' => [
                            '1' => [
                                'label' => 'Reverse Private Tag',
                            ],
                        ],
                    ],
                ],
            ],
            'wlm_register'  => [
                'label'      => 'Registration Forms',
                'attributes' => [
                    'level' => [
                        'type'    => 'select',
                        'options' => [],
                    ],
                ],
            ],
        ],
        'Integrations' => [],
    ];

    /**
     * Custom user data.
     *
     * @var array
     */
    public $custom_user_data = [];

    /**
     * Shortcode functions
     *
     * @var array
     */
    public $shortcode_functions = [];

    /**
     * Membership levels
     *
     * @var array
     */
    public $wpm_levels = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        if (! function_exists('wishlistmember_instance')) {
            return;
        }

        $this->wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        $wpm_levels       = $this->wpm_levels;
        $wpm_levels       = $wpm_levels ? $wpm_levels : []; // Make sure the $wpm_levels is an array.

        if (\WishListMember\Level::any_can_autocreate_account_for_integration()) {
            $this->manifest['Mergecodes']['Member']['wlm_autogen_password'] = ['label' => 'Auto-generated Password'];
        }

        // Generate level options.
        $level_options = [];
        if ($wpm_levels) {
            foreach ((array) $wpm_levels as $level) {
                $level_options[ $level['name'] ] = ['label' => $level['name']];
            }
        }

        // Join date.
        $this->manifest['Mergecodes']['Member']['wlm_joindate']['attributes']['format']['placeholder'] = get_option('date_format');
        $this->manifest['Mergecodes']['Member']['wlm_joindate']['attributes']['level']['options']      = $level_options;

        // Expiration date.
        $this->manifest['Mergecodes']['Member']['wlm_expiration']['attributes']['format']['placeholder'] = get_option('date_format');
        $this->manifest['Mergecodes']['Member']['wlm_expiration']['attributes']['level']['options']      = $level_options;

        // Custom fields.
        $custom_fields = wishlistmember_instance()->get_custom_fields_merge_codes();
        if (count($custom_fields)) {
            foreach ($custom_fields as $custom_field) {
                $this->manifest['Mergecodes']['Custom Fields'][ substr($custom_field, 1, -1) ] = ['label' => $custom_field];
            }
        } else {
            unset($this->manifest['Mergecodes']['Custom Fields']);
        }

        // Private tags and registration form options.
        $this->manifest['Shortcodes']['wlm_private']['attributes']['levels']['options'] = $level_options;
        $this->manifest['Shortcodes']['wlm_register']['attributes']['level']['options'] = $level_options;

        $this->manifest['Shortcodes']   = apply_filters('wishlistmember_shortcodes', $this->manifest['Shortcodes'], $level_options);
        $this->manifest['Mergecodes']   = apply_filters('wishlistmember_mergecodes', $this->manifest['Mergecodes'], $level_options);
        $this->manifest['Integrations'] = apply_filters('wishlistmember_integration_shortcodes', $this->manifest['Integrations']);

        $coursecure_shortcodes = apply_filters('wishlistmember_addons_shortcodes', []);
        if ($coursecure_shortcodes) {
            $this->manifest['CourseCure'] = $coursecure_shortcodes;
        }

        // Initiate custom registration fields array.
        $this->custom_user_data = $wpdb->get_col('SELECT SUBSTRING(`option_name` FROM 8) FROM `' . esc_sql(wishlistmember_instance()->table_names->user_options) . "` WHERE `option_name` LIKE 'custom\_%' AND `option_name` <> 'custom\_' GROUP BY `option_name`");

        // User Information.
        $shortcodes = $this->shortcodes;
        for ($i = 0; $i < count($shortcodes); $i = $i + 3) {
            foreach ((array) $shortcodes[ $i ] as $shortcode) {
                $this->add_shortcode($shortcode, [$this, $shortcodes[ $i + 2 ]]);
            }
        }

        // Get and Post data passed on Registration.
        $shortcodes = [
            'wlmuser',
            'wlm_user',
        ];
        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'get_and_post']);
        }

        // Powered By WishList Member.
        $shortcodes = [
            'wlm_counter',
            'wlmcounter',
        ];
        foreach ($shortcodes as $shortcode) {
            add_shortcode($shortcode, [$this, 'counter']);
        }

        $shortcodes = ['wlm_min_passlength', 'wlmminpasslength'];

        foreach ($shortcodes as $shortcode) {
            add_shortcode($shortcode, [$this, 'min_password_length']);
        }

        // Login Form.
        $shortcodes = [
            'wlm_loginform',
            'wlmloginform',
            'loginform',
        ];
        foreach ($shortcodes as $shortcode) {
            add_shortcode($shortcode, [$this, 'login']);
        }

        // Membership level with access to post/page.
        $shortcodes = [
            'wlm_contentlevels',
            'wlmcontentlevels',
        ];
        foreach ($shortcodes as $shortcode) {
            add_shortcode($shortcode, [$this, 'content_levels_list']);
        }

        // Custom Registration Fields.
        $shortcodes = [
            'wlm_custom',
            'wlmcustom',
        ];
        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'custom_registration_fields']);
        }

        // Is Member and Non Member.
        $shortcodes = [
            'wlm_ismember',
            'wlmismember',
        ];
        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'ismember']);
        }

        $shortcodes = [
            'wlm_nonmember',
            'wlmnonmember',
        ];
        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'nonmember']);
        }

        $invalid_shortcode_chars = '@[<>&/\[\]\x00-\x20]@';

        $shortcodes = [
            'wlm_register',
            'wlmregister',
            'register',
        ];

        /*
         * Disable old register shotrtcodes if configured
         * This will reduce the number of shortcodes WLM is registering,
         * Specially helpful with sites with large number of levels
         */
        if (! wishlistmember_instance()->get_option('disable_legacy_reg_shortcodes')) {
            // Registration Form Tags.
            foreach ($wpm_levels as $level) {
                if (! preg_match($invalid_shortcode_chars, $level['name'])) {
                    $shortcodes[] = 'wlm_register_' . rawurlencode($level['name']);
                }
            }
        }

        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'regform']);
        }

        // Has access.
        $shortcodes = ['has_access', 'wlm_has_access'];

        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'hasaccess']);
        }

        // Has no access.
        $shortcodes = ['has_no_access', 'wlm_has_no_access'];

        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'hasnoaccess']);
        }

        // Private Tags.
        $shortcodes = [
            'wlm_private',
            'wlmprivate',
            'private',
        ];

        /*
         * Disable old private tags if configured
         * This will reduce the number of shortcodes WLM is registering,
         * Specially helpful with sites with large number of levels
         */
        if (! wishlistmember_instance()->get_option('disable_legacy_private_tags')) {
            foreach ($wpm_levels as $level) {
                if (! preg_match($invalid_shortcode_chars, $level['name'])) {
                    $shortcodes[] = 'wlm_private_' . $level['name'];
                }
            }
        }
        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'private_tags']);
        }

        // Reverse Private Tag.
        $shortcodes = [
            '!wlm_private',
            '!wlmprivate',
            '!private',
        ];
        /*
         * Disable old private tags if configured
         * This will reduce the number of shortcodes WLM is registering,
         * Specially helpful with sites with large number of levels
         */
        if (! wishlistmember_instance()->get_option('disable_legacy_private_tags')) {
            foreach ($wpm_levels as $level) {
                if (! preg_match($invalid_shortcode_chars, $level['name'])) {
                    $shortcodes[] = '!private_' . $level['name'];
                    $shortcodes[] = '!wlm_private_' . $level['name'];
                }
            }
        }
        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'reverse_private_tags']);
        }

        // User Payperpost.
        $shortcodes = [
            'wlm_userpayperpost',
            'wlmuserpayperpost',
        ];
        foreach ($shortcodes as $shortcode) {
            $this->add_shortcode($shortcode, [$this, 'user_payperpost']);
        }

        // Process our shortcodes in the sidebar too!
        if (! is_admin()) {
            add_filter('widget_text', 'do_shortcode', 11);
        }

        /*
         * fix where shortcodes are not supported in input tag value attribute
         * https://make.wordpress.org/core/2015/07/23/changes-to-the-shortcode-api/
         */
        add_filter('wp_kses_allowed_html', [$this, 'wlm_kses_allowed_tags'], 10, 2);
    }

    /**
     * Enqueue shortcode inserter JS
     */
    public function enqueue_shortcode_inserter_js()
    {
        wp_enqueue_script('wishlistmember-shortcode-insert-js', wishlistmember_instance()->plugin_url3 . '/assets/js/shortcode-inserter.js', ['jquery'], wishlistmember_instance()->version, true);
    }

    /**
     * Filter for 'wp_kses_allowed_html'
     *
     * @param  array        $allowed_tags Allowed HTML Tags.
     * @param  string|array $context      Context.
     * @return array
     */
    public function wlm_kses_allowed_tags($allowed_tags, $context)
    {
        if (is_admin() || ! in_the_loop()) {
            return $allowed_tags;
        }
        if ('post' === $context && is_array($allowed_tags)) {
            if (! isset($allowed_tags['input'])) {
                $allowed_tags['input'] = ['value' => true];
            } else {
                /*
                 * other might have added some attributes for input
                 * this will prevent from overwriting other attributes
                 */
                if (! isset($allowed_tags['input']['value']) || ! $allowed_tags['input']['value']) {
                    $allowed_tags['input']['value'] = true;
                }
            }
        }
        return $allowed_tags;
    }

    /**
     * Shortcode function for wlm_ismember
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function ismember($atts, $content, $code)
    {
        global $wp_query;

        $is_userpost = false;

        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        if (wlm_arrval($current_user->caps, 'administrator')) {
            return do_shortcode($content);
        }

        if (wishlistmember_instance()->get_option('payperpost_ismember')) {
            $is_userpost = in_array($wp_query->post->ID, wishlistmember_instance()->get_membership_content($wp_query->post->post_type, 'U-' . $current_user->ID));
        }

        $user_levels = wishlistmember_instance()->get_membership_levels($current_user->ID, null, true, null, true);
        if (count($user_levels) || $is_userpost) {
            return do_shortcode($content);
        } else {
            return '';
        }
    }

    /**
     * Shortcode function for wlm_nonmember
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function nonmember($atts, $content, $code)
    {

        global $wp_query;

        $is_userpost = false;

        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        if (wlm_arrval($current_user->caps, 'administrator')) {
            return do_shortcode($content);
        }

        if (wishlistmember_instance()->get_option('payperpost_ismember')) {
            $is_userpost = in_array($wp_query->post->ID, wishlistmember_instance()->get_membership_content($wp_query->post->post_type, 'U-' . $current_user->ID));
        }

        $user_levels = wishlistmember_instance()->get_membership_levels($current_user->ID, null, true, null, true);
        if (count($user_levels) || $is_userpost) {
            return '';
        } else {
            return do_shortcode($content);
        }
    }

    /**
     * Shortcode function for wlm_register
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function regform($atts, $content, $code)
    {
        if (in_array($code, ['wlm_register', 'wlmregister', 'register'], true)) {
            $level_name = implode(' ', $atts);
        } else {
            if ('wlm_register' === substr($code, 0, 12)) {
                $level_name = substr($code, 13);
            } else {
                $level_name = substr($code, 12);
            }
        }

        foreach ($this->wpm_levels as $level_id => $level) {
            if (trim(strtoupper($level['name'])) === trim(strtoupper(html_entity_decode($level_name)))) {
                return do_shortcode(wishlistmember_instance()->reg_content($level_id, true));
            }
        }
        return '';
    }

    /**
     * Shortcode handler for wlm_private
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function private_tags($atts, $content, $code)
    {
        $atts = is_array($atts) ? [implode(' ', $atts)] : $atts; // Lets glue attributes together for level names with spaces.

        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        if (wlm_arrval($current_user->caps, 'administrator')) {
            return do_shortcode($content);
        }

        $user_levels = wishlistmember_instance()->get_membership_levels($current_user->ID, null, true, null, true);

        $level_names = [];

        if ('wlm_private' === $code || 'wlmprivate' === $code) {
            foreach ($atts as $key => $value) {
                $value = wlm_trim($value, "'");
                if (is_int($key)) {
                    $level_names = array_merge($level_names, explode('|', $value));
                    unset($atts[ $key ]);
                }
            }
        } else {
            if ('wlm_private' === substr($code, 0, 11)) {
                $level_names[] = substr($code, 12);
            } else {
                $level_names[] = substr($code, 11);
            }
        }

        $level_names = array_map('trim', $level_names);
        $level_ids   = [];

        foreach ($this->wpm_levels as $level_id => $level) {
            $level_ids[ $level['name'] ] = $level_id;
        }

        $match = false;
        foreach ($level_names as $level_name) {
            $level_id = $level_ids[ $level_name ];
            if (in_array($level_id, $user_levels)) {
                $match = true;
                break;
            }
        }

        if ($match) {
            return do_shortcode($content);
        } else {
            $protectmsg = wishlistmember_instance()->get_option('private_tag_protect_msg');
            $protectmsg = str_replace('[level]', implode(', ', $level_names), $protectmsg);
            $protectmsg = do_shortcode($protectmsg);
            return $protectmsg;
        }
    }

    /**
     * Shortcode function for !wlm_private
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function reverse_private_tags($atts, $content, $code)
    {
        $atts = is_array($atts) ? [implode(' ', $atts)] : $atts; // Lets glue attributes together for level names with spaces.

        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        if (wlm_arrval($current_user->caps, 'administrator')) {
            return do_shortcode($content);
        }

        $user_levels = wishlistmember_instance()->get_membership_levels($current_user->ID, null, true, null, true);
        $level_names = [];

        if ('!private' === $code || '!wlm_private' === $code) {
            foreach ($atts as $key => $value) {
                $value = wlm_trim($value, "'");
                if (is_int($key)) {
                    $level_names = array_merge($level_names, explode('|', $value));
                    unset($atts[ $key ]);
                }
            }
        } else {
            if ('!private' === substr($code, 0, 8)) {
                $level_names[] = substr($code, 9);
            } else {
                $level_names[] = substr($code, 13);
            }
        }

        $level_names = array_map('trim', $level_names);

        // Lets get the valid levels in the tag.
        $tag_levels = [];
        foreach ($this->wpm_levels as $level_id => $level) {
            if (in_array($level['name'], $level_names, true)) {
                $tag_levels[] = $level_id;
            }
        }

        /*
         * now we have the users level and the levels in the tag
         * lets check if one of levels in the tag is in users level
         */
        $user_match_level = array_intersect($tag_levels, $user_levels);

        if (count($user_match_level) > 0) { // If theres a level in the tag that users have.
            // Display the message.
            $protectmsg = wishlistmember_instance()->get_option('reverse_private_tag_protect_msg');
            $protectmsg = str_replace('[level]', implode(', ', $level_names), $protectmsg);
            return $protectmsg;
        } else { // If user does not have all levels in the tag, return the content.
            return do_shortcode($content);
        }
    }

    /**
     * Shortcode function for a bunch of user info shortcodes
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function userinfo($atts, $content, $code)
    {
        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        $wpm_useraddress = wishlistmember_instance()->Get_UserMeta($current_user->ID, 'wpm_useraddress');
        static $password = null;
        switch ($code) {
            case 'firstname':
            case 'wlm_firstname':
            case 'wlmfirstname':
                return $current_user->first_name;
            case 'lastname':
            case 'wlm_lastname':
            case 'wlmlastname':
                return $current_user->last_name;
            case 'email':
                if (( current_user_can('administrator') && is_plugin_active('mailpoet/mailpoet.php') && 'Thrive Theme Builder' === wp_get_theme() )) {
                    return '[email]';
                } else {
                    return $current_user->user_email;
                }
            case 'wlm_email':
            case 'wlmemail':
                return $current_user->user_email;
            case 'memberlevel':
            case 'wlm_memberlevel':
            case 'wlmmemberlevel':
                $user_levels = wishlistmember_instance()->get_membership_levels($current_user->ID, true, null, null, true);
                if ($user_levels) {
                    return $user_levels;
                } else {
                    return __('No Membership Level', 'wishlist-member');
                }

                break;
            case 'username':
            case 'wlm_username':
            case 'wlmusername':
                return $current_user->user_login;
            case 'profileurl':
            case 'wlm_profileurl':
            case 'wlmprofileurl':
                return get_bloginfo('wpurl') . '/wp-admin/profile.php';
            case 'password':
            case 'wlm_password':
            case 'wlmpassword':
                // Password shortcode retired to prevent security issues.
                return '********';
            case 'wlm_autogen_password':
                return empty(wlm_getcookie('wlm_autogen_pass')) ? '********' : wlm_getcookie('wlm_autogen_pass');
            case 'website':
            case 'wlm_website':
            case 'wlmwebsite':
                return $current_user->user_url;
            case 'aim':
            case 'wlm_aim':
            case 'wlmaim':
                return $current_user->aim;
            case 'yim':
            case 'wlm_yim':
            case 'wlmyim':
                return $current_user->yim;
            case 'jabber':
            case 'wlm_jabber':
            case 'wlmjabber':
                return $current_user->jabber;
            case 'biography':
            case 'wlm_biography':
            case 'wlmbiography':
                return $current_user->description;
            case 'company':
            case 'wlm_company':
            case 'wlmcompany':
                return (string) wlm_arrval($wpm_useraddress, 'company');
            case 'address':
            case 'wlm_address':
            case 'wlmaddress':
                $address = $wpm_useraddress['address1'];
                if (! empty($wpm_useraddress['address2'])) {
                    $address .= '<br />' . $wpm_useraddress['address2'];
                }
                return $address;
            case 'address1':
            case 'wlm_address1':
            case 'wlmaddress1':
                return (string) wlm_arrval($wpm_useraddress, 'address1');
            case 'address2':
            case 'wlm_address2':
            case 'wlmaddress2':
                return (string) wlm_arrval($wpm_useraddress, 'address2');
            case 'city':
            case 'wlm_city':
            case 'wlmcity':
                return (string) wlm_arrval($wpm_useraddress, 'city');
            case 'state':
            case 'wlm_state':
            case 'wlmstate':
                return (string) wlm_arrval($wpm_useraddress, 'state');
            case 'zip':
            case 'wlm_zip':
            case 'wlmzip':
                return (string) wlm_arrval($wpm_useraddress, 'zip');
            case 'country':
            case 'wlm_country':
            case 'wlmcountry':
                return (string) wlm_arrval($wpm_useraddress, 'country');
            case 'loginurl':
            case 'wlm_loginurl':
            case 'wlmloginurl':
                return wp_login_url();
            case 'wlm_logouturl':
            case 'wlmlogouturl':
                if (! is_user_logged_in()) {
                    return;
                }
                return wp_logout_url();
        }
    }

    /**
     * Shortcode function for wlm_user
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function get_and_post($atts, $content, $code)
    {
        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        switch ($atts) {
            case 'post':
                $userpost = (array) wishlistmember_instance()->WLMDecrypt($current_user->wlm_reg_post);
                if ($atts[1]) {
                    return $userpost[ $atts[1] ];
                } else {
                    return nl2br(print_r($userpost, true));
                }
            case 'get':
                $userpost = (array) wishlistmember_instance()->WLMDecrypt($current_user->wlm_reg_get);
                if ($atts[1]) {
                    return $userpost[ $atts[1] ];
                } else {
                    return nl2br(print_r($userpost, true));
                }
        }
    }

    /**
     * Shortcode function for wlm_rss
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function rss($atts, $content, $code)
    {
        return get_bloginfo('rss2_url');
    }

    /**
     * Shortcode function for level information shortcodes
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function levelinfo($atts, $content, $code)
    {
        static $wpm_levels = null, $wpm_level_names = null;

        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        if (is_null($wpm_levels)) {
            $wpm_levels = (array) wishlistmember_instance()->get_option('wpm_levels');
        }

        if (is_null($wpm_level_names)) {
            $wpm_level_names = [];
            foreach ($wpm_levels as $id => $level) {
                $wpm_level_names[ wlm_trim($level['name']) ] = $id;
            }
        }

        $format = wlm_arrval($atts, 'format') ? wlm_arrval('lastresult') : get_option('date_format');
        if (isset($atts['format'])) {
            unset($atts['format']);
        }

        $level_name = isset($atts['level']) ? wlm_trim($atts['level']) : $level_name = trim(implode(' ', (array) $atts));
        $level_id = $wpm_level_names[ $level_name ];

        if (! $level_id) {
            return '';
        }

        switch ($code) {
            case 'wlm_expiry':
            case 'wlmexpiry':
            case 'wlm_expiration':
                // Don't return text if user doesn't belong to the level.
                $user_levels = wishlistmember_instance()->get_membership_levels($current_user->ID, null, true, null, true);
                if (count($user_levels)) {
                    if (in_array($level_id, $user_levels)) {
                        $expiry_date = wishlistmember_instance()->level_expire_date($level_id, $current_user->ID);
                        if (false !== $expiry_date) {
                                return date_i18n($format, $expiry_date);
                        }
                    }
                }
                break;
            case 'wlm_joindate':
            case 'wlmjoindate':
                $join_date = wishlistmember_instance()->user_level_timestamp($current_user->ID, $level_id);
                if (false !== $join_date) {
                    return date_i18n($format, $join_date);
                }
                break;
        }
        return '';
    }

    /**
     * Shortcode function for wlm_counter
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function counter($atts, $content, $code)
    {
        $x = wishlistmember_instance()->ReadURL('http://wishlistactivation.com/counter.php');
        if (false !== $x && $x > 0) {
            wishlistmember_instance()->save_option('wlm_counter', $x);
        } else {
            $x = wishlistmember_instance()->get_option('wlm_counter');
        }
        return $x;
    }

    /**
     * Shortcode function for wlm_loginform
     *
     * @param  array  $atts    Attributes.
     * @param  string $content Content.
     * @param  string $code    Code.
     * @return string
     */
    public function login($atts, $content, $code)
    {
        global $wp;
        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        if (! $current_user->ID) {
            if (! empty(wlm_arrval($atts, 'redirect'))) {
                $redirect = wlm_arrval($atts, 'redirect');
            } elseif (wishlistmember_instance()->get_option('enable_login_redirect_override')) {
                $redirect = ! empty(wlm_get_data()['wlfrom']) ? esc_attr(stripslashes((string) wlm_get_data()['wlfrom'])) : 'wishlistmember';
            } else {
                $redirect = '';
            }
            $loginurl  = esc_url(site_url('wp-login.php', 'login_post'));
            $loginurl2 = wp_lostpassword_url();

            $txt_lost = __('Lost your Password?', 'wishlist-member');

            $username_field = wlm_form_field(
                [
                    'label' => __('Username or Email Address', 'wishlist-member'),
                    'type'  => 'text',
                    'name'  => 'log',
                ]
            );
            $password_field = wlm_form_field(
                [
                    'label'  => __('Password', 'wishlist-member'),
                    'type'   => 'password',
                    'name'   => 'pwd',
                    'toggle' => true,
                ]
            );
            $remember_field = wlm_form_field(
                [
                    'type'    => 'checkbox',
                    'name'    => 'rememberme',
                    'options' => ['forever' => __('Remember Me', 'wishlist-member')],
                ]
            );
            $submit_button  = wlm_form_field(
                [
                    'type'  => 'submit',
                    'name'  => 'wp-submit',
                    'value' => __('Login', 'wishlist-member'),
                ]
            );
            $otl = '';
            if (wishlistmember_instance()->get_option('show_onetime_login_option')) {
                $otl = '<p class="wlmember_login_shortcode_otl_request"><a href="' . add_query_arg('action', 'wishlistmember-otl', wp_login_url()) . '">' . wishlistmember_instance()->get_option('onetime_login_link_label') . '</a></p>';
            }
            $form = <<<STRING
<form action="{$loginurl}" method="post" class="wlm_inpageloginform">
<input type="hidden" name="wlm_redirect_to" value="{$redirect}" />
<input type="hidden" name="redirect_to" value="{$redirect}" />
<div class="wlm3-form">
{$username_field}
{$password_field}
{$remember_field}
{$submit_button}
{$otl}
<p>
<a href="{$loginurl2}">{$txt_lost}</a>
</p>
</div>
</form>
STRING;
            /**
             * Filters the login form markup
             *
             * @param string  $form              HTML Markup.
             * @param string  $form_class        Form class.
             */
            if (isset($atts['ajax_login']) && $atts['ajax_login']) {
                $form = apply_filters('wishlistmember_login_form_shortcode', $form, 'wlm_inpageloginform');
            }
        } else {
            $form = wishlistmember_instance()->widget([], true);
        }

        /**
         * Add Filterable variables on the login form shortcode's before and after HTML content.
         *
         * $before_login_shortcode HTML before the login form.
         * $after_login_shortcode  HTML after the login form.
         */
        $before_login_shortcode = apply_filters('wishlistmember_login_form_shortcode_before', '');
        $after_login_shortcode  = apply_filters('wishlistmember_login_form_shortcode_after', '');
        $login_form = sprintf(
            '%s<div class="WishListMember_LoginMergeCode">%s</div>%s',
            $before_login_shortcode,
            $form,
            $after_login_shortcode
        );
        wp_enqueue_style('wishlistmember-frontend-styles-login_mergecode');
        wp_enqueue_style('wishlistmember-frontend-styles-combined');
        return $login_form;
    }

    /**
     * Shortcode function for wlm_contentlevels
     *
     * @param  array  $atts    Shortcode attributes.
     * @param  string $content Shortcode content.
     * @param  string $code    Shortcode.
     * @return string          Shortcode output.
     */
    public function content_levels_list($atts, $content, $code)
    {
        $wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
        $type_list  = ['comma', 'ol', 'ul'];
        if (! is_array($atts)) {
            $atts = [];
        }
        $atts['link_target']    = isset($atts['link_target']) ? esc_attr($atts['link_target']) : '_blank';
        $atts['type']           = isset($atts['type']) ? $atts['type'] : 'comma';
        $atts['class']          = isset($atts['class']) ? $atts['class'] : 'wlm_contentlevels';
        $atts['show_link']      = isset($atts['show_link']) ? $atts['show_link'] : 1;
        $atts['salespage_only'] = isset($atts['salespage_only']) ? $atts['salespage_only'] : 1;

        $atts['type']           = in_array($atts['type'], $type_list, true) ? $atts['type'] : 'comma';
        $atts['link_target']    = '' !== $atts['link_target'] ? "target='{$atts['link_target']}'" : '';
        $atts['class']          = '' !== $atts['class'] ? $atts['class'] : 'wlm_contentlevels';
        $atts['show_link']      = 0 === (int) $atts['show_link'] ? false : true;
        $atts['salespage_only'] = 0 === (int) $atts['salespage_only'] ? false : true;

        $redirect = ! empty(wlm_get_data()['wlfrom']) ? wlm_get_data()['wlfrom'] : false;
        $post_id  = url_to_postid($redirect);
        $ret      = [];
        if ($redirect && $post_id) {
            $ptype  = get_post_type($post_id);
            $levels = wishlistmember_instance()->get_content_levels($ptype, $post_id);
            foreach ($levels as $level) {
                $salespage        = wlm_trim(wlm_arrval($wpm_levels[ $level ], 'salespage'));
                $enable_salespage = (bool) wlm_arrval($wpm_levels[ $level ], 'enable_salespage');
                if (isset($wpm_levels[ $level ])) {
                    if ($atts['show_link'] && $salespage && $enable_salespage) {
                        $ret[] = "<a class='{$atts['class']}_link' href='{$wpm_levels[$level]['salespage']}' {$atts['link_target']}>{$wpm_levels[$level]['name']}</a>";
                    } else {
                        if (! $atts['salespage_only']) {
                            $ret[] = $wpm_levels[ $level ]['name'];
                        }
                    }
                }
            }
        }
        if ($ret) {
            if ('comma' === $atts['type']) {
                $holder = implode(',', $ret);
                $holder = wlm_trim($holder, ',');
            } else {
                $holder  = "<{$atts['type']} class='{$atts['class']}'><li>";
                $holder .= implode('</li><li>', $ret);
                $holder .= "</li></{$atts['type']}>";
            }
            $ret = $holder;
        } else {
            $ret = '';
        }
        return $ret;
    }

    /**
     * Shortcode function for wlm_custom
     *
     * @param  array  $atts    Shortcode attributes.
     * @param  string $content Shortcode content.
     * @param  string $code    Shortcode.
     * @return string          Shortcode output.
     */
    public function custom_registration_fields($atts, $content, $code)
    {
        global $wpdb;
        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }

        $atts = array_values($atts);
        if (! is_array($atts[0])) {
            switch ($atts[0]) {
                case '':
                    $results = $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT * FROM `' . esc_sql(wishlistmember_instance()->table_names->user_options) . '` WHERE `user_id`=%d AND `option_name` LIKE %s',
                            $current_user->ID,
                            'custom\_%'
                        )
                    );
                    $results = wishlistmember_instance()->get_user_custom_fields($current_user->ID);
                    if (! empty($results)) {
                        $output = [];
                        foreach ($results as $key => $value) {
                                $output[] = sprintf('<li>%s : %s</li>', $key, implode('<br />', (array) $value));
                        }
                        $output = trim(implode('', $output));
                        if ($output) {
                            return '<ul>' . $output . '</ul>';
                        }
                    }
                    break;
                default:
                    $field = 'custom_' . $atts[0];
                    return trim(wishlistmember_instance()->Get_UserMeta($current_user->ID, $field));
                    // Return implode( '<br />', (array) wishlistmember_instance()->Get_UserMeta( $current_user->ID, $field ) );
            }
        }
    }

    /**
     * Manually process shortcodes
     *
     * @param  integer|WP_User $user     User ID or WP_User object.
     * @param  string          $content  Content.
     * @param  boolean         $dataonly True to return data only. Default false.
     * @return string|array           Processed $content if $dataonly is false or
     *                                an array of processed shortcode data if $dataonly is true
     */
    public function manual_process($user, $content, $dataonly = false)
    {
        $user = is_a($user, 'WP_User') ? $user : get_userdata($user);
        if ($user->ID) {
            $GLOBALS['wlm_shortcode_user'] = $user;
            $pattern                       = get_shortcode_regex();
            preg_match_all('/' . $pattern . '/s', $content, $matches, PREG_SET_ORDER);
            if (is_array($matches) && count($matches)) {
                $data = [];
                foreach ($matches as $match) {
                    $scode = $match[2];
                    $code  = $match[0];
                    if (isset($this->shortcode_functions[ $scode ])) {
                        if (! isset($data[ $code ])) {
                            $data[ $code ] = do_shortcode_tag($match);
                        }
                    }
                }
                if (! $dataonly) {
                    $content = str_replace(array_keys($data), $data, $content);
                } else {
                    $content = $data;
                }
            }
        }
        return $content;
    }

    /**
     * Return minimum password length
     *
     * @return integer
     */
    public function min_password_length()
    {
        global $wpdb;
        $min_value = wishlistmember_instance()->get_option('min_passlength');
        if (! $min_value) {
            $min_value = 8;
        }
        return $min_value;
    }

    /**
     * Shortcode function for wlm_has_access
     *
     * @param  array  $atts    Shortcode attributes.
     * @param  string $content Shortcode content.
     * @return string          Shortcode output.
     */
    public function hasaccess($atts, $content)
    {
        extract(
            shortcode_atts(
                [
                    'post' => null,
                ],
                $atts
            )
        );

        $pid = $post;
        if (empty($pid)) {
            global $post;
            $pid = $post->ID;
        }

        global $current_user;

        if (wishlistmember_instance()->has_access($current_user->ID, $pid)) {
            return $content;
        }
        return null;
    }

    /**
     * Shortcode function for wlm_has_no_access
     *
     * @param  array  $atts    Shortcode attributes.
     * @param  string $content Shortcode content.
     * @return string          Shortcode output.
     */
    public function hasnoaccess($atts, $content)
    {
        extract(
            shortcode_atts(
                [
                    'post' => null,
                ],
                $atts
            )
        );

        $pid = $post;
        if (empty($pid)) {
            global $post;
            $pid = $post->ID;
        }

        global $current_user;

        if (wishlistmember_instance()->has_access($current_user->ID, $pid)) {
            return null;
        }
        return $content;
    }

    /**
     * Shortcode function for wlm_userpayperpost
     *
     * @param  string $atts Attributes.
     * @return string
     */
    public function user_payperpost($atts)
    {
        extract(
            shortcode_atts(
                [
                    'sort'          => 'ascending',
                    'sortby'        => 'date-assigned',
                    'total'         => 5,
                    'liststyletype' => 'none',
                    'showmoretext'  => __('Show More Pay Per Posts', 'wishlist-member'),
                    'totalshowmore' => 3,
                    'buttonstyle'   => 'link',
                    'ineditor'      => false,
                ],
                $atts
            )
        );

        if (! is_user_logged_in()) {
            return __('You need to log in to view your Pay Per Posts.', 'wishlist-member');
        }

        if (wlm_arrval(wlm_arrval($GLOBALS, 'wlm_shortcode_user'), 'ID')) {
            $current_user = $GLOBALS['wlm_shortcode_user'];
        } else {
            $current_user = wlm_arrval($GLOBALS, 'current_user');
        }
        $ppp_uid      = 'U-' . $current_user->ID;
        $user_ppplist = wishlistmember_instance()->get_user_pay_per_post($ppp_uid, true, null, false, $sortby, $sort);

        /**
        * Filters user pay per posts for shortcode
        *
        * @param array $user_ppplist Array of user's pay per posts
        */
        $user_ppplist = apply_filters('wishlistmember_payperpost_shortcode_user_ppplist', $user_ppplist);

        $jss          = <<<HEREDOC
		<script type="text/javascript">
		jQuery(document).ready(function ($) {

				size_li = $(".wishlistmember-mergecode-payperposts-list li").size();
				x=$total;
				$('.wishlistmember-mergecode-payperposts-list li:lt('+x+')').show();
				$('.wishlistmember-mergecode-payperposts-showmorebutton').click(function (a) {


					if ($totalshowmore==-1) {
						x = size_li;
					}else{
						x= (x+$totalshowmore <= size_li) ? x+$totalshowmore : size_li;
					}
					$('.wishlistmember-mergecode-payperposts-list li:lt('+x+')').show();


					if ( x == size_li ){
						$('.wishlistmember-mergecode-payperposts-showmorebutton').hide();
					}else{
						$('.wishlistmember-mergecode-payperposts-showmorebutton').show();
					};
				});

				if ( x == size_li ){
					$('.wishlistmember-mergecode-payperposts-showmorebutton').hide();
				}else{
					$('.wishlistmember-mergecode-payperposts-showmorebutton').show();
				};
			});
		</script>
HEREDOC;

        $style    = "<style>
		.wishlistmember-mergecode-payperposts-list li {
			list-style-type: $liststyletype;
		}
		</style>";
        $ppp_list = $jss . $style;
        if (( $ineditor && ( 0 == $total ) ) || ( empty($user_ppplist) )) {
            $ppp_list .= '<p>No Posts to Display</p>';
            return $ppp_list;
        }
        $i = 0;
        if (0 !== $total) {
            $ppp_list .= '<ul class="wishlistmember-mergecode-payperposts-list">';
            foreach ($user_ppplist as $list) {
                if ($ineditor && ( $i >= $total ) && ( '-1' !== $total )) {
                    break;
                }
                $link      = get_permalink($list->content_id);
                $ppp_list .= '<li><a href="' . $link . '">' . get_the_title($list->content_id) . '</a></li>';
                ++$i;
            }
            $ppp_list .= '</ul>';
        }

        // If total number of user's PPP is less than the $total then don't show the show more text.
        if ($total > count($user_ppplist)) {
            return $ppp_list;
        }

        if (0 !== ( (int) $totalshowmore ) && ( (int) $total >= 0 )) {
            if ('button' === $buttonstyle) {
                $ppp_list .= '<div><button class="wishlistmember-mergecode-payperposts-showmorebutton">' . $showmoretext . '</button></div>';
            } else {
                $ppp_list .= '<div><a href="javascript:void(0);" class="wishlistmember-mergecode-payperposts-showmorebutton">' . $showmoretext . '</a></div>';
            }
        }
        return '' . $ppp_list . '';
    }

    /**
     * Shortcode function for wlm_wlm_payperpost
     *
     * @param  array $atts Attributes.
     * @return string
     */
    public function registered_payperpost($atts)
    {
        $ppp = wlm_trim(wlm_get_data()['l']);
        if (! $ppp || ! wishlistmember_instance()->is_ppp_level($ppp) || ! preg_match('/\d+$/', $ppp, $match)) {
            return '';
        }

        $title = get_the_title($match[0]);
        $url   = get_permalink($match[0]);

        if (! $url) {
            return '';
        }
        if (! $title) {
            $title = $url;
        }

        return sprintf('<a href="%s">%s</a>', $url, $title);
    }

    /**
     * Add shortcode
     *
     * @param string   $shortcode Shortcode to add.
     * @param callable $function  Function to call.
     */
    private function add_shortcode($shortcode, $function)
    {
        $this->shortcode_functions[ $shortcode ] = $function;
        add_shortcode($shortcode, $function);
    }

    /**
     * Generate the shortcodes menu for TinyMCE inserter
     *
     * @param array $codes Codes to render.
     */
    public function render_tinymce_shortcode_menu($codes = null)
    {
        static $menu_level;
        $this->enqueue_shortcode_inserter_js();
        ++$menu_level;
        if (is_null($codes)) {
            $codes = $this->manifest;
        }
        $output = [];
        foreach ($codes as $key => $code) {
            if (isset($code['label'])) {
                $attributes = ( ! empty($code['attributes']) || ! empty($code['enclosing']) );
                $output[]   = [
                    'text'    => $code['label'],
                    'onclick' => sprintf('function() { wlm.tinymce_show_shortcode( %s, %s, %s ) }', wp_json_encode($key), wp_json_encode($code['label']), wp_json_encode($attributes)),
                ];
            } else {
                // Group menu.
                $menu = ['text' => $key];
                if (is_array($code)) {
                    $menu = $menu + (array) $this->render_tinymce_shortcode_menu($code);
                }
                $output[] = $menu;
            }
        }
        --$menu_level;
        return ['menu' => $output];
    }

    /**
     * Displays the shortcodes menu
     *
     * @param array $codes Codes to render.
     */
    public function render_shortcode_menu($codes = null)
    {
        static $menu_level;
        $this->enqueue_shortcode_inserter_js();
        ++$menu_level;
        if (1 === $menu_level) {
            echo '<div class="wlm-shortcodes-menu mb-3">';
        }
        if (is_null($codes)) {
            $codes = $this->manifest;
        }
        foreach ($codes as $key => $code) {
            if (isset($code['label'])) {
                if (! empty($code['attributes']) || ! empty($code['enclosing'])) {
                    $key = '#wlm-shortcode-inserter-' . $key;
                }
                printf('<a class="dropdown-item shortcode-creator" href="#" data-value="%s">%s</a>', esc_attr($key), esc_html($code['label']));
            } else {
                if (empty($code)) {
                    continue;
                }
                $variation     = $menu_level < 2 ? 'dropdown' : 'dropright';
                $dropdown_item = $menu_level < 2 ? 'pr-3 py-2' : 'dropdown-item';

                $id = $menu_level > 1 ? 'wlm-codes-' . preg_replace('/[^0-9a-z]+/', '-', trim(strtolower($key))) : '';
                printf('<div class="btn-group %s"><a class="%s dropdown-toggle" data-toggle="dropdown" data-target="#%s" aria-haspopup="true" aria-expanded="false">%s</a>', esc_attr($variation), esc_attr($dropdown_item), esc_attr($id), esc_html($key));
                printf('<div class="dropdown-menu" id="%s">', esc_attr($id));
                $this->render_shortcode_menu($code);
                echo '</div></div>';
            }
        }
        --$menu_level;
        if (! $menu_level) {
            echo '</div>';
        }
    }

    /**
     * Displays the shortcodes attributes
     *
     * @param array $shortcodes Shortcodes to render the form form.
     */
    public function render_shortcode_attributes_form($shortcodes = null)
    {
        $this->enqueue_shortcode_inserter_js();
        $screen = get_current_screen();

        if (is_null($shortcodes)) {
            $shortcodes = $this->manifest;
        }
        foreach ($shortcodes as $shortcode => $options) {
            if (is_array($options)) {
                if (! empty($options['attributes']) || ! empty($options['enclosing'])) {
                    echo '<form data-shortcode="' . esc_attr($shortcode) . '" id="wlm-shortcode-inserter-' . esc_attr($shortcode) . '" class="wlm-shortcode-attributes row" style="display:none">';
                    printf('<h3 class="mb-3 col-12">%s</h3>', esc_html($options['label']));
                    $has_preview = ! empty($options['has_preview']);
                    if ($has_preview) {
                        echo '<div class="col-6"><div class="row">';
                    }
                    if (isset($options['attributes']) && is_array($options['attributes'])) {
                        foreach ($options['attributes'] as $attr_name => $attr_options) {
                                $dependency = empty($attr_options['dependency']) ? '' : $attr_options['dependency'];
                                $columns    = wlm_or(abs((int) wlm_arrval($attr_options, 'columns')), 12);
                            if ($columns < 0) {
                                echo '<div class="w-100"></div>';
                            }
                                printf(
                                    '<div data-dependency="%s" class="wlm-shortcode-attribute col-%d">',
                                    esc_attr($dependency),
                                    esc_attr($columns)
                                );
                                echo '<div class="form-group ' . esc_attr(wlm_arrval($attr_options, 'form_group_class')) . '">';
                                $tooltip = '';
                            if (! empty($attr_options['tooltip'])) {
                                if ($screen->parent_base == 'edit') {
                                    $tooltip = '<span class="dashicons dashicons-editor-help" title="' . esc_attr(strip_tags(trim(preg_replace('#<(br|p|div)\s*.*?/?>#i', "\n", $attr_options['tooltip'])))) . '"></span>';
                                } else {
                                    $tooltip_size = wlm_arrval($attr_options, 'tooltip_size');
                                    $tooltip = wishlistmember_instance()->tooltip($attr_options['tooltip'], $tooltip_size ? $tooltip_size : 'sm', true);
                                }
                            }
                                echo '<label class="d-block ' . esc_attr(wlm_arrval($attr_options, 'label_class')) . '"><span style="position: relative";>' . esc_html(wlm_arrval($attr_options, 'label')) . $tooltip . ' </span></label>';
                                $multiple = '';
                            switch (wlm_arrval($attr_options, 'type')) {
                                case 'select-multiple':
                                    $multiple = 'multiple';
                                    // Continue to 'select'.
                                case 'select':
                                    $separator   = wlm_or(wlm_trim(wlm_arrval($attr_options, 'separator')), '|');
                                    $placeholder = ! empty($attr_options['placeholder']) ? $attr_options['placeholder'] : '';
                                    printf(
                                        '<select style="width:100%%;" data-separator="%1$s" class="wlm-select form-control" name="%2$s" %3$s data-%4$s="%5$s">',
                                        esc_attr($separator),
                                        esc_attr($attr_name),
                                        esc_attr($multiple),
                                        esc_attr($placeholder ? 'placeholder' : 'placeholderx'),
                                        esc_attr($placeholder)
                                    );
                                    if ($placeholder) {
                                        echo '<option value="" />';
                                    }
                                    // Recursive function to render options and optgroups.
                                    $this->options($attr_options['options'], $attr_options);
                                    echo '</select>';
                                    break;
                                case 'checkbox':
                                case 'radio':
                                    $inline = (bool) wlm_arrval($attr_options, 'inline') ? 'form-check-inline' : '';
                                    foreach ($attr_options['options'] as $value => $value_options) {
                                        $checked    = ( isset($attr_options['default']) && in_array($value, (array) $attr_options['default']) ) ? 'checked' : '';
                                        $dependency = empty($value_options['dependency']) ? '' : $value_options['dependency'];
                                        printf(
                                            '<div class="form-check %8$s %9$s"><input id="%7$s" class="form-check-input" type="%2$s" name="%3$s" value="%4$s" %5$s><label for="%7$s" data-dependency="%1$s" class="form-check-label">%6$s',
                                            esc_attr($dependency),
                                            esc_attr($attr_options['type']),
                                            esc_attr($attr_name),
                                            esc_attr($value),
                                            esc_attr($checked),
                                            esc_html($value_options['label']),
                                            esc_attr(uniqid('id.', true)),
                                            esc_attr($inline),
                                            esc_attr(wlm_arrval($attr_options, 'form_check_class'))
                                        );
                                        if (! empty($value_options['tooltip'])) {
                                            if ($screen->parent_base == 'edit') {
                                                echo '<span class="dashicons dashicons-editor-help" title="' . esc_attr(strip_tags(trim(preg_replace('#<(br|p|div)\s*.*?/?>#i', "\n", $value_options['tooltip'])))) . '"></span>';
                                            } else {
                                                $tooltip_size = wlm_arrval($value_options, 'tooltip_size');
                                                wishlistmember_instance()->tooltip($value_options['tooltip'], $tooltip_size ? $tooltip_size : 'sm');
                                            }
                                        }
                                        echo '</label>';
                                        if (isset($value_options['unchecked'])) {
                                            printf('<input type="hidden" name="%s" value="%s">', esc_attr($attr_name), esc_attr($value_options['unchecked']));
                                        }
                                        echo '</div>';
                                    }
                                    break;
                                case 'text':
                                default:
                                    printf('<input class="form-control" type="text" name="%s" value="%s" placeholder="%s">', esc_attr($attr_name), esc_attr(wlm_arrval($attr_options, 'default')), esc_attr(wlm_arrval($attr_options, 'placeholder')));
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    if (! empty($options['enclosing'])) {
                        $placeholder = preg_match('/[a-zA-Z]/', $options['enclosing']) ? $options['enclosing'] : '';
                        printf('<div class="form-group col-12"><label>Content</label><textarea class="form-control" name="__enclosed_content__" placeholder="%s"></textarea></div>', esc_attr($placeholder));
                    }
                    if ($has_preview) {
                        echo '</div></div><div class="col-6 wlm-shortcode-inserter-preview"></div>';
                    }

                    echo '</form>';
                } else {
                    $this->render_shortcode_attributes_form($options);
                }
            }
        }
    }

    /**
     * Render select box option markup
     *
     * @param array $options      Options.
     * @param array $attr_options ?
     */
    private function options($options, $attr_options)
    {
        foreach ($options as $value => $voptions) {
            $dependency = empty($voptions['dependency']) ? '' : $voptions['dependency'];
            if (isset($voptions['options']) && is_array($voptions['options'])) {
                // Optgroup.
                printf('<optgroup label="%s" data-dependency="%s">', esc_attr($voptions['label']), esc_attr($dependency));
                $this->options($voptions['options'], $attr_options);
                echo '</optgroup>';
            } else {
                // Option.
                $selected = ( isset($attr_options['default']) && in_array($value, (array) $attr_options['default']) ) ? 'selected' : '';
                printf('<option data-dependency="%s" value="%s" %s>%s</option>', esc_attr($dependency), esc_attr($value), esc_attr($selected), esc_html($voptions['label']));
            }
        }
    }
}
