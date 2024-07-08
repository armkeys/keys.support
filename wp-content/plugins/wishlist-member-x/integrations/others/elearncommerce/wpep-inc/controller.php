<?php

class WPEPAddOnWishList extends WPEP_AddOn_Integration
{
    protected static $_instance;

    /**
     * Create and return class instance
     *
     * @return WPEPAddOnWishList
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
        return 'WishList Integration';
    }

    public function get_alias()
    {
        return 'wishlist-member-integration';
    }

    public function get_information()
    {
        return [
            'official'   => true,
            'store_id'   => '3024',
            'store_name' => 'eLearnCommerce Wishlist Member Integration',
            'version'    => '1.4',
            'link'       => 'https://codestore.codeiscode.com/downloads/wpep-wishlistmember-integration/',
        ];
    }

    /**
     * Content library integration
     *
     * @var WPEPAddOnWishListContent
     */
    public $contentLibraryIntegration;

    /**
     * License and updates integration
     *
     * @var WPEP_Integration_License_And_Updates
     */
    public $licenseAndUpdatesIntegration;

    /**
     * Runs after WPEP has been fully loaded.
     *
     * @return void
     */
    public function init()
    {
        $this->_setup_content_library();
        // $this->_setup_license_and_updates();
    }

    public function has_requirements_met()
    {
        if (function_exists('wlmapi_get_member_levels')) {
            return true;
        }

        return [
            'WishList Member' => 'https://wishlistmember.com/',
        ];
    }

    private function _setup_content_library()
    {
        if (! class_exists('WPEP_Content_Library_Integration')) {
            return;
        }

        if (! class_exists('WPEPAddOnWishListContent')) {
            require_once wishlistmember_instance()->plugin_dir3 . '/integrations/others/elearncommerce/wpep-inc/content.php';
        }
        $this->contentLibraryIntegration = new WPEPAddOnWishListContent();
    }

    private function _setup_license_and_updates()
    {
        if (! class_exists('WPEP_Integration_License_And_Updates')) {
            return;
        }

        $plugin_information = $this->get_information();

        $this->licenseAndUpdatesIntegration = new WPEP_Integration_License_And_Updates(
            $plugin_information['store_name'],
            intval($plugin_information['store_id']),
            'wpep-wishlist-member',
            $plugin_information['version'],
            'wpep_addon_wish_list_'
        );

        $this->licenseAndUpdatesIntegration->setup_license_and_updates(
            wishlistmember_instance()->plugin_dir3 . '/integrations/others/elearncommerce/',
            false
        );
    }
}
