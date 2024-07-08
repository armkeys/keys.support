<?php

/**
 * Setup > Levels Admin page
 *
 * @package WishListMember/UI
 */

// Lets run sync every 5 mins.
// This will make sure that the membership count is up to date.
if (false === get_transient('wlm_membership_count_sync_done')) {
    $this->sync_membership_count();
    set_transient('wlm_membership_count_sync_done', 1, DAY_IN_SECONDS);
}

// Load membership levels.
$wpm_levels = $this->get_option('wpm_levels');

$level_edit_tabs = wlm_or(apply_filters('wishlistmember_level_edit_tabs', []), []);

$x = wlm_get_data()['howmany'];
if ($x) {
    $this->save_option('levels-pagination-size', $x);
}
$pagination_size = wlm_or($this->get_option('levels-pagination-size'), 25);

$pagination = new \WishListMember\Pagination(count($wpm_levels), $pagination_size, wlm_get_data()['offset'], 'offset', sprintf('%s&search=%s&filter=%s', admin_url('admin.php?page=' . $this->menu_id . '&wl=setup/levels/'), wlm_get_data()['search'], wlm_get_data()['filter']), $this->pagination_items);

$stats        = [];
$members_link = 'admin.php?page=WishListMember&wl=members/manage';

// Load roles.
$_roles = $GLOBALS['wp_roles']->roles;
$caps   = [];
foreach ((array) $_roles as $key => $_role) {
    if (( isset($_role['capabilities']['level_10']) && $_role['capabilities']['level_10'] ) || ( isset($_role['capabilities']['level_9']) && $_role['capabilities']['level_9'] ) || ( isset($_role['capabilities']['level_8']) && $_role['capabilities']['level_8'] )) {
        unset($_roles[ $key ]);
    } else {
        list($_roles[ $key ]) = explode('|', $_role['name']);
        $caps[ $key ]         = count($_role['capabilities']);
    }
}
array_multisort($caps, SORT_ASC, $_roles);
// Supply options for role select.
$js_roles = [];
foreach ($_roles as $k => $v) {
    $js_roles[] = [
        'value' => $k,
        'text'  => $v,
    ];
}

// Supply options for add to and remove from select.
$js_levels = [];
foreach ($wpm_levels as $k => $v) {
    $js_levels[]            = [
        'value' => $k,
        'text'  => $v['name'],
        'id'    => $k,
        'name'  => $v['name'],
    ];
    $wpm_levels[ $k ]['id'] = $k;

    foreach (['removeFromLevel', 'addToLevel', 'cancelFromLevel', 'cancel_removeFromLevel', 'cancel_addToLevel', 'cancel_cancelFromLevel', 'remove_removeFromLevel', 'remove_addToLevel', 'remove_cancelFromLevel'] as $option) {
        $wpm_levels[ $k ][ $option ] = wlm_arrval($wpm_levels, $k, $option) ? wlm_arrval('lastresult') : [];
    }
}

$shortcodes     = $this->wlmshortcode->shortcodes;
$wlm_shortcodes = [
    [
        'name'    => 'Merge Codes',
        'options' => [
            [
                'value' => '',
                'text'  => '',
            ],
        ],
    ],
];

$shortcode_count = count($shortcodes);
for ($i = 0; $i < $shortcode_count; $i += 3) {
    $wlm_shortcodes[0]['options'][] = [
        'value' => sprintf('[%s]', $shortcodes[ $i ][0]),
        'text'  => $shortcodes[ $i + 1 ],
    ];
}

$custom_user_data = $this->wlmshortcode->custom_user_data;
if ($custom_user_data) {
    $wlm_shortcodes[] = [
        'name'    => 'Custom Registration Fields',
        'options' => [],
    ];
    foreach ($custom_user_data as $c) {
        $wlm_shortcodes[0]['options'][] = [
            'value' => sprintf('[wlm_custom %s]', $c),
            'text'  => $c,
        ];
    }
}

$wlm_sender_default = [
    'name'  => $this->get_option('email_sender_name'),
    'email' => $this->get_option('email_sender_address'),
];

$registration_forms = $this->get_custom_reg_forms();
foreach ($registration_forms as $_id => &$f) {
    $f = [
        'value' => $_id,
        'text'  => $f->option_value['form_name'],
    ];
}
unset($f);
array_unshift(
    $registration_forms,
    [
        'value' => '',
        'text'  => 'Default Registration Form',
    ]
);

