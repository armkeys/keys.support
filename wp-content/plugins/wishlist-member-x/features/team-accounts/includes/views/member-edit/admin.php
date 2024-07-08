<?php

/**
 * Member Edit - Team tab for parent accounts
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

?>
<div class="horizontal-tabs">
    <div class="row no-gutters">
        <div class="col-12 col-md-auto">
            <!-- Nav tabs -->
            <div class="horizontal-tabs-sidebar" style="min-width: 100px; min-height: 200px;">
                <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                    <li role="presentation" class="nav-item">
                        <a href="#wlm-team-accounts-team-settings" class="nav-link pp-nav-link active" aria-controls="wlm-team-accounts-team-settings" role="tab" data-type="wlm-team-accounts-team-settings" data-title="Settings" data-toggle="tab"><?php esc_html_e('Settings', 'wishlist-member'); ?></a>
                    </li>
                    <li role="presentation" class="nav-item">
                        <a href="#wlm-team-accounts-team-members" class="nav-link pp-nav-link" aria-controls="wlm-team-accounts-team-members" role="tab" data-type="wlm-team-accounts-team-members" data-title="Team Members" data-toggle="tab"><?php esc_html_e('Team Members', 'wishlist-member'); ?></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col">
            <!-- Tab panes -->
            <div class="tab-content">
                <?php
                    $team_members = ( new \WishListMember\Features\Team_Accounts\Team_Parent($user_id) )->children;

                    require_once __DIR__ . '/admin/settings.php';
                    // Require_once __DIR__ . '/admin/manage.php';
                ?>
            </div>
        </div>
    </div>
</div>
