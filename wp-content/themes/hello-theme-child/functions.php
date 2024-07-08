<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
include_once(get_stylesheet_directory() . '/inc/careers-helper-functions.php');
include_once(get_stylesheet_directory() . '/inc/betterdocs-helper-functions.php');
include_once(get_stylesheet_directory() . '/inc/installation-guide-helper-functions.php');

function hello_elementor_child_enqueue_scripts() {
	wp_enqueue_style(
        'hello-elementor-child-style', 
		get_stylesheet_directory_uri() . '/style.css', 
        array(), 
        filemtime(get_stylesheet_directory_uri() . '/style.css')
    );

	//Child Theme Custom JS
	wp_register_script( 'hello-theme-custom-script' , get_stylesheet_directory_uri(). '/js/script.js' , array('jquery') , time() );
	wp_localize_script( 'hello-theme-custom-script', 'ajax_object',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce('wp_nonce'),
			)
	);
	wp_enqueue_script ( 'hello-theme-custom-script' );	

	//Bootstrap CSS
	wp_enqueue_style( 'bootstrap-style', get_stylesheet_directory_uri() . '/bootstrap/bootstrap.min.css', array(), time() );
	//Bootstrap JS
		wp_register_script( 'bootstrap-script', get_stylesheet_directory_uri(). '/bootstrap/bootstrap.min.js' , array('jquery') , time() );
		wp_enqueue_script ( 'bootstrap-script' );

	wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array('jquery'), null, true);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 20 );

//Add IT information in User Profile
add_action('show_user_profile', 'it_show_user_profile_extra_fields');
add_action('edit_user_profile', 'it_show_user_profile_extra_fields');
function it_show_user_profile_extra_fields($user) { 
	$current_user = wp_get_current_user();
	$access = get_user_meta($current_user->ID, 'it_personnel', true);

	$disabled = ($access === 'No') ? 'style="display:none;"' : '';
	$all_meta_for_user = get_user_meta($user->ID);

	$it_personnel = isset($all_meta_for_user['it_personnel'][0]) ? $all_meta_for_user['it_personnel'][0] : '';
	$translator = isset($all_meta_for_user['translator'][0]) ? $all_meta_for_user['translator'][0] : '';
	?> 
	<table class="form-table">
		<tr <?php echo $disabled; ?>>
			<th><h3>All Access</h3></th>
			<td>
				<select name="it_personnel" id="it_personnel" <?php echo $disabled; ?>>	
					<option value="<?php echo esc_attr($it_personnel); ?>"><?php echo esc_html($it_personnel); ?></option>
					<option value=""></option>
					<option value="Yes">Yes</option>
					<option value="No">No</option>
				</select>
			</td>
		</tr>
		<tr <?php echo $disabled; ?>>
			<th><h3>WPML Translator</h3></th>
			<td>
				<select name="translator" id="translator" <?php echo $disabled; ?>>	
					<option value="<?php echo esc_attr($translator); ?>"><?php echo esc_html($translator); ?></option>
					<option value=""></option>
					<option value="Yes">Yes</option>
					<option value="No">No</option>
				</select>
			</td>
		</tr>
	</table>
<?php
}

//User Profile update IT info
add_action('personal_options_update', 'it_save_user_profile_extra_fields');
add_action('edit_user_profile_update', 'it_save_user_profile_extra_fields');
function it_save_user_profile_extra_fields($user_id) {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
        return;
    }

    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    $fields = ['it_personnel', 'translator'];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, $_POST[$field]);
        }
    }
}

//WPML Translator
add_action( 'admin_menu', 'wpml_url' );
function wpml_url() {
	$user = wp_get_current_user();

	$user_meta = get_userdata($user->ID);
	$all_meta_for_user = get_user_meta($user->ID);
	if($all_meta_for_user['translator'][0] == 'Yes'){
		add_menu_page( 'WPML Translations', 'WPML Translations', 'read', 'my_slug', '', 'dashicons-text', 1 );
	}
}

add_action( 'admin_menu' , 'wpmlurl_function' );
function wpmlurl_function() {
global $menu;
$menu[1][2] = site_url() . "/wp-admin/admin.php?page=wpml-translation-management%2Fmenu%2Ftranslations-queue.php";
}

//Redirect after Logout
add_action('wp_logout','ps_redirect_after_logout');
function ps_redirect_after_logout(){
         wp_redirect( home_url());
         exit();
}

//Check user if logged in
add_action('wp_ajax_is_user_logged_in','ajax_check_user_logged_in');
add_action('wp_ajax_nopriv_is_user_logged_in','ajax_check_user_logged_in');
function ajax_check_user_logged_in(){
	echo is_use_logged_in()? 'yes':'no';
	die();
}

//Collapse Menu
add_action('admin_footer','custom_admin_js');
function custom_admin_js(){
	$user = wp_get_current_user();

	$user_meta = get_userdata($user->ID);
	$all_meta_for_user = get_user_meta($user->ID);
	if($all_meta_for_user['translator'][0] == 'Yes' || $all_meta_for_user['it_personnel'][0] == 'No'){
	echo "<script type='text/javascript'>document.body.className+=' folded';</script>";
	}
}

