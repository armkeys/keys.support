<?php

/**
 * Getting Started Wizard - Video Column
 *
 * @package WishListMember/Wizard
 */

$vidw = $wizard_mode ? wlm_or(empty($video_width) ? 0 : $video_width, 100) : 100;
?>
<div class="col-<?php echo $wizard_mode ? 12 : 5; ?>">
    <div class="<?php echo $wizard_mode ? 'mb-4' : ''; ?>" style="<?php echo $wizard_mode ? 'margin:-1.25rem' : ''; ?>">
        <div class="mx-auto w-<?php echo esc_attr($vidw); ?>">
            <div style="padding:56.25% 0 0 0;position:relative;" class="wlm-wizard-video border-bottom"><iframe src="about:blank" data-src="<?php echo esc_url($video_url); ?>" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;" title="WishList Member Setup Wizard"></iframe></div>
        </div>
    </div>
</div>
