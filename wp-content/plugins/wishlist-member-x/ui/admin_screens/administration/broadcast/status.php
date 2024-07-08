<?php
    $broadcast    = $this->get_email_broadcast($data['id']);
    $tqcount      = $this->get_email_broadcast_queue($data['id'], true, true, 0, true);
    $qcount       = $this->get_email_broadcast_queue($data['id'], false, true, 0, true);
    $scount       = (int) $broadcast->total_queued - (int) $tqcount;
    $fcount       = $this->get_failed_queue($data['id'], true);
    $failed_queue = $this->get_failed_queue($broadcast->id);
?>
<div class="email-status-holder">
    <input type="hidden" name="id" value="<?php echo esc_attr($data['id']); ?>" >
    <div class="row">
        <div class="col-md-4 col-sm-4">
            <div class="form-group text-center">
                <label for="">Sent</label>
                <h3><?php echo esc_html($scount); ?></h3>
            </div>
        </div>
        <div class="col-md-4 col-sm-4">
            <div class="form-group text-center">
                <label for="">Queued</label>
                <h3><?php echo esc_html($qcount); ?></h3>
            </div>
        </div>
        <div class="col-md-4 col-sm-4">
            <div class="form-group text-center">
                <label for="">Failed</label>
                <h3><?php echo esc_html($fcount); ?></h3>
            </div>
        </div>
    </div>
    <?php if ($fcount > 0) : ?>
        <div class="row">
            <div class="col-md-12">
                <label for=""><?php esc_html_e('Failed Emails', 'wishlist-member'); ?></label>
                <a href="#" class="pull-right check-all-failed" data-check="1"><?php esc_html_e('Check All', 'wishlist-member'); ?></a>
            </div>
        </div>
        <div style="max-height: 300px; overflow-y: scroll; overflow-x: hidden; ">
        <?php foreach ($failed_queue as $key => $value) : ?>
            <div class="form-check no-padding">
                <input type="checkbox" name="qid[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value->id); ?>" >
                <label class="form-check-label" for="qid[<?php echo esc_attr($key); ?>]"><?php echo esc_html($value->user_email); ?></label>
            </div>
        <?php endforeach; ?>
        </div>
        <p>&nbsp;</p>
        <div class="row">
            <div class="col-md-6">
                <a href="#" class="pull-left failed-emails-action" data-action="remove_failed_broadcast_emails">Remove Selected</a>
            </div>
            <div class="col-md-6">
                <a href="#" class="btn -primary -condensed pull-right failed-emails-action" data-action="requeue_failed_broadcast_emails">Requeue Selected</a>
            </div>
        </div>
    <?php endif; ?>
</div>
