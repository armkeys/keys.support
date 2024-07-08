<?php

/**
 * Member Edit - Team tab for team members.
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

?>
<div class="row">
    <div class="col">
            <p class="alert alert-primary">
            <?php
            $parent_msg = sprintf(
                // Translators: %s Name of team account parent.
                esc_html__('This user is a member of the team account under %s.', 'wishlist-member'),
                sprintf('<a href="#" class="wlm-team-accounts-teamaccounts-link-to-parent edit-user-btn" data-userid="%d">%s</a>', $team_parent, esc_html(\WishListMember\Features\Team_Accounts\format_member_name($team_parent)))
            );
            echo wp_kses_post($parent_msg);
            ?>
        </p>
    </div>
</div>
<script>
$(function() {
    $('#edit-user-modal').find('#manage-edit-levels,#pay-per-posts').find(':input,a.btn').prop('disabled', true).addClass('disabled');
    $('#edit-user-modal').find('#manage-edit-levels,#pay-per-posts').find('tr a.btn').hide();
    $('#edit-user-modal').find('#member-level,#pay-per-posts').prepend('<div class="row"><div class="col-12"><p class="alert alert-primary"><?php echo wp_kses_post($parent_msg); ?></p></div></div>');
});
</script>
