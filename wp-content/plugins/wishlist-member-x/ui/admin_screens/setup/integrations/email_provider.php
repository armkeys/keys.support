<?php

/**
 * Loader for email integration providers
 *
 * @package WishListMember\Integrations
 */

require '_integration_common.php';

$activate_thirdparty_providers = (array) $this->get_option('active_email_integrations');
$ar_data                       = $this->get_option('Autoresponders');
if (wlm_arrval($ar_data, 'ARProvider')) {
    $activate_thirdparty_providers[] = $ar_data['ARProvider'];
    $activate_thirdparty_providers   = array_unique($activate_thirdparty_providers);

    unset($ar_data['ARProvider']);

    $this->save_option('Autoresponders', $ar_data);
    $this->save_option('active_email_integrations', $activate_thirdparty_providers);
}
?>
<div id="all-integrations-parent" class="show-saving">
<div class="content-wrapper collapse
<?php
if ('*' === $requested_integration) {
    echo 'show';
}
?>
" data-parent="#all-integrations-parent" id="all-integrations">
    <form action="">
        <div class="row integration-providers pt-3">
            <?php
            // Load providers.
            $providers = glob($this->plugin_dir3 . '/integrations/emails/' . strtolower($requested_integration), GLOB_ONLYDIR);

            // This filter will allow third party integrations to add an entry in the Integration >> Email Providers section of WishList Member.
            $providers = apply_filters('wishlistmember_integration_providers_list', $providers, 'email_provider');

            // Load configs.
            $configs = [];
            foreach ($providers as $folder) {
                $configs[ $folder ] = include $folder . '/config.php';
            }
            // Sort by name.
            uasort(
                $configs,
                function ($a, $b) {
                    return strnatcmp(strtolower($a['name']), strtolower($b['name']));
                }
            );

            $thirdparty_providers = [];
            foreach ([1, 0] as $show_active) :
                foreach ($configs as $folder => $config) :
                    if ((bool) wlm_arrval($config, 'do_not_show')) {
                        continue;
                    }

                    $thirdparty_providers[] = $config['id'];
                    $active                 = $this->email_integration_is_active($config['id']) ? ' active ' : '';
                    if (( $show_active && ! $active ) || ( ! $show_active && $active )) {
                        continue;
                    }
                    if (! $show_legacy_integrations && ! $active && wlm_arrval($config, 'legacy')) {
                        continue;
                    }
                    $no_settings      = wlm_arrval($config, 'no_settings') ? ' no-settings ' : '';
                    $integration_name = empty($config['nickname']) ? $config['name'] : $config['nickname'];
                    ?>
                    <div class="col-md-2 col-sm-3 col-xs-4 -providers <?php echo empty($config['pro']) ? '' : '-pro'; ?>" data-name="<?php echo esc_attr($integration_name); ?>">
                        <div id="thirdparty-provider-<?php echo esc_attr($config['id']); ?>" class="integration-toggle-container text-center <?php echo esc_attr($active); ?> <?php echo esc_attr($no_settings); ?>">
                            <a href="
                            <?php
                            echo esc_url(add_query_arg(
                                [
                                    'page' => $this->menu_id,
                                    'wl'   => 'setup/integrations/email_provider/' . $config['id'],
                                ],
                                admin_url('admin.php')
                            ));
                            ?>
                            " class="integration-toggle <?php echo esc_attr($config['id']); ?>" data-provider="<?php echo esc_attr($config['id']); ?>" data-title="<?php echo esc_attr($config['name']); ?>">
                                <img class="img-greyscale" src='<?php echo esc_url(plugins_url('logo.png', $folder . '/x')); ?>' alt="">
                                <span class="marker text-center">
                                    <i class="wlm-icons md-18">check</i>
                                </span>
                            </a>
                            <h5 class="title-label"><?php echo esc_html($config['name']); ?></h5>
                        </div>
                    </div>
                    <?php
                endforeach;
            endforeach;
            ?>
        </div>
    </form>
