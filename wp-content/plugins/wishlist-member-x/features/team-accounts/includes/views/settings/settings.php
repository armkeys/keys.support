<?php

/**
 * Team accounts settings view
 *
 * @package WishListMember/Features/TeamAccounts
 */

use function WishListMember\Features\Team_Accounts\default_admin_join_message;
use function WishListMember\Features\Team_Accounts\default_member_join_message;

$_pages = array_map(
    function ($page) {
        return [
            'value' => $page->ID,
            'text'  => $page->post_title,
        ];
    },
    get_pages('exclude=' . implode(',', wishlistmember_instance()->exclude_pages([], true)))
);
array_unshift(
    $_pages,
    [
        'value' => '',
        'text'  => '',
    ]
); // For placeholder.
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <label>
                <?php esc_html_e('Team Management Page', 'wishlist-member'); ?>
                <?php wishlistmember_instance()->tooltip(__('The Team Management page appears on your site and can be used by the Team Admin to manage their Team Members. Only Team Admins can manage Team accounts. A page named Team Management is automatically created in the WordPress Pages section and set as the Team Management page by default. You can select a different page or create a new page.', 'wishlist-member'), 'lg'); ?>
            </label>
        </div>
    </div>
    <div class="row">
        <template class="wlm3-form-group">
            {
            type: 'select',
            options: <?php echo wp_json_encode($_pages); ?>,
            column: 'col-md-6',
            style: 'width: 100%;',
            'data-placeholder' : '<?php echo esc_js(__('Choose a page...', 'wishlist-member')); ?>',
            name: 'team-accounts/team_management_page',
            value: <?php echo (int) wishlistmember_instance()->get_option('team-accounts/team_management_page'); ?>,
            }
        </template>
        <div class="col-md-4 pl-md-0">
            <button class="btn -primary -icon-only -success -rounded" id="create-page-btn" data-toggle="collapse" data-target="#create-page"><span class="-add wlm-icons">add</span></button>
        </div>
    </div>
    <div class="row collapse" id="create-page">
        <template class="wlm3-form-group">
            {
            type: 'input',
            id: 'create-page-title',
            placeholder: '<?php echo esc_js(__('Page title (Default: Team Management)', 'wishlist-member')); ?>',
            column: 'col-md-6',
            }
        </template>
        <div class="col-md-4">
            <button class="btn -primary -condensed -no-icon create-page"><?php esc_html_e('Create Page', 'wishlist-member'); ?></button>
            <a class="btn -bare -condensed -no-icon" data-toggle="collapse" data-target="#create-page"><i class="wlm-icons">close</i></a>
        </div>
    </div>
    <hr class="mb-5">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="-standalone">
                <?php esc_html_e('Team Admin Welcome Message', 'wishlist-member'); ?>
                <?php wishlistmember_instance()->tooltip(__('This message will be displayed when a user becomes a Team Admin.', 'wishlist-member')); ?>
            </label>
        </div>
        <div class="col-md-auto">
            <button data-toggle="modal" data-target="#team-accounts-admin-welcome" class="btn -primary -condensed"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Message', 'wishlist-member'); ?></span></button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="-standalone">
                <?php esc_html_e('Team Member Welcome Message', 'wishlist-member'); ?>
                <?php wishlistmember_instance()->tooltip(__('This message will be displayed when a user becomes a Team Member.', 'wishlist-member')); ?>
            </label>
        </div>
        <div class="col-md-auto">
            <button data-toggle="modal" data-target="#team-accounts-member-welcome" class="btn -primary -condensed"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Message', 'wishlist-member'); ?></span></button>
        </div>
    </div>
    <div class="panel-footer -content-footer">
        <div class="row">
            <div class="col-lg-12 text-right">
                <a href="#" class="btn -primary save-settings">
                    <i class="wlm-icons">save</i>
                    <span class="text"><?php esc_html_e('Save', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/settings/admin-welcome.php';
require_once __DIR__ . '/settings/member-welcome.php';
?>
