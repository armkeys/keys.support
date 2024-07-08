<?php

/**
 * Buddyboss init
 *
 * @package WishListMember/OtherProviders
 */

if (! class_exists('WLM3_BuddyBoss_Hooks')) {
    /**
     * WLM3_BuddyBoss_Hooks class.
     */
    class WLM3_BuddyBoss_Hooks
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_buddyboss_check_plugin', [$this, 'check_plugin']);
        }

        /**
         * Check if BuddyBoss plugin exists.
         */
        public function check_plugin()
        {
            $data = [
                'status'      => false,
                'message'     => '',
                'groups'      => [],
                'member_type' => [],
            ];
            // Connect and get info.
            try {
                $active_plugins = wlm_get_active_plugins();
                if (in_array('BuddyBoss Platform', $active_plugins, true) || isset($active_plugins['buddyboss-platform/bp-loader.php']) || is_plugin_active('buddyboss-platform/bp-loader.php')) {
                    $data['status']  = true;
                    $data['message'] = 'BuddyBoss Platform plugin is installed and activated';

                    $is_member_type_enabled      = bp_member_type_enable_disable();
                    $is_groups_component_enabled = bp_is_active('groups');

                    if ($is_groups_component_enabled || $is_member_type_enabled) {
                        if ($is_groups_component_enabled) {
                            $g = BP_Groups_Group::get(
                                [
                                    'type'        => 'alphabetical',
                                    'per_page'    => 9999,
                                    'show_hidden' => 'true',
                                ]
                            );
                            if (isset($g['groups'])) {
                                $groups = [];
                                foreach ((array) $g['groups'] as $value) {
                                    $groups[ $value->id ] = $value->name;
                                }
                                $data['groups'] = $groups;
                            }

                            if (! function_exists('groups_accept_invite')) {
                                $data['status']  = false;
                                $data['message'] = 'BuddyBoss Platform plugin is activated but the functions needed are missing. Please contact support.';
                            }
                        }

                        if ($is_member_type_enabled) {
                            $the_posts = new WP_Query(
                                [
                                    'post_type'   => bp_get_member_type_post_type(),
                                    'post_status' => true,
                                    'nopaging'    => true,
                                ]
                            );
                            if (count($the_posts->posts)) {
                                $member_types = [];
                                foreach ($the_posts->posts as $c) {
                                    $member_types[ $c->post_name ] = $c->post_title;
                                }
                                $data['member_type'] = $member_types;
                            }
                        }
                    } else {
                        $data['status']  = false;
                        $data['message'] = "Please enable 'Groups' component or Profile Types feature of BuddyBoss Platform plugin.";
                    }
                } else {
                    $data['message'] = 'Please install and activate BuddyBoss Platform plugin';
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_BuddyBoss_Hooks();
}
