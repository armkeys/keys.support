<style>
@media (min-width: 1400px) {
    .col-xxl-6 {
        -ms-flex: 0 0 50%;
        flex: 0 0 50%;
        max-width: 50%;
    }
}
</style>
<?php
$wpm_levels       = (array) $this->get_option('wpm_levels');
$bypass_licensing = $this->bypass_licensing();

// Get version information.
$latest_wpm_ver = $this->plugin_latest_version();
if (! $latest_wpm_ver) {
    $latest_wpm_ver = $this->version;
}
$reversion   = preg_split('/[ \.-]/', $this->version);
$wlm_version = wlm_arrval($reversion, 0) . '.' . wlm_arrval($reversion, 1);
$wlm_build   = wlm_arrval($reversion, 2);
$wlm_stage   = wlm_arrval($reversion, 3);
// Get the license info.
$WPWLKey       = $this->get_option('LicenseKey');
$WPWLKeyExpire = $this->get_option('LicenseExpiration');
// Make sure we have a valid license info.
$WPWLKey = false !== $WPWLKey ? ( '' != wlm_trim($WPWLKey) ? wlm_trim($WPWLKey) : false ) : false;

$member_count    = $this->member_ids(null, null, true);
$nonmember_count = $this->non_member_count();
$wpuser_count    = $member_count + $nonmember_count;
?>
<div class="row wlm-new-container" id="wlm-warning" style="display:none">
    <div class="col-md-12">
        <div class="form-text text-danger help-block">
            <h3 class="pull-right"><a href="#" data-option="dashboard_warningfeed_dismissed" class="wlm-dismiss-news">&times;</a></h3>
            <p id="wlm-warning-title"><strong></strong></p>
            <a class="btn -primary -condensed pull-right ml-3" id="wlm-warning-link" target="_blank" href=""><?php esc_html_e('Read More', 'wishlist-member'); ?></a>
            <p class="mb-0" id="wlm-warning-content"></p>
        </div>
    </div>
