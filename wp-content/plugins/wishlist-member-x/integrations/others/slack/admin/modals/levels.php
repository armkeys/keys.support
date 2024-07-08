<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="slack-lists-modal-<?php echo esc_attr($level->id); ?>-template" 
    data-id="slack-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="slack-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12"> 
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#slack-when-added-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Added', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#slack-when-removed-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Removed', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#slack-when-cancelled-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Cancelled', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php foreach (['added', 'removed', 'cancelled'] as $_tab) : ?>
            <div class="tab-pane <?php echo esc_attr('added' === $_tab ? 'active in' : ''); ?>" id="slack-when-<?php echo esc_attr($_tab); ?>-<?php echo esc_attr($level->id); ?>">
                <div class="row mb-3">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>',
                            type : 'toggle-adjacent',
                            name : 'slack_settings[<?php echo esc_attr($_tab); ?>][active][<?php echo esc_attr($level->id); ?>]',
                            value : '1',
                            uncheck_value : '0',
                            column : 'col-12',
                        }
                    </template>
                    <div class="col-12" style="
                    <?php
                    if (! $data[ $_tab ]['active'][ $level->id ]) {
                        echo 'display: none;';
                    }
                    ?>
                    ">
                        <div class="row">
                            <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js(__('Message', 'wishlist-member')); ?>',
                                    type : 'textarea',
                                    name : 'slack_settings[<?php echo esc_attr($_tab); ?>][text][<?php echo esc_attr($level->id); ?>]',
                                    help_block : '<?php /* translators: 1: supported shortcodes */ printf(esc_html__('Supported shortcodes: %s', 'wishlist-member'), '{name} {fname} {lname} {email} {level} {sitename} {siteurl}'); ?>',
                                    column : 'col-12',
                                    placeholder : '{name} <?php echo esc_attr($_tab); ?> <?php echo 'added' === $_tab ? 'to' : 'from'; ?> {level} at {sitename}',
                                }
                            </template>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label><?php esc_html_e('Custom Channel', 'wishlist-member'); ?></label>
                            </div>
                        </div>
                        <div class="row">
                            <template class="wlm3-form-group">
                                {
                                    type : 'toggle-adjacent-disable',
                                    name : 'slack_settings[<?php echo esc_attr($_tab); ?>][custom_channel_enabled][<?php echo esc_attr($level->id); ?>]',
                                    value : '1',
                                    uncheck_value : '0',
                                    column : 'col-auto custom-webhook-toggle',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    type : 'text',
                                    name : 'slack_settings[<?php echo esc_attr($_tab); ?>][custom_channel][<?php echo esc_attr($level->id); ?>]',
                                    column : 'col px-0',
                                    placeholder : 'ex. #my-channel',
                                }
                            </template>
                            <div class="col-auto">
                                <button class="btn -default -condensed slack-test-webhook" data-trigger="<?php echo esc_attr($_tab); ?>" data-level="<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Test', 'wishlist-member'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
