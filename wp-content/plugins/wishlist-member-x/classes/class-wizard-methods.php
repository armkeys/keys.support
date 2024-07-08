<?php

/**
 * Wizard Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Member Methods trait
 */
trait Wizard_Methods
{
    /**
     * Save Wizard Steps
     * Called by wp_ajax_wlm_wizard_steps_handler action
     */
    public function steps_handler()
    {
        $nonce_result = check_ajax_referer('wlm-wizard_' . site_url(), 'wlm-wizard-nonce', false);
        if (! $nonce_result) {
            wp_send_json_error();
        }

        switch (wlm_post_data()['process']) {
            case 'exit':
                wishlistmember_instance()->save_option('wizard_ran', 1);

                // Reset license.
                wishlistmember_instance()->save_option('LicenseStatus', 1);

                wp_send_json_success(['redirect' => admin_url('admin.php?page=WishListMember')]);
                break;
            case 'next':
                parse_str(wlm_post_data()['data'], $data);
                switch (wlm_post_data()['stepname']) {
                    case 'license':
                        $license = wlm_trim(wlm_arrval($data, 'license'));
                        if ($license) {
                            wishlistmember_instance()->delete_option('LicenseLastCheck');
                            wishlistmember_instance()->save_option('LicenseKey', $license);
                            wishlistmember_instance()->WPWLKeyProcess();
                            if (1 !== (int) wishlistmember_instance()->get_option('LicenseStatus')) {
                                wp_send_json_error(['message' => wlm_or(wishlistmember_instance()->wpwl_check_response, __('Invalid License Key', 'wishlist-member'))]);
                            }
                        } else {
                            wp_send_json_error(['message' => __('Please provide your license key.', 'wishlist-member')]);
                        }
                        if (wlm_arrval($data, 'license_only')) {
                            wp_send_json_success(['redirect' => admin_url('admin.php?page=WishListMember')]);
                        }

                        break;
                    case 'membership-levels':
                        // Get level names to create, trim and remove empty ones.
                        $levels = array_filter(array_map('wlm_trim', (array) wlm_arrval($data, 'levels/name')));

                        // Get existing level names, remove empty and change case to lower.
                        $level_names = array_map('strtolower', array_filter(array_column(wishlistmember_instance()->get_option('wpm_levels'), 'name')));

                        // Prepare array of duplicate names.
                        $duplicate_names = [];

                        // Check if level names already exist.
                        foreach ($levels as $name) {
                            if (in_array(strtolower($name), $level_names, true)) {
                                $duplicate_names[] = $name;
                            }
                        }

                        if ($duplicate_names) {
                            // Duplicate names found, return error.
                            wp_send_json_error(['message' => _n('The following level name already exist:', 'The following level names already exist:', count($duplicate_names), 'wishlist-member') . '<br>' . implode(', ', $duplicate_names)]);
                        }

                        // Create each level.
                        foreach ($levels as $name) {
                            $level = \WishListMember\Level::create_level(
                                [
                                    'name' => $name,
                                ]
                            );
                        }
                        break;
                    case 'integrations':
                        if (wlm_arrval($data, 'integration/payment') && ! wishlistmember_instance()->payment_integration_is_active($data['integration/payment'])) {
                            wishlistmember_instance()->toggle_payment_provider($data['integration/payment'], true);
                            wishlistmember_instance()->save_option('wizard/integration/payment/configure', $data['integration/payment']);
                        }
                        if (wlm_arrval($data, 'integration/email') && ! wishlistmember_instance()->email_integration_is_active($data['integration/email'])) {
                            wishlistmember_instance()->toggle_email_provider($data['integration/email'], true);
                            wishlistmember_instance()->save_option('wizard/integration/email/configure', $data['integration/email']);
                        }
                        break;
                    case 'membership-pages':
                        // Login styling.
                        if (wlm_arrval($data, 'pages/login/styled')) {
                            wishlistmember_instance()->save_option('login_styling_enable_custom_template', 1);
                            wishlistmember_instance()->save_option('login_styling_custom_template', 'template-09');
                            wishlistmember_instance()->delete_option('checklist/done/customize-styled-member-login-page');
                        }

                        // Free registration page.
                        if (wlm_arrval($data, 'pages/registration/free')) {
                            $content = file_get_contents(wishlistmember_instance()->plugin_dir3 . '/assets/templates/wizard/free-registration-page.txt');

                            $free_registration_level = wishlistmember_instance()->get_option('wpm_levels');
                            $free_registration_level = wlm_arrval(end($free_registration_level), 'name');
                            if ($free_registration_level) {
                                $content = str_replace('#wlm-level-name#', $free_registration_level, $content);
                            }

                            // Prepare page data.
                            $page_data = [
                                'post_title'     => __('Registration Page for Free Users', 'wishlist-member'),
                                'post_type'      => 'page',
                                'comment_status' => 'closed',
                                'ping_status'    => 'closed',
                                'post_author'    => get_current_user_id(),
                                'post_status'    => 'draft',
                                'post_content'   => $content,
                                'meta_input'     => [
                                    'wishlist-member/wizard/pages/registration/free' => wlm_date(),
                                ],
                            ];

                            // Update free registration page if it already exists.
                            $free_registration_page_id = wlm_arrval(
                                get_posts(
                                    [
                                        'post_type'   => 'page',
                                        'post_status' => 'any',
                                        'meta_key'    => 'wishlist-member/wizard/pages/registration/free',
                                        'fields'      => 'ids',
                                    ]
                                ),
                                0
                            );
                            if ($free_registration_page_id) {
                                $page_data['ID'] = $free_registration_page_id;
                                wp_update_post($page_data);
                            }

                            // Create onboarding page and set it as after reg page.
                            if (! $free_registration_page_id) {
                                $free_registration_page_id = wp_insert_post($page_data);
                            }

                            wishlistmember_instance()->delete_option('checklist/done/customize-member-free-registration');
                        }

                        // Paid registration page.
                        if (wlm_arrval($data, 'pages/registration/paid')) {
                            // Prepare page data.
                            $page_data = [
                                'post_title'     => __('Sales Page for Paid Users', 'wishlist-member'),
                                'post_type'      => 'page',
                                'comment_status' => 'closed',
                                'ping_status'    => 'closed',
                                'post_author'    => get_current_user_id(),
                                'post_status'    => 'draft',
                                'post_content'   => file_get_contents(wishlistmember_instance()->plugin_dir3 . '/assets/templates/wizard/paid-registration-page.txt'),
                                'meta_input'     => [
                                    'wishlist-member/wizard/pages/registration/paid' => wlm_date(),
                                ],
                            ];

                            // Update paid registration page if it already exists.
                            $paid_registration_page_id = wlm_arrval(
                                get_posts(
                                    [
                                        'post_type'   => 'page',
                                        'post_status' => 'any',
                                        'meta_key'    => 'wishlist-member/wizard/pages/registration/paid',
                                        'fields'      => 'ids',
                                    ]
                                ),
                                0
                            );
                            if ($paid_registration_page_id) {
                                $page_data['ID'] = $paid_registration_page_id;
                                wp_update_post($page_data);
                            }

                            // Create onboarding page and set it as after reg page.
                            if (! $paid_registration_page_id) {
                                $paid_registration_page_id = wp_insert_post($page_data);
                            }

                            wishlistmember_instance()->delete_option('checklist/done/customize-member-paid-registration');
                        }

                        $dashboard_page_id  = 0;
                        $onboarding_page_id = 0;
                        if (wlm_arrval($data, 'pages/dashboard')) {
                            // Prepare page data.
                            $page_data = [
                                'post_title'     => __('Member Dashboard', 'wishlist-member'),
                                'post_type'      => 'page',
                                'comment_status' => 'closed',
                                'ping_status'    => 'closed',
                                'post_author'    => get_current_user_id(),
                                'post_status'    => 'publish',
                                'post_content'   => file_get_contents(wishlistmember_instance()->plugin_dir3 . '/assets/templates/wizard/after-login.txt'),
                                'meta_input'     => [
                                    'wishlist-member/wizard/pages/dashboard' => wlm_date(),
                                ],
                            ];

                            // Update onboarding page if it already exists.
                            $dashboard_page_id = wlm_arrval(
                                get_posts(
                                    [
                                        'post_type' => 'page',
                                        'meta_key'  => 'wishlist-member/wizard/pages/dashboard',
                                        'fields'    => 'ids',
                                    ]
                                ),
                                0
                            );
                            if ($dashboard_page_id) {
                                $page_data['ID'] = $dashboard_page_id;
                                wp_update_post($page_data);
                            }

                            // Create dashboard page and set it as after login page.
                            if (! $dashboard_page_id) {
                                $dashboard_page_id = wp_insert_post($page_data);
                            }
                            wishlistmember_instance()->save_option('after_login_type', 'internal');
                            wishlistmember_instance()->save_option('after_login_internal', $dashboard_page_id);
                            wishlistmember_instance()->save_option('wizard/membership-pages/dashboard/configure', $dashboard_page_id);
                            wishlistmember_instance()->delete_option('wizard/membership-pages/dashboard/configured');
                            wishlistmember_instance()->delete_option('checklist/done/customize-member-dashboard-page');
                        }

                        if (wlm_arrval($data, 'pages/onboarding')) {
                            $content = file_get_contents(wishlistmember_instance()->plugin_dir3 . '/assets/templates/wizard/after-registration.txt');
                            if ('internal' === wishlistmember_instance()->get_option('after_login_type')) {
                                $dashboard_page_id = wlm_arrval(
                                    get_posts(
                                        [
                                            'post_type' => 'page',
                                            'meta_key'  => 'wishlist-member/wizard/pages/dashboard',
                                            'include'   => wishlistmember_instance()->get_option('after_login_internal'),
                                            'fields'    => 'ids',
                                        ]
                                    ),
                                    0
                                );
                                if ($dashboard_page_id) {
                                    $content = str_replace('#wlm-after-login-url', esc_url(home_url('?page_id=' . $dashboard_page_id)), $content);
                                }
                            }
                            // Prepare page data.
                            $page_data = [
                                'post_title'     => __('Member Welcome', 'wishlist-member'),
                                'post_type'      => 'page',
                                'comment_status' => 'closed',
                                'ping_status'    => 'closed',
                                'post_author'    => get_current_user_id(),
                                'post_status'    => 'publish',
                                'post_content'   => $content,
                                'meta_input'     => [
                                    'wishlist-member/wizard/pages/onboarding' => wlm_date(),
                                ],
                            ];

                            // Update onboarding page if it already exists.
                            $onboarding_page_id = wlm_arrval(
                                get_posts(
                                    [
                                        'post_type' => 'page',
                                        'meta_key'  => 'wishlist-member/wizard/pages/onboarding',
                                        'fields'    => 'ids',
                                    ]
                                ),
                                0
                            );
                            if ($onboarding_page_id) {
                                $page_data['ID'] = $onboarding_page_id;
                                wp_update_post($page_data);
                            }

                            // Create onboarding page and set it as after reg page.
                            if (! $onboarding_page_id) {
                                $onboarding_page_id = wp_insert_post($page_data);
                            }
                            wishlistmember_instance()->save_option('after_registration_type', 'internal');
                            wishlistmember_instance()->save_option('after_registration_internal', $onboarding_page_id);
                            wishlistmember_instance()->save_option('wizard/membership-pages/onboarding/configure', $onboarding_page_id);
                            wishlistmember_instance()->delete_option('wizard/membership-pages/onboarding/configured');
                            wishlistmember_instance()->delete_option('checklist/done/customize-member-welcome-page');
                        }
                        break;
                    case 'done':
                        wishlistmember_instance()->save_option('wizard_ran', 1);
                        wp_send_json_success(['redirect' => admin_url('admin.php?page=WishListMember')]);
                        break;
                }
                wishlistmember_instance()->save_option('wizard/' . wlm_post_data()['stepname'], wlm_date());
                wp_send_json_success($data);
                break;
        }
    }

