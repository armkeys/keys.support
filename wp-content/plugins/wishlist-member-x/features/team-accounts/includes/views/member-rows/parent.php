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
        <a href="#" title="<?php esc_attr_e('Team Admin', 'wishlist-member'); ?>" data-userid="<?php echo esc_attr($user_id); ?>" data-tab-focus="#member-edit-team-accounts" class="edit-user-btn">
            <i class="wlm-icons md-24">supervisor_account</i>
            &nbsp;
            <span class="align-middle">
            <?php
            printf(
                esc_html__('%1$s and %2$s', 'wishlist-member'),
                sprintf(
                    esc_html(_n('%d Team Plan', '%d Team Plans', count($parent_allowed_children), 'wishlist-member')),
                    count($parent_allowed_children)
                ),
                sprintf(
                    esc_html(_n('%d Team Member', '%d Team Members', $parent_children_count, 'wishlist-member')),
                    $parent_children_count
                )
            );
            ?>
            </span>
        </a>
    </td>
</tr>
