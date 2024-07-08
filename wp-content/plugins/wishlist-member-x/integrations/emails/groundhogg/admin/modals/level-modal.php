<?php

/**
 * Groundhogg admin levels modal
 *
 * @package WishListMember/Autoresponders
 */

$level_actions = [
    'add'    => __('Added', 'wishlist-member'),
    'remove' => __('Removed', 'wishlist-member'),
    'cancel' => __('Cancelled', 'wishlist-member'),
    'rereg'  => __('Uncancelled', 'wishlist-member'),
];

$tooltips = [
    'tags' => [
        'add'    => [
            'add'    => __('Adds the selected Tag(s) to the User when Added to the Level.', 'wishlist-member'),
            'remove' => __('Adds the selected Tag(s) to the User when Removed from the Level.', 'wishlist-member'),
            'cancel' => __('Adds the selected Tag(s) to the User when Cancelled from the Level.', 'wishlist-member'),
            'rereg'  => __('Adds the selected Tag(s) to the User when Uncancelled from the Level.', 'wishlist-member'),
        ],
        'remove' => [
            'add'    => __('Removes the selected Tag(s) from the User when Added to the Level.', 'wishlist-member'),
            'remove' => __('Removes the selected Tag(s) from the User when Removed from the Level.', 'wishlist-member'),
            'cancel' => __('Removes the selected Tag(s) from the User when Cancelled from the Level.', 'wishlist-member'),
            'rereg'  => __('Removes the selected Tag(s) from the User when Uncancelled from the Level.', 'wishlist-member'),
        ],
    ],
];
?>
<?php foreach ($wpm_levels as $level_id => $level) : ?>
<div data-process="modal" id="groundhogg-levels-<?php echo esc_attr($level_id); ?>-template" data-id="groundhogg-levels-<?php echo esc_attr($level_id); ?>" data-label="groundhogg-levels-<?php echo esc_attr($level_id); ?>"
    data-title="Editing Level Actions for <strong><?php echo esc_attr($level['name']); ?></strong>" data-show-default-footer="1" data-classes="modal-lg modal-groundhogg-actions" style="display:none">
    <div class="body">
        <ul class="nav nav-tabs">
            <?php foreach ($level_actions as $key => $value) : ?>
            <li class="<?php echo 'add' === $key ? 'active' : ''; ?> nav-item"><a class="nav-link" data-toggle="tab" href="#groundhogg-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>"><?php echo esc_html($value); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content">
            <?php foreach ($level_actions as $key => $value) : ?>
            <div class="tab-pane <?php echo 'add' === $key ? 'active in' : ''; ?>" id="groundhogg-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>">
                <div class="horizontal-tabs">
                    <div class="row no-gutters">
                        <div class="col-12 col-md-auto">
                            <!-- Nav tabs -->
                            <div class="horizontal-tabs-sidebar" style="min-width: 120px;">
                                <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                                    <li role="presentation" class="nav-item">
                                        <a href="#level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-groundhogg-tag" class="nav-link pp-nav-link active" aria-controls="tag" role="tab" data-type="tag" data-title="Tag Actions" data-toggle="tab"><?php esc_html_e('Tag', 'wishlist-member'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col">
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-groundhogg-tag">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label><?php esc_html_e('Add Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip($tooltips['tags']['add'][ $key ]); ?></label>
                                            <select class="groundhogg-tags-select" multiple="multiple" data-placeholder="Select Tags..." style="width:100%" name="groundhogg_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_tag][]"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip($tooltips['tags']['remove'][ $key ]); ?></label>
                                            <select class="groundhogg-tags-select" multiple="multiple" data-placeholder="Select Tags..." style="width:100%" name="groundhogg_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_tag][]"></select>
                                        </div>
                                    </div>
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
<?php endforeach; ?>