//Downloadcenter
add_shortcode('product_category_filter', 'kb_dropdown_categories');
function kb_dropdown_categories($atts) {
    ob_start();

    $lang = apply_filters('wpml_current_language', NULL);

    $labels = [
        'en' => ['category' => 'Category', 'software' => 'Software', 'system' => 'System', 'search_by_sku' => 'Search by Item Number', 'modal_label' => 'Enter Product Item Number', 'submit_label' => 'Submit'],
        'de' => ['category' => 'Kategorie', 'software' => 'Programme', 'system' => 'System', 'search_by_sku' => 'Suche nach Artikelnummer', 'modal_label' => 'Produktartikelnummer eingeben', 'submit_label' => 'Submit'],
        'nl' => ['category' => 'Categorie', 'software' => 'Software', 'system' => 'Systeem', 'search_by_sku' => 'Zoeken op artikelnummer', 'modal_label' => 'Voer het artikelnummer van het product in', 'submit_label' => 'Submit'],
        'fr' => ['category' => 'Catégorie', 'software' => 'Logiciel', 'system' => 'Système', 'search_by_sku' => 'Rechercher par numéro d\'article', 'modal_label' => 'Entrez le numéro d\'article du produit', 'submit_label' => 'Envoyer'],
        'el' => ['category' => 'Κατηγορία', 'software' => 'Λογισμικό', 'system' => 'Σύστημα', 'search_by_sku' => 'Αναζήτηση κατά Κωδικό Προϊόντος(SKU)', 'modal_label' => 'Εισαγάγετε τον αριθμό προϊόντος', 'submit_label' => 'Submit'],
        'it' => ['category' => 'Categoria', 'software' => 'Software', 'system' => 'Sistema', 'search_by_sku' => 'Cerca per numero articolo', 'modal_label' => 'Inserisci il numero dell\'articolo del prodotto', 'submit_label' => 'Submit'],
        'pt-pt' => ['category' => 'Categoria', 'software' => 'Programas', 'system' => 'Sistema', 'search_by_sku' => 'Pesquisar por número de item', 'modal_label' => 'Insira o número do item do produto', 'submit_label' => 'Submit'],
        'es' => ['category' => 'Categoría', 'software' => 'Software', 'system' => 'Sistema', 'search_by_sku' => 'Buscar por número de artículo', 'modal_label' => 'Ingrese el número de artículo del producto', 'submit_label' => 'Submit'],
        'cs' => ['category' => 'Kategorie', 'software' => 'Software', 'system' => 'Systém', 'search_by_sku' => 'Vyhledávání podle čísla položky', 'modal_label' => 'Zadejte číslo položky produktu', 'submit_label' => 'Submit'],
        'tr' => ['category' => 'Kategori', 'software' => 'Yazılım', 'system' => 'Sistem', 'search_by_sku' => 'Ürün Numarasına Göre Ara', 'modal_label' => 'Ürün Öğe Numarasını Girin', 'submit_label' => 'Submit'],
        'sk' => ['category' => 'Kategória', 'software' => 'Softvér', 'system' => 'Systém', 'search_by_sku' => 'Vyhľadávanie podľa čísla položky', 'modal_label' => 'Zadajte číslo položky produktu', 'submit_label' => 'Submit'],
        'be' => ['category' => 'Categorie', 'software' => 'Software', 'system' => 'Systeem', 'search_by_sku' => 'Zoeken op artikelnummer', 'modal_label' => 'Voer het artikelnummer van het product in', 'submit_label' => 'Verzenden']
    ];

    $labels = $labels[$lang] ?? $labels['en'];

    $atts = shortcode_atts(['product_id' => 'none'], $atts, 'product_category_filter');
    $prod_cat_id = get_field("product_category", $atts['product_id']);
    $term = get_term_by('id', $prod_cat_id, 'product_category');
    $post_prod = get_post($atts['product_id']);
    $term_id = $term->term_id ?? 0;

    $category = $term->name ?? $labels['category'];
    $software = $post_prod->post_title ?? $labels['software'];
    $system = $labels['system'];

    ?>

    <div class="form-group">
        <select id="category-filter-download" name="category-filter-download" class="form-control btn-outline-danger">
            <option value=""><?php echo esc_html($category); ?></option>
            <?php
            $taxonomies = get_terms([
                'taxonomy' => 'product_category',
                'hide_empty' => false,
                'meta_key' => 'sort_order',
                'orderby' => 'sort_order',
                'order' => 'ASC'
            ]);

            foreach ($taxonomies as $cat) {
                if ($cat->parent == 0 && !preg_match('/^not-for-sale/', $cat->slug)) {
                    echo '<option class="form-control" value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                }
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <select id="software-filter-download" name="software-filter-download" class="form-control btn-outline-danger">
            <option class="form-control" value="<?php echo esc_attr($post_prod->post_name ?? ''); ?>"><?php echo esc_html($software); ?></option>
            <?php
            global $wpdb;
            $query = $wpdb->get_results($wpdb->prepare(
                "SELECT p.post_name, p.post_title 
                FROM {$wpdb->posts} p 
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE pm.meta_key = 'product_category' AND pm.meta_value = %d 
                AND p.post_status = 'publish' 
                ORDER BY p.menu_order", $term_id
            ), ARRAY_A);

            foreach ($query as $prod) {
                $prod_title = $prod['post_title'];
                $prod_slug = $prod['post_name'];
                $sku = get_post_meta($prod['ID'], 'sku', true);
                $download_sku = get_post_meta($prod['ID'], 'download_sku', true);

                if ($prod_title && ($sku == $download_sku || !preg_match('/(DVD|USB|MAK)/', $prod_title))) {
                    echo '<option class="form-control" value="' . esc_attr($prod_slug) . '">' . esc_html($prod_title) . '</option>';
                }
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <select id="system-filter-download" class="form-control btn-outline-danger system-filter-download">
            <option class="form-control 64-bit-text">64-bit</option>
            <option class="form-control"></option>
            <option class="form-control 32-bit-text">32-bit</option>
            <option class="form-control 64-bit-text">64-bit</option>
        </select>
    </div>

    <div class="col-md-12" style="padding-right:0 !important;padding-left:0 !important;">
        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#searchModalCenter"><?php echo esc_html($labels['search_by_sku']); ?></button>
    </div>

    <div class="modal fade" id="searchModalCenter" tabindex="-1" role="dialog" aria-labelledby="searchModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalCenterTitle"><?php echo esc_html($labels['modal_label']); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input class="form-control" type="text" placeholder="" id="search_by_sku_input">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="search_by_sku_submit" class="close" data-dismiss="modal"><?php echo esc_html($labels['submit_label']); ?></button>
                </div>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

/*Get Software for Download center */
add_action('wp_ajax_get_software', 'get_software');
add_action('wp_ajax_nopriv_get_software', 'get_software');
function get_software() {
    global $wpdb;

    $term_id = intval($_REQUEST['product_term_id']);
    $lang = apply_filters('wpml_current_language', NULL);

    $order_by = in_array($term_id, [278, 294, 408, 293, 295, 296, 297, 298, 657, 409, 680]) ? 'p.post_name' : 'p.menu_order';

    $query = $wpdb->prepare(
        "SELECT p.ID AS prod_id, p.post_title, p.post_name AS prod_slug
         FROM {$wpdb->postmeta} pm
         LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = 'product_category' AND pm.meta_value = %d
         AND p.post_status = 'publish'
         ORDER BY $order_by",
        $term_id
    );

    $results = $wpdb->get_results($query, ARRAY_A);
    $software = [];

    if ($results) {
        foreach ($results as $prod) {
            $prod_id = $prod['prod_id'];
            $prod_title = $prod['post_title'];
            $prod_slug = $prod['prod_slug'];
            $sku = get_post_meta($prod_id, 'sku', true);
            $download_sku = get_post_meta($prod_id, 'download_sku', true);

            if ($prod_title && ($sku == $download_sku || !preg_match('/(DVD|USB|MAK)/', $prod_title))) {
                $software[] = [
                    'prod_id' => $prod_id,
                    'prod_post_title' => $prod_title,
                    'prod_slug' => $prod_slug
                ];
            }
        }
    }

    echo json_encode($software);
    wp_die();
}

/*Get Post name for Download center*/
add_action('wp_ajax_get_post_name', 'get_post_name');
add_action('wp_ajax_nopriv_get_post_name', 'get_post_name');
function get_post_name() {
    global $wpdb;

    // Sanitize the request variables
    $sku = sanitize_text_field($_REQUEST['sku']);
    $lang = sanitize_text_field($_REQUEST['lang']);
    
    $software = [];
    
    // Verify the SKU and language parameters
    if (isset($sku) && isset($lang) && (strlen($sku) == 4 || $sku === "6080-WIN" || strlen($sku) == 6)) {
        
        $sql = $wpdb->prepare(
            "SELECT p.ID AS post_id, p.post_name, t.language_code 
            FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->icl_translations} t ON pm.post_id = t.element_id
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.post_type = 'product' 
            AND pm.meta_key = 'sku' 
            AND pm.meta_value = %s 
            AND t.language_code = %s",
            $sku, $lang
        );

        $results = $wpdb->get_results($sql, ARRAY_A);

        foreach ($results as $prod) {
            $software[] = [
                'prod_id' => $prod['post_id'],
                'prod_lang' => $prod['language_code'],
                'prod_slug' => $prod['post_name']
            ];
        }
    }

    echo json_encode($software);
    wp_die();
}

/*Delete Products for Download center*/
add_action( 'admin_post_delete_ke_products', 'prefix_admin_delete_ke_products' );
add_action( 'admin_post_nopriv_delete_ke_products', 'prefix_admin_delete_ke_products' );
function prefix_admin_delete_ke_products() {
		global $wpdb;
		$sql = $wpdb->prepare("DELETE sup_posts,sup_postmeta FROM  sup_posts INNER JOIN sup_postmeta ON sup_posts.ID = sup_postmeta.post_id WHERE sup_posts.post_type ='product'");
		$wpdb->query($sql);

		wp_redirect(admin_url('edit.php?post_type=product&paged=1'));
}

/*Delete Installation Guide for Download center*/
add_action( 'admin_post_delete_ke_installations', 'prefix_admin_delete_ke_installations' );
add_action( 'admin_post_nopriv_delete_ke_installations', 'prefix_admin_delete_ke_installations' );
function prefix_admin_delete_ke_installations() {
		global $wpdb;
		$sql = $wpdb->prepare("DELETE sup_posts,sup_postmeta FROM  sup_posts INNER JOIN sup_postmeta ON sup_posts.ID = sup_postmeta.post_id WHERE sup_posts.post_type ='installation'");
		$wpdb->query($sql);
		wp_redirect(admin_url('edit.php?post_type=installation&paged=1'));
}

/*Import Products for Download center*/
add_action('admin_post_import_ke_products', 'prefix_admin_import_ke_products');
add_action('admin_post_nopriv_import_ke_products', 'prefix_admin_import_ke_products');
function prefix_admin_import_ke_products() {
    if (isset($_POST["action"])) {
        $filename = $_FILES["file"]["tmp_name"];

        if ($_FILES["file"]["size"] > 0) {
            $file = fopen($filename, "r");
            $x = 0;
            global $wpdb;

            while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
                if ($x > 0) {
                    $category = sanitize_category($emapData[8]);

                    $term_id = get_term_id($category, $emapData[10]);
                    if (!$term_id) continue;

                    $post_content = wpautop(sanitize_textarea_field($emapData[9]));
                    $sort_order = intval($emapData[12]);

                    $args = array(
                        'post_title' => sanitize_text_field($emapData[1]),
                        'post_content' => $post_content,
                        'post_status' => 'publish',
                        'post_author' => get_current_user_id(),
                        'post_type' => 'product',
                        'menu_order' => $sort_order,
                        'meta_input' => array(
                            'sku' => sanitize_text_field($emapData[0]),
                            'description_32' => sanitize_text_field($emapData[2]),
                            'description_64' => sanitize_text_field($emapData[3]),
                            'download_link_32' => esc_url($emapData[4]),
                            'download_link_64' => esc_url($emapData[5]),
                            'installation_guide' => esc_url($emapData[6]),
                            'installation_video' => esc_url($emapData[7]),
                            'image_url' => esc_url(site_url("/wp-content/uploads/product-images/" . $emapData[0] . ".png", 'https')),
                            'product_category' => $term_id,
                            'download_sku' => sanitize_text_field($emapData[11]),
                        ),
                    );

                    $existing_post_id = get_existing_post_id($emapData[0], $emapData[10]);

                    if ($existing_post_id) {
                        update_existing_post($existing_post_id, $args);
                    } else {
                        $new_page_id = wp_insert_post($args);

                        if ($new_page_id && !is_wp_error($new_page_id)) {
                            update_post_meta($new_page_id, '_wp_page_template', 'page-templates/downloadcenter.php');
                            update_translation($new_page_id, $emapData[10], $emapData[0]);
                        }
                    }
                }
                $x++;
            }

            fclose($file);
            wp_redirect(admin_url('edit.php?post_type=product&paged=1'));
            exit;
        }
    }
}

if (!function_exists('sanitize_category')) {
    function sanitize_category($category) {
        $categories = [
            'Tuning & Utilities' => 'Tuning &amp; Utilities',
            'Graphics & Imaging' => 'Graphics &amp; Imaging',
            'Tuning & Dienstprogramme' => 'Tuning &amp; Dienstprogramme',
            'Grafik & Bildbearbeitung' => 'Grafik &amp; Bildbearbeitung',
            'Συντονισμός & Βοηθητικά Προγράμματα' => 'Συντονισμός &amp; Βοηθητικά Προγράμματα',
            'Γραφικά & Απεικόνιση' => 'Γραφικά &amp; Απεικόνιση',
            'Ladění & nástroje' => 'Ladění &amp; nástroje',
            'Ayarlar & Gereçler' => 'Ayarlar &amp; Gereçler',
            'Grafik & Resim' => 'Grafik & Resim',
            'Grafika & zobrazování' => 'Grafika & zobrazování',
            "Tuning & hulpprogramma's" => "Tuning &amp; hulpprogramma's",
            'Grafiek & beeldbewerking' => 'Grafiek & beeldbewerking',
            'Ajustes & Utilitários' => 'Ajustes &amp; Utilitários',
            'Gráficos & imagens' => 'Gráficos & imagens',
            'Tuning & Utilitaires' => 'Tuning & Utilitaires',
            'Graphisme & imagerie' => 'Graphisme & imagerie',
            'Messa a punto & utilità' => 'Messa a punto & utilità',
            'Grafica & immagini' => 'Grafica & immagini',
        ];

        return $categories[$category] ?? $category;
    }
}

function get_term_id($category, $lang) {
    global $wpdb;
    $sql_count = $wpdb->prepare(
        "SELECT COUNT(st.term_id) 
        FROM {$wpdb->terms} st 
        INNER JOIN {$wpdb->term_taxonomy} stt ON st.term_id = stt.term_id 
        WHERE stt.taxonomy = 'product_category' 
        AND st.name = %s", 
        $category
    );
    $count = $wpdb->get_var($sql_count);

    if ($count > 1) {
        $slugs = [
            'en' => ['windows' => 'windows', 'windows-server' => 'windows-server', 'office' => 'office'],
            'de' => ['windows' => 'windows-de', 'windows-server' => 'windows-server-de', 'office' => 'office-de'],
            'nl' => ['windows' => 'windows-nl', 'office' => 'office-nl'],
            'fr' => ['windows' => 'windows-fr', 'office' => 'office-fr'],
            'el' => ['windows' => 'windows-el', 'windows-server' => 'windows-server-el', 'office' => 'office-el'],
            'it' => ['windows' => 'windows-it', 'windows-server' => 'windows-server-it', 'office' => 'office-it', 'software-gratuito' => 'software-gratuito-it'],
            'pt-pt' => ['windows' => 'windows-pt', 'office' => 'office-pt-pt', 'software-gratuito' => 'software-gratuito-pt-pt'],
            'es' => ['windows' => 'windows-es', 'office' => 'office-es', 'software-gratuito' => 'software-gratuito-es'],
            'cs' => ['windows' => 'windows-cs', 'office' => 'office-cs'],
            'sk' => ['windows' => 'windows-sk', 'office' => 'office-sk'],
        ];

        $slug = $slugs[$lang][$category] ?? '';
        if ($slug) {
            $sql = $wpdb->prepare(
                "SELECT st.term_id 
                FROM {$wpdb->terms} st 
                INNER JOIN {$wpdb->term_taxonomy} stt ON st.term_id = stt.term_id 
                WHERE stt.taxonomy = 'product_category' 
                AND st.name = %s 
                AND st.slug = %s", 
                $category, $slug
            );
            return $wpdb->get_var($sql);
        }
    } else {
        $sql = $wpdb->prepare(
            "SELECT st.term_id 
            FROM {$wpdb->terms} st 
            INNER JOIN {$wpdb->term_taxonomy} stt ON st.term_id = stt.term_id 
            WHERE stt.taxonomy = 'product_category' 
            AND st.name = %s", 
            $category
        );
        return $wpdb->get_var($sql);
    }

    return null;
}

function get_existing_post_id($sku, $lang) {
    global $wpdb;
    $sql = $wpdb->prepare(
        "SELECT spt.post_id 
        FROM {$wpdb->postmeta} spt 
        LEFT JOIN {$wpdb->icl_translations} sit ON spt.post_id = sit.element_id 
        LEFT JOIN {$wpdb->posts} sp ON spt.post_id = sp.ID 
        WHERE spt.meta_key = 'sku' 
        AND spt.meta_value = %s 
        AND sit.language_code = %s 
        AND sp.post_type = 'product' 
        AND sp.post_status = 'publish'", 
        $sku, $lang
    );
    return $wpdb->get_var($sql);
}

function update_existing_post($post_id, $args) {
    global $wpdb;

    $post_content = $args['post_content'];
    $post_title = $args['post_title'];
    $sort_order = $args['menu_order'];

    $wpdb->update(
        $wpdb->posts, 
        [
            'post_content' => $post_content,
            'post_title' => $post_title,
            'post_status' => 'publish',
            'menu_order' => $sort_order
        ],
        ['ID' => $post_id]
    );

    foreach ($args['meta_input'] as $meta_key => $meta_value) {
        update_post_meta($post_id, $meta_key, $meta_value);
    }
}

function update_translation($post_id, $lang, $sku) {
    global $wpdb;

    $source_lg = ($lang == 'en') ? '' : 'en';
    $trid = $wpdb->get_var($wpdb->prepare(
        "SELECT sit.trid 
        FROM {$wpdb->posts} sp 
        INNER JOIN {$wpdb->postmeta} spm ON sp.ID = spm.post_id 
        INNER JOIN {$wpdb->icl_translations} sit ON sp.ID = sit.element_id 
        WHERE sp.post_type = 'product' 
        AND spm.meta_key = 'sku' 
        AND spm.meta_value = %s 
        LIMIT 1", 
        $sku
    ));

    $wpdb->update(
        $wpdb->icl_translations, 
        [
            'language_code' => $lang,
            'source_language_code' => $source_lg,
            'trid' => $trid
        ],
        ['element_id' => $post_id]
    );
}

/*Adds Import button on module list page*/
add_action('admin_head-edit.php', 'add_download_import_button');
function add_download_import_button() {
    global $current_screen;

    // Ensure the button is only added on the 'product' post type screen
    if ($current_screen->post_type !== 'product') {
        return;
    }

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var importFormHtml = `
                <div id="wrap">
                    <div class="container">
                        <div class="row">
                            <div class="span3 hidden-phone"></div>
                            <div class="span6" id="form-login">
                                <form class="form-horizontal well" action="<?php echo admin_url('admin-post.php'); ?>" method="post" name="upload_excel" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="import_ke_products" />
                                    <fieldset>
                                        <legend>Import CSV/Excel file</legend>
                                        <div class="control-group">
                                            <div class="controls">
                                                <input type="file" name="file" id="file" class="input-large">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <div class="controls">
                                                <button type="submit" id="submit" name="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">Upload</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <div class="span3 hidden-phone"></div>
                        </div>
                    </div>
                </div>`;

            $('.wrap .page-title-action').first().after(importFormHtml);
        });
    </script>
    <?php
}

