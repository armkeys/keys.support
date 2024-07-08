<div
    id="auto-create-accounts-for-integrations-modal"
    data-id="auto-create-accounts-for-integrations"
    data-label="auto-create-accounts-for-integrations"
    data-title="Auto-Create Accounts for Integrations"
    data-show-default-footer=""
    data-classes="modal-md"
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label      : '<?php echo esc_js(__('Username Format', 'wishlist-member')); ?>',
                    type       : 'text',
                    name       : 'autocreate_account_username',
                    tooltip : '<?php echo esc_js(__('The desired format of the auto-created username. ', 'wishlist-member')); ?>',
                    column     : 'col-12',
                    help_block : 'Shortcodes: {name}, {fname}, {lname}, {email}, {rand_ltr 3}, {rand_num 3}, {rand_mix 3}'
                }
            </template>
        </div>
        <div class="row">
            <div class="col-auto pr-0">
                <div class="form-group">
                    <template class="wlm3-form-group">
                        {
                            label         : '<?php echo esc_js(__('Enable Delay', 'wishlist-member')); ?>',
                            name          : 'autocreate_account_enable_delay',
                            tooltip : '<?php echo esc_js(__('Enabling a delay will result in the member being able to complete the registration process on their own during the amount of time configured. The delay can be set in minutes or hours. ', 'wishlist-member')); ?>',
                            value         : '1',
                            uncheck_value : '0',
                            type          : 'toggle-adjacent',
                        }
                    </template>
                </div>
            </div>
            <div class="col">
                <div class="form-inline pull-left">
                    <template class="wlm3-form-group">
                        {
                            type  : 'text',
                            name  : 'autocreate_account_delay',
                            style : 'width: 60px;',
                            class : 'text-center',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            type    : 'select',
                            name    : 'autocreate_account_delay_type',
                            class   : 'text-center',
                            style   : 'width: 100px;',
                            options : [
                                {text : 'Minute(s)', value : 1},
                                {text : 'Hour(s)', value : 60}
                            ],
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <?php echo wp_kses_post($modal_footer); ?>
    </div>
</div>
