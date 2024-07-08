<div class="wlm-inside wlm-inside03" style="display: none;">
    <?php if ('attachment' !== $post->post_type) : ?>
    <!-- System Pages -->
        <?php
        $system_page_names = [
            'non_members_error_page',
            'wrong_level_error_page',
            'membership_cancelled',
            'membership_expired',
            'membership_forapproval',
            'membership_forconfirmation',
        ];

        $a                 = __('Error Pages', 'wishlist-member');
        $b                 = __('Redirects', 'wishlist-member');
        $default_section   = array_combine($system_page_names, [
            $a,
            $a,
            $a,
            $a,
            $b,
            $b,
        ]);
        $system_page_types = ['default', 'url', 'page', 'message'];
        $page_selections   = get_pages('exclude=' . implode(',', $this->exclude_pages([], true)));
        ?>
    <p><?php esc_html_e('Please specify the error pages that people will see when they try to access this post:', 'wishlist-member'); ?></p><br>
    <div class="wlm-sp-container clearfix">
        <div class="sp-container-left">
            <p><strong><?php esc_html_e('Non-Members', 'wishlist-member'); ?>:</strong></p>
        </div>
        <div class="sp-container-right">
            <a name="<?php esc_html_e('Update Non-Members Page', 'wishlist-member'); ?>" href="#TB_inline?width=750&height=100%&inlineId=system-pages-modal-non_members_error_page" data-target="non_members_error_page" class="system-page-config wlm-btn -with-icons">
                <i class="wlm-icons"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/settings.svg" alt=""></i>
                <span>Configure</span>
            </a>
        </div>
    </div>
    <div class="wlm-sp-container clearfix">
        <div class="sp-container-left">
            <p><strong><?php esc_html_e('Wrong Membership Level', 'wishlist-member'); ?>:</strong></p>
        </div>
        <div class="sp-container-right">
            <a name="<?php esc_html_e('Update Wrong Membership Level Page', 'wishlist-member'); ?>" href="#TB_inline?width=750&height=100%&inlineId=system-pages-modal-wrong_level_error_page" data-target="wrong_level_error_page" class="system-page-config wlm-btn -with-icons">
                <i class="wlm-icons"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/settings.svg" alt=""></i>
                <span>Configure</span>
            </a>
        </div>
    </div>
    <div class="wlm-sp-container clearfix">
        <div class="sp-container-left">
            <p><strong><?php esc_html_e('Membership Cancelled', 'wishlist-member'); ?>:</strong></p>
        </div>
        <div class="sp-container-right">
            <a name="<?php esc_html_e('Update Membership Cancelled Page', 'wishlist-member'); ?>" href="#TB_inline?width=750&height=100%&inlineId=system-pages-modal-membership_cancelled" data-target="membership_cancelled" class="system-page-config wlm-btn -with-icons">
                <i class="wlm-icons"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/settings.svg" alt=""></i>
                <span>Configure</span>
            </a>
        </div>
    </div>
    <div class="wlm-sp-container clearfix">
        <div class="sp-container-left">
            <p><strong><?php esc_html_e('Membership Expired', 'wishlist-member'); ?>:</strong></p>
        </div>
        <div class="sp-container-right">
            <a name="<?php esc_html_e('Update Membership Expired Page', 'wishlist-member'); ?>" href="#TB_inline?width=750&height=100%&inlineId=system-pages-modal-membership_expired" data-target="membership_expired" class="system-page-config wlm-btn -with-icons">
                <i class="wlm-icons"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/settings.svg" alt=""></i>
                <span>Configure</span>
            </a>
        </div>
    </div>
    <div class="wlm-sp-container clearfix">
        <div class="sp-container-left">
            <p><strong><?php esc_html_e('Membership Requires Approval', 'wishlist-member'); ?>:</strong></p>
        </div>
        <div class="sp-container-right">
            <a name="<?php esc_html_e('Update Membership Requires Approval Page', 'wishlist-member'); ?>" href="#TB_inline?width=750&height=100%&inlineId=system-pages-modal-membership_forapproval" data-target="membership_forapproval" class="system-page-config wlm-btn -with-icons">
                <i class="wlm-icons"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/settings.svg" alt=""></i>
                <span>Configure</span>
            </a>
        </div>
    </div>
    <div class="wlm-sp-container clearfix">
        <div class="sp-container-left">
            <p><strong><?php esc_html_e('Membership Requires Confirmation', 'wishlist-member'); ?>:</strong></p>
        </div>
        <div class="sp-container-right">
            <a name="<?php esc_html_e('Update Membership Requires Confirmation Page', 'wishlist-member'); ?>" href="#TB_inline?width=750&height=100%&inlineId=system-pages-modal-membership_forconfirmation" data-target="membership_forconfirmation" class="system-page-config wlm-btn -with-icons">
                <i class="wlm-icons"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/settings.svg" alt=""></i>
                <span>Configure</span>
            </a>
        </div>
    </div>
        <?php include 'systempages-modal.php'; ?>
    <?php endif; ?>
</div>
