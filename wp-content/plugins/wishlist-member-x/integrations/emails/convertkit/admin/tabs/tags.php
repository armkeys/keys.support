<p>
    <?php esc_html_e('ConvertKit Tags can be set to apply an action (Add to Level, Remove from Level, Cancel from Level, Uncancel from Level) in WishList Member by selecting a Tag or creating a new Tag below.', 'wishlist-member'); ?>
</p>
<div id="convertkit-tag-table" class="table-wrapper">
    <table class="table table-striped">
        <tbody />
        <thead>
            <tr>
                <th>
                    <?php esc_html_e('Tags', 'wishlist-member'); ?>
                    <?php $this->tooltip(__('The name of the Tag. Click to edit the actions for the Tag. These include options like Add to Level, Remove from Level, Cancel from Level, Uncancel from Level in WishList Member.', 'wishlist-member'), 'lg'); ?>
                </th>
                <th></th>
                <th width="80px"></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="10">
                    <p><?php esc_html_e('No tag actions found.', 'wishlist-member'); ?></p>
                </td>
            </tr>
            <tr>
                <td colspan="10" class="text-center pt-3">
                    <a href="#convertkit-tag-modal" data-toggle="modal" data-tag-id="new" class="btn -success -condensed"><i class="wlm-icons">add</i><?php esc_html_e('Add Tag Action', 'wishlist-member'); ?></a>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<script type="text/template" id="convertkit-tag-template">
    {% _.each(data.tag_ids || [], function(tag_id) { %}
        <tr class="button-hover">
            <td><a href="#" data-toggle="modal" data-target="#convertkit-tag-modal" data-tag-id="{%- tag_id %}">{%= data.tags[tag_id].text %}</a></td>
            <td align="right">
                {% if( data.tag_settings[tag_id].add.webhook_error) { %}
                    <div>
                        <span class="tag-add-webhook-error text-danger"><?php esc_html_e('Error creating webhook for when this tag is applied. Try re-saving to resolve.', 'wishlist-member'); ?> ({%- data.tag_settings[tag_id].add.webhook_error %})</span>
                    </div>
                {% } %}
                {% if( data.tag_settings[tag_id].remove.webhook_error) { %}
                    <div>
                        <span class="tag-remove-webhook-error text-danger"><?php esc_html_e('Error creating webhook for when this tag is removed. Try re-saving to resolve.', 'wishlist-member'); ?> ({%- data.tag_settings[tag_id].remove.webhook_error %})</span>
                    </div>
                {% } %}
            </td>
            <td class="text-right">
                <div class="btn-group-action">
                    <a href="#" data-toggle="modal" data-target="#convertkit-tag-modal" data-tag-id="{%- tag_id %}" class="btn -tag-btn" title="<?php esc_attr_e('Edit Tag Actions', 'wishlist-member'); ?>"><i class="wlm-icons md-24">edit</i></a>
                    <a href="#" data-tag-id="{%- tag_id %}" class="btn -del-tag-btn" title="<?php esc_attr_e('Delete Tag Actions', 'wishlist-member'); ?>"><i class="wlm-icons md-24">delete</i></a>
                </div>
            </td>
        </tr>
    {% }); %}
</script>
