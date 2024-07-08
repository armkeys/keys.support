<?php

/**
 * Groundhogg hooks class file
 *
 * @package WishListMember/Autoresponders
 */

namespace WishListMember\Autoresponders;

/**
 * WLM3 - Groundhogg hooks class
 */
class WLM3_Groundhogg_Hooks
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('wp_ajax_wlm3_groundhogg_check_plugin', [$this, 'check_plugin']);
    }

    /**
     * Check plugin
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
            if (in_array('Groundhogg', $active_plugins, true) || isset($active_plugins['groundhogg/groundhogg.php']) || is_plugin_active('groundhogg/groundhogg.php')) {
                $data['status']  = true;
                $data['message'] = 'Groundhogg plugin is installed and activated';

                $all_tags = \Groundhogg\get_db('tags')->query();
                $tags    = [];
                foreach ($all_tags as $key => $value) {
                    $tags[ $value->tag_id ] = $value->tag_name;
                }
                $data['tags'] = $tags;
            } else {
                $data['message'] = 'Please install and activate Groundhogg plugin';
            }
        } catch (\Exception $e) {
            $data['message'] = $e->getMessage();
        }
        wp_die(wp_json_encode($data));
    }
}
new WLM3_Groundhogg_Hooks();
