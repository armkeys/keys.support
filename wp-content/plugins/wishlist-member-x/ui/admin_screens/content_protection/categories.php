<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Categories', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>

<?php

$content_type    = 'categories';
$content_comment = false;
$args            = ['hide_empty' => 0];
if ('name' === wlm_trim(wlm_get_data()['orderby'])) {
    $args['orderby'] = 'name';
    $args['order']   = wlm_trim(wlm_get_data()['order']);
}

$sort_name      = 'desc';
$sort_name_icon = '';
if ('name' === $args['orderby']) {
    $sort_name      = 'desc' === strtolower($args['order']) ? 'asc' : 'desc';
    $sort_name_icon = 'desc' === $sort_name ? 'arrow_drop_up' : 'arrow_drop_down';
}

$items      = [];
$taxonomies = get_taxonomies(
    [
        '_builtin'     => false,
        'hierarchical' => true,
    ],
    'names'
);
array_unshift($taxonomies, 'category');
foreach ($taxonomies as $_taxonomy) {
    $x = [];
    foreach (get_terms($_taxonomy, $args) as $item) {
        $item               = (array) $item;
        $item['ID']         = &$item['term_id'];
        $item['post_title'] = &$item['name'];
        $item['taxonomy']   = ucfirst($item['taxonomy']);
        $x[ $item['ID'] ]   = $item;
    }
    if (empty($args['orderby']) && empty($args['order'])) {
        $y = [];
        foreach ($x as $item) {
            $item['deep'] = 0;
            $idx          = $item['name'] . "\t" . $item['term_id'];
            $parents      = [];
            $z            = $item;

            while ($z['parent']) {
                ++$item['deep'];
                $z         = $x[ $z['parent'] ];
                $idx       = $z['name'] . "\t" . $z['term_id'] . "\t" . $idx;
                $parents[] = $z['name'];
            }

            $y[ $idx ]                = $item;
            $y[ $idx ]['parent_cats'] = $parents;
        }
        ksort($y);
        $x = $y;
    }
    $items += $x;
}

// Get Membership Levels.
$wpm_levels = $this->get_option('wpm_levels');
?>
<div class="header-tools -no-border">
    <div class="row">
        <div class="col-sm-12 col-lg-4">
            <div class="form-group">
                <label class="sr-only" for=""><?php esc_html_e('Member Role', 'wishlist-member'); ?></label>
                <select class="form-control wlm-select blk-actions" name="" id="" style="width: 100%">
                    <option value="">- Select an Action -</option>
                    <option value="protection">Edit Protection Status</option>
                    <option value="add_level">Add Levels</option>
                    <option value="remove_level">Remove Levels</option>
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="table-wrapper -special table-responsive -cp-table">
            <table class="table table-condensed">
                <thead>
                    <tr class="button-hover">
                        <th style="width: 40px" class="text-center">
                            <div class="form-check -for-tables">
                                <input value="" type="checkbox" class="chk-all form-check-input">
                                <label for="" class="form-check-label"></label>
                            </div>
                        </th>
                        <th>
                            <a href="
                            <?php
                            echo esc_url(
                                add_query_arg(
                                    [
                                        'orderby' => 'name',
                                        'order'   => $sort_name,
                                    ],
                                    admin_url('admin.php?wl=content_protection/categories&page=' . $this->menu_id)
                                )
                            );
                            ?>
                            "><?php esc_html_e('Name', 'wishlist-member'); ?><span class="wlm-icons"><?php echo esc_html($sort_name_icon); ?></span></a>
                        </th>
                        <th>Status</th>
                        <th class="text-center" style="width: 10%">Type</th>
                    </tr>
                </thead>
                <?php foreach ($items as $item_id => $item) : ?>
                    <?php
                        include $this->plugin_dir3 . '/ui/admin_screens/content_protection/categories/content-item.php';
                    ?>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="protection-modal" data-id="protection-modal" data-label="protection_modal_label" data-title="Edit Protection Status" data-classes="modal-sm" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for="">Protection Status</label>
            <select class="form-control wlm-levels wlm-protection" name="protection" style="width: 100%" required>
                <option><?php esc_html_e('Unprotected', 'wishlist-member'); ?></option>
                <option><?php esc_html_e('Protected', 'wishlist-member'); ?></option>
                <option><?php esc_html_e('Inherited', 'wishlist-member'); ?></option>
            </select>
        </div>
        <?php if ($content_comment) : ?>
            <input type="hidden" name="content_comment" value="1" />
        <?php endif; ?>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal">Close</button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">lock</i>  <span><?php esc_html_e('Update Protection', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="add-level-modal" data-id="add-level-modal" data-label="add_level_modal_label" data-title="Add Levels" data-classes="modal-sm" style="display:none">
    <div class="body">
        <div class="form-group membership-level-select">
            <label for=""><?php esc_html_e('Membership Levels', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels" multiple="multiple" name="wlm_levels[]" id="" style="width: 100%" data-placeholder="Select Membership Levels" required>
                <?php foreach ($wpm_levels as $key => $value) : ?>
                    <?php $disabled = isset($value['allcategories']) && ! empty($value['allcategories']) ? 'disabled' : ''; ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($disabled); ?>><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($content_comment) : ?>
            <input type="hidden" name="content_comment" value="1" />
        <?php endif; ?>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="level_action" value="add" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal">Close</button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">add_circle_outline</i> <span><?php esc_html_e('Add Level', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="remove-level-modal" data-id="remove-level-modal" data-label="remove_level_modal_label" data-title="Remove Levels" data-classes="modal-sm" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for=""><?php esc_html_e('Membership Levels', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels" multiple="multiple" name="wlm_levels[]" id="" style="width: 100%" data-placeholder="Select Membership Levels" required>
                <?php foreach ($wpm_levels as $key => $value) : ?>
                    <?php $disabled = isset($value['allcategories']) && ! empty($value['allcategories']) ? 'disabled' : ''; ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($disabled); ?>><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($content_comment) : ?>
            <input type="hidden" name="content_comment" value="1" />
        <?php endif; ?>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="level_action" value="remove" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal">Close</button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">remove_circle_outline</i> <span><?php esc_html_e('Remove Level', 'wishlist-member'); ?></span></button>
    </div>
</div>
