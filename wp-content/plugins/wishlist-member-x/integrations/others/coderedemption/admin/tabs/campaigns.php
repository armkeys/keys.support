<div id="coderedemption-lists-table" class="table-wrapper -no-shadow">
  <table class="table table-striped">
        <colgroup>
            <col width="25%">
            <col>
            <col width="100">
            <col width="100">
            <col width="80">
        </colgroup>
    <tbody></tbody>
    <thead>
            <tr>
                <th><?php esc_html_e('Campaign Name', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Displays the Name of the Campaign. This will not appear on the live site.', 'wishlist-member')); ?></th>
                <th><?php esc_html_e('Description', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Displays the Description for the Campaign. This will not appear on the live site.', 'wishlist-member')); ?></th>
                <th class="text-center"><?php esc_html_e('Available', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Displays the total number of available codes in the Campaign.', 'wishlist-member')); ?></th>
                <th class="text-center"><?php esc_html_e('Status', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Displays if the Campaign is Active/Inactive.', 'wishlist-member')); ?></th>
                <th></th>
            </tr>
        </thead>
    <tfoot>
      <tr class="d-none">
        <td colspan="10">
          <p><?php esc_html_e('No code redemption campaigns found.', 'wishlist-member'); ?></p>
        </td>
      </tr>
      <tr>
        <td colspan="10" class="text-center pt-3">
          <a href="#coderedemption-campaign-modal" data-toggle="modal" data-campaign-id="new" class="btn -success -condensed"><i class="wlm-icons">add</i><?php esc_html_e('Add Campaign', 'wishlist-member'); ?></a>
        </td>
      </tr>
    </tfoot>
    </table>
</div>
<br>
<script type="text/template" id="coderedemption-lists-template">
    <tbody>
        {% _.each(data.campaigns, function(campaign) { %}
        <tr id="coderedemption-{%- campaign.id %}" class="button-hover">
            <td><a href="#coderedemption-campaign-modal" data-toggle="modal" data-campaign-id="{%- campaign.id %}">{%= campaign.name %}</a></td>
      <td class="campaign-description"><span title="{%- campaign.description %}">{%- campaign.description %}</span></td>
      <td class="text-center">
        <span title="<?php esc_html_e('Available:', 'wishlist-member'); ?> {%- Number(campaign.code_available) %}&#10;<?php esc_html_e('Redeemed:', 'wishlist-member'); ?> {%- Number(campaign.code_redeemed) %}&#10;<?php esc_html_e('Cancelled:', 'wishlist-member'); ?> {%- Number(campaign.code_cancelled) %}&#10;&#10;<?php esc_html_e('Total:', 'wishlist-member'); ?> {%- Number(campaign.code_total) %} ">{%- Number(campaign.code_available).toLocaleString() %}</span>
      </td>
            <td class="text-center">
                <a href="#" data-campaign-id="{%- campaign.id %}" class="toggle-coderedemption-status {%- campaign.status == '1' ? '' : 'coderedemption-inactive' %}"><i class="wlm-icons md-24">check_circle</i></a>
            </td>
            <td class="text-right" style="vertical-align: middle">
                <div class="btn-group-action">
                    <a href="#coderedemption-campaign-modal" data-toggle="modal" data-campaign-id="{%- campaign.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
          <a href="#" data-campaign-id="{%- campaign.id %}" title="<?php esc_html_e('Delete Campaign', 'wishlist-member'); ?>" class="btn -icon-only -del-btn">
                    <i class="wlm-icons md-24" title="<?php esc_html_e('Delete Campaign', 'wishlist-member'); ?>">delete</i>
                </a>
                </div>
            </td>
        </tr>
        {% }); %}
    </tbody>
</script>
