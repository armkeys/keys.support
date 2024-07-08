<div class="wlm3-modal-loader-overlay-holder" style="display: none">
    <div class="wlm3-modal-loader-overlay-content">
        <img class="wlm3-modal-loader-overlay-logo" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/wlm-opaque.png">
        <img class="wlm3-modal-loader-overlay-balls" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/wlm-loader03.gif">
    </div>
</div>
<style type="text/css">
    .wlm3-modal-loader-overlay-holder {
        position: absolute;
        text-align: center;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background: #fff;
    }   
    .wlm3-modal-loader-overlay-content {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .wlm3-modal-loader-overlay-logo {
        margin: 0 0 10px 0;
        display: block;
    }
    .wlm3-modal-loader-overlay-balls {
        margin: 1.5rem 0 0 0;
        opacity: .5;
    }
</style>
