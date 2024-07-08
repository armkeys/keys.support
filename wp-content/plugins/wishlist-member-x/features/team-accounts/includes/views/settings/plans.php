<?php
$access_options     = include WLM_PLUGIN_DIR . '/helpers/access-select-options.php';
$payperpost_options = array_diff_key($access_options, ['__levels__' => '']);
array_walk_recursive(
    $payperpost_options,
    function (&$value, $key) {
        if ('id' === $key) {
            $value = (int) str_replace('payperpost-', '', $value);
        }
    }
);
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col">
            <div class="table-wrapper table-responsive">
                <table class="table table-striped table-condensed" id="teams-table">
                    <colgroup>
                        <col width="25%">
                        <col width="25%">
                        <col width="25%">
                        <col>
                        <col width="100">
                    </colgroup>
                    <tbody/>
                    <tfoot>
                        <tr><td colspan="5" class="text-center"><p><?php esc_html_e('No team plans configured.', 'wishlist-member'); ?></p></td></tr>
                    </tfoot>
                    <thead>
                        <tr>
                            <th>
                                <?php esc_html_e('Team Plan', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('The unique Team Name. Click the Team Name to view and edit the individual Team settings', 'wishlist-member')); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Level Triggers', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('A Team Account will be created when a user is added to any of the set Level(s).', 'wishlist-member')); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Access', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('The Access status. Inherited or a list of the Level(s) and/or Pay Per Post(s) a Team Member can access.', 'wishlist-member')); ?>
                            </th>
                            <th class="text-center" style="white-space: nowrap;">
                                <?php esc_html_e('Team Members', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('The total number of Team Members that can be added to the Team.', 'wishlist-member')); ?>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
            <p class="add-team-plan">
                <button class='btn -success -condensed new-team' data-toggle="modal" data-target="#team-accounts-team"><i class="wlm-icons">add</i><span><?php _e('Add Team Plan', 'wishlist-member'); ?></span></button>
            </p>
        </div>
    </div>
</div>
<script type="text/template" id="team-accounts-teams">
    {% _.each(data.teams, function(team) { %}
    <tr class="button-hover">
        <td><a href="#" class="edit-btn" data-toggle="modal" data-target="#team-accounts-team" data-id="{%- team.id %}" title="<?php esc_attr_e('Edit', 'wishlist-member'); ?>">{%- team.name || '' %}</a></td>
        <td>
            {% if(Array.isArray(team.triggers) && team.triggers.length ) { %}
            {%- team.triggers.map(x => ' ' + (x && data.levels[x]) ) %}
            {% } else { %}
            <em><?php esc_html_e('None', 'wishlist-member'); ?></em>
            {% } %}
        </td>
        <td>
            {% if( +team.mirrored_access ) { %}
            <?php esc_html_e('Inherited', 'wishlist-member'); ?>
            <br>
            <em>
                {%- team.exclude_levels.length ? data.access_text( team.exclude_levels.length, 'level' ) : '' %}
                {%- team.exclude_levels.length && team.exclude_payperposts.length ? wp.i18n.__( 'and', 'wishlist-member' ) : '' %}
                {%- team.exclude_payperposts.length ? data.access_text( team.exclude_payperposts.length, 'ppp' ) : '' %}
                {%- team.exclude_levels.length || team.exclude_payperposts.length ? wp.i18n.__( 'excluded', 'wishlist-member' ) : '' %}
            </em>
            {% } else { %}
            {%- data.access_text( team.access_levels.length, 'level' ) %} <?php esc_html_e('and', 'wishlist-member'); ?> {%- data.access_text( team.access_payperposts.length, 'ppp' ) %}
            {% } %}
        </td>
        <td class="text-center">{%- team.default_children || 0 %}</td>
        <td class="text-right" style="overflow:no-wrap">
            <div class="btn-group-action">
                <a href="#" class="btn edit-btn" data-toggle="modal" data-target="#team-accounts-team" data-id="{%- team.id %}" title="<?php esc_attr_e('Edit', 'wishlist-member'); ?>"><span class="wlm-icons md-24 -icon-only">edit</span></a>
                <a href="#" class="btn delete-team" data-id="{%- team.id %}" title="<?php esc_attr_e('Delete', 'wishlist-member'); ?>"><span class="wlm-icons md-24 -icon-only">delete</span></a>
            </div>
        </td>
    </tr>
    {% }); %}
</script>
<script type="text/javascript">
    var teams = <?php echo wp_json_encode(\WishListMember\Features\Team_Accounts\Team_Account::get_all(true)); ?>;
    var levels = 
    <?php
    echo wp_json_encode(
        array_map(
            function ($x) {
                return $x['name'];
            },
            wishlistmember_instance()->get_option('wpm_levels')
        )
    );
    ?>
    ;
</script>
<style>
    #teams-table tbody:not(.no-rows) ~ tfoot,
    #teams-table tbody.no-rows ~ thead {
        display: none;
    }
    #teams-table tbody.no-rows ~ tfoot td {
        border: none;
    }
    p.add-team-plan.no-rows {
        text-align: center;;
    }

</style>

<?php require_once __DIR__ . '/plans/modals/edit.php'; ?>
