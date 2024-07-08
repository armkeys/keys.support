<?php

/**
 * Getting Started Wizard Main File
 *
 * @package WishListMember/Wizard
 */

$license_key  = wlm_trim(wishlistmember_instance()->get_option('LicenseKey'), "* \n\r\t\v\x00");
$license_ok   = ( wishlistmember_instance()->bypass_licensing() || $license_key && 1 === (int) wishlistmember_instance()->get_option('LicenseStatus') );
$license_only = ! wishlistmember_instance()->bypass_licensing() && 1 !== (int) wishlistmember_instance()->get_option('LicenseStatus');

$wizard_mode = true;

$steps = [];
if (wishlistmember_instance()->get_option('wizard_ran') && $license_only) {
    $glob        = 'step?-license.php';
    $wizard_mode = false;
} else {
    $glob = 'step?-*.php';
}

wp_enqueue_style('wlm-wizard', wishlistmember_instance()->plugin_url3 . '/ui/admin_screens/setup/getting-started/assets/wizard.css', [], WLM_PLUGIN_VERSION);
wp_doing_ajax() && wp_print_styles('wlm-wizard');
require __DIR__ . '/getting-started/parts/functions.php';

echo '<div id="wizard-container" class="getting-started container -no-heading pb-5" data-step="">';

if ($wizard_mode) {
    echo '<div class="row"><div class="col"><div class="wlm-step-progress-container"></div></div></div>';
}

$actual_step = 0;
$step_titles = [];

foreach (glob(__DIR__ . '/getting-started/' . $glob) as $f) {
    $name = basename($f);
    if (preg_match('/\-license\.php$/', $name) && $license_ok) {
        continue;
    }
    preg_match('/^step(\d)-(.+?)\.php$/', $name, $x);
    ++$actual_step;
    $stepcount         = $x[1] + 1;
    $stepname          = $x[2];
    $steps[]           = $name;
    $step_title_header = '';
    require_once $f;
    $step_titles[ $stepname ] = $step_title;
}

$video_width = 0;
?>
</div>
<?php
require __DIR__ . '/getting-started/parts/js.php';
?>
