<?php

/**
 * Autoresponder Class file
 *
 * @package WishListMember
 */

namespace WishListMember;

defined('ABSPATH') || die();

/**
 * WishList Member Autoresponder helper class
 * Easy way to grab settings for specific autoresponder
 */
class Autoresponder
{
    /**
     * Autoresponder settings
     *
     * @var array
     */
    public $settings;

    /**
     * Autoresponder ID
     *
     * @var string
     */
    private $autoresponder;

    /**
     * Constructor
     *
     * @param string $autoresponder Autoresponder ID.
     */
    public function __construct($autoresponder)
    {
        $this->autoresponder = $autoresponder;

        // Grab autoresponder settings or set $settings to empty array.
        $this->settings = wlm_arrval(wishlistmember_instance()->get_option('Autoresponders'), $autoresponder) ? wlm_arrval('lastresult') : [];
    }

    /**
     * Save whatever is stored in the settings property
     */
    public function save_settings()
    {

        // Get autoresponders.
        $autoresponders = wlm_or(wishlistmember_instance()->get_option('Autoresponders'), []);

        // Update settings for $autoresponder.
        $autoresponders[ $this->autoresponder ] = $this->settings;

        // Save autoresponders.
        wishlistmember_instance()->save_option('Autoresponders', $autoresponders);
    }
}
