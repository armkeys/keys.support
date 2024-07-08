<?php
wlm_print_style(plugin_dir_url(__FILE__) . 'assets/admin.css');
?>
<div class="table-wrapper table-responsive -with-input mt-2 presto-player-integration-table-holder">
    <table class="table table-striped table-condensed table-fixed">
        <thead>
             <tr class="d-flex">
                 <th class="col-4"><?php esc_html_e('Content Location', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The location of the video file.', 'wishlist-member')); ?></th>
                 <th class="col-5"><?php esc_html_e('Video Title', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The set name of the video file.', 'wishlist-member')); ?></th>
                 <th class="col-2"><?php esc_html_e('Watch Date', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The date and time the user viewed the video.', 'wishlist-member')); ?></th>
                 <th class="col-1 text-right"><?php esc_html_e('Watched', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The amount (in percentage) of the video viewed by the user.', 'wishlist-member')); ?></th>
             </tr>
        </thead>
        <tbody class="user-level-holder" style="max-height: 500px">
            <?php if (count($visits) > 0) : ?>
                <?php foreach ($visits as $i => $v) : ?>
                    <?php
                        $ptype = get_post_type($v->post_id);
                        $ptype = 'pp_video_block' === $ptype ? 'Media Hub' : ucwords($ptype);
                    ?>
                     <tr class="d-flex">
                         <td class="col-4"><span class="title-holder" title="<?php echo esc_attr($v->post_title); ?>"><?php echo esc_html($v->post_title . ' - ' . $ptype); ?></span></td>
                         <td class="col-5"><span class="title-holder" title="<?php echo esc_attr($v->title); ?>"><?php echo esc_html($v->title); ?></span></td>
                         <td class="col-2"><?php echo esc_html(wishlistmember_instance()->format_date($v->created_at)); ?></td>
                         <td class="col-1 text-right"><?php echo esc_html($v->percent); ?>%</td>
                     </tr>
                <?php endforeach; ?>
            <?php else : ?>
                 <tr class="tr-none"><td class="text-center col-12" colspan="3">No record</td></tr>
            <?php endif; ?>
         </tbody>
     </table>
</div>
