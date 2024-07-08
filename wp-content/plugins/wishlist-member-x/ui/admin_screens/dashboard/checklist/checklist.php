<?php

/**
 * Membership Success Checklist
 *
 * @package WishListMember/Wizard
 */

$dashboard_checklist_closed          = 'closed' === wishlistmember_instance()->get_option('dashboard_checklist_closed');
$dashboard_checklist_done_closed     = 'closed' === wishlistmember_instance()->get_option('dashboard_checklist_done_closed');
$dashboard_checklist_archived_closed = 'closed' === wishlistmember_instance()->get_option('dashboard_checklist_archived_closed');

?>
<div class="panel panel-default d-none" id="wlm-checklist-panel">
    <div class="panel-heading">
        <div class="pull-right align-middle">
            <a href="#wlm-checklist-video" data-toggle="modal" class="btn -bare -icon-only"><i class="wlm-icons -up">ondemand_video</i>
            <a href="#membership-success-checklist" data-toggle="collapse" class="btn -icon-only <?php echo esc_attr($dashboard_checklist_closed ? 'collapsed' : ''); ?>"><i class="wlm-icons -up">arrow_drop_up</i><i class="wlm-icons -down">arrow_drop_down</i></a>
        </div>
        <h3 class="panel-title">
            <span class="align-middle icon-container text-success"><i class="wlm-icons">check_circle</i></span>
            <span class="align-middle"><?php esc_html_e('Membership Success Checklist', 'wishlist-member'); ?></span>
        </h3>
    </div>
    <div id="membership-success-checklist" class="d-none panel-body collapse  <?php echo esc_attr($dashboard_checklist_closed ? '' : 'show'); ?>">
        <div class="row">
            <div class="col-12 checklist-group checklist-todo">
                <h4><?php esc_html_e('Start Here', 'wishlist-member'); ?></h4>
                    <?php
                    // Membership Success Checklist - License.
                    if (! wishlistmember_instance()->bypass_licensing()) {
                        $checklist_license_done = wishlistmember_instance()->get_option('LicenseKey') && wishlistmember_instance()->get_option('LicenseStatus');
                        wishlistmember_instance()->print_checklist_item(
                            [
                                'id'          => 'activate-license-key',
                                'label'       => esc_html__('Activate License Key', 'wishlist-member'),
                                'status_done' => $checklist_license_done,
                                'link_config' => $checklist_license_done ? '' : '#',
                                'link_info'   => 'https://wishlistmember.com/docs/wishlist-member-license-access-deactivation/',
                                'auto_done'   => true,
                                'importance'  => 1,
                            ]
                        );
                    }

                    // Membership Success Checklist - Membership Levels.
                    wishlistmember_instance()->print_checklist_item(
                        [
                            'id'          => 'create-membership-level',
                            'label'       => esc_html__('Create Membership Level', 'wishlist-member'),
                            'status_done' => count(array_filter((array) wishlistmember_instance()->get_option('wpm_levels'))),
                            'link_config' => admin_url('admin.php?page=WishListMember&wl=setup/levels'),
                            'link_info'   => 'https://wishlistmember.com/docs/create-a-new-membership-level/',
                            'auto_done'   => true,
                            'importance'  => 101,
                        ]
                    );

                    // Membership Success Checklist - Payment Integration.
                    $payment = wishlistmember_instance()->get_option('wizard/integration/payment/configure');
                    if ($payment) {
                        wishlistmember_instance()->print_checklist_item(
                            [
                                'id'          => 'configure-payment-provider',
                                'label'       => esc_html__('Configure Payment Provider', 'wishlist-member'),
                                'status_done' => wishlistmember_instance()->get_option('wizard/integration/payment/configured') === $payment,
                                'link_config' => admin_url('admin.php?page=WishListMember&wl=setup/integrations/payment_provider/' . $payment),
                                'link_info'   => 'https://wishlistmember.com/docs/article-categories/payment-providers/',
                                'importance'  => 201,
                            ]
                        );
                    }

                    // Membership Success Checklist - Email Integration.
                    $email = wishlistmember_instance()->get_option('wizard/integration/email/configure');
                    if ($email) {
                        wishlistmember_instance()->print_checklist_item(
                            [
                                'id'          => 'configure-email-provider',
                                'label'       => esc_html__('Configure Email Provider', 'wishlist-member'),
                                'status_done' => wishlistmember_instance()->get_option('wizard/integration/email/configured') === $email,
                                'link_config' => admin_url('admin.php?page=WishListMember&wl=setup/integrations/email_provider/' . $email),
                                'link_info'   => 'https://wishlistmember.com/docs/article-categories/email-providers/',
                                'importance'  => 202,
                            ]
                        );
                    }

                    // Membership Success Checklist - Free Registration Page.
                    $free_reg_page = wlm_arrval(
                        get_posts(
                            [
                                'post_type'   => 'page',
                                'meta_key'    => 'wishlist-member/wizard/pages/registration/free',
                                'fields'      => 'ids',
                                'post_status' => 'all',
                            ]
                        ),
                        0
                    );
                    if ($free_reg_page) {
                        wishlistmember_instance()->print_checklist_item(
                            [
                                'id'          => 'customize-member-free-registration',
                                'label'       => esc_html__('Customize Membership Registration Page for FREE Users', 'wishlist-member'),
                                'link_config' => get_edit_post_link($free_reg_page),
                                'link_info'   => 'https://wishlistmember.com/docs/create-a-registration-page-for-free-users/',
                                'importance'  => 301,
                            ]
                        );
                    }

                    // Membership Success Checklist - Paid Registration Page.
                    $paid_reg_page = wlm_arrval(
                        get_posts(
                            [
                                'post_type'   => 'page',
                                'meta_key'    => 'wishlist-member/wizard/pages/registration/paid',
                                'fields'      => 'ids',
                                'post_status' => 'all',
                            ]
                        ),
                        0
                    );
                    if ($paid_reg_page) {
                        wishlistmember_instance()->print_checklist_item(
                            [
                                'id'          => 'customize-member-paid-registration',
                                'label'       => esc_html__('Customize Membership Registration Page for PAID Users', 'wishlist-member'),
                                'link_config' => get_edit_post_link($paid_reg_page),
                                'link_info'   => 'https://wishlistmember.com/docs/create-a-membership-sales-page-for-paid-users/',
                                'importance'  => 302,
                            ]
                        );
                    }

                    // Membership Success Checklist - Styled login page.
                    if (wishlistmember_instance()->get_option('login_styling_enable_custom_template')) {
                        wishlistmember_instance()->print_checklist_item(
                            [
                                'id'          => 'customize-styled-member-login-page',
                                'label'       => esc_html__('Customize Styled Membership Login Page', 'wishlist-member'),
                                'link_config' => admin_url('admin.php?page=WishListMember&wl=advanced_settings/logins/styling'),
                                'link_info'   => 'https://wishlistmember.com/docs/wp-login-form-custom-styling/',
                                'importance'  => 303,
                            ]
                        );
                    }

                    // Membership Success Checklist - Onboarding Page.
                    $onboarding = (int) wishlistmember_instance()->get_option('wizard/membership-pages/onboarding/configure');
                    if ($onboarding && get_pages(['include' => [$onboarding]])) {
                        wishlistmember_instance()->print_checklist_item(
                            [
                                'id'          => 'customize-member-welcome-page',
                                'label'       => esc_html__('Customize Member Welcome Page', 'wishlist-member'),
                                'link_config' => get_edit_post_link($onboarding),
                                'link_info'   => 'https://wishlistmember.com/docs/creating-an-onboarding-page-after-registration/',
                                'importance'  => 304,
                            ]
                        );
                    }

                    // Membership Success Checklist - Dashboard Page.
                    $dashboard = (int) wishlistmember_instance()->get_option('wizard/membership-pages/dashboard/configure');
                    if ($dashboard && get_pages(['include' => [$dashboard]])) {
                        wishlistmember_instance()->print_checklist_item(
                            [
                                'id'          => 'customize-member-dashboard-page',
                                'label'       => esc_html__('Customize Member Dashboard Page', 'wishlist-member'),
                                'link_config' => get_edit_post_link($dashboard),
                                'link_info'   => 'https://wishlistmember.com/docs/creating-a-dashboard-page-after-login/',
                                'importance'  => 305,
                            ]
                        );
                    }

                    // Membership Success Checklist - Membership Content.
                    if (false === wishlistmember_instance()->get_option('checklist/done/create-membership-content')) {
                        if ((int) $GLOBALS['wpdb']->get_var('select 1 from ' . esc_sql(wishlistmember_instance()->table_names->contentlevels) . ' where level_id="Protection" limit 1')) {
                            wishlistmember_instance()->save_option('checklist/done/create-membership-content', 1);
                        }
                    }
                    wishlistmember_instance()->print_checklist_item(
                        [
                            'id'         => 'create-membership-content',
                            'label'      => esc_html__('Create Membership Content', 'wishlist-member'),
                            'link_info'  => 'https://wishlistmember.com/docs/creating-membership-content/',
                            'importance' => 401,
                        ]
                    );
                    ?>
            </div>
            <div id="membership-success-checklist-done" class="col-12 checklist-group checklist-done">
                <h4 data-target=".checklist-group.checklist-done .checklist-item" data-toggle="collapse" class="<?php echo esc_attr($dashboard_checklist_done_closed ? 'collapsed' : ''); ?>">
                    <span class="pull-right pt-0 btn -icon-only"><i class="wlm-icons -up">arrow_drop_up</i><i class="wlm-icons -down">arrow_drop_down</i></span>
                    <?php esc_html_e('Finished', 'wishlist-member'); ?>
                </h4>
            </div>
            <div class="col-12 checklist-group checklist-archived">
                <h4 data-target=".checklist-group.checklist-archived .checklist-item" data-toggle="collapse" class="<?php echo esc_attr($dashboard_checklist_archived_closed ? 'collapsed' : ''); ?>">
                    <span class="pull-right pt-0 btn -icon-only"><i class="wlm-icons -up">arrow_drop_up</i><i class="wlm-icons -down">arrow_drop_down</i></span>
                    <?php esc_html_e('Skipped', 'wishlist-member'); ?>
                </h4>
            </div>
        </div>
    </div>
</div>
<?php
    $show_checklist_video_popup = ! wishlistmember_instance()->get_option('checklist/video/shown');
if ($show_checklist_video_popup) {
    wishlistmember_instance()->save_option('checklist/video/shown', wlm_date());
}
?>
<div
    id="wlm-checklist-video-modal" 
    data-id="wlm-checklist-video"
    data-label="<?php esc_attr_e('Membership Success Checklist', 'wishlist-member'); ?>"
    data-title="<?php esc_attr_e('Membership Success Checklist', 'wishlist-member'); ?>"
    data-show-default-footer=""
    data-classes="modal-xl <?php echo $show_checklist_video_popup ? esc_attr('auto-open-checklist') : ''; ?>"
    style="display:none; max-width: 100vw; height: 70vw; max-height: 90vw;">
    <div class="body">
        <div class="border">
            <div style="padding:54.72% 0 0 0;position:relative;"><iframe id="wlm-checklist-video-iframe" src="about:blank" data-src="https://wishlistmember.com/docs/videos/wlm/checklist" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Checklist"></iframe></div>
        </div>
    </div>
    <div class="footer">
        <button class="btn -success" data-dismiss="modal"><?php esc_html_e('View Checklist', 'wishlist-member'); ?></button>
    </div>
</div>