</div>
<?php if ($this->access_control->current_user_can('wishlistmember3_dashboard/news')) : ?>
<div class="row wlm-new-container" id="wlm-news" style="display:none">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading -without-icon">
                <h3 class="panel-title">
                <span class="float-right"><a href="#" data-option="dashboard_feed_dismissed" class="wlm-dismiss-news">&times;</a></span>
                <?php esc_html_e('News', 'wishlist-member'); ?> :
                </h3>
            </div>
            <div class="panel-body">
                <a style="line-height: 1.6em" class="btn -primary -condensed pull-right ml-3" id="wlm-news-link" target="_blank" href=""><?php esc_html_e('Read More', 'wishlist-member'); ?></a>
                <div id="wlm-news-content"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="row">
    <!-- Left Column -->
    <div class="col-lg-7 col-md-7 col-sm-12">
        <?php do_action('wishlistmember_dashboard_first_column_start'); ?>
        <!-- Membership Stats -->
        <?php if ($this->access_control->current_user_can('wishlistmember3_dashboard/stats')) : ?>
        <div class="panel panel-default">
            <div class="panel-heading -with-form">
                <div class="row">
                    <div class="col-lg-5 col-md-6 col-sm-5">
                        <h3 class="panel-title"><span class="icon-container"><i class="wlm-icons">insert_chart</i></span> <?php esc_html_e('Your Membership Stats', 'wishlist-member'); ?></h3>
                    </div>
                    <div class="col-lg-7 col-md-6 col-sm-7">
                        <form id="members-search" method="GET" target="_parent">
                            <input type="hidden" name="wl" value="members/manage">
                            <?php
                            foreach (wlm_get_data(true) as $k => $v) {
                                if (in_array($k, ['usersearch', 'wlmdebug', 'wl'], true)) {
                                    continue;
                                }
                                printf('<input type="hidden" name="%s" value="%s">', esc_attr(stripslashes($k)), esc_attr(stripslashes($v)));
                            }
                            ?>
                            <div class="input-group -form-tight">
                                <input type="text" name="wlm_search_term" class="form-control" placeholder="Search Users">
                                <div class="input-group-append">
                                    <button class="btn -default -icon -stroke search-btn">
                                    <i class="wlm-icons">search</i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="registered-count">
                    <div class="row">
                        <div class="col">
                            <a class="stats-link" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=members/manage')); ?>">
                                <div class="card mb-3 -stats -wp-blue">
                                    <div class="card-body">
                                        <span class="icon-container pull-right">
                                            <i class="wlm-icons md-36">people_outline</i>
                                        </span>
                                        <div class="card-title">
                                            <span class="count"><?php echo number_format($wpuser_count); ?></span>
                                        </div>
                                        <p class="card-text" data-original-title="Total active members">
                                            <?php esc_html_e('ALL USERS', 'wishlist-member'); ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a class="stats-link" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=members/manage&level=members')); ?>">
                                <div class="card mb-3 -stats -blue">
                                    <div class="card-body">
                                        <span class="icon-container pull-right">
                                            <i class="wlm-icons md-36">people</i>
                                        </span>
                                        <div class="card-title">
                                            <span class="count"><?php echo number_format($member_count); ?></span>
                                        </div>
                                        <p class="card-text">
                                            <?php esc_html_e('MEMBERS', 'wishlist-member'); ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a class="stats-link" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=members/manage&level=nonmembers')); ?>">
                                <div class="card mb-3 -stats -gray">
                                    <div class="card-body">
                                        <span class="icon-container pull-right">
                                            <i class="wlm-icons md-36">people</i>
                                        </span>
                                        <div class="card-title clearfix">
                                            <span class="count"><?php echo number_format($nonmember_count); ?></span>
                                        </div>
                                        <p class="card-text">
                                            <?php esc_html_e('NON-MEMBERS', 'wishlist-member'); ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a class="stats-link" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=members/manage&status=active')); ?>">
                                <div class="card mb-3 -stats -secondary">
                                    <div class="card-body">
                                        <span class="icon-container pull-left">
                                            <i class="wlm-icons md-30 color-green">active_icon</i>
                                        </span>
                                        <div class="pull-left ml-3">
                                            <p class="card-text">
                                                <?php esc_html_e('ACTIVE', 'wishlist-member'); ?>
                                            </p>
                                            <div class="card-title clearfix">
                                                <span class="count tactive">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a class="stats-link" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=members/manage&status=cancelled')); ?>">
                                <div class="card mb-3 -stats -secondary -cancelled-stats">
                                    <div class="card-body">
                                        <span class="icon-container pull-left">
                                            <i class="wlm-icons md-30 color-red">cancelled_icon</i>
                                        </span>
                                        <div class="pull-left ml-3">
                                            <p class="card-text">
                                                <?php esc_html_e('CANCELLED', 'wishlist-member'); ?>
                                            </p>
                                            <div class="card-title clearfix">
                                                <span class="count tcancelled">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a class="stats-link" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=members/manage&status=expired')); ?>">
                                <div class="card mb-3 -stats -secondary">
                                    <div class="card-body">
                                        <span style="padding-top:6px" class="icon-container pull-left">
                                            <i class="wlm-icons -timmer-off color-orange">timer_off</i>
                                        </span>
                                        <div class="pull-left ml-3">
                                            <p class="card-text">
                                                <?php esc_html_e('EXPIRED', 'wishlist-member'); ?>
                                            </p>
                                            <div class="card-title clearfix">
                                                <span class="count texpired">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a class="stats-link d-block" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=members/manage&status=forapproval')); ?>">
                                <div class="card mb-3 -stats -tertiary">
                                    <!-- <a href="" class="stats-link d-block"> -->
                                    <div class="card-body d-inline-block w-100">
                                        <span class="icon-container pull-left mr-3">
                                            <i class="wlm-icons color-blue02">needs_approval</i>
                                        </span>
                                        <div class="pull-left">
                                            <p class="card-text">
                                                <?php esc_html_e('NEEDS APPROVAL', 'wishlist-member'); ?>
                                            </p>
                                            <div class="card-title clearfix">
                                                <span class="count tforapproval">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a class="stats-link d-block" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=members/manage&status=unconfirmed')); ?>">
                                <div class="card mb-3 -stats -tertiary">
                                    <div class="card-body d-inline-block w-100">
                                        <span class="icon-container pull-left mr-3">
                                            <i class="wlm-icons color-blue02">needs_confirm</i>
                                        </span>
                                        <div class="pull-left">
                                            <p class="card-text">
                                                <?php esc_html_e('UNCONFIRMED', 'wishlist-member'); ?>
                                            </p>
                                            <div class="card-title clearfix">
                                                <span class="count tunconfirmed">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <?php if (count($wpm_levels) > 0) : ?>
                <div class="table-wrapper table-responsive -m-levels-count">
                    <table class="table table-striped table-condensed table-fixed text-center">
                        <thead>
                            <tr class="d-flex">
                                <th class="col-5 text-left" style="margin-right:-6px"><span><?php esc_html_e('Membership Level', 'wishlist-member'); ?></span></th>
                                <!-- <th class="col-3"><span><?php esc_html_e('Active', 'wishlist-member'); ?></span></th> -->
                                <th class="col stats-cell-th"><i title="Active" class="wlm-icons md-24">active_icon</i></th>
                                <!-- <th class="col-3 cancelled-members"><span><?php esc_html_e('Cancelled', 'wishlist-member'); ?></span></th> -->
                                <th class="col stats-cell-th"><i title="Cancelled" class="wlm-icons md-24">cancelled_icon</i></th>
                                <th class="col stats-cell-th" style="padding:0.50rem 0.45rem 0"><i title="Expired" class="wlm-icons md-24">timer_off</i></th>
                                <th class="col stats-cell-th"><i title="Needs Approval" class="wlm-icons md-24">needs_approval</i></th>
                                <th class="col stats-cell-th"><i title="Unconfirmed" class="wlm-icons md-24">needs_confirm</i></th>
                                <th class="th-stats-spacer"></th>
                            </tr>
                        </thead>
                        <tbody style="max-height: 174px;">
                            <?php
                            $totalmembers  = 0;
                            $cancelmembers = 0;
                            foreach (array_keys($wpm_levels) as $level) :
                                $level      = new \WishListMember\Level($level);
                                $level_link = admin_url('admin.php?page=WishListMember&wl=members/manage&level=' . $level->ID);
                                ?>
                            <tr class="d-flex" data-levelid="<?php echo esc_attr($level->ID); ?>">
                                <td class="col-5 text-left"><a href="<?php echo esc_url($level_link); ?>" target="_parent"><?php echo esc_html($level->name); ?></a></td>
                                <td class="active col stats-cell cellsize-7 active-members"><a href="<?php echo esc_url($level_link); ?>&status=active" target="_parent">...</a></td>
                                <td class="cancelled col stats-cell"><a href="<?php echo esc_url($level_link); ?>&status=cancelled" target="_parent">...</a></td>
                                <td class="expired col stats-cell"><a href="<?php echo esc_url($level_link); ?>&status=expired" target="_parent">...</a></td>
                                <td class="forapproval col stats-cell"><a href="<?php echo esc_url($level_link); ?>&status=forapproval" target="_parent">...</a></td>
                                <td style="" class="unconfirmed col stats-cell"><a href="<?php echo esc_url($level_link); ?>&status=unconfirmed" target="_parent">...</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Support -->
            <?php if ($this->access_control->current_user_can('wishlistmember3_dashboard/support')) : ?>
            <div class="col-xxl-6 col-12">
                <div class="panel panel-default">
                    <div class="panel-heading -without-icon">
                        <h3 class="panel-title"><?php esc_html_e('Support', 'wishlist-member'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col" style="min-width:230px;">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <a href="https://wishlistmember.com/doc-categories/video-tutorials/" target="_blank" title="Video Tutorials" class="icon-link wlm-icon-link flex-grow">
                                            <span class="icon-container">
                                                <i class="wlm-icons md-24">ondemand_video</i>
                                            </span>
                                            <span class="title"><?php esc_html_e('Tutorials', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="https://wishlistmember.com/docs" target="_blank" title="Help" class="icon-link wlm-icon-link flex-grow">
                                            <span class="icon-container">
                                                <i class="wlm-icons md-24">find_in_page</i>
                                            </span>
                                            <span class="title"><?php esc_html_e('Help Documentation', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col" style="min-width:230px;">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <a href="https://my.wishlistmember.com/" target="_blank" title="Support" class="icon-link wlm-icon-link flex-grow">
                                            <span class="icon-container">
                                                <i class="wlm-icons md-24">support_icon</i>
                                            </span>
                                            <span class="title"><?php esc_html_e('Support Options', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="https://wishlistmember.com/doc-categories/api/" target="_blank" title="API Documents" class="icon-link wlm-icon-link flex-grow">
                                            <span class="icon-container">
                                                <i class="wlm-icons md-24">code</i>
                                            </span>
                                            <span class="title"><?php esc_html_e('API Documentation', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- Tools -->
            <?php if ($this->access_control->current_user_can('wishlistmember3_dashboard/tools')) : ?>
            <div class="col-xxl-6 col-12">
                <div class="panel panel-default">
                    <div class="panel-heading -without-icon">
                        <h3 class="panel-title"><?php esc_html_e('Tools', 'wishlist-member'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col" style="min-width:230px;">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <a data-toggle="modal" href="#shortcode-creator-modal" title="Shortcode Creator" class="icon-link wlm-icon-link flex-grow">
                                            <span class="icon-container">
                                                <i class="wlm-icons md-24">wp_shortcode</i>
                                            </span>
                                            <span class="title"><?php esc_html_e('Shortcode Creator', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="<?php echo esc_attr(add_query_arg('wl', 'administration/backup')); ?>" title="Backup" class="icon-link wlm-icon-link flex-grow">
                                            <span class="icon-container">
                                                <i class="wlm-icons md-24">baseline_save_alt</i>
                                            </span>
                                            <span class="title"><?php esc_html_e('Backup', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col" style="min-width:230px;">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <a href="<?php echo esc_attr(add_query_arg('wl', 'administration/rollback')); ?>" title="Version Rollback" class="icon-link wlm-icon-link flex-grow">
                                            <span class="icon-container">
                                                <i class="wlm-icons md-24">settings_backup_restore</i>
                                            </span>
                                            <span class="title"><?php esc_html_e('Version Rollback', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="<?php echo esc_attr(add_query_arg('wl', 'administration/broadcasts')); ?>" title="Email Broadcast" class="icon-link wlm-icon-link flex-grow">
                                            <span class="icon-container">
                                                <i class="wlm-icons md-24">email</i>
                                            </span>
                                            <span class="title"><?php esc_html_e('Email Broadcast', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php do_action('wishlistmember_dashboard_first_column_end'); ?>
    </div>
    <!-- Right Column -->
    <div class="col-lg-5 col-md-5 col-sm-12">
        <?php
        do_action('wishlistmember_dashboard_second_column_start');
        $subscription    = $this->get_option('LicenseSubscription');
        $key_is_expired  = wlm_date('Y-m-d 00:00:00') > $WPWLKeyExpire;
        $key_is_expiring = wlm_date('Y-m-d 00:00:00', strtotime('+1 month')) >= $WPWLKeyExpire;
        $lifetime        = substr($WPWLKeyExpire, 0, 4) > 2999;
        $support_text    = ( $key_is_expired ) ? __('Renew your expired Updates and Support Plan Now', 'wishlist-member') : __('Renew your Updates and Support Plan Now', 'wishlist-member');
        $span_style      = $key_is_expired ? ' style="color:red"' : '';
        ?>
        <div class="panel panel-default">
            <div class="panel-heading clearfix -with-btn">
                <h3 class="panel-title pull-left"><?php esc_html_e('Updates', 'wishlist-member'); ?></h3>
                <div class="form-group mb-0 text-center">
                    <a class="btn -primary -condensed btn-min-160 pull-right" href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&checkversion=1')); ?>" target="_parent" rol e="button">
                        <i class="wlm-icons">updates</i>
                        <span><?php esc_html_e('Check for Updates', 'wishlist-member'); ?></span>
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <?php if ($WPWLKey) : ?>
                <p>
                    <?php
                    if ($lifetime) {
                        esc_html_e('Lifetime Updates and Support Plan', 'wishlist-member');
                    } else {
                        $date = wlm_date('F j, Y', strtotime($WPWLKeyExpire));
                        if ($subscription) {
                            // Translators: 1: Date.
                            printf(esc_html__('Plan Automatically Renews on %s', 'wishlist-member'), esc_html($date));
                        } elseif ($key_is_expired) {
                            // Translators: 1: Date.
                            printf(esc_html__('Plan Expired on %s', 'wishlist-member'), sprintf('<span style="color:red">%s</span>', esc_html($date)));
                        } else {
                            // Translators: 1: Date.
                            printf(esc_html__('Updates and Support Plan Through: %s', 'wishlist-member'), esc_html($date));
                        }
                    }
                    ?>
                </p>
                    <?php if (( $key_is_expired || $key_is_expiring ) && ! $lifetime && ! $subscription) : ?>
                <p><a href="http://wishlistmember.com/renewal-page/" target="_blank"><?php echo esc_html($support_text); ?></a></p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($this->plugin_is_latest()) : ?>
                <p class="fadeOut-css text-left
                    <?php
                    if (wlm_get_data()['checkversion']) {
                        echo 'highlight-fade';
                    }
                    ?>
                "><?php /* translators: 1: Version number, 2: Build number, 3: Plugin name */ printf(esc_html__('You have the latest version of %3$s (v%1$s.%2$s)', 'wishlist-member'), esc_html($wlm_version), esc_html($wlm_build), wp_kses_data('<strong>WishList Member&trade;</strong>')); ?></p>
                <?php else : ?>
                <p>
                    <?php /* translators: 1: Version number, 2: Build number, 3: Plugin name */ printf(esc_html__('You are currently running on %3$s (v%1$s.%2$s)', 'wishlist-member'), esc_html($wlm_version), esc_html($wlm_build), wp_kses_data('<strong>WishList Member&trade;</strong>')); ?><br>
                </p>
                    <?php if (! $bypass_licensing) : ?>
                <div class="row no-gutters">
                    <div class="col-lg-12 mb-2">
                        <?php if (current_user_can('update_plugins') && 'WLMNOLICENSEKEY' !== $this->plugin_download_url()) : ?>
                        <a href="<?php echo $WPWLKey ? esc_url($this->plugin_update_url()) : '#'; ?>" class="btn -primary -condensed <?php echo esc_attr($WPWLKey ? '' : 'enter-license-key'); ?>" target="_parent">

                            <i class="wlm-icons">update</i>
                            <span><?php esc_html_e('Upgrade', 'wishlist-member'); ?></span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                    <?php endif; ?>
                <p><span class="text-danger"><?php /* translators: 1: Version number */ printf(esc_html__('* The most current version is v%1$s.', 'wishlist-member'), esc_html($latest_wpm_ver)); ?></p>
                <?php endif; ?>
                <p><a href="https://my.wishlistmember.com/changelogs/" target="_blank"><?php esc_html_e('Release Notes', 'wishlist-member'); ?></a></p>

                <?php do_action('wishlistmember_dashboard_update_card'); ?>

                <p>WordPress <?php echo esc_html(get_bloginfo('version')); ?> | PHP <?php echo esc_html(phpversion()); ?> on <?php echo esc_html(php_sapi_name()); ?></p>
            </div>
        </div>
        <?php if ($WPWLKey && $this->access_control->current_user_can('wishlistmember3_dashboard/activation_settings') && ! $bypass_licensing) : ?>
        <!-- License Information -->
        <div class="panel panel-default">
            <div class="panel-heading clearfix -with-btn">
                <h3 class="panel-title pull-left"><?php esc_html_e('License Information', 'wishlist-member'); ?></h3>
                <div class="form-group pull-right mb-0">
                    <!-- <form method="post" onsubmit="return confirm('<?php esc_html_e('Are you sure that you want to deactivate the license of this plugin for this site?', 'wishlist-member'); ?>')" taregt="_parent"> -->
                    <button data-confirm-popup="1" class="btn -default -condensed btn-min-160 enter-license-key" value="<?php esc_attr_e('Deactivate License', 'wishlist-member'); ?>">
                    <i class="wlm-icons">info_outline</i>
                    <span class="text"><?php esc_html_e('Deactivate License', 'wishlist-member'); ?></span>
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <p><strong><?php esc_html_e('License Key', 'wishlist-member'); ?>:</strong> ************************<?php echo esc_html(substr($WPWLKey, -4)); ?></p>
            </div>
        </div>
        <?php endif; ?>
        <?php if (count($wpm_levels) > 0 && $this->access_control->current_user_can('wishlistmember3_setup/getting-started')) : ?>
        <div class="panel panel-default">
            <div class="panel-heading clearfix -with-btn">
                <h3 class="panel-title pull-left"><?php esc_html_e('Getting Started', 'wishlist-member'); ?></h3>
                <div class="form-group text-center mb-0 pull-right">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=setup/getting-started')); ?>" class="btn -success -condensed btn-min-160" target="_parent">
                        <i class="wlm-icons">input</i>
                        <span><?php esc_html_e('Run Wizard Now', 'wishlist-member'); ?></span>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php do_action('wishlistmember_dashboard_second_column_end'); ?>
    </div>
</div>
<?php
    require_once 'dashboard/modals/shortcode.php'
?>
