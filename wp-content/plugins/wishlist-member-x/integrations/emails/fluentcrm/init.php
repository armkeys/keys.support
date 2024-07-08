<?php

/**
 * FLuentCRM Init
 *
 * @package WishListMember/Autoresponders
 */

if (! class_exists('WLM3_FluentCRM_Hooks')) {
    /**
     * WLM3_FluentCRM_Hooks class
     */
    class WLM3_FluentCRM_Hooks
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_wlm3_fluentcrm_check_plugin', [$this, 'check_plugin']);
        }

        /**
         * Check plugin existsence.
         */
        public function check_plugin()
        {
            $data = [
                'status'  => false,
                'message' => '',
                'lists'   => [],
                'tags'    => [],
            ];
            // Connect and get info.
            try {
                $active_plugins = wlm_get_active_plugins();
                if (in_array('FluentCRM - Marketing Automation For WordPress', $active_plugins, true) || isset($active_plugins['fluent-crm/fluent-crm.php']) || is_plugin_active('fluent-crm/fluent-crm.php')) {
                    $data['status']  = true;
                    $data['message'] = 'FluentCRM plugin is installed and activated';

                    $list_api  = FluentCrmApi('lists');
                    $all_lists = $list_api->all();
                    $lists     = [];
                    foreach ($all_lists as $value) {
                        $lists[ $value->id ] = $value->title;
                    }
                    $data['lists'] = $lists;

                    $tag_api  = FluentCrmApi('tags');
                    $all_tags = $tag_api->all();
                    $tags     = [];
                    foreach ($all_tags as $value) {
                        $tags[ $value->id ] = $value->title;
                    }
                    $data['tags'] = $tags;
                } else {
                    $data['message'] = 'Please install and activate FluentCRM plugin';
                }
            } catch (\Exception $e) {
                $data['message'] = $e->getMessage();
            }
            wp_die(wp_json_encode($data));
        }
    }
    new WLM3_FluentCRM_Hooks();
}
