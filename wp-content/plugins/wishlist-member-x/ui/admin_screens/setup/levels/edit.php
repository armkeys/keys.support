<div id="levels-create" style="display:none;" class="show-saving">
    <form id="level-form">
        <input type="hidden" id="first-save">
        <div id="save-action-fields">
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save_membership_level" />
            <input type="hidden" name="id">
        </div>
         <div class="page-header">
            <div class="large-form">
                <div class="row">
                    <div class="col-sm-auto col-md-auto col-lg-auto">
                        <h2 class="page-title"><?php esc_html_e('Level Name', 'wishlist-member'); ?></h2>
                    </div>
                    <div class="col-sm-5 col-md-6 col-lg-6 level-name-holder">
                        <input name="name" placeholder="Enter Level Name" data-initial="" required="required" class="form-control input-lg" type="text">
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="all-level-data">
            <div class="col-md-12">
                <!-- Nav tabs -->
                <!-- start: v4 -->
                <ul class="nav nav-tabs responsive-tabs -no-background levels-edit-tabs" role="tablist">
                <!-- end: v4 -->
                    <li role="presentation" class="nav-item"><a class="nav-link" href="#" data-href="#levels_access" role="tab" data-toggle="tab"><?php esc_html_e('Access', 'wishlist-member'); ?></a></li>
                    <li role="presentation" class="nav-item"><a class="nav-link" href="#" data-href="#levels_registrations" role="tab" data-toggle="tab"><?php esc_html_e('Registrations', 'wishlist-member'); ?></a></li>
                    <li role="presentation" class="nav-item"><a class="nav-link" href="#" data-href="#levels_requirements" role="tab" data-toggle="tab"><?php esc_html_e('Requirements', 'wishlist-member'); ?></a></li>
                    <li role="presentation" class="nav-item"><a class="nav-link" href="#" data-href="#levels_additional_settings" role="tab" data-toggle="tab"><?php esc_html_e('Additional Settings', 'wishlist-member'); ?></a></li>
                    <li role="presentation" class="nav-item"><a class="nav-link" href="#" data-href="#levels_notifications" role="tab" data-toggle="tab"><?php esc_html_e('Email Notifications', 'wishlist-member'); ?></a></li>
                    <?php foreach ($level_edit_tabs as $tab_key => $tab_label) : ?>
                    <li role="presentation" class="nav-item"><a class="nav-link" href="#" data-href="#levels_<?php echo esc_attr($tab_key); ?>" role="tab" data-toggle="tab"><?php echo esc_html($tab_label); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <?php
                        $level_id   = wlm_get_data()['level_id' ];
                        $level_data = ( new \WishListMember\Level($level_id) )->get_data();
                        // Tab panes.
                        require_once 'edit/access.php';
                        require_once 'edit/registrations.php';
                        require_once 'edit/requirements.php';
                        require_once 'edit/additional_settings.php';
                        require_once 'edit/notifications.php';
                        require_once 'edit/hidden.php';
                    ?>
                    <?php foreach ($level_edit_tabs as $tab_key => $tab_label) : ?>
                        <div role="tabpanel" class="tab-pane extra-tabs" id="" data-id="levels_<?php echo esc_attr($tab_key); ?>">
                            <?php do_action('wishlistmember_pre_level_edit_tab_pane_' . $tab_key, $level_id, $level_data); ?>
                            <div class="content-wrapper">
                                <?php do_action('wishlistmember_level_edit_tab_pane_' . $tab_key, $level_id, $level_data); ?>
                                <br>
                                <div class="panel-footer -content-footer">
                                    <div class="row">
                                        <div class="col-md-12 text-right">
                                            <?php echo wp_kses_post($tab_footer); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php do_action('wishlistmember_post_level_edit_tab_pane_' . $tab_key, $level_id, $level_data); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
            // Per level modals.
            require_once 'edit/modal/header_footer.php';
            require_once 'edit/modal/autocreate_account.php';
            require_once 'edit/modal/email_notifications.php';
            require_once 'edit/modal/terms_and_conditions.php';
            require_once 'edit/modal/custom_redirects.php';
        ?>
    </form>
</div>
<?php
    // Global modals.
    require_once 'edit/modal/recaptcha.php';
?>
