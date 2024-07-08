<div role="tabpanel" class="tab-pane" id="" data-id="levels_registrations">
<div class="content-wrapper">

<div class="row">
    <template class="wlm3-form-group">
        {
            label: '<?php echo esc_js(__('Member Registration Options', 'wishlist-member')); ?>',
            type: 'select',
            name: 'disableexistinglink',
            column: 'col-md-6',
            tooltip_size: 'md',
            style: 'width: 80%',
            options: [
                {value: 0, text: 'Both New and Existing Members'},
                {value: 1, text: 'New Members Only'},
                {value: 2, text: 'Existing Members Only'},
            ],
            addon_left: 'Allow',
            tooltip: '<?php echo esc_js(__('This option will affect how a registration form functions. This is helpful when using a form that is only available for new members. It\'s also  helpful in a situation where forms are specifically intended for existing users. For example, upsells or upgrades.', 'wishlist-member')); ?>',
        }
    </template>
</div>
<div class="row">
    <div class="col-xxxl-4 col-md-6 col-sm-8 col-xs-8">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Enable Registration Form Header/Footer', 'wishlist-member')); ?>',
                name  : 'enable_header_footer',
                tooltip : '<?php echo esc_js(__('Enable this feature to customize the Header section that will be displayed above a registration form and / or the Footer section that will be displayed below a registration form. ', 'wishlist-member')); ?>',
                value : '1',
                tooltip_size: 'md',
                type  : 'toggle-adjacent-disable'
            }
        </template>
    </div>
    <div class="col-md-6 col-sm-4 col-xs-4">
        <button data-toggle="modal" data-target="#header-footer" href="" class="btn -primary -condensed">
            <i class="wlm-icons">settings</i>
            <span><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
        </button>
    </div>
</div>
<div class="row">
    <div class="col-xxxl-4 col-md-6">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Enable Custom Registration Form', 'wishlist-member')); ?>',
                name  : 'enable_custom_reg_form',
                value : '1',
                tooltip_size: 'md',
                type  : 'toggle-adjacent',
                tooltip : '<?php echo esc_js(__('By default WishList Member will create a simple registration form that can be used for each membership level. If you\'d like to make modifications (for example adding fields and collecting customized information) you can enable this feature and select a custom registration form. (Custom Registrations are created in the Registration Forms section located under Advanced Options.)', 'wishlist-member')); ?>'
            }
        </template>
    </div>
    <div class="col col-md col-sm-8 mb-sm-3">
        <template class="wlm3-form-group">
            {
                group_class : 'no-margin',
                name  : 'custom_reg_form',
                value : '',
                type  : 'select',
                options : wpm_regforms,
                tooltip : '<?php echo esc_js(__('todo', 'wishlist-member')); ?>',
                style : 'width: 100%'
            }
        </template>
    </div>
    <div class="col-md-auto col-sm-2 pl-0 pr-0 edit-custom-regform">
        <a href="#" data-link="<?php echo esc_url(admin_url('admin.php')); ?>?page=WishListMember&wl=advanced_settings/registration_forms/custom#editform-" class="btn -icon-only edit-custom-regform-btn" title="Edit">
            <i class="wlm-icons md-24">edit</i>
        </a>
    </div>
    <div class="col-md-auto col-sm-2 pl-0">
        <a href="#create_reg_form" data-toggle="collapse" class="btn -success -icon-only">
            <i class="wlm-icons">add</i>
        </a>
    </div>