/*Adds Import button on Installation CTP*/
add_action('admin_head-edit.php', 'add_installation_import_button');
function add_installation_import_button() {
    global $current_screen;

    // Ensure the button is only added on the 'installation' post type screen
    if ($current_screen->post_type !== 'installation') {
        return;
    }

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var importFormHtml = `
                <div id="wrap">
                    <div class="container">
                        <div class="row">
                            <div class="span3 hidden-phone"></div>
                            <div class="span6" id="form-login">
                                <form class="form-horizontal well" action="<?php echo admin_url('admin-post.php'); ?>" method="post" name="upload_excel" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="import_ke_installations" />
                                    <fieldset>
                                        <legend>Import CSV/Excel file</legend>
                                        <div class="control-group">
                                            <div class="controls">
                                                <input type="file" name="file" id="file" class="input-large">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <div class="controls">
                                                <button type="submit" id="submit" name="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">Upload</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <div class="span3 hidden-phone"></div>
                        </div>
                    </div>
                </div>`;

            $('.wrap .page-title-action').first().after(importFormHtml);
        });
    </script>
    <?php
}

/*Add Installation guide*/
add_action( 'save_post_installation', 'installation_guide_added_manually', 10, 3 );
function installation_guide_added_manually( $post_id, $post, $update ) {
	global $current_screen;
    if ('installation' == $current_screen->post_type) {	
	update_post_meta( $post_id, '_wp_page_template', 'page-templates/installation.php');
	?>
	<style>  #poststuff .inside select#page_template ,
		.inside .post-attributes-label-wrapper label{
		display:none !important;
	}
	</style>
    <?php
	}

}

