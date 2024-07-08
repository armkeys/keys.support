<?php

/**
 * TinyMCE shortcode inserter lightbox markup
 *
 * @package WishListMember/Helpers
 */

?>
<div class="wlmtnmcelbox" id="wlm-tinymce-shortcode-creator">
    <div class="media-modal wp-core-ui" style="display: none !important;">
        <button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></button>
        <div class="media-frame-title">
            <h1>Shortcode Creator &raquo; <span></span></h1>
        </div>
        <div class="media-modal-content">
            <!-- Main Contend Starts -->
            <div class="wlmtnmcelbox-content">
                <?php wishlistmember_instance()->wlmshortcode->render_shortcode_attributes_form(); ?>
                <div class="row">
                    <div class="form-group col-12">
                        <div>
                            <br>
                            <div style="float:right"><button id="wlm-tinymce-insert-shortcode" class="button button-primary"><?php esc_html_e('Insert Shortcode', 'wishlist-member'); ?></button></div>
                            <label><?php esc_html_e('Shortcode Preview', 'wishlist-member'); ?></label>
                        </div>
                        <textarea class="form-control" id="wlm-tinymce-shortcode-creator-preview"></textarea>
                    </div>
                </div>
            </div>
            <!-- Main Contend Ends -->
        </div>
    </div>
    <div class="media-modal-backdrop" style="display: none !important;"></div>
</div>
