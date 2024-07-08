<?php

/**
 * Divi Integration File
 * Original Integration Author : Mike Lopez
 */

if (! class_exists('WLM_OTHER_INTEGRATION_DIVI')) {
    /**
     * Class implementing our Divi integration
     */
    class WLM_OTHER_INTEGRATION_DIVI
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            // Check if Divi is active.
            $this->load_hooks();
        }
        /**
         * Hook into Divi Builder
         */
        private function load_hooks()
        {
            add_filter('et_builder_get_parent_modules', [$this, 'add_wlm_settings']);
            add_filter('do_shortcode_tag', [$this, 'apply_wlm_settings'], 10, 3);
        }

        /**
         * Apply WishList Member settings to Divi modules
         * Filter: do_shortcode_tag
         *
         * @param  string $content   Content to filter
         * @param  string $shortcode Shortcode
         * @param  array  $atts      Shortcode attributes
         * @return string            Filtered content
         */
        public function apply_wlm_settings($content, $shortcode, $atts)
        {
            /**
             * Array of levels for the current user
             *
             * @var array
             */
            static $user_levels = [];

            // Do not filter if shortcode is not a divi shortcode (et_pb_*), WP is doing ajax, or Divi Builder is active.
            if (! preg_match('/^et_pb_/', $shortcode) || wp_doing_ajax() || ( function_exists('et_fb_is_enabled') && et_fb_is_enabled() )) {
                return $content;
            }

            // Get WishList Member settings from attributes.
            $divi_wlm_level_condition = wlm_arrval($atts, 'wlm_level_condition');
            $divi_wlm_levels          = array_diff(explode(',', (string) wlm_arrval($atts, 'wlm_levels')), ['', false, null]);

            // Get current user.
            $current_user = get_current_user_id();

            switch ($divi_wlm_level_condition) {
                // Logged in.
                case 'logged_in':
                    if (! $current_user) {
                        return '';
                    }
                    break;
                // Not logged in.
                case 'not_logged_in':
                    if ($current_user) {
                        return '';
                    }
                    break;

                // Members.
                case 'in_level':
                    $in_level = true;
                    // Proceed to not_in_level.
                case 'not_in_level':
                    $in_level = empty($in_level) ? false : $in_level;

                    // User must be logged in.
                    if (! $current_user) {
                        return '';
                    }

                    // Get current user's active levels if not yet set.
                    $user_levels = $user_levels ? $user_levels : wlmapi_get_member($current_user)['member'][0]['active_levels'];

                    // Return empty if intersection of configured levels against current user's active levels is either:
                    // Greater than 0 and $in_level is false.
                    // Less than or equal to 0 and $in_level is true.
                    if (! ( count(array_intersect($divi_wlm_levels, $user_levels)) > 0 === $in_level )) {
                        return '';
                    }
                    break;
            }

            // Still here? return `$content` as-is.
            return $content;
        }

        /**
         * Add WishList Member settings to Divi Builder's Advanced Tab
         *
         * @param array $modules Divi Builder modules
         */
        public function add_wlm_settings($modules)
        {
            /**
             * State of WishList Member settings
             *
             * @var boolean
             */
            static $is_applied = false;

            // Do nothing if we already applied our settings or if `$modules` is empty.
            if ($is_applied || empty($modules)) {
                return $modules;
            }

            // Fixed for the issue where Divi says there's a plugin conflict on the post/page editor.
            // When the Divi setting "Enable The Latest Divi Builder Experience" is disabled.
            $bfb_settings = get_option('et_bfb_settings');
            if ($bfb_settings['enable_bfb'] === 'off') {
                global $pagenow;
                // Return $modules if it's an ajax request and the request is coming from post/page editor.
                if ('admin-ajax.php' === $pagenow) {
                    if (false !== strpos(wp_get_referer(), 'post.php')) {
                        return $modules;
                    }
                }
            }

            // Generate options for membership level dropdown.
            // $wlm_levels = array( '' => __( 'Choose a level', 'wishlist-member' ) );
            foreach (\WishListMember\Level::get_all_levels(true) as $level) {
                $wlm_levels[ $level->id ] = $level->name;
            }

            // Go through each module.
            foreach ($modules as &$module) {
                // Skip if any of the following are true:
                // - toggles list don't exist.
                // - fields list don't exist.
                // - module is not a structural element (section, row, inner row)
                // - module is not Advanced.
                if (! isset($module->settings_modal_toggles) || empty($module->fields_unprocessed) || empty(wlm_arrval($module, 'settings_modal_toggles', 'custom_css', 'toggles'))) {
                    continue;
                }

                // Add 'WishList Member' to 'Advanced' tab.
                $module->settings_modal_toggles['custom_css']['toggles']['member_toggle'] = [
                    'title'    => __('WishList Member', 'wishlist-member'),
                    'priority' => 220,
                ];

                // Add conditions dropdown to WishList Member options group.
                $module->fields_unprocessed['wlm_level_condition'] = [
                    'label'       => __('Show this section to:', 'wishlist-member'),
                    'description' => ' ',
                    'type'        => 'select',
                    'options'     => [
                        'all'           => __('Everybody', 'wishlist-member'),
                        'not_logged_in' => __('Not Logged-in', 'wishlist-member'),
                        'logged_in'     => __('Logged-in', 'wishlist-member'),
                        'in_level'      => __('Members in Membership Level(s)', 'wishlist-member'),
                        'not_in_level'  => __('Members not in Membership Level(s)', 'wishlist-member'),
                    ],
                    'default'     => 'all',
                    'toggle_slug' => 'member_toggle',
                    'tab_slug'    => 'custom_css',
                ];
                // Add levels dropdown to WishList Member options group.
                $module->fields_unprocessed['wlm_levels'] = [
                    'type'                => 'categories',
                    'meta_categories'     => $wlm_levels, // custom categories
                    'renderer_options'    => [
                        'use_terms' => true,
                        'term_name' => 'a11d961d5b6082f960b849c2c6e76005', // invalid term name
                    ],
                    'depends_on'          => ['wlm_level_condition'],
                    'depends_show_if_not' => ['all', 'not_logged_in', 'logged_in'],
                    'toggle_slug'         => 'member_toggle',
                    'tab_slug'            => 'custom_css',
                ];
            }
            // Unset passed-by reference $module to be safe.
            unset($module);

            // Set `$is_applied` to true so we no longer run this function again.
            $is_applied = true;

            // Return filtered modules.
            return $modules;
        }
    }
}

// Initialize.
new WLM_OTHER_INTEGRATION_DIVI();
