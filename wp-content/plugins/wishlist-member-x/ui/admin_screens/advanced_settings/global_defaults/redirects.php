<?php
    $system_pages_arr = [
        'error' => [
            'title'   => 'Error Pages',
            'options' => [
                'after_registration'         => [
                    'label'        => 'After Registration',
                    'tooltip'      => '<br>The After Registration page will be displayed to members after a successful registration to a membership level.<br><br>Note: This page will only appear one time for the member immediately after registration. The After Registration page cannot be viewed again. The After Login page will appear to the member after each login moving forward. <br><br>The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'lg',
                ],
                'membership_forapproval'     => [
                    'label'        => 'Membership Requires Approval',
                    'tooltip'      => 'The Membership Requires Approval page will be displayed to members after a successful registration to a membership level that requires approval from the site admin. <br><br>Note: The “Require Admin Approval” setting is located in the Setup > Levels > Edit Level > Requirements section of WishList Member. <br><br>The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'md',
                ],
                'membership_forconfirmation' => [
                    'label'        => 'Membership Requires Confirmation',
                    'tooltip'      => 'The Membership Requires Confirmation page will be displayed to members after a successful registration to a membership level that requires the member to confirm using the email that is sent after registration. <br><br>Note: The “Require Member to Confirm” setting is located in the Setup > Levels > Edit Level > Requirements section of WishList Member. <br><br>The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'md',
                ],
                'after_login'                => [
                    'label'        => 'After Login',
                    'tooltip'      => 'The After Login page will be displayed to members after each login. <br><br>Note: The After Login page that will be displayed to members in multiple membership levels is based on Level Order. <br><br>The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'md',
                ],
                'after_logout'               => [
                    'label'        => 'After Logout',
                    'tooltip'      => 'The After Logout page will be displayed to members after each login. <br><br>Note: The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'md',
                ],
                'unsubscribe'                => [
                    'label'        => 'Unsubscribe Confirmation',
                    'tooltip'      => 'The Unsubscribe Confirmation page will be displayed when a member unsubscribes from the Email Broadcast list in WishList Member. <br><br>Note: The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'md',
                ],
                'resubscribe'                => [
                    'label'        => 'Resubscribe Confirmation',
                    'tooltip'      => 'The Resubscribe Confirmation page will be displayed when a member resubscribes to the Email Broadcast list in WishList Member. <br><br>Note: The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'md',
                ],
            ],
        ],
    ];

    $_pages = get_pages('exclude=' . implode(',', $this->exclude_pages([], true)));
    ?>
