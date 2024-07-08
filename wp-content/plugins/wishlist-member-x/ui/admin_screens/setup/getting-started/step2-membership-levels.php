<?php

/**
 * Getting Started Wizard - Membership Levels
 *
 * @package WishListMember/Wizard
 */

$step_title        = __('Levels', 'wishlist-member');
$step_title_header = __('Membership Levels', 'wishlist-member');
$video_url         = 'https://wishlistmember.com/docs/videos/wlm/wizard/levels';
$existing_levels   = array_filter(array_column(wishlistmember_instance()->get_option('wpm_levels'), 'name'));
?>
<div class="card wizard wizard-form d-none mx-auto <?php echo wishlistmember_instance()->get_option('wizard/' . $stepname) ? 'is-run' : ''; ?>" data-step-name="<?php echo esc_attr($stepname); ?>" id="<?php echo esc_attr($stepname); ?>">
    <div class="card-header border-0 bg-light px-0">
        <h2 class="wizard-title-heading my-0"><?php echo esc_html(wlm_or($step_title_header, $step_title)); ?></h2>
    </div>
    <div class="card-body border border-bottom-0">
        <div class="row">
            <?php
            require __DIR__ . '/parts/video-column.php';
            ?>
            <div class="col-12">
                <div class="row">
                    <div class="col-12">
                        <h3 class="mb-3"><?php esc_html_e('Create Membership Level', 'wishlist-member'); ?></h3>
                        <p><?php esc_html_e('All you need to do is enter a name for your membership level and you\'re all set. Examples of membership level names are Bronze, Silver, Gold, etc. or Level 1, Level 2, Level 3, etc. The "Add Another Level" button can be used if you\'d like to create more than one membership level now. You can always create more levels or edit the level you\'ve created at any point in the future.', 'wishlist-member'); ?></p>
                    </div>
                    <div class="col-12">
                        <label>
                            <?php echo esc_html_e('Membership Level', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip('Membership levels are used to group members together and allow them to access protected content.', 'lg'); ?>
                        </label>
                        <div class="row membership-levels">
                            <template class="wlm3-form-group">
                                {
                                    column: 'col',
                                    class: 'membership-level',
                                    placeholder: '<?php echo esc_js(__('Membership Level Name', 'wishlist-member')); ?>',
                                    name: 'levels/name[]',
                                    tooltip_size: 'lg',
                                    group_class: 'mb-3',
                                }
                            </template>
                            <div class="col-auto pl-0">
                                <a href="#" class="wlm-icons pull-right mt-1 mr-2 remove-level-btn" title="<?php esc_attr_e('Remove', 'wishlist-member'); ?>">close</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn -success -condensed" id="add-level"><i class="wlm-icons">add</i> <?php esc_html_e('Add Level', 'wishlist-member'); ?></button>
                    </div>
                </div>
                <hr>
                <div class="row existing-levels <?php echo $existing_levels ? '' : 'd-none'; ?>">
                    <div class="col">
                        <?php
                            wlm_wizard_table(__('Existing Membership Levels', 'wishlist-member'), __('Level Name', 'wishlist-member'), $existing_levels);
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-12 text-center">
                <span class="wizard-step-result form-text text-danger d-none"></span>
            </div>
        </div>
    </div>
    <div class="card-footer bg-light border border-top-0 py-3 text-center">
        <button class="btn -primary pull-right -wizard-next"><span class="d-none d-sm-inline"><?php esc_html_e('Save & Continue', 'wishlist-member'); ?></span><i class="wlm-icons">arrow_forward</i></button>
        <button class="btn -default pull-right -wizard-prev mr-3"><i class="wlm-icons">arrow_back</i><span class="d-none d-sm-inline"><?php esc_html_e('Back', 'wishlist-member'); ?></span></button>
        <a href="#" class="btn -bare -outline pull-left bg-light border -exit-wizard"><?php esc_html_e('Exit Wizard', 'wishlist-member'); ?></a>
    </div>
</div>
