<?php
/*
Template Name: Download Center
Template Post Type: post, page, event, product
*/

get_header();

$id = get_the_ID();

$fields = [
    "sku",
    "description_32",
    "description_64",
    "download_label_1",
    "download_link_32",
    "download_label_2",
    "download_link_64",
    "additional_download_label",
    "additional_download_link",
    "installation_guide",
    "installation_video",
    "image_url",
    "note",
    "download_page_content"
];

$data = [];
foreach ($fields as $field) {
    $data[$field] = get_field($field, $id);
}

$post = get_post($id);
$lang = apply_filters('wpml_current_language', NULL);

/*Label Translations*/
$installation_labels = [
    'en' => "Installation Guide",
    'de' => "Installationsanleitung",
    'el' => "Οδηγός εγκατάστασης",
    'fr' => "Guide d'installation",
    'it' => "Guida d'installazione",
    'pt-pt' => "Guia de instalação",
    'pt-br' => "Guia de instalação",
    'es' => "Guía de instalación",
    'tr' => "Yükleme Rehberi",
    'cs' => "Průvodce instalací",
    'sk' => "Návod na inštaláciu",
    'be' => "Installatiegids"
];

$installation_label = $installation_labels[$lang] ?? $installation_labels['en'];

$keysexpressURL =  "https://keys.express";
?>
 
