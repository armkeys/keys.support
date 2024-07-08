<?php

$active_plugins = wlm_get_active_plugins();
if (in_array('Elementor', $active_plugins) || isset($active_plugins['elementor/elementor.php']) || is_plugin_active('elementor/elementor.php')) {
    return true;
} else {
    printf('<p>Please install and activate your Elementor plugin</p>');
    return false;
}
