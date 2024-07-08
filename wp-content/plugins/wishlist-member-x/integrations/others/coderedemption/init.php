<?php

$class = '\WishListMember\Integrations\Others\CodeRedemption';

// Admin interface actions.
add_action('wp_ajax_wlm_coderedemption_save_campaign', [$class, 'save_campaign']);
add_action('wp_ajax_wlm_coderedemption_delete_campaign', [$class, 'delete_campaign']);
add_action('wp_ajax_wlm_coderedemption_generate_codes', [$class, 'generate_codes']);
add_action('wp_ajax_wlm_coderedemption_search_codes', [$class, 'search_codes']);
add_action('wp_ajax_wlm_coderedemption_import_codes', [$class, 'import_codes']);
add_action('wp_ajax_wlm_coderedemption_export_codes', [$class, 'export_codes']);
add_action('wp_ajax_wlm_coderedemption_delete_code', [$class, 'delete_code']);
add_action('wp_ajax_wlm_coderedemption_cancel_code', [$class, 'cancel_code']);
add_action('wp_ajax_wlm_coderedemption_uncancel_code', [$class, 'uncancel_code']);
add_filter('wishlistmember_integration_shortcodes', [$class, 'add_shortcode_to_manifest']);
add_filter('wlm_after_login_redirect', [$class, 'login_redirect'], 99999);

// Claim form.
add_shortcode('wlm_coderedemption', [$class, 'shortcode_coderedemption']);
add_action('wp_loaded', [$class, 'claim_code_from_form']);

// Create table.
add_action(
    'wp_loaded',
    function () {
        $settings = wishlistmember_instance()->get_option('coderedemption_settings');
        if (wishlistmember_instance()->version != wlm_arrval($settings, 'table_version')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            $table     = wishlistmember_instance()->table_prefix . 'coderedemption';
            $structure = "CREATE TABLE $table (
      ID bigint(20) NOT NULL AUTO_INCREMENT,
      campaign_id bigint(20) NOT NULL,
      code varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
      status tinyint(4) NOT NULL DEFAULT 0,
      claimed datetime DEFAULT NULL,
      cancelled datetime DEFAULT NULL,
      user_id bigint(20) DEFAULT NULL,
      PRIMARY KEY  (ID),
      UNIQUE KEY campaign_id_code (campaign_id,code),
      KEY campaign_id (campaign_id),
      KEY status (status),
      KEY user_id (user_id)
    ) {$charset_collate};";
            dbDelta($structure);
            wishlistmember_instance()->table_names->$table = $table;
            if (! is_array($settings)) {
                $settings = [];
            }
            $settings['table_version'] = wishlistmember_instance()->version;
            wishlistmember_instance()->save_option('coderedemption_settings', $settings);
        }
    }
);