/*Add Product*/
add_action( 'save_post_product', 'download_products_added_manually', 10, 3 );
function download_products_added_manually( $post_id, $post, $update ) {
	global $current_screen;
    if ('product' == $current_screen->post_type) {
       	update_post_meta( $post_id, '_wp_page_template', 'page-templates/downloadcenter.php');
	?>
		<style>  #poststuff .inside select#page_template,
		.inside .post-attributes-label-wrapper label{
			display:none !important;
		}
        </style>
	<?php
    }
}

/*Import Installation Guide */
add_action('admin_post_import_ke_installations', 'prefix_admin_import_ke_installations');
add_action('admin_post_nopriv_import_ke_installations', 'prefix_admin_import_ke_installations');
function prefix_admin_import_ke_installations() {
    if (isset($_POST["action"]) && isset($_FILES["file"]) && $_FILES["file"]["size"] > 0) {
        $filename = $_FILES["file"]["tmp_name"];
        $file = fopen($filename, "r");
        $x = 0;
        global $wpdb;
        $lang = apply_filters('wpml_current_language', NULL);

        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($x > 0) {
                $sku = sanitize_text_field($emapData[0]);
                $post_title = sanitize_text_field($emapData[1]);
                $post_content = wp_kses_post(wpautop($emapData[2]));
                $lang_code = sanitize_text_field($emapData[3]);
                $product_skus = str_replace(['{', '}'], '', $emapData[4]);
                $product_names = str_replace(['{', '}'], '', $emapData[5]);
                $installation_video_url = esc_url($emapData[6]);

                $str_arr_skus = explode(',', $product_skus);
                $str_arr_names = explode(',', $product_names);

                $args = array(
                    'post_title' => $post_title,
                    'post_content' => $post_content,
                    'post_status' => 'publish',
                    'post_author' => get_current_user_id(),
                    'post_type' => 'installation',
                    'meta_input' => array(
                        'sku' => $sku,
                        'installation_video_url' => $installation_video_url
                    ),
                );

                $id_exist = $wpdb->get_var($wpdb->prepare(
                    "SELECT spt.post_id 
                    FROM {$wpdb->postmeta} spt 
                    LEFT JOIN {$wpdb->icl_translations} sit ON spt.post_id = sit.element_id 
                    LEFT JOIN {$wpdb->posts} sp ON spt.post_id = sp.ID 
                    WHERE spt.meta_key = 'sku' 
                    AND spt.meta_value = %s 
                    AND sit.language_code = %s 
                    AND sp.post_type = 'installation' 
                    AND sp.post_status = 'publish'", 
                    $sku, $lang_code
                ));

                if ($id_exist) {
                    $update_post = array(
                        'ID' => $id_exist,
                        'post_title' => $post_title,
                        'post_content' => $post_content,
                        'post_type' => 'installation',
                        'post_status' => 'publish'
                    );
                    wp_update_post($update_post);

                    update_post_meta($id_exist, 'installation_repeater_group', "");
                    update_repeater_group($id_exist, $str_arr_skus, $str_arr_names);

                } else {
                    $new_page_id = wp_insert_post($args);
                    if ($new_page_id && !is_wp_error($new_page_id)) {
                        update_post_meta($new_page_id, '_wp_page_template', 'page-templates/installation.php');
                        update_translation($new_page_id, $lang_code, $sku);

                        update_repeater_group($new_page_id, $str_arr_skus, $str_arr_names);
                    }
                }
            }
            $x++;
        }
        fclose($file);
        wp_redirect(admin_url('edit.php?post_type=installation&paged=1'));
        exit;
    }
}

