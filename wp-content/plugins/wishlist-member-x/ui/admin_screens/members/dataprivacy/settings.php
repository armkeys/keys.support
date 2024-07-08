<?php

/**
 * Data Privacy Settings Page
 *
 * @package WishListMember\UI
 */

$_pages = get_pages('exclude=' . implode(',', wishlistmember_instance()->exclude_pages([], true)));
foreach ($_pages as &$_page) {
    $_page = [
        'value' => $_page->ID,
        'text'  => $_page->post_title,
    ];
}
array_unshift(
    $_pages,
    [
        'value' => 0,
        'text'  => '- None -',
    ]
);
unset($_page);
?>
<div class="content-wrapper data-privacy">
    <div class="row">
        <div class="col-md-12">
            <h3 class="main-title"><?php esc_html_e('Terms of Service Agreement', 'wishlist-member'); ?></h3>
        </div>
    </div>
    <div class="row">
        <?php $x = wishlistmember_instance()->get_option('privacy_require_tos_on_registration'); ?>
        <template class="wlm3-form-group">
            {
            label : '<?php echo esc_js(__('Require Terms of Service Agreement on Registration Form', 'wishlist-member')); ?>',
            name : 'privacy_require_tos_on_registration',
            type : 'toggle-adjacent-disable',
            value : 1,
            uncheck_value : 0,
            checked_value : '<?php echo esc_js($x); ?>',
            column: 'col-md-6',
            tooltip : '<?php echo esc_js(__('If enabled, this option will insert a Terms of Service checkbox into site registration forms. When a member registers, they will be required to accept your Terms of Service. They will not be able to register if they don’t accept the terms.', 'wishlist-member')); ?>'
            }
        </template>
        <div class="col-md-6">
            <button data-toggle="modal" data-target="#require-tos-on-regform-modal" class="btn -primary -condensed <?php echo ! $x ? '-disable' : ''; ?>" <?php echo ! $x ? 'disabled' : ''; ?>>
                <i class="wlm-icons">settings</i>
                <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><br>
            <h3 class="main-title"><?php esc_html_e('Additional Marketing Consent', 'wishlist-member'); ?></h3>
        </div>
    </div>
    <div class="row">
        <?php $x = wishlistmember_instance()->get_option('privacy_enable_consent_to_market'); ?>
        <template class="wlm3-form-group">
            {
            label : '<?php echo esc_js(__('Display Consent Checkbox for Additional Marketing on Registration Form', 'wishlist-member')); ?>',
            name : 'privacy_enable_consent_to_market',
            type : 'toggle-adjacent-disable',
            value : 1,
            uncheck_value : 0,
            checked_value : '<?php echo esc_js($x); ?>',
            column: 'col-md-6',
            tooltip : '<?php echo esc_js(__('If enabled, this option adds a checkbox to allow new members to give consent to receive additional marketing from you. If a member doesn’t select this checkbox while registering, they will not be added to any Email Provider integrations and you will not be able to send them Emails Broadcasts from within WishList Member.', 'wishlist-member')); ?>'
            }
        </template>
        <div class="col-md-6">
            <button data-toggle="modal" data-target="#additional-marketing-consent-modal" class="btn -primary -condensed <?php echo ! $x ? '-disable' : ''; ?>" <?php echo ! $x ? 'disabled' : ''; ?>>
                <i class="wlm-icons">settings</i>
                <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><br>
            <h3 class="main-title"><?php esc_html_e('Legal Pages', 'wishlist-member'); ?></h3>
        </div>
    </div>
    <div class="row">
        <?php $x = wishlistmember_instance()->get_option('privacy_display_tos_on_footer'); ?>
        <template class="wlm3-form-group">
            {
            label : '<?php echo esc_js(__('Display Terms of Service Link on Site Footer', 'wishlist-member')); ?>',
            name : 'privacy_display_tos_on_footer',
            type : 'toggle-adjacent',
            value : 1,
            uncheck_value : 0,
            checked_value : '<?php echo esc_js($x); ?>',
            column: 'col-md-6',
            tooltip : '<?php echo esc_js(__('If enabled, you can set a link to your Terms of Service to be displayed in the footer of your site. You can select the page that you’ve placed your Terms of Service on.', 'wishlist-member')); ?>'
            }
        </template>
        <div class="col" <?php echo ! $x ? 'style="display:none;"' : ''; ?>>
            <template class="wlm3-form-group">
                {
                group_class : 'mb-0',
                name : 'privacy_tos_page',
                type : 'select',
                options : <?php echo wp_json_encode($_pages); ?>,
                value : '<?php echo esc_js(wishlistmember_instance()->get_option('privacy_tos_page')); ?>',
                style : 'width: 100%',
                class : 'privacy-page',
                }
            </template>
        </div>
        <div class="col-auto" <?php echo ! $x ? 'style="display:none;"' : ''; ?>>
            <a href="#privacy_tos_create_page" data-toggle="collapse" class="btn -success -icon-only" style="margin-bottom: 15px">
                <i class="wlm-icons">add</i>
            </a>
        </div>
    </div>
    <div class="collapse" id="privacy_tos_create_page">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col">
                <div class="form-group">
                    <input type="text" class="form-control create-page" placeholder="Page title" required="required">
                </div>
            </div>
            <div class="col-auto">
                <a href="#" data-input="privacy_tos_page" class="btn -primary -condensed -no-icon create-page-btn" title="Create Page">
                    <span><?php esc_html_e('Create Page', 'wishlist-member'); ?></span>
                </a>
                <a href="#privacy_tos_create_page" data-toggle="collapse" class="btn -bare -condensed -icon-only" title="Create Page">
                    <i class="wlm-icons">close</i>
                </a>
            </div>
        </div>
    </div>

    <br class="d-sm-block">

    <div class="row">
        <?php $x = wishlistmember_instance()->get_option('privacy_display_pp_on_footer'); ?>
        <template class="wlm3-form-group">
            {
            label : '<?php echo esc_js(__('Display Privacy Policy Link on Site Footer', 'wishlist-member')); ?>',
            name : 'privacy_display_pp_on_footer',
            type : 'toggle-adjacent',
            value : 1,
            uncheck_value : 0,
            checked_value : '<?php echo esc_js($x); ?>',
            column: 'col-md-6',
            tooltip : '<?php echo esc_js(__('If enabled, you can set a link to your Privacy Policy to be displayed in the footer of your site. You can select the page that you’ve placed your Privacy Policy on.', 'wishlist-member')); ?>'
            }
        </template>
        <div class="col" <?php echo ! $x ? 'style="display:none;"' : ''; ?>>
            <template class="wlm3-form-group">
                {
                group_class : 'mb-0',
                name : 'privacy_pp_page',
                type : 'select',
                options : <?php echo wp_json_encode($_pages); ?>,
                value : '<?php echo esc_js(wishlistmember_instance()->get_option('privacy_pp_page')); ?>',
                style : 'width: 100%',
                class : 'privacy-page',
                }
            </template>
        </div>
        <div class="col-auto" <?php echo ! $x ? 'style="display:none;"' : ''; ?>>
            <a href="#privacy_pp_create_page" data-toggle="collapse" class="btn -success -icon-only" style="margin-bottom: 15px">
                <i class="wlm-icons">add</i>
            </a>
        </div>
    </div>
    <div class="collapse" id="privacy_pp_create_page">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col">
                <div class="form-group">
                    <input type="text" class="form-control create-page" placeholder="Page title" required="required">
                </div>
            </div>
            <div class="col-auto">
                <a href="#" data-input="privacy_pp_page" class="btn -primary -condensed -no-icon create-page-btn" title="Create Page">
                    <span><?php esc_html_e('Create Page', 'wishlist-member'); ?></span>
                </a>
                <a href="#privacy_pp_create_page" data-toggle="collapse" class="btn -bare -condensed -icon-only" title="Create Page">
                    <i class="wlm-icons">close</i>
                </a>
            </div>
        </div>
    </div>

    <br class="d-sm-block">

    <div class="row">
        <div class="col-md-12"><br>
            <h3 class="main-title"><?php esc_html_e('IP Address Tracking', 'wishlist-member'); ?></h3>
        </div>
    </div>
    <div class="row">
        <?php $x = wishlistmember_instance()->get_option('privacy_disable_ip_tracking'); ?>
        <template class="wlm3-form-group">
            {
            label : '<?php echo esc_js(__('Disable IP Address Tracking', 'wishlist-member')); ?>',
            name : 'privacy_disable_ip_tracking',
            type : 'toggle-adjacent-disable',
            value : 1,
            uncheck_value : 0,
            checked_value : '<?php echo esc_js($x); ?>',
            column: 'col-md-6'
            }
        </template>
        <div class="col-md-6">
            <button data-toggle="modal" data-target="#purge-login-ip-modal" class="btn -primary -condensed <?php echo ! $x ? '-disable' : ''; ?>" <?php echo ! $x ? 'disabled' : ''; ?>>
                <i class="wlm-icons">delete</i>
                <span class="text"><?php esc_html_e('Purge Data', 'wishlist-member'); ?></span>
            </button>
        </div>
    </div>

    <?php
    $modal_footer = <<<STRING
	<button class="btn -bare modal-cancel save-button">
		<span>Close</span>
	</button>
	<button class="modal-save-and-continue save-button btn -primary">
		<i class="wlm-icons">save</i>
		<span>Save</span>
	</button>
	&nbsp;
	<button class="modal-save-and-close save-button btn -success">
		<i class="wlm-icons">save</i>
		<span>Save &amp; Close</span>
	</button>
STRING;
    require_once 'settings/modal/additional_marketing_consent.php';
    require_once 'settings/modal/require_tos_on_reg_form.php';
    require_once 'settings/modal/purge_login_ip.php';
    ?>
</div>
