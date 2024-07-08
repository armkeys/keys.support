<div
    id="purge-login-ip-markup" 
    data-id="purge-login-ip-modal"
    data-label="purge-login-ip-modal"
    data-title="Purge Login IP Address Data"
    data-classes="modal-md"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col">
                <p><?php esc_html_e('Would you like to purge all member IP addresses?', 'wishlist-member'); ?></p>
            </div>
        </div>
    </div>
    <div class="footer">
        <button class="save-button btn -danger">
            <i class="wlm-icons">delete</i>
            <span><?php esc_html_e('Purge', 'wishlist-member'); ?></span>
        </button>       
        <button class="btn -bare save-button modal-cancel">
            <span><?php esc_html_e('Cancel', 'wishlist-member'); ?></span>
        </button>
    </div>
</div>