<div class="container download-center-template-new">
    <h1>Download Center</h1>
    <div class="row">
        <!-- Filters Section -->
        <div class="filter-section col-md-4 col-sm-12">
            <label for="find-product" class="section-label">Find your product</label>
              <!--Start Language Switcher-->
               <div class="lang-switcher">
                        <div id="wpml-ls-legacy-dropdown" class="wpml-ls-sidebars- wpml-ls wpml-ls-legacy-dropdown js-wpml-ls-legacy-dropdown">
                                <ul>
                                     <li tabindex="0" class="wpml-ls-slot- wpml-ls-item wpml-ls-item-en wpml-ls-current-language wpml-ls-first-item wpml-ls-item-legacy-dropdown">
                                          <a href=""<?php echo $keysexpressURL; ?>/en/download-center/"" class="js-wpml-ls-item-toggle wpml-ls-item-toggle"><img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/en.svg" alt="" width="18" height="12"><span class="wpml-ls-native">English</span></a>

                                          <ul id="wpml-ls-sub-menu" class="wpml-ls-sub-menu">
                                            
                                               <li class="wpml-ls-slot wpml-ls-item wpml-ls-item-/en/,en"><a href="<?php echo $keysexpressURL; ?>/en/download-center/" class="">
                                                                                <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/en.svg" alt="" width="18" height="12"><span class="wpml-ls-native">English</span></a></li>
                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-de">
                                                    <a href="<?php echo $keysexpressURL; ?>/de/download-center/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/de.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="de">Deutsch</span></a>
                                                </li>

                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-fr">
                                                    <a href="<?php echo $keysexpressURL; ?>/fr/centre-de-telechargement/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/fr.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="fr">Français</span></a>
                                                </li>

                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-it">
                                                    <a href="<?php echo $keysexpressURL; ?>/it/centro-download/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/it.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="it">Italiano</span></a>
                                                </li>

                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-es">
                                                    <a href="<?php echo $keysexpressURL; ?>/es/centro-de-descargas/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/es.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="es">Español</span></a>
                                                </li>

                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-pt-pt">
                                                    <a href="<?php echo $keysexpressURL; ?>/pt-pt/centro-de-download/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/pt-pt.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="pt-pt">Português</span></a>
                                                </li>

                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-be">
                                                    <a href="<?php echo $keysexpressURL; ?>/be/downloadcentrum/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/be.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="be">Vlaams</span></a>
                                                </li>

                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-cs">
                                                    <a href="<?php echo $keysexpressURL; ?>/cs/centrum-stahovani/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/cs.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="cs">Čeština</span></a>
                                                </li>

                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-sk">
                                                    <a href="<?php echo $keysexpressURL; ?>/sk/centrum-stahovania/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/sk.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="sk">Slovenčina</span></a>
                                                </li>

                                            
                                                <li class="wpml-ls-slot- wpml-ls-item wpml-ls-item-el wpml-ls-last-item">
                                                    <a href="<?php echo $keysexpressURL; ?>/el/%ce%ba%ce%ad%ce%bd%cf%84%cf%81%ce%bf-%ce%bb%ce%ae%cf%88%ce%b7%cf%82/" class="wpml-ls-link">
                                                                                            <img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/el.svg" alt="" width="18" height="12"><span class="wpml-ls-native" lang="el">Ελληνικά</span></a>
                                                </li>

                                            </ul>

                                    </li>

                                </ul>
                        </div>
                 </div><!--End Language Switcher-->
            <?php echo do_shortcode('[product_category_filter product_id=' . $id . ']'); ?>
        </div>

        <!-- Search Section -->
        <div class="search-section col-md-4 col-sm-12">
            <label for="search-item-number" class="section-label">Search by Item Number</label>
            <div id="search-item-number" class="search-box">
                <input type="text" placeholder="Enter Item Number" id="search_by_sku_input">
                <button id="search_by_sku_submit">
                    <span class="search-icon"></span>
                </button>
            </div>
        </div>
    </div> 

    <?php if ( !empty($data['sku']) ): ?>
    <!-- Product Download Details Section -->
    <div class="row">
        <div class="product-details-section col-md-8 col-sm-12">
            <div class="product-info">
                <img src="<?php echo $data['image_url']; ?>" alt="Product Image" class="product-image">
                <div class="product-description">
                    <h2><?php the_title(); ?></h2>
                    <p><?php echo $data['description_64']; ?></p>
                    <p><?php echo $data['note']; ?></p>
                </div>
            </div>
        </div>   
        <div class="product-download-links-section col-md-4 col-sm-12">
            <h3>Download Files</h3>
            <?php if (isset($data) && is_array($data)): ?>
                <ul class="download-links">
                    <?php if (!empty($data['download_link_64']) && !empty($data['download_label_2'])): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($data['download_link_64'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                <span class="download-icon"></span> <?php echo htmlspecialchars($data['download_label_2'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['download_link_32']) && !empty($data['download_label_1'])): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($data['download_link_32'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                <span class="download-icon"></span> <?php echo htmlspecialchars($data['download_label_1'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['additional_download_link']) && !empty($data['additional_download_label'])): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($data['additional_download_link'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                <span class="download-icon"></span> <?php echo htmlspecialchars($data['additional_download_label'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['installation_guide'])): ?>
                        <li>
                            <a href="<?php echo $keysexpressURL . "/" . $lang . "/installation-guide/?id=" . $data['installation_guide']; ?>" target="_blank" class="guide-link">
                                <span class="external-icon"></span> Installation Guide
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <p>No download links available at the moment.</p>
            <?php endif; ?>
        </div> 
    </div>    
    <?php endif; ?>
    <!-- Bestseller Section -->
    <?php 
        include_once 'bestseller.php';           
    ?>
       <!-- Modal -->
       <div class="modal fade" id="notFoundModalCenter" tabindex="-1" role="dialog" aria-labelledby="notFoundModalCenter" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h5 class="modal-title" id="notFoundModalCenter">Product SKU</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                          </button>
                      </div>
                      <div class="modal-body">
                          Not Found
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                      </div>
                  </div>
              </div>
        </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    font-family: 'Source Sans Pro', sans-serif;
}

.container.download-center-template-new .row {
    margin-bottom: 30px;
}

/* Full-width background for the header */
.header-wrapper {
    width: 100%;
    background-color: #f2f2f2; /* Update with desired background color or image */
    padding: 0;
    font-family: 'Source Sans Pro', sans-serif;
    height: 80px;
}

/* Center header content */
.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px; /* Same width as the container */
    margin: 0 auto; /* Center the header content */
    padding: 5px 0 5px 0;
}

/* Header Section */
header .site-logo {
    width: 203px;
    height: 63px;
}

header nav {
    display: flex;
    gap: 20px;
    /*margin-left: 15%;*/
    padding-top: 40px;

}

header nav a {
    font-size: 18px;
    font-weight: none;
    color: #000;
    text-decoration: none;
}

header nav a.active {
    font-weight: bold !important;
}

header nav a:hover{
    color:#BF3436;
}


.language-selector {
    margin-left: auto;
}

.language-selector select {
    padding: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
}

/* Page Title */
h1 {
    font-size: 30px;
    margin: 20px 0;
    text-align: left;
    color: #000;
}

/* Section Labels */
.section-label {
    font-family: 'Source Sans Pro', sans-serif;
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 10px;
    display: block;
    color: #000;
}

/* Filter Section */

.filter-section {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

.filter-section select {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
}

/* Search Section */
.search-section {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

.search-box {
    display: flex;
    /*gap: 10px;*/
}

.search-box input {
    flex: 1;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    padding: .1rem 0 .1rem .5rem;
    font-family: 'Source Sans Pro', sans-serif;
    height: 32px;
    border-radius: 3px 0 0 3px;
}

.search-box button {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 16px;
    font-weight: bold;
    background-color: #BF3436;
    color: #fff;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-transform: uppercase;
    border-radius: 0 2px 2px 0;
    height: 32px;
}

.search-box button:hover {
    background-color: #a52b2d;
}

/*.icon-search {
    display: inline-block;
    width: 16px;
    height: 16px;
    background-image: url('https://keys.support/wp-content/uploads/2025/01/search-icon.svg');
    background-size: contain;
    background-repeat: no-repeat;
}*/
#search-item-number .search-icon::before{
    content: "";
    display: inline-block;
    width: 15px; /* Adjust as needed */
    height: 20px; /* Adjust as needed */
    background-image: url('https://keys.support/wp-content/uploads/2025/01/search-icon.svg');
    background-size: contain;
    background-repeat: no-repeat;
    margin-right: 0; /* Add space if needed */
    vertical-align: middle; /* Helps maintain consistency */
}
.page-template-downloadcenter2 .elementor-top-section {
    display: none !important;
    }

   .download-center-template-new .filter-section #category-filter-download,
   .download-center-template-new .filter-section  #software-filter-download{
    display: block;
    text-decoration: none;
    color: black;
    border: 1px solid #cdcdcd;
    background-color: #fff;
    padding: 5px 10px;
    padding-right: 10px;
    line-height: 1;
   }

   .download-center-template-new .wpml-ls-legacy-dropdown,
   .download-center-template-new .filter-section #category-filter-download,
   .download-center-template-new .filter-section #select2-software-filter-download-container,
   .download-center-template-new .filter-section .select2-container,
   .download-center-template-new .filter-section #software-filter-download,
   .wpml-ls-legacy-dropdown a {
  width: 22.5em !important;
  max-width: 100%;
  color:black;
}

.download-center-template-new select,
.select2-container--default .select2-selection--single{
    border-radius: 1px;
}


.select2-selection__arrow {
  display: none;
}
/*#select2-software-filter-download-container::after{
  content: "";
  vertical-align: middle;
  display: inline-block;
  border: .35em solid transparent;
  border-top-width: 0.35em;
  border-top-style: solid;
  border-top-color: transparent;
  border-top: .5em solid;
  position: absolute;
  right: 12px;
  top: calc(50% - .2em);
}*/


/* Hide the default dropdown arrow */
#category-filter-download,
#software-filter-download {
  -webkit-appearance: none !important; /* For Chrome and Safari */
  -moz-appearance: none !important; /* For Firefox */
  appearance: none !important;
  background: transparent !important;
  position: relative !important;
  padding-right: 2em !important; /* Adjust as needed for spacing */
  z-index: 1;
  flex-grow: 1;
}

/* Add a custom dropdown arrow */
.category-filter-wrapper,
.software-filter-wrapper{
  position: relative;
}

.download-center-template-new .filter-section select#software-filter-download option:hover,
.download-center-template-new .filter-section select#category-filter-download option:hover {
    background-color: #eee !important;
    color: #000 !important;
}
/*.download-center-template-new .filter-section select#software-filter-download:hover,
.download-center-template-new .filter-section select#category-filter-download:hover {
    background-color: red !important;
    color: #000 !important;
}*/


.download-center-template-new .filter-section .category-filter-wrapper .category-filter-arrow,
.download-center-template-new .filter-section .software-filter-wrapper .software-filter-arrow
 {
    content: "" !important;
  display: inline-block !important;
  border: 0.35em solid transparent;
  border-top: 0.5em solid #000; /* Adjust color */
  position: absolute;
  right: 6%; /* Adjust based on the dropdown's width */
  top: 55%; /* Vertically center */
  transform: translateY(-50%);
  pointer-events: none; /* Prevent interference with dropdown */
  z-index: 2; /* Ensure the arrow stays on top */
  margin-left: auto; 

}

/* Product Info */
.product-details-section{
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}
.product-info{
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}
.product-image {
    max-width: 180px;
    height: auto;
    /*border: 1px solid #ccc;*/
    border-radius: 5px;
}

.product-description {
    font-family: 'Source Sans Pro', sans-serif;
    color: #333;
}

.product-description h2 {
    font-size: 16px;
    margin-bottom: 10px;
    color: black;
    font-weight: 600;
}

.product-description p {
    font-size: 16px;
    line-height: 1.5;
    font-weight: 400;
    width: 100%;
}

.product-download-links-section h3 {
    font-size: 16px;
    margin-bottom: 15px;
    color: black;
    font-weight: 600;
}

.download-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.download-links li {
    margin-bottom: 10px;
}

.download-links a {
    text-decoration: none;
    font-size: 16px;
    color:rgb(0, 106, 48);
    display: flex;
    align-items: center;
    gap: 10px;
}

.download-links a:hover {
    color: #005bb5;
}

.download-links i {
    font-size: 18px;
    color: #333;
}

.guide-link {
    font-weight: bold;
    color: #BF3436 !important;
}
.guide-link:hover {
    color: #a52b2d;
}

.download-links .download-icon::before {
    content: "";
    display: inline-block;
    width: 20px; /* Adjust as needed */
    height: 20px; /* Adjust as needed */
    background-image: url('https://keys.support/wp-content/uploads/2025/01/icon-download-green.svg');
    background-size: contain;
    background-repeat: no-repeat;
    margin-right: 8px; /* Add space if needed */
    vertical-align: middle; /* Helps maintain consistency */
}

.download-links  .external-icon::before{
    content: "";
    display: inline-block;
    width: 20px; /* Adjust as needed */
    height: 20px; /* Adjust as needed */
    background-image: url('https://keys.support/wp-content/uploads/2025/01/icon-external-link-red.svg');
    background-size: contain;
    background-repeat: no-repeat;
    margin-right: 8px; /* Add space if needed */
    vertical-align: middle; /* Helps maintain consistency */
}

/* Make the layout responsive and adjust sections */
@media (max-width: 768px) {
    .container.download-center-template-new .row {
        flex-direction: column;
        align-items: center;
    }
    .filter-section,
    .search-section{
        width: 100%;
         margin-bottom: 20px;
    }
    
    .product-details-section,
    .product-download-links-section {
        width: 100%;
        text-align: center;
        margin-bottom: 20px;
    }

    .filter-section select,
    .search-box input {
        width: 100%;
    }
    .download-center-template-new .wpml-ls-legacy-dropdown,
   .download-center-template-new .filter-section #category-filter-download,
   .download-center-template-new .filter-section #select2-software-filter-download-container,
   .download-center-template-new .filter-section .select2-container,
   .download-center-template-new .filter-section #software-filter-download,
   .wpml-ls-legacy-dropdown a {
    width: 100% !important;
    max-width: 100%;
    }
    .product-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .product-description,
    .product-description p  {
        width: 100%;
        text-align: center;
    }

    .product-image {
        margin-bottom: 20px;
        max-width: 100%;
        height: auto;
    }

    .download-links a {
        justify-content: center;
    }
    .download-center-template-new .filter-section .category-filter-wrapper .category-filter-arrow, .download-center-template-new .filter-section .software-filter-wrapper .software-filter-arrow{
        right: 2%;
    }
}


</style>
<?php 
$http_referrer = $_SERVER['HTTP_REFERER'];
$url_components = parse_url($http_referrer);
parse_str($url_components['query'], $params);

$sku = $params['sku'];
$lang = $lang = isset($params['lang']) && $params['lang'] !== '' ? $params['lang'] : $lang;
$langSelected = false;
$slug = false;

// Check if the parameter is set in the URL
if (isset($_GET['lang_selected'])) {
    $langSelected = htmlspecialchars($_GET['lang_selected']);
}

if (isset($_GET['slug'])) {
    $slug = true;
}

$search_terms = [  
    'en' => 'download-center',
    'fr' => 'centre-de-telechargement',
    'es' => 'centro-de-descargas',
    'pt-pt' => 'centro-de-download',
    'pt-br' => 'centro-de-download',
    'it' => 'centro-download',
    'el' => 'κέντρο-λήψης',
    'cs' => 'centrum-stahovani',
    'sk' => 'centrum-stahovania',
    'be' => 'downloadcentrum'
];
$parsed_url = parse_url($http_referrer);
$path = $parsed_url['path'];
$found = false;
foreach ($search_terms as $term) {
    if (strpos($path, $term) !== false) {
        $found = true;
        break;
    }
}

$product_found = false;
if (strpos($_SERVER['REQUEST_URI'], 'product') !== false) {
    $product_found = true;
}


?>

<?php if( (( $sku!="" && $lang!="" ) || ( !$found && !$product_found )) && $langSelected == false && $slug == false): 
?>

<script>
jQuery(document).ready(function($) {
    const ur_sc = window.location.href;
    let lang = ur_sc.match(/\/(en|de|el|fr|it|pt-pt|es|tr|cs|nl|sk|be)\//);

    var $downloadCol = $('.download-col');
    if ($downloadCol.length) {
        $downloadCol.hide();
    }

    var $langSwitcherContainer = $('#wpml-ls-legacy-dropdown');
    var $currentLanguageItem = $('.wpml-ls-current-language');
    var $firstAnchor = $('#wpml-ls-legacy-dropdown > ul > li > a').first();

    if ($firstAnchor.length) {
        var $clonedAnchor = $firstAnchor.clone();
        $clonedAnchor.attr('href', '<?php echo get_permalink(); ?>');
        $clonedAnchor.removeClass('js-wpml-ls-item-toggle wpml-ls-item-toggle');

        var $newLi = $('<li>', {
            class: 'wpml-ls-slot wpml-ls-item wpml-ls-item-' + lang
        }).append($clonedAnchor);
        
        var $dropdown = $('#wpml-ls-legacy-dropdown > ul > li').first();
   
        if ($dropdown.length) {
            var label;
            
            switch (lang ? lang[1] : '') {
                case 'de':
                    label = 'Sprache...';
                    break;
                case 'fr':
                    label = 'Langue...';
                    break;
                case 'it':
                    label = 'Lingua..';
                    break;
                case 'sk':
                case 'cs':
                    label = 'Jazyk...';
                    break;
                case 'pt-pt':
                case 'pt-br':
                    label = 'Língua...';
                    break;
                case 'es':
                    label = 'Idioma...';
                    break;
                case 'be':
                    label = 'Taal...';
                    break;
                case 'el':
                    label = 'Γλώσσα...';
                    break;
                default:
                    label = 'Select Language...';
            }

            var $firstAnchor = $dropdown.find('a').first();
            $firstAnchor.remove();

            var labelHTML = '<a href="#" class="js-wpml-ls-item-toggle wpml-ls-item-toggle"><span class="wpml-ls-native js-wpml-ls-item-toggle wpml-ls-item-toggle">' + label + '</span></a>';
            var $labelElement = $(labelHTML);

            $dropdown.prepend($labelElement);

            var $submenu = $('.wpml-ls-sub-menu');
            if ($submenu.length) {
                $submenu.prepend($newLi);
            }
        }
    }
});
</script>
<?php elseif ($langSelected || $slug):   
?>
<script>
jQuery(document).ready(function($) {
    const ur_sc = window.location.href;
    let lang = ur_sc.match(/\/(en|de|el|fr|it|pt-pt|es|tr|cs|nl|sk|be)\//);
    var $downloadCol = $('.download-col');
    if ($downloadCol.length) {
        $downloadCol.hide();
    }

    var $langSwitcherContainer = $('#wpml-ls-legacy-dropdown');
    var $currentLanguageItem = $('.wpml-ls-current-language');
    var $firstAnchor = $('#wpml-ls-legacy-dropdown > ul > li > a').first();
   
    if ($firstAnchor.length) {
        var $clonedAnchor = $firstAnchor.clone();
        $clonedAnchor.attr('href', '<?php echo get_permalink(); ?>');
        $clonedAnchor.removeClass('js-wpml-ls-item-toggle wpml-ls-item-toggle');

        var $newLi = $('<li>', {
            class: 'wpml-ls-slot wpml-ls-item wpml-ls-item-' + lang
        }).append($clonedAnchor);

        var $dropdown = $('#wpml-ls-legacy-dropdown > ul > li').first();
        console.log(lang);
        if ($dropdown.length) {
            var label;

            switch (lang ? lang[1] : '') {
                case 'de':
                    label = '<img class="wpml-ls-flag" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/de.svg" alt="" width="18" height="12"> Deutsch';
                    break;
                case 'fr':
                    label = '<img style="vertical-align:baseline;" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/fr.svg" alt="" width="18" height="12"> Français';
                    break;
                case 'it':
                    label = '<img style="vertical-align:baseline;" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/it.svg" alt="" width="18" height="12"> Italiano';
                    break;
                case 'sk':
                case 'cs':
                    label = '<img style="vertical-align:baseline;" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/cs.svg" alt="" width="18" height="12"> Čeština';
                    break;
                case 'pt-pt':
                case 'pt-br':
                    label = '<img style="vertical-align:baseline;" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/pt-pt.svg" alt="" width="18" height="12"> Português';
                    break;
                case 'es':
                    label = '<img style="vertical-align:baseline;" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/es.svg" alt="" width="18" height="12"> Español';
                    break;
                case 'be':
                    label = '<img style="vertical-align:baseline;" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/be.svg" alt="" width="18" height="12"> Vlaams';
                    break;
                case 'el':
                    label = '<img style="vertical-align:baseline;" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/el.svg" alt="" width="18" height="12"> Ελληνικά';
                    break;
                default:
                    label = '<img style="vertical-align:baseline;" src="https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/en.svg" alt="" width="18" height="12"> English';
            }

            var $firstAnchor = $dropdown.find('a').first();
            $firstAnchor.remove();

            var labelHTML = '<a href="#" class="js-wpml-ls-item-toggle wpml-ls-item-toggle"><span class="wpml-ls-native js-wpml-ls-item-toggle wpml-ls-item-toggle">' + label + '</span></a>';
            var $labelElement = $(labelHTML);
            $dropdown.prepend($labelElement);

            var $submenu = $('.wpml-ls-sub-menu');
            if ($submenu.length) {
                $submenu.prepend($newLi);
            }
        }
    }
});
</script>
<?php endif; ?>
<script>
jQuery(document).ready(function($) {
    
    // Add click event to dropdown links to append a URL parameter
    $('.wpml-ls-legacy-dropdown a').on('click', function(e) {
        const ur_sc = window.location.href;
        let lang = ur_sc.match(/\/(en|de|el|fr|it|pt-pt|es|tr|cs|nl|sk|be)\//);

        var href = $(this).attr('href');
        if (href) {
            // Append a URL parameter to indicate the dropdown was clicked
            var newHref = href + (href.includes('?') ? '&' : '?') + 'lang_selected=true';
            $(this).attr('href', newHref);
        }
    });

    // Listen for dropdown item clicks
    $('.wpml-ls-sub-menu li a').on('click', function (e) {
        e.preventDefault(); // Prevent default link action

        // Get the selected language and update the dropdown label
        //const selectedLanguage = $(this).find('.wpml-ls-native').text().trim();
        //$('.js-wpml-ls-item-toggle .wpml-ls-item-toggle').text(selectedLanguage);

     
        // Redirect to the selected language URL
        const languageUrl = $(this).attr('href');
        window.location.href = languageUrl;
    });

    // Optional: Add an additional class for styling active items
    $('.wpml-ls-sub-menu li.active-language').css({
        fontWeight: 'bold',
        textDecoration: 'underline',
    });


    $("#wpml-ls-sub-menu li:first").remove();
});
</script>
<?php get_footer(); ?>
