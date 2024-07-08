<?php

/**
 * WishList Member ACL
 */
class WishListAcl
{
    /**
     * Map of WordPress capabilities to WishList Member 3.0 capabilities
     *
     * @var array multidimensional array
     */
    private $maps = [
        'allow_plugin_WishListMember' => [
            'manage_options',
        ],
        'list_users' => [
            'wishlistmember3_members/manage',
            'wishlistmember3_members/import',
            'wishlistmember3_members/export',
            'wishlistmember3_members/mass_move_add',
        ],
    ];

    /**
     * Checks whether current user has a specific capability with the following extra checks
     *
     * - return true if the capability being checked begins with wishlistmember3_ and the current user has the manage_options capability
     * - return true if the capability being checked is mapped to any of the current user's capabilities
     *
     * @uses current_user_can https://codex.wordpress.org/current_user_can
     *
     * @param  string $cap Capability to check
     * @return boolean
     */
    public function current_user_can($cap)
    {
        // The actual capability being checked.
        if (current_user_can($cap)) {
            return true;
        }

        // Does the capability being checked start with wishlistmember3_ and the user has the manage_options capability?
        if (preg_match('/^wishlistmember3_/', $cap) && current_user_can('manage_options')) {
            return true;
        }

        // Is the capability being checked mapped to any of the current user's capabilities?
        if (isset($this->maps[ $cap ])) {
            foreach ($this->maps[ $cap ] as $c) {
                if (current_user_can($c)) {
                    return true;
                }
            }
        }

        // Still here, return false.
        return false;
    }
}
