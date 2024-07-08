<div class="form">
    <div class="activecampaign-fields-table table-wrapper" style="max-width:540px">
        <table class="table table-striped">
            <colgroup>
                <col width="50%">
            </colgroup>
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('Field', 'wishlist-member'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Personalization Tag', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('Personalization tags can be found in your ActiveCampaign account under Lists > Manage Fields.', 'wishlist-member'), 'md'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (wishlistmember_instance()->get_custom_fields() as $key => $field) : ?>
                    <?php
                    if (in_array($key, ['firstname', 'lastname'])) {
                        continue;
                    }
                    ?>
                <tr>
                    <td><?php echo esc_html(preg_replace('/:+$/', '', $field['label'])); ?></td>
                    <td><input type="text" name="fields[<?php echo esc_attr(explode('[', $key)[0]); ?>]" class="form-control"></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td><?php _e('Membership Level', 'wishlist-member'); ?></td>
                    <td><input type="text" name="fields[__level__]" class="form-control"></td>
                </tr>
                <tr>
                    <td><?php _e('Level Registration Date', 'wishlist-member'); ?></td>
                    <td><input type="text" name="fields[__regdate__]" class="form-control"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <p>
        <button class="btn -primary save-button" type="button"><?php esc_html_e('Save Custom Fields Map', 'wishlist-member'); ?></button>
    </p>
</div>
