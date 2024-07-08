<?php
    $version                = wlm_is_unpackaged() ? str_replace(implode('', ['{', 'WLP_VERSION}']), '9999999999', $this->version) : $this->version;
    $official_versions      = $this->get_official_versions();
    $oldest_version_allowed = '2.80';
?>
<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Version Rollback', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <h4><?php esc_html_e('WishList Member Version Rollback', 'wishlist-member'); ?></h4>
    <p><?php esc_html_e('A Version Rollback will restore the selected previous version of WishList Member. This does not rollback any member or site settings.', 'wishlist-member'); ?></p>
    <div class="form-text text-danger help-block">
        <p class="mb-0"><?php esc_html_e('Note: Any customization to the code of WishList Member will be lost.', 'wishlist-member'); ?></p>
    </div>
    <?php if (! $this->plugin_is_latest()) : ?>
    <p><em><?php /* translators: 1: latest version */ printf(esc_html__('The latest version is %s', 'wishlist-member'), esc_html($this->plugin_latest_version())); ?></em></p>
    <?php endif; ?>
    <p><em><?php /* translators: 1: current version */ printf(esc_html__('Your current version is %s', 'wishlist-member'), esc_html($this->version)); ?></em></p>
    <div class="table-wrapper table-responsive">
        <table id="rollback-table" class="table table-condensed table-striped">
            <colgroup>
                <col>
                <col width="120">
            </colgroup>
            <tbody>
            <?php
                    $versions = glob(WLM_ROLLBACK_PATH . '*.*.*');
                    usort($versions, 'version_compare');
                    $versions = array_reverse($versions);

            foreach ($versions as $ver) :
                if (! in_array(basename($ver), $official_versions)) {
                    continue;
                }
                if (! is_file($ver)) {
                    continue;
                }
                $ver = basename(( $ver ));
                if (version_compare($ver, $oldest_version_allowed) < 0) {
                    continue;
                }
                if (strpos($ver, implode('', ['{', 'WLP_VERSION}']))) {
                    continue;
                }
                if (version_compare($ver, $version) > -1) {
                    continue;
                }
                ?>
                <tr class="button-hover">
                    <td><?php /* translators: 1: version */ printf(esc_html__('Version %s', 'wishlist-member'), esc_html($ver)); ?></td>
                    <td>
                        <div class="btn-group-action text-right">
                            <a title="Rollback" onclick="return false;" href="<?php echo esc_attr(wp_nonce_url('update.php?wlm3_rollback=' . $ver . '&action=upgrade-plugin&plugin=' . $this->plugin_file, 'upgrade-plugin_' . $this->plugin_file)); ?>" data-version="<?php echo esc_attr($ver); ?>" class="btn rollback-btn"><span class="wlm-icons md-24 -icon-only">update</span></a>
                            <a title="Download" href="<?php echo esc_url($this->plugin_download_url($ver)); ?>" target="_blank" class="btn download-btn"><span class="wlm-icons md-24 -icon-only">file_download</span></a>
                            <a title="Delete Rollback" data-version="<?php echo esc_attr($ver); ?>" href="#" target="_blank" class="btn rollback-delete-btn"><span class="wlm-icons md-24 -icon-only">delete</span></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?></tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center">
                        <p><?php esc_html_e('No versions to rollback.', 'wishlist-member'); ?></p>
                    </td>
                </tr>
            </tfoot>
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('WishList Member Version', 'wishlist-member'); ?> 
                        <?php $this->tooltip(__('Only official releases are listed in this section', 'wishlist-member')); ?>
                    </th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div
    id="rollback-in-progress-template" 
    data-id="rollback-in-progress"
    data-label="rollback-in-progress"
    data-title=""
    style="display:none">
    <div class="body">
        <div class="row" id="wlm-simple-loader-container">
            <div class="col-12 text-center">
                <div class="d-inline-block align-middle" style="margin: 75px auto">
                    <img class="l-logo" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/wlm-opaque.png" alt="">
                    <img class="d-block mt-4" style="opacity: .5; margin: auto" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/wlm-loader03.gif" alt="">
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        new wlm3_modal( '#rollback-in-progress-template' );
        $('.rollback-delete-btn').do_confirm({ yes_button: '<?php echo esc_js(__('Delete', 'wishlist-member')); ?>', confirm_message : '<?php echo esc_js(__('Delete this Rollback Version?', 'wishlist-member')); ?>' })
        .on('yes.do_confirm', function() {
            var row = $(this).closest('tr');
            $.post(
                WLM3VARS.ajaxurl,
                {
                    action : 'admin_actions',
                    WishListMemberAction : 'delete_rollback',
                    rollback_version : $(this).data('version')
                },
                function(result) {
                    row.remove();
                    $('.wlm-message-holder').show_message({
                        message : '<?php echo esc_js(__('Rollback version deleted.', 'wishlist-member')); ?>'
                    });
                }
            );
        });

        $('.rollback-btn').do_confirm({ confirm_message : '<?php echo esc_js(__('Rollback to this version?', 'wishlist-member')); ?>' })
        .on('yes.do_confirm', function() {
            document.location = this.href;
        });
    });
</script>
<style type="text/css">
#rollback-table tbody:not(:empty) ~ tfoot {
    display: none;
}
#rollback-table tbody:empty ~ thead {
    display: none;
}
#rollback-in-progress .modal-header button {
    display: none;
}
</style>
