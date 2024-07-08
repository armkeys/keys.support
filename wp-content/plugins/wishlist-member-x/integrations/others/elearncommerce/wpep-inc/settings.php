<?php

class WPEPAddOnWishListSettings extends WPEP_AddOn_Settings_Integration
{
    /**
     * Instance
     *
     * @var WPEPAddOnWishListSettings
     */
    protected static $_instance;

    /**
     * Create and return class instance
     *
     * @return WPEPAddOnWishListSettings
     */
    public static function instance()
    {
        if (self::null === $_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get_name()
    {
        return $this->get_controller()->get_name();
    }

    public function get_plugin_basename()
    {
        return plugin_basename(wishlistmember_instance()->plugin_dir3 . '/integrations/others/elearncommerce/');
    }

    public function get_alias()
    {
        return $this->get_controller()->get_alias();
    }

    public function get_option_prefix()
    {
        return 'wpep_addon_wish_list_';
    }

    public function get_controller()
    {
        return WPEPAddOnWishList::instance();
    }

    public function get_field_list()
    {
        $field_list = [];

        if (null !== $this->get_controller()->licenseAndUpdatesIntegration) {
            $field_list = $this->get_controller()->licenseAndUpdatesIntegration->get_license_key_field_as_indexed_array();
        }

        return $this->get_controller()->contentLibraryIntegration->administrationCoordinator->get_global_options_settings($field_list);
    }
}
