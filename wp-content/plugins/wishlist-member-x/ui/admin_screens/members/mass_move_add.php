<?php

/**
 * Members > Mass Move Add Admin page
 *
 * @package WishListMember/UI
 */

// Lets run sync every 5 mins.
// This will make sure that the membership count is up to date.
if (false === get_transient('wlm_membership_count_sync_done')) {
    $this->sync_membership_count();
    set_transient('wlm_membership_count_sync_done', 1, DAY_IN_SECONDS);
}

$wpm_levels     = $this->get_option('wpm_levels');
$nonmembers_cnt = $this->non_member_count();
$levels         = [
    'NONMEMBERS' => [
        'name'  => 'Non-Members',
        'count' => $nonmembers_cnt,
    ],
] + $wpm_levels;
$total_cnt      = count($levels);

$howmany = $this->get_option('massmoveadd_pagination');
if (is_numeric(wlm_get_data()['howmany']) || ! $howmany || 'Show All' === wlm_get_data()['howmany']) {
    $howmany = wlm_get_data()['howmany'];
    if (! $howmany) {
        $howmany = $this->pagination_items[1];
    }
    if (! in_array(is_numeric($howmany) ? (int) $howmany : (string) $howmany, $this->pagination_items, true)) {
        $howmany = $this->pagination_items[1];
    }
    // We only save if not show all.
    if ('Show All' !== $howmany) {
        $this->save_option('massmoveadd_pagination', $howmany);
    }
}
$howmany = 'Show All' === $howmany ? 999999999 : $howmany;

$offset = wlm_get_data()['offset'] - 1;
if ($offset < 0) {
    $offset = 0;
}
$offset            = $offset * $howmany;
$membership_levels = array_slice($levels, $offset, $howmany, true);
$current_page      = $offset / $howmany + 1;
++$offset;
$total_pages = ceil($total_cnt / $howmany);

$form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'members/mass_move_add' );
?>
    <div class="page-header">
        <div class="row">
            <div class="col-md-9 col-sm-9 col-xs-8">
                <h2 class="page-title">
                    <?php esc_html_e('Mass Move/Add Members', 'wishlist-member'); ?>
                </h2>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-4">
                <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
            </div>
        </div>
    </div>
    <div class="row">