function update_repeater_group($post_id, $skus, $names) {
    $new = array();
    $count = count($skus);
    for ($i = 0; $i < $count; $i++) {
        $new[$i]['sku'] = stripslashes(strip_tags($skus[$i]));
        $new[$i]['sdesc'] = stripslashes($names[$i]);
    }
    update_post_meta($post_id, 'installation_repeater_group', $new);
}

if (!function_exists('update_translation')) {
    function update_translation($post_id, $lang_code, $sku) {
        global $wpdb;

        $source_lang = ($lang_code == 'en') ? '' : 'en';
        $trid = $wpdb->get_var($wpdb->prepare(
            "SELECT sit.trid 
            FROM {$wpdb->posts} sp 
            INNER JOIN {$wpdb->postmeta} spm ON sp.ID = spm.post_id 
            INNER JOIN {$wpdb->icl_translations} sit ON sp.ID = sit.element_id 
            WHERE sp.post_type = 'installation' 
            AND spm.meta_key = 'sku' 
            AND spm.meta_value = %s 
            LIMIT 1", 
            $sku
        ));

        $wpdb->update(
            "{$wpdb->prefix}icl_translations", 
            array(
                'language_code' => $lang_code,
                'source_language_code' => $source_lang,
                'trid' => $trid
            ),
            array('element_id' => $post_id)
        );
    }
}

