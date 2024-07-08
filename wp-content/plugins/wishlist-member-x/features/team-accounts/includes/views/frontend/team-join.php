<?php

/**
 * Team Registration view
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

?>
<div id="wishlist-member-team-accounts-join" class="wishlist-member-team-accounts-frontend">
    <?php if ($parent_id && $team_id && $email) : ?>
        <?php if (get_user_by('email', $email)) : ?>
        <form method="POST" id="team-accounts-registration" class="wlm3-form">
            <div id="wlm-team-accounts-team-message"></div>

            <input type="hidden" name="action" value="wishlistmember_team_accounts_register">
            <input type="hidden" name="<?php echo esc_attr(get_wlm_nonce_field_name()); ?>" value="<?php echo esc_attr(get_wlm_nonce()); ?>">
            <input type="hidden" name="existing-user" value="1">
            <input type="hidden" name="parent_id" value="<?php echo esc_attr($parent_id); ?>">
            <input type="hidden" name="team_id" value="<?php echo esc_attr($team_id); ?>">
            <input type="hidden" name="email" value="<?php echo esc_attr($email); ?>">
            <input type="hidden" name="join_key" value="<?php echo esc_attr($invite_key); ?>">
            <p>
            <?php
            printf(
                // Translators: %s Email address.
                esc_html__('Enter password for %s', 'wishlist-member'),
                esc_html($email)
            );
            ?>
            </p>
            <?php
                echo wp_kses(
                    wlm_form_field(
                        [
                            'label' => __('Password', 'wishlist-member'),
                            'type'  => 'password',
                            'name'  => 'password',
                        ]
                    ) .
                    wlm_form_field(
                        [
                            'type'  => 'submit',
                            'value' => __('Join Team', 'wishlist-member'),
                        ]
                    ),
                    kses_allowed_html()
                );
            ?>
        </form>
        <?php else : ?>
    <form method="POST" id="team-accounts-registration" class="wlm3-form wlm_regform_container">
        <div id="wlm-team-accounts-team-message"></div>

        <input type="hidden" name="action" value="wishlistmember_team_accounts_register">
        <input type="hidden" name="<?php echo esc_attr(get_wlm_nonce_field_name()); ?>" value="<?php echo esc_attr(get_wlm_nonce()); ?>">
        <input type="hidden" name="parent_id" value="<?php echo esc_attr($parent_id); ?>">
        <input type="hidden" name="team_id" value="<?php echo esc_attr($team_id); ?>">
        <input type="hidden" name="join_key" value="<?php echo esc_attr($invite_key); ?>">
        <input type="hidden" name="new-user" value="1">
            <?php
            echo wp_kses(
                wlm_form_field(
                    [
                        'label' => __('First Name', 'wishlist-member'),
                        'type'  => 'text',
                        'name'  => 'first_name',
                    ]
                ) .
                wlm_form_field(
                    [
                        'label' => __('Last Name', 'wishlist-member'),
                        'type'  => 'text',
                        'name'  => 'last_name',
                    ]
                ) .
                wlm_form_field(
                    [
                        'label'    => __('Email', 'wishlist-member'),
                        'type'     => 'email',
                        'name'     => 'email',
                        'readonly' => 'true',
                        'value'    => $email,
                    ]
                ) .
                wlm_form_field(
                    [
                        'label' => __('Username', 'wishlist-member'),
                        'type'  => 'text',
                        'name'  => 'username',
                    ]
                ) .
                wlm_form_field(
                    [
                        'label' => __('Password', 'wishlist-member'),
                        'type'  => 'password',
                        'name'  => 'password',
                    ]
                ) .
                wlm_form_field(
                    [
                        'type'  => 'submit',
                        'value' => __('Join Team', 'wishlist-member'),
                    ]
                ),
                kses_allowed_html()
            );
            ?>
    </form>
        <?php endif; ?>
    <?php else : ?>
    <p><?php esc_html_e('Invalid team registration key', 'wishlist-member'); ?></p>
    <?php endif; ?>
</div>
