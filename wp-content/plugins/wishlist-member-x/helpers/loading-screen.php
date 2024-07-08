<?php

/**
 * Loading screen.
 *
 * @package WishListMember/Helpers
 */

?>
<div class="container wlm3block" style="display: none">
    <div class="row" id="wlm-simple-loader-container">
        <div class="col-12 text-center" style="height: calc(100vh - 155px);">
            <!-- <div class="d-inline-block align-middle h-100"></div> -->
            <div class="d-inline-block align-middle" style="margin-top: 170px">
                <img class="l-logo" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/wlm-opaque.png" alt="">
                <img class="d-block mt-4" style="opacity: .5; margin: auto" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/wlm-loader03.gif" alt="">
            </div>
        </div>
    </div>
</div>
