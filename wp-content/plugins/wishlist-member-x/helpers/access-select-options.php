<?php

/**
 * Returns an array of access (membership levels and payperposts) select options
 *
 * @package WishListMember/Helpers
 */

return ( function () {
    static $access = null;

    if (is_array($access)) {
        return $access;
    }

    $access = [];

    $levels = wishlistmember_instance()->get_option('wpm_levels');
    $levels = array_map(
        function ($l, $id) {
            $l          = [
                'text' => $l['name'],
                'id'   => $id,
            ];
            $l['name']  = &$l['text'];
            $l['value'] = &$l['id'];
            return $l;
        },
        $levels,
        array_keys($levels)
    );

    $x             = [
        'text'    => __('Membership Levels', 'wishlist-member'),
        'options' => $levels,
    ];
    $x['children'] = &$x['options'];
    $x['name']     = &$x['text'];
    $access        = ['__levels__' => $x];

    $payperposts = wishlistmember_instance()->get_payperposts(['post_title', 'post_type']);
    $ppkeys      = array_keys($payperposts);
    $post_types  = get_post_types('', 'objects');

    $payperposts = array_map(
        function ($posts, $ptype) use ($post_types) {
            $posts         = array_map(
                function ($p) {
                    $p          = [
                        'text' => $p['post_title'],
                        'id'   => $p['id'],
                    ];
                    $p['name']  = &$p['text'];
                    $p['value'] = &$p['id'];
                    return $p;
                },
                $posts
            );
            if (!$posts) {
                return false;
            }
            $x             = [
                'text'    => $post_types[ $ptype ]->labels->name,
                'options' => $posts,
            ];
            $x['children'] = &$x['options'];
            $x['name']     = &$x['text'];
            return $x;
        },
        $payperposts,
        array_keys($payperposts)
    );

    $access = array_merge($access, array_filter(array_combine($ppkeys, $payperposts)));

    return $access;
} )();
