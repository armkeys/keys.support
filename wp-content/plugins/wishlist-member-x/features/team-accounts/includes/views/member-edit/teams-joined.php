<?php

/**
 * Member Edit - Team - Teams Sub-pane
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

?>
<div role="tabpanel" class="tab-pane team-child-tab" id="wlm-team-accounts-teams">
    <?php echo wp_kses_post($not_member_message); ?>
    <p class="team-membership-required">
        <?php esc_html_e('The Team Plans the Team Member has been added to by a Team Admin or they have joined are listed below.', 'wishlist-member'); ?>
    </p>
    <div class="table-wrapper table-responsive team-membership-required">
        <table class="table table-condensed">
            <thead>
                    <tr>
                        <th>
                            <?php esc_html_e('Team Plan', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The name of the Team Plan the Team Member has been added to/joined.', 'wishlist-member')); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Team Admin', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The Team Admin for that Team Plan.', 'wishlist-member')); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Access', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The level(s) and/or pay per post(s) the Team Member can access.', 'wishlist-member')); ?>
                        </th>
                    </tr>
            </thead>
            <tbody>
            <?php
            foreach ($child->teams as $team) :
                $cp   = new Team_Parent($team['parent_id']);
                $name = format_member_name($team['parent_id'], true);
                ?>
                <tr>
                    <td>
                        <?php echo esc_html($cp->teams_grouped()[ $team['team_id'] ][0]['name']); ?>
                    </td>
                    <td>
                        <a href="mailto:<?php echo esc_attr($name[1]); ?>"><?php echo esc_html($name[1]); ?></a>
                    </td>
                    <td>
                    <?php
                        printf(
                            // Translators: 1 - Number of Membership Levels, 2 - Number of Pay Per Posts.
                            esc_html__('%1$s and %2$s', 'wishlist-member'),
                            sprintf(esc_html(_n('%d Level', '%d Levels', count((array) $cp->active_team_levels[ $team['team_id'] ]), 'wishlist-member')), count((array) $cp->active_team_levels[ $team['team_id'] ])),
                            sprintf(esc_html(_n('%d Pay Per Post', '%d Pay Per Posts', count((array) $cp->pay_per_posts[ $team['team_id'] ]), 'wishlist-member')), count((array) $cp->pay_per_posts[ $team['team_id'] ]))
                        );
                    ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
