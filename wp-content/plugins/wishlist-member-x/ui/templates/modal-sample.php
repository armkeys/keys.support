<?php

/**
 * Modal sample usage
 *
 * @package WishListMember/UI
 */

?>
<!-- make sure the modal template is loaded -->

<!-- create your data mark-up -->
<div
    id="id-for-this-markup" 
    data-id="id-for-modal"
    data-label="label-for-modal"
    data-title="Modal Title"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        body of modal
    </div>
    <div class="footer">
        footer of modal
    </div>
</div>

<!-- compile the template together -->
<script>
$(function() {
    var mymodal = new wlm3_modal(
        '#id-for-this-markup', // pointer to mark-up
        function(event) {event.data.modal.close()} // optional save handler
    );
});
</script>

<!-- two ways to open the modal -->
<!-- 1. Javascript -->
<script>
    mymodal.open();
</script>
<!-- 2. HTML -->
<a href="" class="btn -primary -condensed" data-toggle="modal" data-target="#id-for-modal"><?php esc_html_e('Open Modal', 'wishlist-member'); ?></a>
