<?php
    $system_info = new \WishListMember\System_Info();
?>

<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('System Information', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
                <ul class="nav nav-tabs mt-0 -no-background" role="tablist">
                    <?php $active = 'active'; ?>
                    <?php foreach ($system_info->fields as $key => $fld) : ?>
                        <li role="presentation" class="nav-item"><a class="nav-link <?php echo esc_attr($active); ?>" href="#<?php echo esc_attr($key); ?>" aria-controls="member-info" role="tab" data-toggle="tab"><?php echo esc_html($fld['title']); ?></a></li>
                        <?php $active = ''; ?>
                    <?php endforeach; ?>
                </ul>
<div class="content-wrapper">

    <div class="tab-content">
        <?php $active = 'active'; ?>
        <?php foreach ($system_info->fields as $key => $fld) : ?>
            <div role="tabpanel" class="tab-pane <?php echo esc_attr($active); ?>" id="<?php echo esc_attr($key); ?>">
                
                    
                <div class="table-wrapper -no-shadow">
                    <table class="table table-striped table-condensed table-fixed text-center">
                            <tbody>
                            <?php foreach ($fld['fields'] as $fld_key => $fld_label) : ?>
                            <tr class="d-flex">
                                <td class="col-4 text-left">
                                    <?php if (isset($system_info->info[ $key ][ $fld_key ]['fld_url'])) : ?>
                                        <?php echo '<a target="_blank" href="' . esc_attr($system_info->info[$key][$fld_key]['fld_url']) . '">' . esc_html($fld_label) . '</a>'; ?>
                                    <?php else : ?>
                                        <?php echo esc_html($fld_label); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="col-8 text-left">
                                    <?php if (isset($system_info->info[ $key ][ $fld_key ]['val_url'])) : ?>
                                        <?php echo '<a target="_blank" href="' . esc_attr($system_info->info[$key][$fld_key]['val_url']) . '">' . esc_html($system_info->info[$key][$fld_key]['value']) . '</a>'; ?>
                                    <?php else : ?>
                                        <?php echo esc_html($system_info->info[ $key ][ $fld_key ]['value']); ?>
                                    <?php endif; ?>
                                    <?php if (isset($system_info->info[ $key ][ $fld_key ]['notes'])) : ?>
                                        <br /><span class="text-muted" id="helpBlock"><?php echo esc_html($system_info->info[ $key ][ $fld_key ]['notes']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                    
                
            </div>
            <?php $active = ''; ?>
        <?php endforeach; ?>
    </div>  
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <h3 class="panel-title pull-left"><?php esc_html_e('Copy & Paste Info', 'wishlist-member'); ?></h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <textarea class="form-control copyable" readonly="readonly" data-lpignore="true"><?php echo esc_textarea($system_info->get_raw()); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer -content-footer">
        <div class="row">
             <div class="col-md-12 text-left">
                <?php $form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'advanced_settings/system_info' ); ?>
                <form method="post" action="<?php echo esc_url($form_action); ?>" target="_parent" id="sysinfo_form">
                    <input type="hidden" name="action" value="wlm3_download_sysinfo">
                    <a href="#" class="btn -primary download-btn" >
                        <i class="wlm-icons">file_download</i>
                        <span><?php esc_html_e('Download System Info', 'wishlist-member'); ?></span>
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
