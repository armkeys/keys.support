<?php

/**
 * Box_Basic Core
 *
 * @package WishListMember\Features
 */

namespace WishListMember\Features\Box_Basic;

/**
 * Core class
 */
class Core
{
    /**
     * Instance
     *
     * @var \WishListMember\Features\Box_Basic\Core
     */
    private static $instance;

    /**
     * Blocks
     *
     * @var WishListMember\Features\Box_Basic\Blocks
     */
    public $blocks;

    /**
     * Instance function
     *
     * @return instance
     */
    public static function instance()
    {
        if (! isset(self::$instance) && ! ( self::$instance instanceof Core )) {
            self::$instance         = new Core();
            self::$instance->blocks = new Blocks();
        }
        return self::$instance;
    }
}
