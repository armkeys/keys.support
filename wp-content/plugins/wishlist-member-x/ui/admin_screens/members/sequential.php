<?php
$wpm_levels = $this->get_option('wpm_levels');
$total_cnt  = count($wpm_levels);
$this->sort_levels($wpm_levels, 'a', 'levelOrder');

$keys = array_flip(
    [
        'name',
        'upgradeAfter',
        'upgradeAfterPeriod',
        'upgradeEmailNotification',
        'upgradeMethod',
        'upgradeOnDate',
        'upgradeSchedule',
        'upgradeTo',
    ]
);
foreach ($wpm_levels as &$level) {
    $level = array_intersect_key($level, $keys);
}

$howmany = $this->get_option('sequential_pagination');
if (is_numeric(wlm_get_data()['howmany']) || ! $howmany || 'Show All' == wlm_get_data()['howmany']) {
    $howmany = wlm_get_data()['howmany'];
    if (! $howmany) {
        $howmany = $this->pagination_items[1];
    }
    if (! in_array($howmany, $this->pagination_items)) {
        $howmany = $this->pagination_items[1];
    }
    // We only save if not show all.
    if ('Show All' !== $howmany) {
        $this->save_option('sequential_pagination', $howmany);
    }
}
$howmany = 'Show All' === $howmany ? 999999999 : $howmany;

$offset = wlm_get_data()['offset'] - 1;
if ($offset < 0) {
    $offset = 0;
}
$offset            = $offset * $howmany;
$membership_levels = array_slice($wpm_levels, $offset, $howmany, true);
$current_page      = $offset / $howmany + 1;
++$offset;
$total_pages = ceil($total_cnt / $howmany);
$form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'members/sequential' );
?>
<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title"><?php esc_html_e('Sequential Upgrade', 'wishlist-member'); ?></h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<?php if ($total_cnt && $total_cnt > $this->pagination_items[0]) : ?>
<div class="row">
    <div class="col-md-12">
        <div class="pagination -minimal pull-right">
            <div class="count pull-left">
                <div role="presentation" class="dropdown page-rows">
                    <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <?php echo (int) $offset; ?> - <?php echo (int) ( ( $howmany * $current_page ) > $total_cnt ? $total_cnt : $howmany * $current_page ); ?>
                    </a> of <?php echo (int) $total_cnt; ?>
                    <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                        <?php foreach ($this->pagination_items as $key => $value) : ?>
                        <a class="dropdown-item" target="_parent" href="<?php echo esc_url($form_action . '&howmany=' . $value); ?>"><?php echo esc_html($value); ?></a>
                        <?php endforeach; ?>
                    </ul>
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
                <a target="_parent" href="<?php echo esc_url($previous_link); ?>" >
                    <i class="wlm-icons md-26">keyboard_arrow_left</i>
                </a>
                <?php
                if ($current_page < $total_pages) {
                    $next_link = $form_action . '&offset=' . ( $current_page + 1 );
                } else {
                    $next_link = $form_action . '&offset=1';
                }
                ?>
                <a target="_parent" href="<?php echo esc_url($next_link); ?>">
                    <i class="wlm-icons md-26">keyboard_arrow_right</i>
                </a>
            </div>
            <?php endif; ?>
        </div>
        <br class="d-none d-sm-block d-md-none">
        <br class="d-none d-sm-block d-md-none">
        <br class="d-none d-sm-block d-md-none">
    </div>
</div>
<?php endif; ?>
<div class="row">
    <div class="col-md-12">
        <p><em>
            <?php
                // If WordPress Timezone is just 'UTC', set DateTimeZone paramater to null to prevent returning an "Unknown or bad timezone" error.
                 $this->get_wp_tzstring(true) === 'UTC' ? $date_timezone = null : $date_timezone = new DateTimeZone($this->get_wp_timezone());

                // Translators: 1: Date/Time , 2: Timezone.
                printf(esc_html__('WordPress Time: %1$s %2$s', 'wishlist-member'), esc_html(( new DateTime('now', $date_timezone) )->format(get_option('date_format') . ' ' . get_option('time_format'))), esc_html($this->get_wp_tzstring(true)));
            ?>
        </em></p>
        <div class="table-wrapper table-responsive">
            <table class="table table-striped table-condensed">
                <colgroup>
                    <col width="33%">
                    <col>
                    <col width="20">
                </colgroup>
                <thead>
                    <tr>
                        <th><?php esc_html_e('Membership Level', 'wishlist-member'); ?></th>
                        <th>
                            <?php esc_html_e('Upgrade', 'wishlist-member'); ?>
                            <?php $this->tooltip(__('The scheduled Upgrade method and time period are displayed.', 'wishlist-member'), 'lg'); ?>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="sequential-list"></tbody>
            </table>
        </div>
    </div>

