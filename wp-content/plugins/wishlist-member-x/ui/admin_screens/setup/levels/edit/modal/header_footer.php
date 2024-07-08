<div
    id="header-footer-modal"
    data-id="header-footer"
    data-label="header-footer"
    data-title="Header/Footer"
    data-show-default-footer=""
    data-classes="modal-lg"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#set-header" aria-controls="set-header" role="tab" data-toggle="tab">Header</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#set-footer" aria-controls="set-footer" role="tab" data-toggle="tab">Footer</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <!-- AS HTML -->
                    <div role="tabpanel" class="tab-pane" id="set-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">HTML Code to insert ABOVE the Registration Form<?php $this->tooltip(__('Any HTML inserted into this area will appear ABOVE the Registration Form for the selected Membership Level.', 'wishlist-member')); ?></label>
                                    <textarea name="regform_before" id="modal-regform-before" cols="30" rows="18" class="form-control" style="height:300px;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- AS Text -->
                    <div role="tabpanel" class="tab-pane" id="set-footer">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">
                                        HTML Code to insert BELOW the Registration Form
                                        <?php $this->tooltip(__('Any HTML inserted into this area will appear BELOW the Registration Form for the selected Membership Level.', 'wishlist-member')); ?>
                                    </label>
                                    <textarea name="regform_after" id="modal-regform-after" cols="30" rows="18" class="form-control" style="height:300px;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <?php echo wp_kses_post($modal_footer); ?>
    </div>
</div>