    /**
     * Display getting started on dashboard
     * Called by wishlistmember_dashboard_first_column_start action
     */
    public function dashboard_getting_started()
    {
        include wishlistmember_instance()->plugin_dir3 . '/ui/admin_screens/setup/getting-started/dashboard-getting-started.php';
    }

    /**
     * Hide the anonymous usage optin banner
     *
     * @param  string $wl WishList Member page being viewed.
     * @return string Returns $wl as-is.
     */
    public function hide_tracking_optin($wl)
    {
        'setup/getting-started' === $wl && add_filter('wishlistmember_show_anonymous_usage_tracking_optin', '__return_false');
        return $wl;
    }

    /**
     * Disable existing user registration for registration forms
     * displayed in the free registration page
     *
     * @param  array $level_data Level Data.
     * @return array
     */
    public function disable_existing_user_registration($level_data)
    {
        if (is_page() && get_post_meta(get_the_ID(), 'wishlist-member/wizard/pages/registration/free')) {
            $level_data['disableexistinglink'] = 1;
        }
        return $level_data;
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wp_ajax_wlm_wizard_steps_handler', [$wlm, 'steps_handler']);
        add_action('wishlistmember_dashboard_first_column_start', [$wlm, 'dashboard_getting_started']);
        add_action('wishlistmember_current_admin_screen', [$wlm, 'hide_tracking_optin']);
        add_action('wishlistmember_registration_level_data', [$wlm, 'disable_existing_user_registration']);
    }
);
