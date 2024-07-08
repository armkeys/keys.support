<div data-process="modal" id="coderedemption-campaign-modal-template" data-id="coderedemption-campaign-modal" data-label="coderedemption-campaign-modal" data-title="" data-show-default-footer="1" data-classes="modal-lg" style="display:none">
    <div class="body">
        <form>
            <input type="hidden" name="action" value="wlm_coderedemption_save_campaign">
            <input type="hidden" id="campaign-id" name="id" value="">
            <ul class="nav nav-tabs edit-only">
                <li class="nav-item">
                    <a class="show-modal-footer nav-link" data-toggle="tab" href="#coderedemption-campaign-modal-settings"><?php esc_html_e('Settings', 'wishlist-member'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="generate-code nav-link" data-toggle="tab" href="#coderedemption-campaign-modal-codes"><?php esc_html_e('Codes', 'wishlist-member'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="show-modal-footer nav-link" data-toggle="tab" href="#coderedemption-campaign-modal-actions"><?php esc_html_e('Actions', 'wishlist-member'); ?></a>
                </li>
            </ul>
            <div class="tab-content">
                <?php
                    require_once 'campaign/settings.php';
                    require_once 'campaign/codes.php';
                    require_once 'campaign/actions.php';
                ?>
            </div>
        </form>
    </div>
</div>
