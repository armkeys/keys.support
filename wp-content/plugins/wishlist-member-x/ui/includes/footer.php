<?php

/**
 * Footer markup
 *
 * @package WishListMember/UI
 */

?>
    <div class="clipboard-template d-none">
        <div class="row">
            <div class="col-md-12">
                <div class="clipboard-content well well-sm text-center" tabindex="0" style="white-space: pre; max-height:150px; overflow-x: auto;"></div>
            </div>
            <div class="col-md-12">
                <p class="text-center instruction"><?php echo esc_html(wishlistmember_instance()->copy_command); ?></p>
            </div>
        </div>
    </div>
    <?php do_action('wishlistmember_ui_footer_scripts'); ?>
    </div><!-- .wlm3body -->
</div><!-- .wlm3wrapper -->
