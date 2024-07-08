<div class="incomplete -holder">
    <div class="row">
        <?php
        printf(
            wp_kses(
                $enable_as_default,
                [
                    'div' => ['class' => true],
                    'template' => ['class' => true],
                ]
            ),
            'incomplete_notification'
        );
        ?>
        <template class="wlm3-form-group">{
            addon_left: 'Subject',
            group_class : '-label-addon mb-2',
            type: 'text',
            name: 'incomplete_subject',
            column : 'col-md-12',
            class: 'email-subject'
        }</template>
        <template class="wlm3-form-group">{
            name: 'incomplete_message',
            type: 'textarea',
            class : 'richtextx',
            column : 'col-md-12',
            group_class : 'mb-2',
        }</template>
        <div class="col-md-12">
            <button class="btn -default -condensed email-reset-button" data-target="incomplete">Reset to Original Message</button>
            <template class="wlm3-form-group">{
                type : 'select',
                column : 'col-md-5 pull-right no-margin no-padding',
                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                group_class : 'shortcode_inserter mb-0',
                style : 'width: 100%',
                options : get_merge_codes([{value : '[incregurl]', text: '<?php echo esc_js(__('Incomplete Registration URL', 'wishlist-member')); ?>'}]),
                grouped: true,
                class : 'insert_text_at_caret',
                'data-target' : '[name=incomplete_message]'
            }</template>
        </div>
    </div>
</div>
