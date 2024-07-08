<style>
/* copy text */
.wlm-shortcodes-dropdown .shortcode-creator-preview:not(:focus) ~ small {
    display: none;
}
/* flyout styling */
.dropright {
    display: block;
}
.dropright a:after {
    float: right;
    margin-top: 5px;
    margin-right: -30px;
}
.dropright:hover > .dropdown-menu {
    display: block;
    margin-top: calc( -.5rem + -1px );
}
/* maintain background color for flyout parent menu on hover */
.btn-group .btn-group:hover {
    background: rgb(248, 249, 250);
}
/* don't change the background color for submenu parents */
a.dropdown-item.dropdown-toggle {
    background: none;
    padding-right: 50px;
}

#shortcode-creator-modal .wlm-shortcode-attributes [data-dependency]:not([data-dependency=""]) {
    display: none;
}

a.dropdown-toggle {
    cursor: pointer;
}
</style>

<div
    id="shortcode-creator-markup" 
    data-id="shortcode-creator-modal"
    data-label="shortcode-creator-modal"
    data-title="<?php esc_attr_e('Shortcode Creator', 'wishlist-member'); ?>"
  data-classes="modal-lg"
    style="display:none">
    <div class="body">
    <div class="row">
            <div class="col-12">
                <?php wishlistmember_instance()->wlmshortcode->render_shortcode_menu(); ?>
            </div>
        </div>
        <?php wishlistmember_instance()->wlmshortcode->render_shortcode_attributes_form(); ?>
        <div class="row">
            <div class="col-12">
              <template class="wlm3-form-group">
            {
              type: 'textarea',
                        placeholder: 'Shortcode Preview',
                        readonly: 'readonly',
                        style: 'font-size: 1.2rem',
                        class: 'shortcode-creator-preview',
                        help_block: <?php echo json_encode($this->copy_command); ?>,
            }
          </template>
            </div>
    </div>
    </div>
</div>

<script>

</script>