// Supply options for after reg and after login select.
$_pages   = get_pages('exclude=' . implode(',', $this->exclude_pages([], true)));
$js_pages = [
    [
        'value' => '',
        'text'  => 'WordPress Home Page',
    ],
];
if ($_pages) {
    foreach ($_pages as $_page) {
        $js_pages[] = [
            'value' => $_page->ID,
            'text'  => $_page->post_title,
        ];
    }
}

$recaptcha_settings = [
    'recaptcha_public_key'  => wlm_or($this->get_option('recaptcha_public_key'), ''),
    'recaptcha_private_key' => wlm_or($this->get_option('recaptcha_private_key'), ''),
];

$inline_script = <<<SCRIPT
var pagination = %s;
var js_roles = %s;
var js_levels = %s;
var wpm_levels = %s;
var level_stats = %s;
var members_link = %s;
var wlm_shortcodes = %s;
var wlm_sender_default = %s;
var wlm_level_edit_tabs = %s
var wpm_regforms = %s;
var wpm_regform_defaults = %s;
var js_pages = %s;
var recaptcha_settings = %s;
SCRIPT;

wp_add_inline_script(
    'wp-codemirror',
    sprintf(
        $inline_script,
        wp_json_encode($pagination),
        wp_json_encode($js_roles),
        wp_json_encode($js_levels),
        wp_json_encode($wpm_levels),
        wp_json_encode($stats),
        wp_json_encode($members_link),
        wp_json_encode($wlm_shortcodes),
        wp_json_encode($wlm_sender_default),
        wp_json_encode(array_keys($level_edit_tabs)),
        wp_json_encode(array_values($registration_forms)),
        wp_json_encode(preg_replace('/<script.+?<\/script>/i', '', $this->get_legacy_registration_form('-----dummy-----', '', true))),
        wp_json_encode($js_pages),
        wp_json_encode($recaptcha_settings)
    ),
    'before'
);

wlm_print_script('wp-codemirror');
wlm_print_style('wp-codemirror');

$modal_footer = <<<STRING
	<button class="btn -bare modal-cancel">
		<span>Close</span>
	</button>
	<button class="modal-save-and-continue btn -primary">
		<i class="wlm-icons">save</i>
		<span>Save</span>
	</button>
	&nbsp;
	<button class="modal-save-and-close btn -success">
		<i class="wlm-icons">save</i>
		<span>Save &amp; Close</span>
	</button>
STRING;

$tab_footer = <<<STRING
	<button href="#" class="btn -primary done">
		<i class="wlm-icons">levels_icon</i>
		<span>Return to Levels</span>
	</button>
STRING;

require_once 'levels/list.php';
require_once 'levels/edit.php';
?>
<!-- Modal 01 -->

<!-- Modal 02 (Email Notifications) -->

<style type="text/css">
    #email-notification-settings .modal-body .-holder {
        display: none;
    }
    #email-notification-settings .modal-body.cancel .-holder.cancel,
    #email-notification-settings .modal-body.uncancel .-holder.uncancel,
    #email-notification-settings .modal-body.newuser .-holder.newuser,
    #email-notification-settings .modal-body.requireemailconfirmation .-holder.requireemailconfirmation,
    #email-notification-settings .modal-body.requireadminapproval-free .-holder.requireadminapproval-free,
    #email-notification-settings .modal-body.requireadminapproval-paid .-holder.requireadminapproval-paid,
    #email-notification-settings .modal-body.incomplete .-holder.incomplete,
    #email-notification-settings .modal-body.expiring .-holder.expiring {
        display: block;
    }

    #custom-redirects .modal-body .-holder {
        display: none;
    }
    #custom-redirects .modal-body.afterreg-redirect .-holder.afterreg-redirect,
    #custom-redirects .modal-body.login-redirect .-holder.login-redirect,
    #custom-redirects .modal-body.logout-redirect .-holder.logout-redirect {
        display: block;
    }

    .shortcode_inserter {
        margin: 0;
        padding: 0;
        min-height: auto;
    }

    .CodeMirror { border: 1px solid #ddd; }
    .CodeMirror pre { padding-left: 8px; line-height: 1.25; }

</style>
