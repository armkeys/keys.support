<div id="levels-list" style="display:none">
    <div class="page-header">
        <div class="row">
            <div class="col-md-9 col-sm-9 col-xs-8">
                <h2 class="page-title">
                    <?php esc_html_e('Membership Levels', 'wishlist-member'); ?>
                    <a href="#levels_access-new" class="-new-btn save-button btn -primary -icon-only -success -rounded" target="_parent">
                        <i class="wlm-icons">add</i>
                    </a>
                </h2>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-4">
                <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <?php $pagination->print_html(); ?>
        </div>
        <div class="col-md-12">
            <div class="table-wrapper table-responsive">
                <table id="levels-list-table" class="table table-striped table-condensed d-none">
                    <colgroup>
                        <col width="70">
                        <col>
                        <col width="70">
                        <col width="70">
                        <col width="70">
                        <col width="70">
                        <col width="70">
                        <col width="20">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center stats-cell-th">
                                <i title="Level Order" class="wlm-icons md-24">swap_vert</i>
                                <?php $this->tooltip(__('The Level Order for each level can be used to change the order in which the levels are ranked. The lower the level appears on the list, the higher the level will rank. <br><br>The Level Order will affect which After Login page appears to members who have multiple levels. If a member has multiple levels, they will see the After Login page assigned to the lowest level they can access on the list.', 'wishlist-member'), 'md'); ?>
                            </th>
                            <th><?php esc_html_e('Level Name', 'wishlist-member'); ?></th>
                            <th class="text-center stats-cell-th"><i title="Active" class="wlm-icons md-24">active_icon</i></th>
                            <th class="text-center stats-cell-th"><i title="Cancelled" class="wlm-icons md-24">cancelled_icon</i></th>
                            <th class="text-center stats-cell-th"><i title="Expired" class="wlm-icons md-24">timer_off</i></th>
                            <th class="text-center stats-cell-th"><i title="Needs Approval" class="wlm-icons md-24">needs_approval</i></th>
                            <th class="text-center stats-cell-th"><i title="Unconfirmed" class="wlm-icons md-24">needs_confirm</i></th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8">
                                <div class="text-center">There are no membership levels</div>
                            </td>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>
        <div class="col-12">
            <?php $pagination->print_html(); ?>
        </div>
    </div>
</div>
<script type="text/template" id="levels-list-template">
{% _.each(data.levels, function(level) { %}
{% var levelcount = parseInt('0' + level.count); %}
<tr data-id="{%- level.id %}" data-sort="{%- level.levelOrder %}" data-count="{%- parseInt('0' + levelcount) %}" class="button-hover">

    <td class="text-center level-order text-muted" ></td>
    <td>
        <a class="edit-level -edit-btn" href="admin.php?page=WishListMember&wl=setup/levels&level_id={%- level.id %}#levels_access-{%- level.id %}" target="_parent">{%- level.name %}</a>
    </td>
    <td class="text-center active"><a href="{%= data.link %}&level={%- level.id %}&status=active">...</a></td>
    <td class="text-center cancelled"><a href="{%= data.link %}&level={%- level.id %}&status=cancelled">...</a></td>
    <td class="text-center expired"><a href="{%= data.link %}&level={%- level.id %}&status=expired">...</a></td>
    <td class="text-center forapproval"><a href="{%= data.link %}&level={%- level.id %}&status=forapproval">...</a></td>
    <td class="text-center unconfirmed"><a href="{%= data.link %}&level={%- level.id %}&status=unconfirmed">...</a></td>
    <td class="text-center" style="white-space: nowrap">
        <div class="btn-group-action">
            <a href="admin.php?page=WishListMember&wl=setup/levels&level_id={%- level.id %}#levels_access-{%- level.id %}" title="Edit Membership Level" class="btn -icon-only -edit-btn" target="_parent">
                <i class="wlm-icons md-24">edit</i>
            </a>
            <a href="#" title="Duplicate Membership Level" class="btn -icon-only -clone-btn">
                <i class=" wlm-icons md-24">content_copy</i>
            </a>
            {% if(levelcount < 1) { %}
            <a href="#" title="Delete Membership Level" class="btn -icon-only -del-btn">
                <i class="wlm-icons md-24" title="Delete Membership Level">delete</i>
            </a>
            {% } else { %}
            <a href="#" class="btn -icon-only -no-delete -disabled" data-placement="left" title="This membership level cannot be deleted because it has members in it.">
                <i class="wlm-icons md-24">delete</i>
            </a>
            {% } %}
            {% if(_.size(data.levels) > 1) { %}         
            <a href="#" style="cursor: move" title="Move Membership Level" class="btn -icon-only handle">
                <i class="wlm-icons md-24">swap_vert</i>
            </a>
            {% } %}
        </div>
    </td>
</tr>
{% }); %}
</script>
<style type="text/css">
    table#levels-list-table tbody tr.ui-sortable-helper {
        display: table-row;
        border: 1px solid #DBE4EE;
    }

    #levels-list-table tbody:not(:empty)~tfoot {
        display: none;
    }
</style>