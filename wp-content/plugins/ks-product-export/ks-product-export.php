<?php
/*
Plugin Name: Download Product Export & Import
Description: Export and Import custom post type products with WPML languages into CSV.
Version: 1.0
Author: ARM
*/

// Hook to add admin menu
add_action('admin_menu', 'cpe_add_admin_menu');

function cpe_add_admin_menu() {
    add_menu_page('Download Product Export & Import', 'Product Export/Import', 'manage_options', 'download-product-export-import', 'cpe_admin_page', 'dashicons-download', 20);
}

// Hook to handle the CSV export before any HTML output
add_action('admin_init', 'cpe_handle_export');

function cpe_admin_page() {
    ?>
    <div class="wrap">
        <h1>Export/Import Products</h1>
        <hr>
        <form method="post" action="" enctype="multipart/form-data" style="padding:20px 0 20px 0;">
            <input type="hidden" name="cpe_export_csv_nonce" value="<?php echo wp_create_nonce('cpe_export_csv_nonce'); ?>">
            <input type="submit" name="cpe_export_csv" class="button button-primary" value="Export to CSV">
        </form>
        <hr>
        <h2>Import Products</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="cpe_import_csv_nonce" value="<?php echo wp_create_nonce('cpe_import_csv_nonce'); ?>">
            <input type="file" name="cpe_import_file" accept=".csv">
            <input type="submit" name="cpe_import_csv" class="button button-primary" value="Import from CSV">
        </form>
    </div>
    <?php

    if (isset($_POST['cpe_export_csv'])) {
        if (!isset($_POST['cpe_export_csv_nonce']) || !wp_verify_nonce($_POST['cpe_export_csv_nonce'], 'cpe_export_csv_nonce')) {
            return;
        }
        cpe_export_csv();
    }

    if (isset($_POST['cpe_import_csv'])) {
        if (!isset($_POST['cpe_import_csv_nonce']) || !wp_verify_nonce($_POST['cpe_import_csv_nonce'], 'cpe_import_csv_nonce')) {
            return;
        }
        cpe_import_csv();
    }
}

function cpe_handle_export() {
    if (isset($_POST['cpe_export_csv'])) {
        if (!isset($_POST['cpe_export_csv_nonce']) || !wp_verify_nonce($_POST['cpe_export_csv_nonce'], 'cpe_export_csv_nonce')) {
            return;
        }
        cpe_export_csv();
    }
}

function cpe_export_csv() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $languages = ['en', 'de', 'fr', 'it', 'pt-pt', 'es', 'el', 'be', 'sk', 'cz'];

    $csv_data = [];
    $csv_data[] = ['Post Title', 'SKU', 'Download Link 32', 'Download Link 64', 'Language'];

    foreach ($languages as $lang) {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'suppress_filters' => false,
            'lang' => $lang,
        ];

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $sku = get_field('sku', $post->ID);
            $download_link_32 = get_field('download_link_32', $post->ID);
            $download_link_64 = get_field('download_link_64', $post->ID);

            $csv_data[] = [
                $post->post_title,
                $sku,
                $download_link_32,
                $download_link_64,
                $lang
            ];
        }
    }

    // Set headers to download the file rather than displaying it
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=products.csv');

    // Open the output stream
    $output = fopen('php://output', 'w');

    // Output the column headings
    foreach ($csv_data as $row) {
        fputcsv($output, $row);
    }

    // Close the output stream
    fclose($output);
    exit();
}

function cpe_import_csv() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!isset($_FILES['cpe_import_file']) || $_FILES['cpe_import_file']['error'] != UPLOAD_ERR_OK) {
        echo '<div class="notice notice-error"><p>File upload error.</p></div>';
        return;
    }

    $file = $_FILES['cpe_import_file']['tmp_name'];
    $csv_data = array_map('str_getcsv', file($file));
    $headers = array_shift($csv_data);

    foreach ($csv_data as $row) {
        $data = array_combine($headers, $row);

        $args = [
            'post_type' => 'product',
            'name' => sanitize_title($data['Post Title']),
            'posts_per_page' => 1,
            'suppress_filters' => false,
            'lang' => $data['Language'],
        ];

        $posts = get_posts($args);

        if (!empty($posts)) {
            $post_id = $posts[0]->ID;
        } else {
            $post_id = wp_insert_post([
                'post_title' => $data['Post Title'],
                'post_type' => 'product',
                'post_status' => 'publish',
                'lang' => $data['Language']
            ]);
        }

        update_field('sku', $data['SKU'], $post_id);
        update_field('download_link_32', $data['Download Link 32'], $post_id);
        update_field('download_link_64', $data['Download Link 64'], $post_id);
    }

    echo '<div class="notice notice-success"><p>Products imported successfully.</p></div>';
}
