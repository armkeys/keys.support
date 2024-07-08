<?php

/**
 * Member Manage - Rows - Child
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

?>
<tr class="level-details-tr level-details-tr-<?php echo esc_attr($user_id); ?> <?php echo 'minimal' === $tbl_collapse ? 'd-none' : ''; ?>">
    <td></td>
    <td colspan="6">
        <a href="#" title="<?php esc_attr_e('Team Member', 'wishlist-member'); ?>" data-userid="<?php echo esc_attr($user_id); ?>" data-tab-focus="#member-edit-team-accounts,#wlm-team-accounts-teams" class="edit-user-btn">
            <i class="wlm-icons md-24">members</i>
            &nbsp;
            <span class="align-middle">
            <?php
            printf(
                esc_html__('%1$s and %2$s via %3$s', 'wishlist-member'),
                sprintf(esc_html(_n('%d Level', '%d Levels', count((array) $child->active_levels), 'wishlist-member')), count((array) $child->active_levels)),
                sprintf(esc_html(_n('%d Pay Per Post', '%d Pay Per Posts', count((array) $child->pay_per_posts), 'wishlist-member')), count((array) $child->pay_per_posts)),
                sprintf(esc_html(_n('%d Team Plan', '%d Team Plans', count((array) $child->teams), 'wishlist-member')), count((array) $child->teams))
            );
            ?>
            </span>
        </a>
    </td>
</tr>