<!--        <div class="col-md-8">
            <p>All Members within a Membership Level can be Moved/Added to a selected Membership Level using the Move/Add method below. <?php $this->tooltip(__('todo', 'wishlist-member')); ?></p>
        </div> -->
        <?php if ($total_cnt && $total_cnt > $this->pagination_items[0]) : ?>
            <div class="col-md-12">
                <div class="pagination -minimal pull-right">
                    <div class="count pull-left">
                        <div role="presentation" class="dropdown page-rows">
                            <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <?php echo esc_html($offset); ?> - <?php echo esc_html(( $howmany * $current_page ) > $total_cnt ? $total_cnt : $howmany * $current_page); ?>
                            </a> of <?php echo esc_html($total_cnt); ?>
                            <div class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                <?php foreach ($this->pagination_items as $key => $value) : ?>
                                    <a class="dropdown-item" target="_parent" href="<?php echo esc_url($form_action . '&howmany=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($howmany <= $total_cnt) : ?>
                        <div class="arrows pull-right">
                            <?php
                            if ($current_page <= 1) {
                                $previous_link = $form_action . '&offset=' . $total_pages;
                            } else {
                                $previous_link = $form_action . '&offset=' . ( $current_page - 1 );
                            }
                            ?>
                            <a target="_parent" href="<?php echo esc_url($previous_link); ?>" class="wlm-icons">keyboard_arrow_left</a>
                            <?php
                            if ($current_page < $total_pages) {
                                $next_link = $form_action . '&offset=' . ( $current_page + 1 );
                            } else {
                                $next_link = $form_action . '&offset=1';
                            }
                            ?>
                            <a target="_parent" href="<?php echo esc_url($next_link); ?>" class="wlm-icons">keyboard_arrow_right</a>
                        </div>
                    <?php endif; ?>
                </div>
                <br class="d-none d-sm-block d-md-none">
                <br class="d-none d-sm-block d-md-none">
                <br class="d-none d-sm-block d-md-none">
            </div>
        <?php endif; ?>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="table-wrapper table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th style="width:20px"></th>
                            <th style="width: 30%"><?php esc_html_e('From Membership Level', 'wishlist-member'); ?></th>
                            <th style="width: 10%" class="text-center"><?php esc_html_e('Members', 'wishlist-member'); ?></th>
                            <th style="width: 30%" class="text-center"><?php esc_html_e('To Membership Level', 'wishlist-member'); ?></th>
                            <th style="width: 30%" class="text-center">
                                <?php esc_html_e('Actions', 'wishlist-member'); ?>
                                <?php $this->tooltip(__('The Move Action will remove members from the current Level and place them into the new Level specified in the To Membership Level field. The Add Action will add members to the new Level specified in the To Membership Level field and those members will also remain in the current Level.', 'wishlist-member'), 'lg'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($membership_levels as $lvlid => $lvl) : ?>
                            <?php
                                $lvl['count'] = $lvl['count'] ? $lvl['count'] : 0;
                            ?>
                            <tr class="level-holder-<?php echo esc_attr($lvlid); ?>">
                                <td style="width:20px"></td>
                                <td class="level-name"><?php echo esc_html($lvl['name']); ?></td>
                                <td class="text-center level-count"><?php echo esc_html($lvl['count']); ?></td>
                                <td class="text-center">
                                    <div class="form-group" <?php echo $lvl['count'] ? '' : "style='visibility:hidden;'"; ?>>
                                        <label class="sr-only" for=""><?php esc_html_e('Member Role', 'wishlist-member'); ?></label>
                                        <select class="form-control wlm-select move-to-level">
                                            <option value="">- Select a Level -</option>
                                            <?php foreach ($wpm_levels as $key => $value) : ?>
                                                <?php if ($lvlid != $key) : ?>
                                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="text-left" style="width: 190px ; margin: 0px auto 0px auto;">
                                        <?php $disabled = $lvl['count'] ? false : true; ?>
                                        <?php if (! $disabled) : ?>
                                            <a href="#" class="btn -success -condensed moveadd-members" data-action="add" data-levelid="<?php echo esc_attr($lvlid); ?>">
                                                <i class="wlm-icons">add</i>
                                                <span><?php esc_html_e('Add', 'wishlist-member'); ?></span>
                                            </a>
                                        <?php endif; ?>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <?php if (! $disabled && 'NONMEMBERS' !== $lvlid) : ?>
                                            <a href="#" class="btn -info -condensed moveadd-members" data-action="move" data-levelid="<?php echo esc_attr($lvlid); ?>">
                                                <i class="wlm-icons">swap_horiz</i>
                                                <span><?php esc_html_e('Move', 'wishlist-member'); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Modal -->
<div id="massmove-modal" data-id="massmove-modal" data-label="massmove_modal_label" data-title="Mass Move/Add Members" data-classes="modal-md" style="display:none">
    <div class="body">
        <h5 class="text-center message"><?php esc_html_e('** Mass Move/Add Message goes here **', 'wishlist-member'); ?></h5>
        <h5 class="text-danger text-center operation-warning"></h5>
        <h5 class="text-center message2"><?php esc_html_e('** Mass Move/Add Message goes here **', 'wishlist-member'); ?></h5>
        <div class="row progress-holder" style="display: none;">
            <div class="col-md-12">
                <div class="export-progress">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="0" ></div>
                    </div>
                    <h5 class="text-center operation-msg"></h5>
                    <div class="text-center mb-0 operation-loading"><img class="wlm3-modal-loader-overlay-balls" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/wlm-loader03.gif" /></div>
                </div>
                <br />
            </div>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn -bare cancel-button" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span>Yes</span></button>
    </div>
</div>