/*Phpmailer*/
add_action( 'phpmailer_init', 'fix_my_email_return_path' );
function fix_my_email_return_path( $phpmailer ) {
    $phpmailer->Sender = $phpmailer->From;
}
   
/*Post License issue for Service center */
add_action('wp_ajax_email_license_issue','email_license_issue');
add_action('wp_ajax_nopriv_email_license_issue','email_license_issue');
function email_license_issue(){
	global $wpdb;
	
	$mailresult;
	echo json_encode($mailresult); 
	wp_die();
}

add_action('wp_head', 'wpb_hook_js_css_downloadpage');
function wpb_hook_js_css_downloadpage() {
	if (is_page ('2609')) { 
	  ?>
		 <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
         <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	  <?php
	}
}

/*Installation Meta Box*/
add_action('admin_init', 'installation_product_repeater_meta_boxes', 2);
function installation_product_repeater_meta_boxes() {
	add_meta_box( 'single-repeter-data', 'Product SKUs', 'installation_repeatable_meta_box_callback', 'installation');
}

function installation_repeatable_meta_box_callback($post) {

	$installation_repeater_group = get_post_meta($post->ID, 'installation_repeater_group', true);
	$banner_img = get_post_meta($post->ID,'post_banner_img',true);
	wp_nonce_field( 'repeterBox', 'formType' );
	?>
	<script type="text/javascript">
		jQuery(document).ready(function( $ ){
			$( '#add-row' ).on('click', function() {
				var row = $( '.empty-row.custom-repeter-text' ).clone(true);
				row.removeClass( 'empty-row custom-repeter-text' ).css('display','table-row');
				row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
				return false;
			});

			$( '.remove-row' ).on('click', function() {
				$(this).parents('tr').remove();
				return false;
			});
		});

	</script>

	<table id="repeatable-fieldset-one" width="100%">
		<tbody>
			<?php
			if ( $installation_repeater_group ) :
				foreach ( $installation_repeater_group as $field ) {
					?>
					<tr>
						<td><input type="text"  style="width:98%;" name="sku[]" value="<?php if($field['sku'] != '') echo esc_attr( $field['sku'] ); ?>" placeholder="Enter Product SKU" /></td>
						<td><input type="text"  style="width:98%;" name="sdesc[]" value="<?php if ($field['sdesc'] != '') echo esc_attr( $field['sdesc'] ); ?>" placeholder="Description" /></td>
						<td><a class="button remove-row" href="#1">Remove</a></td>
					</tr>
					<?php
				}
			else :
				?>
				<tr>
					<td><input type="text"   style="width:98%;" name="sku[]" placeholder="Enter Product SKU"/></td>
					<td><input type="text"  style="width:98%;" name="sdesc[]" value="" placeholder="Description" /></td>
					<td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
				</tr>
			<?php endif; ?>
			<tr class="empty-row custom-repeter-text" style="display: none">
				<td><input type="text" style="width:98%;" name="sku[]" placeholder="Enter Product SKU"/></td>
				<td><input type="text" style="width:98%;" name="sdesc[]" value="" placeholder="Description"/></td>
				<td><a class="button remove-row" href="#">Remove</a></td>
			</tr>
			
		</tbody>
	</table>
	<p><a id="add-row" class="button" href="#">Add another</a></p>
	<?php
}