</div>
<div id="wlm3-thirdparty-provider">
    <?php

    // Load active email integrations.
    foreach ($thirdparty_providers as $provider) :
        if (basename($requested_integration) !== $provider) {
            continue;
        }
        $_path  = $this->plugin_dir3 . '/integrations/emails/' . strtolower($provider);

        // Filter that changes the config path so third party integrations can load their settings on the integration section of WishList Member.
        $_path =  apply_filters('wishlistmember_integration_providers_config_path', $_path, $provider, 'email_provider');

        $config = include $_path . '/config.php';

        if ((bool) wlm_arrval($config, 'do_not_show')) {
            continue;
        }

        printf("\n<script type='text/javascript'>\nvar wlm3_integration_config = %s\n</script>\n", wp_json_encode($config));

        $is_active = $this->email_integration_is_active($provider);
        if (! $show_legacy_integrations && ! $is_active && wlm_arrval($config, 'legacy')) {
            continue;
        }
        $no_save = true === wlm_arrval($config, 'no_settings');

        $config_button = sprintf('<button type="button" class="btn -primary" data-target="#configure-%s" data-toggle="modal"><i class="wlm-icons">settings</i><span>Configure</span></button>', $config['id']);
        ?>
        <div id="thirdparty-provider-container-<?php echo esc_attr($provider); ?>" data-parent="#all-integrations-parent" data-type="email" data-link="<?php echo esc_attr(wlm_arrval($config, 'link')); ?>" data-name="<?php echo esc_attr(wlm_arrval($config, 'name')); ?>" data-provider="<?php echo esc_attr($provider); ?>" class="thirdparty-provider-container collapse">
            <div class="page-header -no-background">
                <div class="row">
                    <div class="col-auto" id="integration-toggle" data-provider="<?php echo esc_attr($config['id']); ?>">
                        <label class="switch-light switch-wlm mt-1">
                            <input type="checkbox" value="1" name="toggle-thirdparty-provider" skip-save="1">
                            <span>
                                <span>
                                    <i class="wlm-icons md-18 ico-check">
                                    check</i>
                                </span>
                                <span>
                                    <i class="wlm-icons md-18 ico-close">
                                    close</i>
                                </span>
                                <a>
                                </a>
                            </span>
                        </label>
                    </div>
                    <div class="col pl-0">
                        <div class="large-form">
                            <h2 class="page-title"><?php echo esc_html($config['name']); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-wrapper -active">
            <?php
                include_once $_path . '/admin.php'; // Include admin interface.
                $modals = glob($_path . '/admin/modals/*.php');
            foreach ($modals as $modal) {
                include_once $modal;
            }
            ?>
                <div class="panel-footer -content-footer">
                    <div class="row">
                        <div class="col-md-12 text-right">
                        <?php echo wp_kses_post($tab_footer); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-wrapper -inactive">
                <?php if (file_exists($_path . '/inactive.php')) : ?>
                    <?php include_once($_path . '/inactive.php'); ?>
                <?php else : ?>
                <div class="row">
                    <div class="col-md-12">
                        <h3><?php esc_html_e('Integration is Inactive', 'wishlist-member'); ?></h3>
                        <br>
                        <p><?php esc_html_e('Activate this integration by clicking the toggle button above.', 'wishlist-member'); ?></p>
                        <p class="inactive-text"><a href="<?php echo esc_url($config['link']); ?>" class="inactive-link" target="_blank">Learn more about <span class="inactive-name"><?php echo esc_html($config['name']); ?></span></a></p>
                    </div>
                </div>
                <?php endif; ?>
                <div class="panel-footer -content-footer">
                    <div class="col-md-12 text-right">
                        <?php echo wp_kses_post($tab_footer); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</div>

<script type='text/javascript'>
    var activate_thirdparty_providers = <?php echo wp_json_encode(array_values($activate_thirdparty_providers)); ?>;
    var thirdparty_provider_index_format = '%s';
</script>
