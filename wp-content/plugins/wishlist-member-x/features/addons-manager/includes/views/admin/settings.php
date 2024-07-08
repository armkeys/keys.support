<?php

/**
 * Add-ons Settings file
 *
 * @package WishListMember/Features/Addons_Manager
 */

?>
<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Add-ons', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require wishlistmember_instance()->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div id="addons-manager-parent" class="show-saving">
    <div class="py-0">
        <div class="row addons-row">
            <?php
            $no_addons            = true;
            $addons               = \WishListMember\Features\Addons_Manager\Addons::instance()->get_addons();
            $is_coursecure_active = \WishListMember\Features\Addons_Manager\Addons::instance()->is_coursecure_active();
            ?>
            <?php foreach ((array) $addons as $slug => $info) : ?>
                <?php
                $info = (object) $info;
                if (! isset($info->extra_info)) {
                    continue;
                }
                $no_addons    = false;
                $status_label = '';
                $installed    = isset($info->extra_info->directory) && is_dir(WP_PLUGIN_DIR . '/' . $info->extra_info->directory);
                $active       = isset($info->extra_info->main_file) && is_plugin_active($info->extra_info->main_file);
                if ($installed && $active) {
                    $addon_status = 'active';
                    $status_label = esc_html__('Active', 'wishlist-member');
                } elseif (! $installed && $info->installable) {
                    $addon_status = 'download';
                    $status_label = esc_html__('Not Installed', 'wishlist-member');
                } elseif ($installed && ! $active) {
                    $addon_status = 'inactive';
                    $status_label = esc_html__('Inactive', 'wishlist-member');
                } else {
                    $addon_status = 'upgrade';
                }
                ?>
                <div class="addons-item col-lg-4 col-md-6 col-sm-12 col-xs-12 m-0 p-0" data-name="<?php echo esc_html($info->extra_info->list_title); ?>">
                    <div class="addons-toggle-container text-center m-3 <?php echo esc_attr($addon_status); ?>" id="addons-<?php echo esc_attr($slug); ?>">
                        <div class="row addon-info m-2">
                            <div class="col-3 m-0 p-2 py-0">
                                <a href="#" class="d-block addons-toggle <?php echo esc_attr($slug); ?>" data-provider="<?php echo esc_attr($slug); ?>" data-title="<?php echo esc_attr($info->extra_info->list_title); ?>">
                                    <img class="logo w-100" src='<?php echo esc_url($info->extra_info->cover_image); ?>' alt="">
                                </a>
                            </div>
                            <div class="col-9 m-0 pl-2 pr-0 py-2 text-left addon-details">
                                <h3 class="addon-title p-0 mx-0 mb-2 mt-0 font-weight-bold"><?php echo esc_html($info->extra_info->list_title); ?></h3>
                                <p class="addon-desc m-0 p-0"><?php echo esc_html($info->extra_info->description); ?></p>
                            </div>
                        </div>
                        <div class="row addon-actions m-0 p-2">
                            <div class="col-md-3 m-0 p-0 text-center">
                                <?php $hide_toggle = ''; ?>
                                <?php if (! file_exists(WP_PLUGIN_DIR . '/' . $info->extra_info->main_file)) : ?>
                                    <?php $hide_toggle = 'hidden'; ?>
                                    <button href="#" class="btn -primary mb-1 -condensed install-addon" addon_url="<?php echo esc_attr($info->url); ?>">
                                        <i class="wlm-icons">baseline_save_alt</i>
                                        <span><?php esc_html_e('Install', 'wishlist-member'); ?></span>
                                    </button>
                                <?php endif; ?>
                                <?php $option_val = 1; ?>
                                    <div class="toggle-enable-addon-holder <?php echo esc_attr($hide_toggle); ?>">
                                        <template class="wlm3-form-group text-center">
                                            {
                                                name : 'enable_preview',
                                                type : 'toggle-switch',
                                                addon_file : '<?php echo esc_js($info->extra_info->main_file); ?>',
                                                class : 'toggle-enable-addon',
                                                value : '1',
                                                checked_value : '<?php echo esc_js('active' === $addon_status ? 1 : 0); ?>',
                                                uncheck_value : '0',
                                            }
                                        </template>
                                    </div>
                            </div>
                            <?php
                            if (
                                $is_coursecure_active &&
                                ( 'wishlist-member-badges/main.php' === $info->extra_info->main_file ||
                                'wishlist-member-points/main.php' === $info->extra_info->main_file ||
                                'coursecure-courses/main.php' === $info->extra_info->main_file ||
                                'coursecure-quizzes/main.php' === $info->extra_info->main_file )
                            ) :
                                ?>
                            <div class="col-md-9 m-0 pl-2 p-0 text-center action-message <?php echo esc_attr($hide_toggle); ?>">
                                <p class="text-danger mb-0">Please deactivate the CourseCure plugin to enable this addon.</p>
                                <p class="mb-0"><a href="#" class="deactivate-coursecure">Click here to deactivate</a></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if ($no_addons) : ?>
            <div class="col">
                <p><?php esc_html_e('You do not have access to any WishList Member Add-ons', 'wishlist-member'); ?></p>
                <p>
                    <?php
                    printf(
                        // Translators: %s is a link to wishlistmember.com.
                        esc_html__('For more information, please visit %s', 'wishlist-member'),
                        '<a href="https://wishlistmember.com/" target="_blank">wishlistmember.com</a>'
                    );
                    ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