/*Save Meta Box values.*/
add_action('save_post', 'installation_repeatable_meta_box_save');
function installation_repeatable_meta_box_save($post_id) {

	if (!isset($_POST['formType']) && !wp_verify_nonce($_POST['formType'], 'repeaterBox'))
		return;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;

	if (!current_user_can('edit_post', $post_id))
		return;

	$old = get_post_meta($post_id, 'installation_repeater_group', true);

	$new = array();
	$titles = $_POST['sku'];
	$tdescs = $_POST['sdesc'];
	$count = count( $titles );
	for ( $i = 0; $i < $count; $i++ ) {
		if ( $titles[$i] != '' ) {
			$new[$i]['sku'] = stripslashes( strip_tags( $titles[$i] ) );
			$new[$i]['sdesc'] = stripslashes( $tdescs[$i] );
		}
	}

	if ( !empty( $new ) && $new != $old ){
		update_post_meta( $post_id, 'installation_repeater_group', $new );
	} elseif ( empty($new) && $old ) {
		delete_post_meta( $post_id, 'installation_repeater_group', $old );
	}
	$repeater_status= $_REQUEST['installation_repeater_status'];
	update_post_meta( $post_id, 'installation_repeater_status', $repeater_status );
}

/*New User Reg*/
add_action('wp_ajax_form_new_user_reg', 'form_new_user_reg');
add_action('wp_ajax_nopriv_form_new_user_reg', 'form_new_user_reg');
function form_new_user_reg() {
    global $wpdb;

    if (!isset($_REQUEST['email']) || !is_email($_REQUEST['email'])) {
        echo json_encode(['error' => 'Invalid email address']);
        wp_die();
    }

    $email = sanitize_email($_REQUEST['email']);
    $table_users = $wpdb->prefix . 'users';

    $user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$table_users} WHERE user_email = %s", $email));

    if ($user_id) {
        update_user_meta($user_id, 'it_personnel', 'No');
        echo json_encode(['success' => 'User updated']);
    } else {
        echo json_encode(['error' => 'User not found']);
    }

    wp_die();
}

