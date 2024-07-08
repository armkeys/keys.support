<?php

/**
 * Team invite Email template View
 *
 * @package WishListMember/Features/TeamAccounts
 */

?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-4">
            <label class="-standalone">
                <?php esc_html_e('Team Member Invite', 'wishlist-member'); ?>
                <?php wishlistmember_instance()->tooltip(__('An email inviting a user to join the team is sent when they are added to a team. The email contains a shortcode that displays an "Accept Invite" link the user can click to join the team.', 'wishlist-member')); ?>
            </label>
        </div>
        <div class="col-md-auto text-right">
            <button data-toggle="modal" data-target="#email-notification-team-invite" class="btn -primary -condensed" data-notif-setting="newuser" data-notif-title="<?php esc_attr_e('Team Invite', 'wishlist-member'); ?>"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notification', 'wishlist-member'); ?></span></button>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/email-templates/team-invite.php';
?>
