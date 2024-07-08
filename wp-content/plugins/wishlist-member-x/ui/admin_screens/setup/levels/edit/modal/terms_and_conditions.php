<div
    id="terms-and-conditions-modal"
    data-id="terms-and-conditions"
    data-label="terms-and-conditions"
    data-title="Terms and Conditions"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">{
                name : 'tos',
                type : 'textarea',
                style : 'min-height: 20em;',
                column : 'col-md-12'
            }</template>
        </div>
    </div>
    <div class="footer">
        <?php echo wp_kses_post($modal_footer); ?>
    </div>
</div>
