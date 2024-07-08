<?php
$wpm_levels = $this->get_option('wpm_levels');
?>
<form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="post" id="export-form" target="_parent">
    <div class="page-header">
        <div class="row">
            <div class="col-md-9 col-sm-9 col-xs-8">
                <h2 class="page-title">
                    <?php esc_html_e('Export Members', 'wishlist-member'); ?>
                </h2>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-4">
                <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
            </div>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12">
                <p><?php esc_html_e('Export Members as a CSV file by selecting the appropriate Membership Level(s) and settings below.', 'wishlist-member'); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Select the desired Membership Level to export to a .csv file.</label>
                            <select name="wpm_to[]" multiple="multiple" class="form-control wlm-select wlm-select-selectall select_mlevels" data-placeholder="Select Membership Levels">
                                <?php foreach ($wpm_levels as $key => $value) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                                <?php endforeach; ?>
                            <option value="nonmember"><?php esc_html_e('Non-Members', 'wishlist-member'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="">Additional Options</label>
                    <!-- start: v4 -->
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Export Full Data', 'wishlist-member')); ?>',
                            name  : 'full_data_export',
                            id : 'full_data_export',
                            value : '1',
                            type  : 'checkbox',
                            tooltip: '<?php echo esc_js(__('All data fields associated with the Member user profiles will be exported. This includes any custom fields that may be associated with the members.', 'wishlist-member')); ?>',
                            tooltip_size: 'md',
                            column: 'mb-2',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Include Password (Encrypted)', 'wishlist-member')); ?>',
                            name  : 'include_password',
                            id : 'include_password',
                            value : '1',
                            type  : 'checkbox',
                            tooltip: '<?php echo esc_js(__('Passwords will be encrypted and included in the export. They will be shown as random characters in the exported file.', 'wishlist-member')); ?>',
                            tooltip_size: 'md',
                            column: 'mb-2',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Include Inactive Members', 'wishlist-member')); ?>',
                            name  : 'include_inactive',
                            id : 'include_inactive',
                            value : '1',
                            type  : 'checkbox',
                            tooltip: '<?php echo esc_js(__('Any Cancelled or Expired Members are considered as Inactive Members.', 'wishlist-member')); ?>',
                            tooltip_size: 'md'
                        }
                    </template>
                    <!-- end: v4 -->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <input type="hidden" class="per_page" name="per_page" value="100"/>
            <input type="hidden" class="current_page" name="current_page" value="0"/>
            <input type="hidden" name="WishListMemberAction" value="ExportMembersChunked" />
            <?php print_wlm_nonce_field(); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="export-progress">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="0" ></div>
                </div>
                <div class="text-center">Exported <span class="export-low">0</span> to <span class="export-high">0</span> of <span class="export-total">0</span></div>
                <div class="text-danger text-center"><?php esc_html_e('Please do not close your browser while the export is ongoing', 'wishlist-member'); ?></div>
            </div>
            <br />
        </div>
    </div>
    <div class="panel-footer -content-footer">
        <div class="row">
            <div class="col-lg-12 text-right">
                <a href="#" class="btn -primary start-export">
                    <i class="wlm-icons">file_download</i>
                    <span class="text"><?php esc_html_e('Export Members', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
    </div>
</div>
</form>

<div id="error-export-modal" data-id="error-export-modal" data-label="error-export_modal_label" data-title="Export Members" data-classes="modal-md" style="display:none">
    <div class="body">
        <h5 class="message"><?php esc_html_e('** Delete Message **', 'wishlist-member'); ?></h5>
    </div>
</div>
