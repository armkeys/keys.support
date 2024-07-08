<?php

namespace WPDeveloper\BetterDocs\Utils;

class Helper extends Base {

    public static function get_plugins( $plugin_basename = null ) {
        if ( ! function_exists( 'get_plugins' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        return $plugin_basename == null ? $plugins : isset( $plugins[$plugin_basename] );
    }

    public static function is_plugin_active( $plugin_basename ) {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active( $plugin_basename );
    }

    public static function get_tax( $tax = '' ) {
        global $wp_query;
        
        if ( is_tax( 'knowledge_base' ) ) {
            $_taxes = $wp_query->tax_query->queried_terms;
            if ( array_key_exists( "doc_category", $_taxes ) ) {
                $tax = 'doc_category';
            } else {
                $tax = 'knowledge_base';
            }
        } elseif ( is_tax( 'doc_category' ) ) {
            $tax = 'doc_category';
        } elseif ( is_tax( 'doc_tag' ) ) {
            $tax = 'doc_tag';
        }

        return $tax;
    }

    public function is_templates() {
        global $wp_query;
        $slug  = betterdocs()->settings->get('encyclopedia_root_slug', 'encyclopedia');

        $tax = $this->get_tax();
        if ( is_post_type_archive( 'docs' ) || $tax === 'knowledge_base' || $tax === 'doc_category' || $tax === 'doc_tag' || is_singular( 'docs' ) || is_tax('glossaries')) {
            return true;
        }

        if(isset($wp_query->query['pagename']) && $wp_query->query['pagename'] === $slug)
        {
            return true;
        }

        return false;
    }

    public function is_el_templates() {
        $_return_val = betterdocs()->editor->get( 'elementor' )->is_templates();

        if ( $_return_val !== null ) {
            return $_return_val;
        }

        $this->is_templates();
    }

    /**
     * Which tab to show.
     *
     * 1. Drag and Drop UI
     * 2. Post List UI
     *
     * * 1. dnd
     * * 2. classic
     *
     * look into views/admin/docs-ui directory to know more.
     *
     * @return string
     */
    public static function admin_tab() {
        $admin_ui = 'grid';
        if ( isset( $_GET['mode'], $_GET['page'] ) && $_GET['page'] === 'betterdocs-admin' && ! empty( $_GET['mode'] ) ) {
            $admin_ui = $_GET['mode'] === 'grid' ? 'grid' : 'list';
        }

        return $admin_ui;
    }

    public static function is_active( $prev, $current, $class = 'active' ) {
        if ( $current == $prev ) {
            return $class;
        }

        return '';
    }

    public function get_users( $args ) {
        $cache_key = 'betterdocs_cache_admin_user_roles';
        $users     = betterdocs()->database->get_cache( $cache_key );

        if ( false === $users ) {
            $users = get_users( $args );
            betterdocs()->database->set_cache( $cache_key, $users );
        }

        return $users;
    }

    /**
     * Normalize Menu Array
     * Menu creator helper
     *
     * @since 2.5.0
     *
     * @param string $title
     * @param string $slug
     * @param string $cap
     * @param array  $callback
     *
     * @return array
     */
    public static function normalize_menu( $title, $slug, $cap = 'edit_docs', $callback = null ) {
        $args = [
            'page_title' => $title,
            'menu_title' => $title,
            'capability' => $cap,
            'menu_slug'  => $slug
        ];

        if ( $callback != null ) {
            $args['callback'] = $callback;
        }

        return $args;
    }

    /**
     * Check if the current theme is a block theme.
     *
     * @since x.x.x
     * @return bool
     */
    public function current_theme_is_fse_theme() {
        if ( function_exists( 'wp_is_block_theme' ) ) {
            return (bool) wp_is_block_theme();
        }
        if ( function_exists( 'gutenberg_is_fse_theme' ) ) {
            return (bool) gutenberg_is_fse_theme();
        }

        return false;
    }

    protected static function is_assoc_array( $array ) {
        return array_keys( $array ) !== range( 0, count( $array ) - 1 );
    }

    public static function merge( &$array1, &$array2 ) {
        $merged = $array1;

        foreach ( $array2 as $key => &$value ) {
            if ( is_array( $value ) && self::is_assoc_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ) {
                $merged[$key] = self::merge( $merged[$key], $value );
            } elseif ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ) {
                $merged[$key] = array_merge( $merged[$key], $value );
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public static function get_custom_excerpt($content, $numOfWords)
    {
        $content = strip_shortcodes($content);
        $content = wp_strip_all_tags($content);
        $words = explode(' ', $content);
        $excerptWords = array_slice($words, 0, $numOfWords);
        $excerpt = implode(' ', $excerptWords);
        if (count($words) > $numOfWords) {
            $excerpt .= '...';
        }
        return $excerpt;
    }


    public static function get_current_letter_docs($current_letter, $limit = '')
    {
        global $wpdb;

        // Check if the encyclopedia_prefix parameter is set

        $encyclopeia_suorce  = betterdocs()->settings->get('encyclopedia_source', 'docs');
        $enable_glossaries  = betterdocs()->settings->get('enable_glossaries', false);

        // if($enable_glossaries && $encyclopeia_suorce === 'glossaries'){
        if($enable_glossaries && $encyclopeia_suorce === 'glossaries'){
            $query = "
                SELECT t.term_id, t.name AS post_title, t.slug as slug, '' AS post_excerpt, CONCAT('" . get_home_url() . "/glossaries/', t.slug) AS permalink, tt.description AS post_content
                FROM {$wpdb->terms} t
                INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                WHERE tt.taxonomy = 'glossaries'
                AND LEFT(t.name, 1) = %s
                ORDER BY t.name ASC
                $limit
            ";
        }
        else{
            $query = "
                SELECT ID, post_title, post_excerpt, guid, post_content
                FROM {$wpdb->posts}
                WHERE post_type = 'docs'
                AND post_status = 'publish'
                AND LEFT(post_title, 1) = %s
                ORDER BY post_date DESC
                $limit
            ";
        }

        $current_letter_docs = $wpdb->get_results($wpdb->prepare($query, $current_letter), ARRAY_A);

        return $current_letter_docs;
    }

    public static function docs_sort_by_letter($limit = 10)
    {
        global $wpdb;
        $letters = range('A', 'Z');
        $docs_by_letter = array();
        $encyclopeia_suorce  = betterdocs()->settings->get('encyclopedia_source', 'docs');
        $enable_glossaries  = betterdocs()->settings->get('enable_glossaries', false);


        foreach ($letters as $letter) {

            $posts = self::get_current_letter_docs($letter, "LIMIT $limit"); 

            if (is_array($posts) && !empty($posts)) {
                foreach ($posts as $post) {
                    $post_data = array(
                        'id'            => isset($post['ID']) ? $post['ID'] : '',
                        'post_title'    => isset($post['post_title']) ? $post['post_title'] : '',
                        'post_excerpt'  => isset($post['post_excerpt']) && !empty($post['post_excerpt']) ? $post['post_excerpt'] : self::get_custom_excerpt(isset($post['post_content']) ? $post['post_content'] : '', 15),
                    );

                    if($enable_glossaries && $encyclopeia_suorce === 'glossaries'){
                        $post_data['permalink'] = isset($post['slug']) ? get_term_link($post['slug'], 'glossaries') : '';
                    }
                    else{
                        $post_data['permalink'] = isset($post['ID']) ? get_the_permalink($post['ID']) : '';
                    }

                    $docs_by_letter[$letter][] = $post_data;
                }
            }
        }

        return $docs_by_letter;
    }

    public static function get_glossaries()
    {
        global $wpdb;
    
        $query = "
            SELECT t.name
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = 'glossaries'
            ORDER BY t.name ASC
        ";
    
        $glossaries = $wpdb->get_col($query);
    
        return $glossaries;
    }
    

 
}
