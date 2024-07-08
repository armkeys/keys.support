<?php

$wl               = wlm_get_data()['wl'];
$wl               = explode('/', $wl);
$content_type     = $wl[1];
$content_comment  = false;
$custom_post_type = $wl[1];

$enabled_types             = (array) $this->get_option('protected_custom_post_types');
$enabled_custom_post_types = in_array($custom_post_type, $enabled_types) ? 1 : 0;

require $this->plugin_dir3 . '/ui/admin_screens/content_protection/post_page_files/content.php';
