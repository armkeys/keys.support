<?php

/**
 * Groundhogg class file
 *
 * @package WishListMember/Autoresponders
 */

namespace WishListMember\Autoresponders;

use Groundhogg\Contact;

use function Groundhogg\create_contact_from_user;
use function Groundhogg\get_array_var;
use function Groundhogg\get_contactdata;
use function Groundhogg\is_a_contact;

/**
 * Groundhogg class
 */
class Groundhogg
{
    /**
     * Magic method __callStatic
     *
     * @param string $name Method name.
     * @param array  $args Arguments.
     */
    public static function __callStatic($name, $args)
    {
        $interface = self::gh_interface();
        if ($interface->api()) {
            call_user_func_array([$interface, $name], $args);
        }
    }

    /**
     * Delete tag action
     *
     * @wp-hook wp_ajax_wishlistmember_groundhogg_delete_tag_action
     */
    public static function delete_tag_action()
    {
        $groundhogg_settings = new \WishListMember\Autoresponder('groundhogg');
        try {
            unset($groundhogg_settings->settings['groundhogg_settings']['tag'][ wlm_post_data()['tag_id'] ]);
        } catch (\Exception $e) {
            null;
        }
        $groundhogg_settings->save_settings();
        wp_send_json_success();
    }

    /**
     * Groundhogg interface wrapper
     *
     * @return Groundhogg_Interface
     */
    public static function gh_interface()
    {
        static $interface;
        if (! $interface) {
            $interface = new Groundhogg_Interface();
        }
        return $interface;
    }
}
