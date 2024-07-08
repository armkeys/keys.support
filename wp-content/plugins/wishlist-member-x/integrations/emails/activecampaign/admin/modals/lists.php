<?php

$tooltips = [
    'lists' => [
        'add'    => [
            'added'       => __('Adds the User to the selected List(s) when Added to the Level.', 'wishlist-member'),
            'removed'     => __('Adds the User to the selected List(s) when Removed from the Level.', 'wishlist-member'),
            'cancelled'   => __('Adds the User to the selected List(s) when Cancelled from the Level.', 'wishlist-member'),
            'uncancelled' => __('Adds the User to the selected List(s) when Uncancelled from the Level.', 'wishlist-member'),
        ],
        'remove' => [
            'added'       => __('Removes the User from the selected List(s) when Added to the Level.', 'wishlist-member'),
            'removed'     => __('Removes the User from the selected List(s) when Removed from the Level.', 'wishlist-member'),
            'cancelled'   => __('Removes the User from the selected List(s) when Cancelled from the Level.', 'wishlist-member'),
            'uncancelled' => __('Removes the User from the selected List(s) when Uncancelled from the Level.', 'wishlist-member'),
        ],
    ],
    'tags'  => [
        'add'    => [
            'added'       => __('Adds the selected Tag(s) to the User when Added to the Level.', 'wishlist-member'),
            'removed'     => __('Adds the selected Tag(s) to the User when Removed from the Level.', 'wishlist-member'),
            'cancelled'   => __('Adds the selected Tag(s) to the User when Cancelled from the Level.', 'wishlist-member'),
            'uncancelled' => __('Adds the selected Tag(s) to the User when Uncancelled from the Level.', 'wishlist-member'),
        ],
        'remove' => [
            'added'       => __('Removes the selected Tag(s) from the User when Added to the Level.', 'wishlist-member'),
            'removed'     => __('Removes the selected Tag(s) from the User when Removed from the Level.', 'wishlist-member'),
            'cancelled'   => __('Removes the selected Tag(s) from the User when Cancelled from the Level.', 'wishlist-member'),
            'uncancelled' => __('Removes the selected Tag(s) from the User when Uncancelled from the Level.', 'wishlist-member'),
        ],
    ],
];

foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div data-process="modal" id="activecampaign-lists-modal-<?php echo esc_attr($level->id); ?>-template" data-id="activecampaign-lists-modal-<?php echo esc_attr($level->id); ?>" data-label="activecampaign-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>" data-show-default-footer="1" data-classes="modal-lg" style="display:none">
    <div class="body">
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#activecampaign-ar-when-added-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Added', 'wishlist-member'); ?></a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#activecampaign-ar-when-removed-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Removed', 'wishlist-member'); ?></a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#activecampaign-ar-when-cancelled-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Cancelled', 'wishlist-member'); ?></a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#activecampaign-ar-when-uncancelled-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Uncancelled', 'wishlist-member'); ?></a></li>
        </ul>
        <div class="tab-content">
            <?php foreach (['added', 'removed', 'cancelled', 'uncancelled'] as $_tab) : ?>
            <div class="row tab-pane" id="activecampaign-ar-when-<?php echo esc_attr($_tab); ?>-<?php echo esc_attr($level->id); ?>">
                <div class="horizontal-tabs">
                    <div class="row no-gutters">
                        <div class="col-12 col-md-auto">
                            <!-- Nav tabs -->
                            <div class="horizontal-tabs-sidebar" style="min-width: 120px;">
                                <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                                    <li role="presentation" class="nav-item">
                                        <a href="#activecampaign-ar-when-<?php echo esc_attr($_tab); ?>-<?php echo esc_attr($level->id); ?>-lists" class="nav-link" data-toggle="tab"><?php esc_html_e('Lists', 'wishlist-member'); ?></a>
                                    </li>
                                    <li role="presentation" class="nav-item">
                                        <a href="#activecampaign-ar-when-<?php echo esc_attr($_tab); ?>-<?php echo esc_attr($level->id); ?>-tags" class="nav-link" data-toggle="tab"><?php esc_html_e('Tags', 'wishlist-member'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col">
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane" id="activecampaign-ar-when-<?php echo esc_attr($_tab); ?>-<?php echo esc_attr($level->id); ?>-lists">
                                    <div class="form-group">
                                        <label><?php esc_html_e('Add to Lists', 'wishlist-member'); ?></label>
                                        <select class="activecampaign-lists-select wlm-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="level_actions[<?php echo esc_attr($level->id); ?>][<?php echo esc_attr($_tab); ?>][add][]"></select>
                                    </div>
                                    <div class="form-group">
                                        <label><?php esc_html_e('Remove from Lists', 'wishlist-member'); ?></label>
                                        <select class="activecampaign-lists-select wlm-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="level_actions[<?php echo esc_attr($level->id); ?>][<?php echo esc_attr($_tab); ?>][remove][]"></select>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="activecampaign-ar-when-<?php echo esc_attr($_tab); ?>-<?php echo esc_attr($level->id); ?>-tags">
                                    <template class="wlm3-form-group">
                                        {
                                        label : '<?php echo esc_js(__('Add Tags', 'wishlist-member')); ?>',
                                        type : 'select',
                                        class : 'activecampaign-tags-select',
                                        multiple : 'multiple',
                                        style : 'width: 100%',
                                        name : 'level_tag_actions[<?php echo esc_attr($level->id); ?>][<?php echo esc_attr($_tab); ?>][add][]',
                                        column : 'col-12',
                                        'data-placeholder' : '<?php echo esc_js(__('Select Tag(s)', 'wishlist-member')); ?>',
                                        tooltip : '<?php echo esc_js($tooltips['tags']['add'][ $_tab ]); ?>',
                                        }
                                    </template>
                                    <template class="wlm3-form-group">
                                        {
                                        label : '<?php echo esc_js(__('Remove Tags', 'wishlist-member')); ?>',
                                        type : 'select',
                                        class : 'activecampaign-tags-select',
                                        multiple : 'multiple',
                                        style : 'width: 100%',
                                        name : 'level_tag_actions[<?php echo esc_attr($level->id); ?>][<?php echo esc_attr($_tab); ?>][remove][]',
                                        column : 'col-12',
                                        'data-placeholder' : '<?php echo esc_js(__('Select Tag(s)', 'wishlist-member')); ?>',
                                        tooltip : '<?php echo esc_js($tooltips['tags']['remove'][ $_tab ]); ?>',
                                        }
                                    </template>
                                </div>
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
