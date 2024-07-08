<?php

$active_plugins = wlm_get_active_plugins();

if (in_array('Presto Player', $active_plugins) || isset($active_plugins['presto-player/presto-player.php']) || is_plugin_active('presto-player/presto-player.php')) {
    return true;
} else {
    printf('<p>Please install and activate your Presto Player Pro plugin</p>');
    return false;
}
