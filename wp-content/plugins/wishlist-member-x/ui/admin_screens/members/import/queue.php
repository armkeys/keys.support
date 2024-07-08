<?php
    $progress_bar = ( ( $in_queue - $queue_count ) / $in_queue ) * 100;
    $progress_bar = number_format($progress_bar, 2, '.', '');

    $pause = $this->get_option('import_member_pause');
    $pause = 1 === (int) $pause ? true : false;
?>
<div class="row import-queue-holder">
    <div class="col-md-12">
        <h4>Importing <span class="import-total"><?php echo esc_html($in_queue); ?></span> Members
            <span class="no-padding pull-right text-muted import-status"><?php echo $pause ? 'PAUSED' : ''; ?></span>
        </h4>
        <div class="import-progress" title="<?php echo esc_attr($progress_bar); ?>%">
            <div class="progress">
                <div style="width: <?php echo esc_attr($progress_bar); ?>%" class="progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr($in_queue - $queue_count); ?>" aria-valuemin="0" aria-valuemax="<?php echo esc_attr($in_queue); ?>" ></div>
            </div>
            <div class="text-center">
                <span class="import-count"><?php echo esc_html($queue_count); ?></span> left in queue
                <span class="import-action pull-right">
                    <a href="#" title="<?php echo esc_attr($pause ? 'Start Import' : 'Pause Import'); ?>" action="<?php echo $pause ? 'start' : 'pause'; ?>" class="btn import-pause-btn no-padding"><span class="wlm-icons md-24 -icon-only"><?php echo $pause ? 'play_arrow' : 'pause'; ?></span></a>
                    <a href="#" title="Cancel Import" class="btn import-cancel-btn no-padding"><span class="wlm-icons md-24 -icon-only text-danger">close</span></a>
                </span>
            </div>
        </div>
        <br />
    </div>
</div>