/*Promo Exchange*/
add_shortcode('promo_exchange_notification', 'kb_promo_exchange_notification');
function kb_promo_exchange_notification($atts) {
    ob_start();

    $lang = apply_filters('wpml_current_language', NULL);
    $notifications = [
        'en' => ["Thank you for your message!", "We will get in touch with you shortly"],
        'de' => ["Danke für deine Nachricht!", "Wir werden uns in Kürze mit Ihnen in Verbindung setzen"],
        'el' => ["Σας ευχαριστούμε για το μήνυμά σας!", "Θα επικοινωνήσουμε μαζί σας σύντομα"],
        'fr' => ["Merci pour votre message!", "Nous vous contacterons bientôt"],
        'it' => ["Grazie per il vostro messaggio!", "Ci metteremo in contatto con te a breve"],
        'pt-pt' => ["Obrigado pela sua mensagem!", "Nós entraremos em contato com você em breve"],
        'es' => ["Gracias por tu mensaje!", "Nos pondremos en contacto con usted en breve"],
        'tr' => ["Mesajın için teşekkürler!", "Kısa süre içinde sizinle iletişime geçeceğiz"],
        'cs' => ["Děkuji vám za vaši zprávu!", "Brzy se vám ozveme"],
        'nl' => ["Bedankt voor je bericht!", "We nemen spoedig contact met je op"],
        'sk' => ["Ďakujem vám za vašu správu!", "Čoskoro vás budeme kontaktovať"],
    ];

    $notif_1 = $notifications[$lang][0] ?? $notifications['en'][0];
    $notif_2 = $notifications[$lang][1] ?? $notifications['en'][1];
    
    ?>
    <div class="container">
        <div class="row">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><?php echo esc_html($notif_1); ?></strong> <?php echo esc_html($notif_2); ?>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
    <?php

    return ob_get_clean();
}

//Increase auth cookie
add_filter( 'auth_cookie_expiration', 'keep_me_logged_in_for_1_year' );
function keep_me_logged_in_for_1_year( $expirein ) {
	return 31556926; // 1 year in seconds
}

add_filter('wpml_user_can_translate', function ($user_can_translate, $user){
    if (in_array('editor', (array) $user->roles, true) && current_user_can('translate')) {
        return true;
    }
       
    return $user_can_translate;
}, 10, 2);

