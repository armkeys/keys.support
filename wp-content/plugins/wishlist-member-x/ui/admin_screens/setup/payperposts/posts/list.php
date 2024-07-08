<div id="ppps-list">
    <!-- Pagination Starts -->
    <div class="header-tools -no-border">
        <div class="row">
            <div class="col-md-7">
                <form method="GET" action="<?php echo esc_url($admin_page); ?>">
                    <input type="hidden" name="page" value="<?php echo esc_attr(wlm_get_data()['page']); ?>">
                    <input type="hidden" name="wl" value="<?php echo esc_attr(wlm_get_data()['wl']); ?>">
                    <div class="input-group -form-tight">
                        <input type="text" name="search" class="form-control" placeholder="<?php esc_attr_e('Search Text', 'wishlist-member'); ?>" value="<?php echo esc_attr(wlm_get_data()['search' ]); ?>">
                        <select class="form-control wlm-select ppp-filter" name="filter" style="width: 200px">
                            <option value=""><?php esc_html_e('Show All', 'wishlist-member'); ?></option>
                            <?php
                            foreach (array_keys($ppps_grouped) as $ptype) {
                                $selected = $filter == $ptype ? 'selected' : '';
                                // Translators: 1: post type label.
                                printf('<option %s value="%s">%s</option>', esc_attr($selected), esc_attr($post_types[ $ptype ]->name), sprintf(esc_html__('Show only %s', 'wishlist-member'), esc_html($post_types[ $ptype ]->label)));
                            }
                            ?>
                        </select>
                        <div class="input-group-append">
                            <button class="btn -default -icon -stroke">
                                <i class="wlm-icons">search</i>
                            </button>                       
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-5">
                <?php $pagination->print_html(); ?>
            </div>
        </div>
    </div>
    <!-- Pagination Ends -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-wrapper table-responsive">
                <table id="ppps-list-table" class="table table-striped table-condensed">
                    <colgroup>
                        <col>
                        <col width="100">
                        <col width="100">
                        <col width="20">
                    </colgroup>
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Name', 'wishlist-member'); ?></th>
                            <th><?php esc_html_e('Type', 'wishlist-member'); ?></th>
                            <th><?php esc_html_e('Status', 'wishlist-member'); ?></th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($ppps as $ppp) :
                        $post_type_name = $post_types[ $ppp->post_type ]->labels->singular_name;
                        ?>
                        <tr class="button-hover">
                            <td><a href="<?php echo esc_url($admin_page . $ppp->ID); ?>" class="-ajax-btn"><?php echo esc_html($ppp->post_title); ?></a></td>
                            <td><?php echo esc_html($post_type_name); ?></td>
                            <td><?php echo esc_html($post_statuses[ $ppp->post_status ]); ?></td>
                            <td class="text-center" style="white-space: nowrap">
                                <div class="btn-group-action">
                                    <a href="<?php echo esc_url($admin_page . $ppp->ID); ?>" title="<?php esc_attr_e('Edit Pay Per Post', 'wishlist-member'); ?>" class="btn -icon-only -ajax-btn">
                                        <i class="wlm-icons md-24">edit</i>
                                    </a>
                                    <a target="_blank" href="<?php echo esc_url($wp_edit_link . $ppp->ID); ?>" title="<?php /* translators: 1: post type name */ printf(esc_html__('Edit %s in WordPress', 'wishlist-member'), esc_html($post_type_name)); ?>" class="btn -icon-only">
                                        <i class="wlm-icons md-24">open_in_new</i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8">
                                <div class="text-center"><?php esc_html_e('No items found', 'wishlist-member'); ?></div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
table#ppps-list-table tbody tr.ui-sortable-helper {
    display: table-row;
    border: 1px solid #DBE4EE;
}
#ppps-list-table tbody:not(:empty) ~ tfoot {
    display: none;
}
</style>
