<?php foreach ($system_page_names as $system_page) : ?>
    <?php
    $x_type_name  = sprintf('%s_type_%d', $system_page, $post->ID);
    $x_type_value = $this->get_option($x_type_name);
    if (empty($x_type_value)) {
        $x_type_value = 'default';
    }
    ?>
<div id="system-pages-modal-<?php echo esc_attr($system_page); ?>" style="display: none">
    <div class="system-pages-modal" data-pagetype="<?php echo esc_attr($system_page); ?>">
        <div class="wlm-modal-content-container clearfix">
            <p><strong>Select one of the following options:</strong></p>
            <div class="wlm-modal-content modal-content-left">
                <ul data-pagetype="<?php echo esc_attr($system_page); ?>">
                    <li>
                        <div class="form-check -with-tooltip">
                            <label class="cb-container">
                                <input class="wlm3_system_page_types" type="radio" name="<?php echo esc_attr($x_type_name); ?>" value="default" <?php echo 'default' === $x_type_value ? 'checked="checked"' : ''; ?>>
                                <span class="btn-radio"></span>
                                <span class="text-content"><?php esc_html_e('Default', 'wishlist-member'); ?> <a class="wlm-help-icon" data-tooltip="<?php esc_attr_e('Selecting the Default option will apply the selection that has been set in the Advanced Options > Global Defaults > Error Pages section.', 'wishlist-member'); ?>"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-help-24px.svg" alt=""></a></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="form-check -with-tooltip">
                            <label class="cb-container">
                                <input class="wlm3_system_page_types" type="radio" name="<?php echo esc_attr($x_type_name); ?>" value="page" <?php echo 'page' === $x_type_value ? 'checked="checked"' : ''; ?>>
                                <span class="btn-radio"></span>
                                <span class="text-content"><?php esc_html_e('Page', 'wishlist-member'); ?> <a class="wlm-help-icon" data-tooltip="<?php esc_attr_e('This option can be used in order to select a specific page created in WordPress.', 'wishlist-member'); ?>"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-help-24px.svg" alt=""></a></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="form-check -with-tooltip">
                            <label class="cb-container">
                                <input class="wlm3_system_page_types" type="radio" name="<?php echo esc_attr($x_type_name); ?>" value="message" <?php echo 'message' === $x_type_value ? 'checked="checked"' : ''; ?>>
                                <span class="btn-radio"></span>
                                <span class="text-content"><?php esc_html_e('Message', 'wishlist-member'); ?> <a class="wlm-help-icon" data-tooltip="<?php esc_attr_e('This option can be used in order to specify a message that will automatically be displayed by WishList Member.', 'wishlist-member'); ?>"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-help-24px.svg" alt=""></a></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="form-check -with-tooltip">
                            <label class="cb-container">
                                <input class="wlm3_system_page_types" type="radio" name="<?php echo esc_attr($x_type_name); ?>" value="url" <?php echo 'url' === $x_type_value ? 'checked="checked"' : ''; ?>>
                                <span class="btn-radio"></span>
                                <span class="text-content"><?php esc_html_e('URL', 'wishlist-member'); ?> <a class="wlm-help-icon" data-tooltip="<?php esc_attr_e('This option can be used in order to redirect to a specific URL that may be located or hosted outside of your WordPress site.', 'wishlist-member'); ?>"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-help-24px.svg" alt=""></a></span>
                            </label>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="wlm-modal-content modal-content-right">
                <div
                <?php
                if ('default' !== $x_type_value) {
                    echo 'style="display: none;"';
                }
                ?>
                 class="wlm3-system-pages" id="<?php printf('wlm3-%s-default', esc_attr($system_page)); ?>">
                    <?php /* translators: %s: system page */ printf(wp_kses_data(__('Using value from <strong>Advanced Options &gt; Global Defaults &gt; %s</strong> section', 'wishlist-member')), esc_html($default_section[ $system_page ])); ?>
                </div>
                <div
                <?php
                if ('page' !== $x_type_value) {
                    echo 'style="display: none;"';
                }
                ?>
                 class="wlm3-system-pages" id="<?php printf('wlm3-%s-page', esc_attr($system_page)); ?>">
                    <div class="wlm-page-select clearfix">
                        <div class="wlm-modal-content wlm-page-select-left">
                            <?php $x = sprintf('%s_internal_%d', $system_page, $post->ID); ?>
                            <select class="form-control wlm-select wlm3-system-page-dropdown" data-placeholder="<?php esc_html_e('Select a Page', 'wishlist-member'); ?>" name="<?php echo esc_attr($x); ?>" id="" style="width: 100%">
                                <option></option>
                                <?php
                                    $x = $this->get_option($x);
                                foreach ($page_selections as $_page) {
                                    $selected = $x == $_page->ID ? 'selected' : '';
                                    printf('<option value="%d" %s>%s</option>', esc_attr($_page->ID), esc_attr($selected), esc_html($_page->post_title));
                                }
                                ?>
                            </select>
                        </div>
                        <div class="wlm-modal-content wlm-page-select-right">
                            <a href="#" class="wlm-btn -icon-only -success wlm3-show-add-page">
                                <img style="color: #fff" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-add-24px.svg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="wlm-page-title clearfix wlm3-create-page" style="display: none">
                        <div class="wlm-modal-content wlm-page-title-left">
                            <input type="text" class="form-control wlm3-create-systempage-title" value="" placeholder="Page title" data-lpignore="true">
                        </div>
                        <div class="wlm-modal-content wlm-page-title-right">
                            <a href="#" class="wlm3-create-systempage wlm-btn"><?php esc_html_e('Create Page', 'wishlist-member'); ?></a>
                            <a href="#" class="wlm-btn -bare -icon-only wlm3-show-add-page"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-close-24px-dark.svg" alt=""></a>
                        </div>
                    </div>
                </div>
                <div
                <?php
                if ('message' !== $x_type_value) {
                    echo 'style="display: none;"';
                }
                ?>
                 class="wlm3-system-pages" id="<?php printf('wlm3-%s-message', esc_attr($system_page)); ?>">
                    <div class="clearfix">
                        <?php
                            $name      = sprintf('%s_message_%d', $system_page, $post->ID);
                            $editor_id = sprintf('%s_message_mce', $system_page);
                            $mce_value = wlm_trim($this->get_option($name));
                        if (empty($mce_value)) {
                            $mce_value = $this->page_templates[ $system_page . '_internal' ];
                        }
                        ?>
                        <textarea name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($editor_id); ?>"><?php echo esc_textarea($mce_value); ?></textarea>
                        <br>
                    </div>
                    <div class="clearfix">
                        <div class="wlm-modal-content wlm-message-left">
                            <a href="#" class="wlm-btn -default wlm3-reset-message"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></a>
                        </div>
                        <div class="wlm-modal-content wlm-message-right">
                            <select class="form-control wlm-select wlm3-shortcodes" style="width: 100%" data-placeholder="<?php esc_html_e('Insert Merge Code', 'wishlist-member'); ?>">
                                <option value=""></option>
                                <?php if ('non_members_error_page' === $system_page) : ?>
                                <option value="[loginurl]"><?php esc_html_e('Login URL', 'wishlist-member'); ?></option>
                                <?php else : ?>
                                <optgroup label="<?php esc_html_e('Common', 'wishlist-member'); ?>">
                                    <option value="[firstname]"><?php esc_html_e('First Name', 'wishlist-member'); ?></option>
                                    <option value="[lastname]"><?php esc_html_e('Last Name', 'wishlist-member'); ?></option>
                                    <option value="[email]"><?php esc_html_e('Email', 'wishlist-member'); ?></option>
                                    <option value="[username]"><?php esc_html_e('Username', 'wishlist-member'); ?></option>
                                    <option value="[memberlevel]"><?php esc_html_e('Membership Level', 'wishlist-member'); ?></option>
                                    <option value="[loginurl]"><?php esc_html_e('Login URL', 'wishlist-member'); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_html_e('Other', 'wishlist-member'); ?>">
                                    <option value="[wlm_website]"><?php esc_html_e('Website URL', 'wishlist-member'); ?></option>
                                    <option value="[wlm_biography]"><?php esc_html_e('Biography', 'wishlist-member'); ?></option>
                                    <option value="[wlm_company]"><?php esc_html_e('Company', 'wishlist-member'); ?></option>
                                    <option value="[wlm_address]"><?php esc_html_e('Address', 'wishlist-member'); ?></option>
                                    <option value="[wlm_address1]"><?php esc_html_e('Address 1', 'wishlist-member'); ?></option>
                                    <option value="[wlm_address2]"><?php esc_html_e('Address 2', 'wishlist-member'); ?></option>
                                    <option value="[wlm_state]"><?php esc_html_e('State', 'wishlist-member'); ?></option>
                                    <option value="[wlm_city]"><?php esc_html_e('City', 'wishlist-member'); ?></option>
                                    <option value="[wlm_zip]"><?php esc_html_e('Zip', 'wishlist-member'); ?></option>
                                    <option value="[wlm_country]"><?php esc_html_e('Country', 'wishlist-member'); ?></option>
                                </optgroup>
                                <?php endif; ?>
                                </select>
                        </div>
                    </div>
                    <br>
                </div>
                <div
                <?php
                if ('url' !== $x_type_value) {
                    echo 'style="display: none;"';
                }
                ?>
                 class="wlm3-system-pages" id="<?php printf('wlm3-%s-url', esc_attr($system_page)); ?>">
                    <?php $x = sprintf('%s_%d', $system_page, $post->ID); ?>
                    <input type="text" class="form-control system-page-url" name="<?php echo esc_attr($x); ?>" value="<?php echo esc_attr($this->get_option($x)); ?>" placeholder="Specify the URL" data-lpignore="true">
                </div>
            </div>
        </div>
        <div class="wlm-modal-footer">
            <div class="" style="float:right; margin-top: 20px">
                <button class="wlm-btn -bare" onclick="tb_remove(); return false;"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
                <button class="wlm3-save-system-page wlm-btn -with-icons">
                <i class="wlm-icons"><img class="wlm3-save-icon" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-save-24px.svg" alt=""><img class="wlm3-save-icon" style="display: none" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-update-24px.svg" alt=""></i>
                <span>Save</span>
                </button>
                <button class="wlm3-save-system-page -close wlm-btn -with-icons -success">
                <i class="wlm-icons"><img class="wlm3-save-icon" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-save-24px.svg" alt=""><img class="wlm3-save-icon" style="display: none" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-update-24px.svg" alt=""></i>
                <span>Save & Close</span>
                </button>
            </div>
        </div>
        <?php include 'modal-overlay.php'; ?>
    </div>
</div>
<?php endforeach; ?>
