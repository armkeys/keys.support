<?php
    $levels        = $this->get_option('wpm_levels');
    $level_options = [];
foreach ($levels as $level_id => $level) {
    if (is_numeric($level_id)) {
        $level_options[] = [
            'value' => $level_id,
            'text'  => $level['name'],
        ];
    }
}
?>
<div class="content-wrapper -no-header">
    <h4><?php esc_html_e('Select the WishList Member Settings to Export:', 'wishlist-member'); ?></h4>
        <?php $form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'administration/site_migration/export_settings' ); ?>
        <form method="post" action="<?php echo esc_url($form_action); ?>" target="_parent" id="export_form">
            <div class="row">
                <template class="wlm3-form-group">
                    {
                        label: '<?php echo esc_js(__('Membership Levels', 'wishlist-member')); ?>',
                        type: 'checkbox',
                        value: 1,
                        tooltip: '<?php echo esc_js(__('Includes all settings specific to an individual membership level. Only the level(s) selected will be included in the export file.', 'wishlist-member')); ?>',
                        column: 'col-md-3 pt-2',
                        id: 'export-membership-levels',
                        name: 'export_levels',
                        class: 'chk_settings',
                    }
                </template>
            </div>
            <div id="membership-levels" class="row ml-4" style="display: none;">
                <div class="col-md-6 mb-3 membership-level-select">
                    <template class="wlm3-form-group">
                        {
                            label: '<?php echo esc_js(__('Select Membership Levels to Export', 'wishlist-member')); ?>',
                            'data-placeholder': '<?php echo esc_js(__('Select Membership Levels', 'wishlist-member')); ?>',
                            type: 'select',
                            name: 'levels[]',
                            style: 'width:100% !important',
                            options: <?php echo json_encode($level_options); ?>,
                            multiple: true,
                            class: 'wlm-select-selectall',
                            group_class: 'no-margin'
                        }
                    </template>
                </div>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    {
                        label: '<?php echo esc_js(__('Global Settings', 'wishlist-member')); ?>',
                        type: 'checkbox',
                        value: 1,
                        name: 'global_settings',
                        column: 'col-md-3 pt-2',
                        tooltip: '<?php echo esc_js(__('Includes all settings saved in a WishList Member site that are not specific to an individual membership level.', 'wishlist-member')); ?>',
                        class: 'chk_settings',
                    }
                </template>
                <div class="col-md-12">
                    <!-- start: v4 -->
                    <small class="form-text text-muted">A file containing the selected settings will be downloaded to your computer.</small>
                    <small class="form-text text-muted mb-3">* Please note the export file created will only be compatible with WishList Member 3.0 or higher.</small>
                    <!-- end: v4 -->
                </div>
            </div>
            <input type="hidden" name="action" value="wlm3_export_settings">
        </form>
    <div class="panel-footer -content-footer">
        <div class="row">
             <div class="col-md-12 text-right">
                <a href="#" class="btn -default export-settings-btn disabled" disabled="disabled">
                    <i class="wlm-icons">file_download</i>
                    <span><?php esc_html_e('Export Settings', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
    </div>
</div>
