<?php

/**
 * Header markup
 *
 * @package WishListMember/UI
 */

?>
<div class="wlm3wrapper wlm3block">
<?php
    do_action('wishlistmember_ui_header_scripts');
    $body_classes = ['wlm3body', 'show-saving'];

if ($this->get_option('wlm3sidebar_state')) {
    $body_classes[] = 'nav-collapsed';
}

if (! $this->get_option('show_legacy_features')) {
    $body_classes[] = 'hide-legacy-features';
}

    $body_classes = trim(implode(' ', $body_classes));
?>
<div class="<?php echo esc_attr($body_classes); ?>">
