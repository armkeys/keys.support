<?php
    $system_pages_arr = [
        'error' => [
            'title'   => 'Error Pages',
            'options' => [
                'non_members_error_page'    => [
                    'label'        => 'Non-Members',
                    'tooltip'      => 'The Non-Members error page will be displayed to Non-Members (users with no membership level assigned to them) if they attempt to access any protected content. It will also be displayed to those who are not logged in if they attempt to access any protected content. <br><br>Note: The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'lg',
                ],
                'wrong_level_error_page'    => [
                    'label'        => 'Wrong Membership Level',
                    'tooltip'      => 'The Wrong Membership Level error page will be displayed to logged in members if they attempt to access protected content that is not assigned to their member level(s). <br><br>Example: A logged in member with the Silver membership level will be redirected to the Wrong Membership Level page if they try to access a protected page only assigned to members with the Gold membership level. <br><br>Note: The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'lg',
                ],
                'membership_cancelled'      => [
                    'label'        => 'Membership Cancelled',
                    'tooltip'      => 'The Membership Cancelled error page will be displayed to a member if their membership level has been cancelled. <br><br>Example: A logged in member with a cancelled Bronze membership level will be redirected to the Membership Cancelled page if they try to access a protected page only assigned to members with the Bronze membership level. The member can not access Bronze content since they are cancelled from the Bronze level. <br><br>Note: The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'lg',
                ],
                'membership_expired'        => [
                    'label'        => 'Membership Expired',
                    'tooltip'      => 'The Membership Expired error page will be displayed to a member if their membership level has expired. <br><br>Example: A logged in member with an expired Platinum membership level will be redirected to the Membership Expired page if they try to access a protected page only assigned to members with the Platinum membership level. The member can not access Platinum content since they are expired from the Platinum level. <br><br>Note: The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'lg',
                ],
                'duplicate_post_error_page' => [
                    'toggle'       => 'PreventDuplicatePosts',
                    'label'        => 'Prevent duplicate payment provider registrations',
                    'tooltip'      => 'If enabled, a member who has previously registered and has an existing account and then attempts to use the same information to register again (username, password, etc.) will not have a new account created, but instead the existing account will be updated. <br><br>Note: The Configure button can be used to configure the message for this setting.',
                    'tooltip_size' => 'lg',
                ],
            ],
        ],
    ];
    $_pages           = get_pages('exclude=' . implode(',', $this->exclude_pages([], true)));
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

                $button_disable = '';
            if (! empty($option['toggle'])) {
                if (! $this->get_option($option['toggle'])) {
                    $button_disable = '-disable';
                }
            }
            ?>

            <div class="row">
                <div class="col-sm-7 col-md-6 col-lg-6 col-xxxl-3 col-xxl-6">
                    <div class="form-group">
                        <?php if (empty($option['toggle'])) : ?>
                            <label>
                                <span class="title-label"><?php echo esc_html($option['label']); ?></span>
                                <?php $this->tooltip($option['tooltip'], $option['tooltip_size']); ?>
                            </label>
                        <?php else : ?>
                            <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js($option['label']); ?>',
                                    name  : '<?php echo esc_js($option['toggle']); ?>',
                                    value : '1',
                                    uncheck_value : '',
                                    class : 'auto-save',
                                    type  : 'toggle-adjacent-disable',
                                    tooltip_size: '<?php echo esc_js($option['tooltip_size']); ?>',
                                    tooltip : '<?php echo esc_js($option['tooltip']); ?>',
                                    <?php
                                    if ($this->get_option($option['toggle'])) {
                                        echo 'checked : "checked"';
                                    }
                                    ?>
                                }
                            </template>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-sm-5 col-md-4">
                    <a href="#" class="btn -primary configure-btn -condensed <?php echo esc_attr($button_disable); ?>" ptype="<?php echo esc_attr($page_type); ?>" key="<?php echo esc_attr($key); ?>" title="<?php echo esc_attr($option['label']); ?>">
                        <i class="wlm-icons">settings</i>
                        <span><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                    </a>
                </div>
            </div>
<!--            <div class="row">
                <div class="col-xxxl-3 col-xxl-6 col-lg-6 col-md-6">
                    <div class="form-group">
                        <?php if (empty($option['toggle'])) : ?>
                            <label>
                                <span class="title-label"><?php echo esc_html($option['label']); ?></span>
                                <?php $this->tooltip($option['tooltip'], $option['tooltip_size']); ?>
                            </label>
                        <?php else : ?>
                            <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js($option['label']); ?>',
                                    name  : '<?php echo esc_js($option['toggle']); ?>',
                                    value : '1',
                                    uncheck_value : '',
                                    class : 'auto-save',
                                    type  : 'toggle-adjacent-disable',
                                    tooltip_size: '<?php echo esc_js($option['tooltip_size']); ?>',
                                    tooltip : '<?php echo esc_js($option['tooltip']); ?>',
                                    <?php
                                    if ($this->get_option($option['toggle'])) {
                                        echo 'checked : "checked"';
                                    }
                                    ?>
                                }
                            </template>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <a href="#" class="btn -primary configure-btn -condensed <?php echo esc_attr($button_disable); ?>" ptype="<?php echo esc_attr($page_type); ?>" key="<?php echo esc_attr($key); ?>" title="<?php echo esc_attr($option['label']); ?>">
                        <i class="wlm-icons">settings</i>
                        <span><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
                    </a>
                </div>
            </div> -->
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
            <div class="col-md-2 col-border-right">
                <template class="wlm3-form-group">
                  {
                    label : '<?php echo esc_js(__('Page', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'internal',
                    type  : 'radio',
                    id : 'sp-internal',
                    tooltip : '<?php echo esc_js(__(' A page can be set by choosing from the list in the dropdown. The dropdown includes the WordPress pages on the site. A new page can also be created using the green plus button.', 'wishlist-member')); ?>',
                    tooltip_size : 'md'
                  }
                </template>
                <template class="wlm3-form-group">
                  {
                    label : '<?php echo esc_js(__('Message', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'text',
                    type  : 'radio',
                    id : 'sp-text',
                    tooltip : '<?php echo esc_js(__('A default message is available and can be set to be displayed on a page. The message can be edited or a completely new message can be added.<br><br>WishList Member mergecodes can be inserted into the message using the available dropdown.<br><br>There is also a “Reset to Default” option if you decide you would like to change the message back to the original message.', 'wishlist-member')); ?>',
                    tooltip_size : 'lg'
                  }
                </template>
                <template class="wlm3-form-group">
                  {
                    label : '<?php echo esc_js(__('URL', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'url',
                    type  : 'radio',
                    id : 'sp-url',
                    tooltip : '<?php echo esc_js(__('A URL can be set by entering the URL into the available field. This is useful if you want to send users to another site. An example could be a sales page located on another site.', 'wishlist-member')); ?>',
                    tooltip_size : 'md'
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