<div class="content-wrapper">
    <?php foreach ($system_pages_arr as $system_pages) : ?>
        <?php foreach ($system_pages['options'] as $key => $option) : ?>
            <?php
                $page_type = $this->get_option($key . '_type');
            if (false === $page_type) {
                $p = $this->get_option($key . '_internal');
                if ($p) {
                    $page_type = 'internal';
                } else {
                    $_pages_url = $this->get_option($key);
                    $page_type  = $_pages_url ? 'url' : 'text';
                }
            }
            ?>
            <div class="row">
                <div class="col-sm-7 col-md-4 col-lg-4 col-xl-3 col-xxl-3">
                    <div class="form-group">
                        <label>
                            <span class="title-label"><?php echo esc_html($option['label']); ?></span>
                            <?php $this->tooltip($option['tooltip'], $option['tooltip_size']); ?>
                        </label>
                    </div>
                </div>
                <div class="col-sm-5 col-md-4">
                    <a href="#" class="btn -primary configure-btn -condensed" ptype="<?php echo esc_attr($page_type); ?>" key="<?php echo esc_attr($key); ?>" title="<?php echo esc_attr($option['label']); ?>">
                        <i class="wlm-icons">settings</i>
                        <span><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div id="configure-pages" data-id="configure-pages" data-label="configure-pages-label" data-title="Configure System Pages" data-classes="modal-lg" style="display:none">
    <div class="body">
        <div class="row settings-content">
            <div class="col-md-12">
                <h4 class="mb-3"><?php esc_html_e('Select one of the following options:', 'wishlist-member'); ?></h4>
            </div>
            <div class="col-md-2 mb-3 col-border-right">
                <template class="wlm3-form-group">
                  {
                    label : '<?php echo esc_js(__('Page', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'internal',
                    type  : 'radio',
                    id : 'sp-internal',
                    tooltip : '<?php echo esc_js(__('This option can be used in order to select a specific page created in WordPress.', 'wishlist-member')); ?>'
                  }
                </template>
                <template class="wlm3-form-group">
                  {
                    label : '<?php echo esc_js(__('Message', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'text',
                    type  : 'radio',
                    id : 'sp-text',
                    tooltip : '<?php echo esc_js(__('This option can be used in order to specify a message that will automatically be displayed by WishList Member.', 'wishlist-member')); ?>'
                  }
                </template>
                <template class="wlm3-form-group">
                  {
                    label : '<?php echo esc_js(__('URL', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'url',
                    type  : 'radio',
                    id : 'sp-url',
                    tooltip : '<?php echo esc_js(__('This option can be used in order to redirect to a specific URL that may be located or hosted outside of your WordPress site.', 'wishlist-member')); ?>'
                  }
                </template>
            </div>
            <div class="col-md-10">
                <input type="hidden" name="type" class="system-page-type" value="" />
                <div class="sp-text-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <textarea class="form-control system-page-text" name="text" cols="30" rows="10" placeholder="Your message" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button class="btn -default -condensed page-message-reset-button">Reset to Default</button>
                            <template class="wlm3-form-group">{
                                type : 'select',
                                column : 'col-md-5 pull-right no-margin no-padding',
                                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                                group_class : 'shortcode_inserter mb-0',
                                style : 'width: 100%',
                                options : get_merge_codes([{value : '[password]', text : 'Password'}]),
                                grouped: true,
                                class : 'insert_text_at_caret',
                                'data-target' : '.system-page-text',
                            }</template>
                        </div>
                    </div>
                </div>
                <div class="sp-page-content" style="display:none">
                    <div class="row">
                        <div class="col-md-8">
                          <div class="form-group">
                            <select class="form-control wlm-select wlm-select-pages system-page-internal" name="internal" style="width: 100%" data-placeholder="Select a page">
                              <option></option>
                              <?php foreach ($_pages as $p) : ?>
                                <option value="<?php echo esc_attr($p->ID); ?>" ><?php echo esc_html($p->post_title); ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <a href="javascript:void(0);" class="btn -success -icon-only add-page-btn" style="margin-bottom: 15px" title="Add a page">
                            <i class="wlm-icons">add</i>
                          </a>
                        </div>
                    </div>
                    <div class="row create-page-holder">
                        <div class="col-md-8">
                            <div class="form-group">
                                <input type="text" class="form-control " name="page_title" value="" placeholder="Page title" required="required" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <a href="javascript:void(0);" class="btn -primary -condensed -no-icon create-page-btn" title="Create Page">
                                <span><?php esc_html_e('Create Page', 'wishlist-member'); ?></span>
                            </a>
                            <a href="javascript:void(0);" class="btn -bare -condensed -no-icon hide-create-page-btn" title="Create Page">
                                <i class="wlm-icons">close</i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="sp-url-content" style="display:none">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group ">
                                <input type="text" class="form-control system-page-url" name="url" value="" placeholder="Specify the URL" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
    <button type="button" class="btn -bare" data-dismiss="modal">
        <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
    </button>
    <button type="button" class="btn -primary save-button">
        <i class="wlm-icons">save</i>
        <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
    </button>
    <button class="-close btn -success -modal-btn save-button">
        <i class="wlm-icons">save</i>
        <span><?php esc_html_e('Save & Close', 'wishlist-member'); ?></span>
    </button>
    </div>
</div>

