<div class="horizontal-tabs">
    <div class="row no-gutters">
        <div class="col-12 col-md-auto">
            <div class="horizontal-tabs-sidebar">
                <ul class="nav nav-tabs -h-tabs flex-column" id="xys">
                    <li class="active nav-item"><a class="active nav-link" data-toggle="tab" href="#" data-target="#loginform-logo"><?php esc_html_e('Logo', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#" data-target="#loginform-box"><?php esc_html_e('Box', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#" data-target="#loginform-fields"><?php esc_html_e('Fields', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#" data-target="#loginform-button"><?php esc_html_e('Button', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="col">
            <div class="tab-content">
                <div class="tab-pane active" id="loginform-logo">
                    <div class="row">
                        <!-- Logo -->
                        <template class="wlm3-form-group">
                            {
                                type : 'wlm3media',
                                label : '<?php echo esc_js(__('Custom Logo', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_logo',
                                value : <?php echo wp_json_encode(htmlentities((string) $this->get_option('login_styling_custom_logo'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-8',
                                group_class : 'img-uploader-big',
                                tooltip : '<?php echo esc_js(__('An uploaded Custom Logo will replace the WordPress Logo on the Login Form.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <input type="hidden" name="login_styling_custom_logo_height" value="<?php echo esc_attr($this->get_option('login_styling_custom_logo_height')); ?>">
                        <input type="hidden" name="login_styling_custom_logo_width" value="<?php echo esc_attr($this->get_option('login_styling_custom_logo_width')); ?>">
                    </div>
                </div>
                <div class="tab-pane" id="loginform-box">
                    <div class="row">
                        <h4 class="col-12">
                            <?php esc_html_e('Box', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The Login Box can be customized based on the options below.', 'wishlist-member')); ?>
                            <hr>
                        </h4>
                        
                        <!-- Box Alignment -->
                        <template class="wlm3-form-group">
                            {
                                type : 'select',
                                label : '<?php echo esc_js(__('Alignment', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_position',
                                value : <?php echo json_encode($this->get_option('login_styling_custom_loginbox_position')); ?>,
                                options : [
                                    { value : '', text : '<?php esc_attr_e('Theme Default', 'wishlist-member'); ?>' },
                                    { value : '0 auto', text : '<?php esc_attr_e('Center Aligned', 'wishlist-member'); ?>' },
                                    { value : '0 auto 0 0', text : '<?php esc_attr_e('Left Aligned', 'wishlist-member'); ?>' },
                                    { value : '0 0 0 auto', text : '<?php esc_attr_e('Right Aligned', 'wishlist-member'); ?>' }
                                ],
                                column : 'col-md-4',
                                style : 'width: 100%',
                                tooltip : '<?php echo esc_js(__('The Alignment of the Login Box can be set relative to the Login Form.', 'wishlist-member')); ?>',
                            }
                        </template>

                        <!-- Box Width -->
                        <template class="wlm3-form-group">
                            {
                                type : 'number',
                                min : 0,
                                label : '<?php echo esc_js(__('Width (px)', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_width',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_width'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                tooltip : '<?php echo esc_js(__('The Width of the Login Box can be set in pixels.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <div class="col-md-4 d-none d-md-block"></div>

                        <!-- Box BG Color -->
                        <template class="wlm3-form-group">
                            {
                                type : 'text',
                                label : '<?php echo esc_js(__('Background Color', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_bgcolor',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_bgcolor'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                class : 'wlm3colorpicker',
                                tooltip : '<?php echo esc_js(__('The Background Color of the Login Box can be set using the Color Picker or by inputting the RGBA code.', 'wishlist-member')); ?>',
                            }
                        </template>

                        <!-- Box FG Color -->
                        <template class="wlm3-form-group">
                            {
                                type : 'text',
                                label : '<?php echo esc_js(__('Text Color', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_fgcolor',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_fgcolor'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                class : 'wlm3colorpicker',
                                tooltip : '<?php echo esc_js(__('The Text Color of the Login Box can be set using the Color Picker or by inputting the RGBA code.', 'wishlist-member')); ?>',
                            }
                        </template>

                        <!-- Box Text Size -->
                        <template class="wlm3-form-group">
                            {
                                type : 'number',
                                min : 0,
                                label : '<?php echo esc_js(__('Text Size (px)', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_fontsize',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_fontsize'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                tooltip : '<?php echo esc_js(__('The Text Size within the Login Box can be set in pixels.', 'wishlist-member')); ?>',

                            }
                        </template>
                    </div>
                </div>
                <div class="tab-pane" id="loginform-fields">
                    <div class="row">
                        <h4 class="col-12">
                            <?php esc_html_e('Fields', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The Fields can be customized based on the options below.', 'wishlist-member')); ?>
                            <hr>
                        </h4>

                        <!-- Field BG Color -->
                        <template class="wlm3-form-group">
                            {
                                type : 'text',
                                label : '<?php echo esc_js(__('Background Color', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_fld_bgcolor',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_fld_bgcolor'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                class : 'wlm3colorpicker',
                                tooltip : '<?php echo esc_js(__('The Background Color of the Fields can be set using the Color Picker or by inputting the RGBA code.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Field FG Color -->
                        <template class="wlm3-form-group">
                            {
                                type : 'text',
                                label : '<?php echo esc_js(__('Text Color', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_fld_fgcolor',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_fld_fgcolor'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                class : 'wlm3colorpicker',
                                tooltip : '<?php echo esc_js(__('The Text Color of the Fields can be set using the Color Picker or by inputting the RGBA code.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Field Text Size -->
                        <template class="wlm3-form-group">
                            {
                                type : 'number',
                                min : 0,
                                label : '<?php echo esc_js(__('Text Size (px)', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_fld_fontsize',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_fld_fontsize'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                tooltip : '<?php echo esc_js(__('The Text Size within the Fields can be set in pixels.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Field Border Color -->
                        <template class="wlm3-form-group">
                            {
                                type : 'text',
                                label : '<?php echo esc_js(__('Border Color', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_fld_bordercolor',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_fld_bordercolor'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                class : 'wlm3colorpicker',
                                tooltip : '<?php echo esc_js(__('The Border Color of the Fields can be set using the Color Picker or by inputting the RGBA code.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Field Border Thickness -->
                        <template class="wlm3-form-group">
                            {
                                type : 'number',
                                min : 0,
                                label : '<?php echo esc_js(__('Border Thickness (px)', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_fld_bordersize',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_fld_bordersize'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                tooltip : '<?php echo esc_js(__('The Border Thickness of the Fields can be set in pixels.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Field Rounder Corners -->
                        <template class="wlm3-form-group">
                            {
                                type : 'number',
                                min : 0,
                                label : '<?php echo esc_js(__('Rounded Corners (px)', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_fld_roundness',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_fld_roundness'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                tooltip : '<?php echo esc_js(__('The Rounded Corners of the Fields can be set in pixels.', 'wishlist-member')); ?>',
                            }
                        </template>
                    </div>
                </div>
                <div class="tab-pane" id="loginform-button">
                    <div class="row">
                        <h4 class="col-12">
                            <?php esc_html_e('Button', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The Button can be customized based on the options below.', 'wishlist-member')); ?>
                            <hr>
                        </h4>

                        <!-- Button BG Color -->
                        <template class="wlm3-form-group">
                            {
                                type : 'text',
                                label : '<?php echo esc_js(__('Background Color', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_btn_bgcolor',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_btn_bgcolor'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                class : 'wlm3colorpicker',
                                tooltip : '<?php echo esc_js(__('The Background Color of the Button can be set using the Color Picker or by inputting the RGBA code.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Button FG Color -->
                        <template class="wlm3-form-group">
                            {
                                type : 'text',
                                label : '<?php echo esc_js(__('Text Color', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_btn_fgcolor',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_btn_fgcolor'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                class : 'wlm3colorpicker',
                                tooltip : '<?php echo esc_js(__('The Text Color of the Button can be set using the Color Picker or by inputting the RGBA code.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Button Text Size -->
                        <template class="wlm3-form-group">
                            {
                                type : 'number',
                                min : 0,
                                label : '<?php echo esc_js(__('Text Size (px)', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_btn_fontsize',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_btn_fontsize'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                tooltip : '<?php echo esc_js(__('The Text Size within the Button can be set in pixels.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Button Border Color -->
                        <template class="wlm3-form-group">
                            {
                                type : 'text',
                                label : '<?php echo esc_js(__('Border Color', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_btn_bordercolor',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_btn_bordercolor'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                class : 'wlm3colorpicker',
                                tooltip : '<?php echo esc_js(__('The Border Color of the Button can be set using the Color Picker or by inputting the RGBA code.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Button Border Thickness -->
                        <template class="wlm3-form-group">
                            {
                                type : 'number',
                                min : 0,
                                label : '<?php echo esc_js(__('Border Thickness (px)', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_btn_bordersize',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_btn_bordersize'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                tooltip : '<?php echo esc_js(__('The Border Thickness of the Button can be set in pixels.', 'wishlist-member')); ?>',
                            }
                        </template>
                        <!-- Button Rounder Corners -->
                        <template class="wlm3-form-group">
                            {
                                type : 'number',
                                min : 0,
                                label : '<?php echo esc_js(__('Rounded Corners (px)', 'wishlist-member')); ?>',
                                name : 'login_styling_custom_loginbox_btn_roundness',
                                value : <?php echo json_encode(wlm_trim($this->get_option('login_styling_custom_loginbox_btn_roundness'))); ?>,
                                placeholder : '<?php echo esc_js(__('Theme Default', 'wishlist-member')); ?>',
                                column : 'col-md-4',
                                tooltip : '<?php echo esc_js(__('The Rounded Corners of the Button can be set in pixels.', 'wishlist-member')); ?>',
                            }
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
