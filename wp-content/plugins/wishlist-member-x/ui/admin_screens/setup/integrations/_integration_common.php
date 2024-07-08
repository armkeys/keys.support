<?php

$integration_link = add_query_arg(
    [
        'page' => $this->menu_id,
        'wl'   => $wl_path,
    ],
    admin_url('admin.php')
);

$requested_integration = $virtual_path ? array_pop($virtual_path) : '*';

$wpm_levels = $this->get_option('wpm_levels');
foreach ($wpm_levels as $level_id => &$level) {
    $level['id'] = $level_id;
}
unset($level);
$payperposts = array_filter($this->get_payperposts(['post_title', 'post_type']));
$all_levels  = array_merge(
    [
        '__levels__' => $wpm_levels,
    ],
    $payperposts
);

$post_types = get_post_types('', 'objects');
foreach ($post_types as &$ptype) {
    $ptype = (object) ['labels' => (object) ['name' => sprintf('Pay Per Post: %s', $ptype->labels->name)]];
}
unset($ptype);
$post_types['__levels__'] = (object) ['labels' => (object) ['name' => 'Membership Levels']];

if (preg_match('#^setup/integrations/payment_provider/#', $wl)) {
    /**
     * Filters the payment integration levels
     *
     * @param array An array of levels and pay per posts
     */
    $all_levels = apply_filters('wishlistmember_payment_integration_levels', $all_levels);
    /**
     * Filters the labels for the payment integration levels and pay per posts
     *
     * @param array $post_types Array of labels
     */
    $post_types = apply_filters('wishlistmember_payment_integration_levels_labels', $post_types);
}

$all_levels_select_options = [];
foreach ($all_levels as $l_type => $levels) {
    $options = [];
    foreach ($levels as $level) {
        $options[] = [
            'text'  => $level['name'],
            'name'  => $level['name'],
            'value' => $level['id'],
            'id'    => $level['id'],
        ];
    }
    if ($options) {
        $all_levels_select_options[] = [
            'name'     => $post_types[ $l_type ]->labels->name,
            'text'     => $post_types[ $l_type ]->labels->name,
            'options'  => $options,
            'children' => $options,
        ];
    }
}

$wpm_scregister = $this->make_thankyou_url('');

?>
<script type="text/javascript">
    var wpm_scregister = <?php echo wp_json_encode($wpm_scregister); ?> || '';
    var all_levels = <?php echo wp_json_encode($all_levels); ?> || {};
    var post_types = <?php echo wp_json_encode($post_types); ?> || {};
    var all_levels_select_options = <?php echo wp_json_encode($all_levels_select_options); ?> || {};
</script>
<?php

function thirdparty_integration_data($id, $data)
{
    echo "<script type='text/javascript'>\n";
    if (empty($data)) {
        $data = new \stdClass();
    }
    printf("WLM3ThirdPartyIntegration['%s'] = %s;\n", esc_js($id), wp_json_encode($data));
    echo "</script>\n";
}

$show_legacy_integrations = $this->get_option('show_legacy_integrations');

$tab_footer = <<<STRING
	<a href="$integration_link" class="btn -primary done -return-button">
		<i class="wlm-icons">date_range</i>
		<span>Return to Integrations</span>
	</a>
STRING;

$api_status_markup = <<<STRING
<div class="col api-status pt-2">
	<div class="text-warning"><p><em>Checking...</em></p></div>
</div>
STRING;

?>
<template id="thirdparty-provider-toggle">
    <label class="switch-light switch-wlm">
        <input type="checkbox" value="1" name="toggle-thirdparty-provider" skip-save="1">       
        <span>
            <span>
                <i class="wlm-icons md-18 ico-check">
                check</i>
            </span>
            <span>
                <i class="wlm-icons md-18 ico-close">
                close</i>
            </span>
            <a>
            </a>
        </span>
    </label>
</template>

<!-- This has to be here -->
<script type="text/javascript">
    var integration_save_validators = [];
    var integration_takeover_save = [];
    var integration_modal_save = [];
    var integration_before_open = {};
    var integration_after_open = {};
    var wlm3_integration_config;
    var WLM3ThirdPartyIntegration = {};
    var all_levels_flat = {};
    $.each(all_levels, function(a, b) {
        $.each(b, function(c, d) {
            d.__type__ = a;
            all_levels_flat[d.id] = d;
        });
    });
    var requested_integration = <?php echo json_encode($requested_integration); ?>;
    $(function() {
        $.getScript(WLM3VARS.pluginurl + '/ui/js/admin_js/setup/integrations/_integration_common.js?build=3.26.9');
    });
</script>

<!-- todo: move this somewhere else -->
<style type="text/css">
    .-has-settings .close-buttons,
    .-no-settings .save-buttons {
        display: none;
    }

    .-is-active .-inactive,
    .-is-inactive .-active {
        display: none;
    }

    .thirdparty-provider-container {
        position: relative;
    }

    .thirdparty-provider-container .save-keys span {
        display: none;
    }
    .thirdparty-provider-container .save-keys.disabled .-processing,
    .thirdparty-provider-container:not(.api-fail) .save-keys:not(.disabled) .-connected,
    .thirdparty-provider-container.api-fail .save-keys:not(.disabled) .-disconnected {
        display: inline;
    }
    .thirdparty-provider-container.api-fail .api-required {
        display: none;
    }
    .thirdparty-provider-container .api-status p::before {
        content: "API Status : ";
    }

    #all-integrations-parent .collapsing {
        transition: none !important;
    }
    #all-integrations-parent .collapsing:not(.show) {
        display: none;
    }

    #all-integrations-parent td.text-center .switch {
        display: inline-block;
    }
    #all-integrations-parent td.text-center .switch-body {
        display: none;
    }
    .integration-video {
      position: relative;
      padding-bottom: 56.25%;
      height: 0;
      overflow: hidden;
      }
      .integration-video iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>