</div>
<div class="collapse mb-sm-4" id="create_reg_form">
    <div class="row justify-content-lg-end">
        <div class="col-md-6 d-sm-none d-lg-block"></div>
        <div class="col">
            <div class="form-group mb-1">
                <input type="text" id="new-reg-form-name" class="form-control" placeholder="Enter Name of Custom Registration Form">
            </div>
        </div>
        <div class="col-md-auto col-sm-auto">
            <a href="#" class="btn -primary -condensed -no-icon create-form-button" title="Create Form">
                <span><?php esc_html_e('Create Form', 'wishlist-member'); ?></span>
            </a>
            <a href="#create_reg_form" data-toggle="collapse" class="btn -bare -condensed -icon-only" title="Close">
                <i class="wlm-icons">close</i>
            </a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Grant Continued Access', 'wishlist-member')); ?>',
                name  : 'isfree',
                value : '1',
                tooltip : '<?php echo esc_js(__('Grant Continued Access allows a Member to retain access to content they have been given access to AFTER they cancel their membership.', 'wishlist-member')); ?>',
                type  : 'toggle-switch',
            }
        </template>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Prevent Member from Editing Pre-filled Information', 'wishlist-member')); ?>',
                tooltip : '<?php echo esc_js(__('When a membership level is connected to a payment integration, the payment provider will pass the First Name, Last Name and Email Address from the payment to the registration form. Enabling this option will prevent the user from editing any information that was pre-filled on the registration form.', 'wishlist-member')); ?>',
                name  : 'disableprefilledinfo',
                value : '1',
                tooltip_size: 'md',
                type  : 'toggle-switch'
            }
        </template>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Uncancel on Re-registration', 'wishlist-member')); ?>',
                name  : 'uncancelonregistration',
                tooltip : '<p><?php echo esc_js(__('When this option is selected it will allow a user with a cancelled status to register for the level again and remove their cancelled status. Without this option selected the user will get a message that says they are already registered to this level.', 'wishlist-member')); ?></p><p><?php echo esc_js(__('This only effects a standard registration form. If you are using an integration this setting will have no effect. The user will be uncancelled upon a successful payment.', 'wishlist-member')); ?></p>',
                value : '1',
                tooltip_size: 'md',
                type  : 'toggle-switch'
            }
        </template>
    </div>
</div>
<div class="row">
    <div class="col-md-auto">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Enable Simple Registration URL', 'wishlist-member')); ?>',
                name  : 'allow_free_reg',
                value : '1',
                uncheck_value : '0',
                tooltip_size: 'md',
                tooltip : '<?php echo esc_js(__('Integrations with Payment Providers create a specific registration form. This feature will enable a URL which includes a registration form that does not require a payment provider integration. This is helpful for testing registrations, free registrations and collecting payments from systems that do not have a specific integration included in WishList Member.', 'wishlist-member')); ?>',
                type  : 'toggle-adjacent'
            }
        </template>
    </div>

    <div class="col-md-auto">
        <template class="wlm3-form-group">
            {
                name : 'url',
                'data-initial' : '',
                addon_left : '<?php echo esc_js(WLM_REGISTRATION_URL); ?>/',
                class : 'text-center -url',
                group_class : '-url-group mb-0',
            }
        </template>
    </div>
</div>

<div class="row">
    <div class="col-xxxl-4 col-md-6 col-sm-8 col-xs-8">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Enable Auto-Created Accounts for Integrations', 'wishlist-member')); ?>',
                name  : 'autocreate_account_enable',
                tooltip : '<?php echo esc_js(__('By default, payment integrations will create an incomplete registration until the member completes the registration process.<br><br>If enabled, this feature will automatically create an account for the member.<br><br>It can be configured to create an account immediately or after a delay. This will allow a member to complete the registration process on their own during the amount of time configured.', 'wishlist-member')); ?>',
                value : '1',
                uncheck_value : '0',
                tooltip_size: 'md',
                type  : 'toggle-adjacent-disable'
            }
        </template>
    </div>
    <div class="col-md-6 col-sm-4 col-xs-4">
        <button data-toggle="modal" data-target="#auto-create-accounts-for-integrations" href="" class="btn -primary -condensed">
            <i class="wlm-icons">settings</i>
            <span><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
        </button>
    </div>
</div>

<div class="row">
    <template class="wlm3-form-group">
        {
            column : 'col',
            label : '<?php echo esc_js(__('Automatically Add non-WishList Member User Registrations to this Level', 'wishlist-member')); ?>',
            name  : 'autoadd_other_registrations',
            tooltip : '<?php echo esc_js(__('Enabling this option will automatically add the user to the Level if they join through a non-WishList Member registration option on the site. For example, the WordPress registration or a 3rd party registration form on the WordPress site. Registration needs to happen on the WordPress site for this to function.', 'wishlist-member')); ?>',
            value : '1',
            uncheck_value : '0',
            tooltip_size: 'md',
            type : 'toggle-switch'
        }
    </template>
</div>
<br>
<div class="panel-footer -content-footer">
    <div class="row">
        <div class="col-md-12 text-right">
            <?php echo wp_kses_post($tab_footer); ?>
        </div>
    </div>
</div>
</div>
</div>