</div>
<div class="content-wrapper -no-background">
    <h4><?php esc_html_e('Advanced Settings', 'wishlist-member'); ?></h4>
    <div class="row">
        <div class="col-md-12">
            <p><?php esc_html_e('Sequential Upgrades are automatically triggered when a member signs in to their account. If you would like to set your system to trigger upgrades without requiring a member to sign in, you must create a Cron Job on your server.', 'wishlist-member'); ?></p>
            <p><a href="?page=WishListMember&wl=advanced_settings/cron_jobs" target="_blank"><?php esc_html_e('Click Here', 'wishlist-member'); ?></a> for instructions on how to set-up a Cron Job for WishList Member.</p>
        </div>
    </div>
</div>

<script type="text/template" id="wlm-sequential-list-template">
{% _.each(data.levels, function(lvl, lvlid) { %}
    {%
        // Reset upgrade info if upgradeTo is not a valid level.
        if( lvl.upgradeMethod != 'REMOVE' && lvl.upgradeTo in data.wpm_levels === false ) {
            lvl.upgradeMethod = '';
            lvl.upgradeTo = null;
            lvl.upgradeSchedule = null;
            lvl.upgradeOnDate = null;
            lvl.upgradeAfter = null;
            lvl.upgradeAfterPeriod = null;
        }

        // Set upgradeOnDate to today if it is empty.
        if(!lvl.upgradeOnDate) {
            lvl.upgradeOnDate = Date.now() / 1000;
        }
    %}
    <tr class="tr tr-{%- lvlid %} button-hover" data-level-id="{%- lvlid %}" data-level-name="{%- lvl.name %}">
        <td>
            <span class="text"><a href="#sequential-upgrade" data-toggle="modal">{%- lvl.name %}</a></span>
        </td>
        <td>
            <div class="seq-values">
                <input type="hidden" name="upgradeMethod" value="{%- String(lvl.upgradeMethod).replace( /(\d+|undefined)/, '' ) || 'inactive' %}">
                <input type="hidden" name="upgradeTo" value="{%- lvl.upgradeTo %}">
                <input type="hidden" name="upgradeSchedule" value="{%- lvl.upgradeSchedule %}">
                <input type="hidden" name="sched_toggle" value="{%- lvl.upgradeSchedule %}">
                <input type="hidden" name="upgradeAfter" value="{%- lvl.upgradeAfter %}">
                <input type="hidden" name="upgradeAfterPeriod" value="{%- lvl.upgradeAfterPeriod %}">
                <input type="hidden" name="upgradeOnDate" value="{%- isNaN(lvl.upgradeOnDate) ? lvl.upgradeOnDate : wlm.date(lvl.upgradeOnDate) %}">
                <input type="hidden" name="upgradeEmailNotification" value="{%- lvl.upgradeEmailNotification || '' %}">
            </div>
            {%
                var method;
                var configured = true;
                switch ( lvl.upgradeMethod ) {
                    case 'MOVE':
                        method = '<?php echo esc_js(/* Translators: Membership level name. */ __('Move to %s', 'wishlist-member')); ?>'.replace('%s', '<strong>' + data.wpm_levels[ lvl.upgradeTo ].name + '</strong>' );
                        break;
                    case 'ADD':
                        method = '<?php echo esc_js(/* Translators: Membership level name. */ __('Add to %s', 'wishlist-member')); ?>'.replace('%s', '<strong>' + data.wpm_levels[ lvl.upgradeTo ].name + '</strong>' );
                        break;
                    case 'REMOVE':
                        method = '<?php echo esc_js(/* Translators: Membership level name. */ __('Remove from %s', 'wishlist-member')); ?>'.replace('%s', '<strong>' + data.wpm_levels[ lvlid ].name + '</strong>' );
                        break;
                    default:
                        configured = false;
                        method = '<?php echo esc_js(__('None', 'wishlist-member')); ?>';
                }

                var schedule = '';
                if( configured ) {
                    switch ( lvl.upgradeSchedule ) {
                        case 'ondate':
                            schedule = '<?php echo esc_js(/* Translators: Date. */ __('on %s', 'wishlist-member')); ?>'.replace('%s', isNaN(lvl.upgradeOnDate) ? lvl.upgradeOnDate + ' ' + WLM3VARS.js_timezone_string_pretty : wlm.date(lvl.upgradeOnDate, {include_timezone:true}) );
                            break;
                        default:
                            schedule = lvl.upgradeAfter;
                            switch ( lvl.upgradeAfterPeriod ) {
                                case 'years':
                                    schedule = ( schedule == 1 ?
                                        '<?php echo esc_js(__('after 1 Year', 'wishlist-member')); ?>' :
                                        '<?php echo esc_js(/* Translators: %d - Number of years. */ __('after %d Years', 'wishlist-member')); ?>' ).replace('%d', schedule );
                                    break;
                                case 'months':
                                    schedule = ( schedule == 1 ?
                                        '<?php echo esc_js(__('after 1 Month', 'wishlist-member')); ?>' :
                                        '<?php echo esc_js(/* Translators: %d - Number of months. */ __('after %d Months', 'wishlist-member')); ?>' ).replace('%d', schedule );
                                    break;
                                case 'weeks':
                                    schedule = ( schedule == 1 ?
                                        '<?php echo esc_js(__('after 1 Week', 'wishlist-member')); ?>' :
                                        '<?php echo esc_js(/* Translators: %d - Number of weeks. */ __('after %d Weeks', 'wishlist-member')); ?>' ).replace('%d', schedule );
                                    break;
                                default:
                                    schedule = ( schedule == 1 ?
                                        '<?php echo esc_js(__('after 1 Day', 'wishlist-member')); ?>' :
                                        '<?php echo esc_js(/* Translators: %d - Number of days. */ __('after %d Days', 'wishlist-member')); ?>'
                                    ).replace('%d', schedule );
                                    break;
                            }
                            break;
                    }
                }
            %}
            {%= method + ' ' + schedule %}
            </td>
        <td>
            <div class="btn-group-action">
                <a href="#sequential-upgrade" data-toggle="modal" title="Edit Sequential Upgrade" class="btn -icon-only -edit-btn" target="_parent">
                    <i class="wlm-icons md-24">edit</i>
                </a>
            </div>
        </td>
    </tr>
{% }); %}
</script>

<script type="text/javascript">
    function sequential_list(membership_levels) {
        if(!this.levels) {
            this.levels = <?php echo json_encode($membership_levels); ?>;
        }
        if(membership_levels) {
            this.levels = $.extend(true, levels, membership_levels);
        }
        var data = {
            levels : this.levels,
            wpm_levels : <?php echo json_encode($wpm_levels); ?>,
        }
        var html = _.template($('script#wlm-sequential-list-template').html(), {variable: 'data'})(data);
        $('#sequential-list').html(html.trim());
    }

</script>

<?php require_once 'sequential/edit.php'; ?>

<style type="text/css">
    tr.schedule-ondate .schedule-after-holder,
    tr.schedule-after .schedule-ondate-holder,
    tr.method-inactive .schedule-type-holder,
    tr.method-inactive .schedule-ondate-holder,
    tr.method-inactive .schedule-after-holder,
    tr.method-inactive .remove-from-holder,
    tr.method-add .remove-from-holder,
    tr.method-move .remove-from-holder {
        display: none;
    }
    tr.method-inactive .upgrade-to-holder,
    tr.method-remove .upgrade-to-holder {
        visibility: hidden;
        height: 0;
    }
</style>
