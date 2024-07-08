<?php

/**
 * Member Edit - Team tab pane
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

$teams_grouped = Team_Parent::current_user()->teams_grouped();
if (empty(wlm_get_data()['team_id'])) {
    wlm_get_data()['team_id'] = $teams_grouped[ key($teams_grouped) ][0]['id'];
}
?>
<div id="wishlist-member-team-accounts-management" class="wishlist-member-team-accounts-frontend" data-team-id="<?php echo esc_attr(wlm_get_data()['team_id']); ?>">
    <form>
        <select name="team_id" style="width: 100%" onchange="this.form.submit()">
            <?php foreach ($teams_grouped as $team) : ?>
            <option value="<?php echo esc_attr($team[0]['id']); ?>" <?php echo $team[0]['id'] == wlm_get_data()['team_id'] ? 'selected' : ''; ?>><?php echo esc_html($team[0]['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php if (! empty(wlm_get_data()['team_id']) && ! empty(Team_Parent::current_user()->get_matching_teams(['id' => wlm_get_data()['team_id']]))) : ?>
        <?php $team_members = wlm_arrval(Team_Parent::current_user()->children, wlm_get_data()['team_id']); ?>
    <p id="wlm-team-accounts-team-message"></p>

    <form id="wlm-team-accounts-team-invite-form" class="closed">
        <button type="button" class="open-form" onclick="this.form.classList.toggle('closed')"><?php esc_html_e('Invite Team Member', 'wishlist-member'); ?></button>
        <p id="wlm-team-accounts-team-count">
            <?php
            printf(
                // Translators: 1: Users added, 2: Total users allowed.
                esc_html__('You have %1$s of %2$d Team Members in this Team Plan.', 'wishlist-member'),
                wp_kses_data(sprintf('<span>%d</span>', count($team_members))),
                Team_Parent::current_user()->get_max_allowed_children(wlm_get_data()['team_id'])
            );
            ?>
        </p>
        <div class="form-fields">
            <h3>
                <button type="button" class="close-form" onclick="this.form.classList.toggle('closed')">&#10006;</button>
                <?php esc_attr_e('Invite Team Member', 'wishlist-member'); ?>
            </h3>
            <input type="email" name="email" placeholder="<?php esc_attr_e('Email', 'wishlist-member'); ?>">
            <button type="submit"><?php esc_html_e('Send Invite', 'wishlist-member'); ?></button>
        </div>
    </form>

    <form id="wlm-team-accounts-team-search">
        <input type="search" name="s" placeholder="<?php esc_attr_e('Team Member search', 'wishlist-member'); ?>">
        <button type="submit"><?php echo esc_html_e('Search', 'wishlist-member'); ?></button>
    </form>

    <table style="display: none;" id="wlm-team-accounts-team-members">
        <colgroup>
            <col>
            <col>
            <col width="1">
            <col width="100">
        </colgroup>
        <thead>
            <tr>
                <th style="text-align: left"><?php esc_html_e('Name', 'wishlist-member'); ?></th>
                <th style="text-align: left"><?php esc_html_e('E-mail', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Status', 'wishlist-member'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr class="wlm-team-accounts-no-members">
                <td colspan="4"><p><?php esc_html_e('No Team Members found', 'wishlist-member'); ?></p></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>
</div>
