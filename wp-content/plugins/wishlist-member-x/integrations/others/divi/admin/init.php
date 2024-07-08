<?php

if (defined('ET_BUILDER_VERSION') && ET_BUILDER_VERSION) {
    return true;
} else {
    printf('<p>%s</p>', esc_html__('Please install and activate your Divi plugin or Divi theme', 'wishlist-member'));
    return false;
}
