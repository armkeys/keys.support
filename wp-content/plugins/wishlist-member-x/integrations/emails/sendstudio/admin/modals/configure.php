<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template" 
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <p><a href="#sendstudio-enable-api" class="hide-show"><?php esc_html_e('Enable the XML API in Interspire Email Marketer', 'wishlist-member'); ?></a></p>
                <div class="panel d-none" id="sendstudio-enable-api">
                    <div class="panel-body">
                        <ol style="list-style: decimal">
                            <li><p><?php esc_html_e('Log in to the Interspire Email Marketer account', 'wishlist-member'); ?></p></li>
                            <li><p><?php esc_html_e('Navigate to the following section:', 'wishlist-member'); ?><br><?php esc_html_e('Users & Groups > View User Accounts', 'wishlist-member'); ?></p></li>
                            <li><p><?php esc_html_e('The User Accounts page will appear. Click the Edit option for a User Account.', 'wishlist-member'); ?></p></li>
                            <li><p><?php esc_html_e('The Edit a User Account page will appear. Click the Advanced User Settings tab.', 'wishlist-member'); ?></p></li>
                            <li><p><?php esc_html_e('Click the checkbox to Enable the XML API (“Yes, allow this user to use the XML API”)', 'wishlist-member'); ?></p></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('XML Path', 'wishlist-member')); ?>',
                    tooltip : '<p><?php echo esc_js(__('The XML Path URL is located in the Advanced User Settings tab in Interspire Email Marketer as described in the “Enable the XML API in Interspire Email Marketer” section above. Copy the XML Path URL and paste it into the corresponding field below.', 'wishlist-member')); ?></p>',
                    tooltip_size : 'lg',
                    name : 'sspath',
                    column : 'col-md-12',
                    type : 'url',
                    help_block : '<?php echo esc_js(__('Example: http://www.yourdomain.com/[path/to/IEM/installation]/xml.php', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('XML Username', 'wishlist-member')); ?>',
                    tooltip : '<p><?php echo esc_js(__('The XML Username is located in the Advanced User Settings tab in Interspire Email Marketer as described in the “Enable the XML API in Interspire Email Marketer” section above. Copy the XML Username and paste it into the corresponding field below.', 'wishlist-member')); ?></p>',
                    tooltip_size : 'lg',
                    name : 'ssuname',
                    column : 'col-md-4',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('XML Token', 'wishlist-member')); ?>',
                    tooltip : '<p><?php echo esc_js(__('The XML Token is located in the Advanced User Settings tab in Interspire Email Marketer as described in the “Enable the XML API in Interspire Email Marketer” section above. Copy the XML Token and paste it into the corresponding field below.', 'wishlist-member')); ?></p>',
                    tooltip_size : 'lg',
                    name : 'sstoken',
                    column : 'col-md-8',
                }
            </template>
        </div>
        <div class="row">
            <div class="col-md-12">
                <p><a href="#sendstudio-custom-fields" class="hide-show"><?php esc_html_e('Assign the Custom Field IDs for the First Name and Last Name', 'wishlist-member'); ?></a></p>
                <div class="panel d-none" id="sendstudio-custom-fields">
                    <div class="panel-body">
                        <ol style="list-style: decimal">
                            <li><p><?php esc_html_e('Log in to the Interspire Email Marketer account', 'wishlist-member'); ?></p></li>
                            <li><p><?php esc_html_e('Navigate to the following section:', 'wishlist-member'); ?><br><?php esc_html_e('Contact Lists > View Custom Fields', 'wishlist-member'); ?></p></li>
                            <li><p><?php esc_html_e('The View Custom Fields page will appear. Click the Edit option for the First Name custom field.', 'wishlist-member'); ?></p></li>
                            <li>
                                <p>
                                    <?php esc_html_e('The Edit Custom Field page will appear.', 'wishlist-member'); ?>
                                    <br><?php esc_html_e('Copy the number of the ID parameter at the end of the URL displayed in the address bar of the browser.', 'wishlist-member'); ?>
                                    <br><?php esc_html_e('URL Example:', 'wishlist-member'); ?>
                                    <br>http://www.yourdomain.com/[path/to/IEM]/admin/index.php?Page=CustomFields&Action=Edit&id=<mark>2</mark>
                                    <br>(<?php esc_html_e('The number 2 is the ID parameter in this example', 'wishlist-member'); ?>)
                                </p>
                            </li>
                            <li><p><?php esc_html_e('The process can be repeated to get the ID parameter for the Last Name custom field.', 'wishlist-member'); ?></p></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('First Name Custom Field ID', 'wishlist-member')); ?>',
                    name : 'ssfnameid',
                    'data-default' : 2,
                    column : 'col-md-6',
                    tooltip : '<p><?php echo esc_js(__('The First Name Custom Field ID is located in the Edit Custom Field section in Interspire Email Marketer as described in the “Assign the Custom Field IDs for the First Name and Last Name” section above. Copy the First Name Custom Field ID and paste it into the corresponding field below.', 'wishlist-member')); ?></p><p><?php printf(esc_js(/* Translators: %s the number 2 in bold text */ __('Note: The number %s is set as the First Name Custom Field ID in Interspire Email Marketer by default.', 'wishlist-member')), '<strong>2</strong>'); ?></p>',
                    tooltip_size : 'lg',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Last Name Custom Field ID', 'wishlist-member')); ?>',
                    name : 'sslnameid',
                    'data-default' : 3,
                    column : 'col-md-6',
                    tooltip : '<p><?php echo esc_js(__('The Last Name Custom Field ID is located in the Edit Custom Field section in Interspire Email Marketer as described in the “Assign the Custom Field IDs for the First Name and Last Name” section above. Copy the Last Name Custom FIeld ID and paste it into the corresponding field below.', 'wishlist-member')); ?></p><p><?php printf(esc_js(/* Translators: %s the number 3 in bold text */ __('Note: The number %s is set as the Last Name Custom Field ID in Interspire Email Marketer by default.', 'wishlist-member')), '<strong>3</strong>'); ?></p>',
                    tooltip_size : 'lg',
                }
            </template>
        </div>
    </div>
</div>
