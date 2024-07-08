<?php

/**
 * Integration Tutorial Video Tab
 *
 * @package WishListMember/Integrations
 */

if (empty($config['video_id'])) {
    return;
} else {
    printf('<div class="integration-video"><iframe src="https://wishlistmember.com/docs/videos/wlm/%s" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>', esc_attr($config['video_id']));
}
