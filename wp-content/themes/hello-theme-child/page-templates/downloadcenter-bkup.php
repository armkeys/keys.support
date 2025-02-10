<?php
/*
Template Name: Download Center BackUp
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

<div class="container download-center-template" base-url="<?php echo home_url(); ?>"><!--start of root element -->
      <div class="container">
          <div class="row">
              <div class="col-md-12">
                  <header>
                      <h1><?php echo $post->post_title; ?></h1>
                  </header>
              </div>
          </div>

          <!-- Start: alert when category not found -->
          <?php if(isset($_GET['return']) && $_GET['return'] == 'false') { ?>
              <div class="row" id="dc-noresult">
                  <div class="col-md-6 col-sm-12">
                      <div class="alert alert-danger" role="alert">
                          <?php
                          $no_data_messages = [
                              'en' => "No data found for selected Category",
                              'de' => "Keine Daten für die ausgewählte Kategorie gefunden",
                              'fr' => "Aucune donnée trouvée pour la catégorie sélectionnée",
                              'el' => "Δεν βρέθηκαν δεδομένα για την επιλεγμένη κατηγορία",
                              'it' => "Nessun dato trovato per la categoria selezionata",
                              'es' => "No se encontraron datos para la categoría seleccionada",
                              'pt-pt' => "Nenhum dado encontrado para a categoria selecionada",
                              'pt-br' => "Não foram encontrados dados para a categoria selecionada",
                              'cs' => "Pro vybranou kategorii nebyla nalezena žádná data",
                              'tr' => "Seçilen Kategori için veri bulunamadı",
                              'sk' => "Pre vybratú kategóriu sa nenašli žiadne údaje",
                              'be' => "Er zijn geen gegevens gevonden voor de geselecteerde categorie"
                          ];

                          echo $no_data_messages[$lang] ?? $no_data_messages['en'];
                          ?>
                      </div>
                  </div>
              </div>
              <script>
                  jQuery(document).ready(function () {
                      setTimeout(function() {
                          jQuery('#dc-noresult').fadeOut('fast');
                      }, 2000); // <-- time in milliseconds
                  });
              </script>
          <?php } ?>
          <!-- End: alert when category not found -->
          <div class="row">
              <div class="col-md-4 col-sm-12">
                    <div class="lang-switcher">
                            <div class="wpml-ls-sidebars- wpml-ls wpml-ls-legacy-dropdown js-wpml-ls-legacy-dropdown">
                                <ul>
                                    <li tabindex="0" class="wpml-ls-slot- wpml-ls-item wpml-ls-item-en wpml-ls-current-language wpml-ls-first-item wpml-ls-item-legacy-dropdown"><a href="#" class="js-wpml-ls-item-toggle wpml-ls-item-toggle"><span class="wpml-ls-native js-wpml-ls-item-toggle wpml-ls-item-toggle">Language</span></a>
                                            <ul class="wpml-ls-sub-menu">
                                                <li class="wpml-ls-slot wpml-ls-item wpml-ls-item-/en/,en"><a href="<?php echo $keysexpressURL; ?>/en/download-center" class="">
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
                    </div>
                  <?php 
                  //echo '<div class="lang-switcher">';
                  //  echo do_shortcode('[wpml_language_switcher type="widget" flags=1 native=1 translated=0][/wpml_language_switcher]');
                  //echo '</div>';

                  echo do_shortcode('[product_category_filter product_id=' . $id . ']');

                  ?>
                  <br/>
              </div>

              <?php if (empty($data['sku']) && empty($data['description_32']) && empty($data['download_link_32']) && empty($data['installation_guide']) && empty($data['installation_video'])) { ?>
                  <div class="col-md-4 col-sm-12">
                      <div class="download-software-wrapper custom-success-bg">
                          <div class="row">
                              <div class="col-sm-12">
                                  <?php echo $data['download_page_content']; ?>
                              </div>
                          </div>
                      </div>
                  </div>
              <?php } else { ?>
                  <div class="col-md-4 col-sm-12 col-2-color">
                      <div class="row">
                          <div class="col-md-6 product-image">
                              <img src="<?php echo $data['image_url'] ?: 'https://via.placeholder.com/252/dc3545/FFFFFF/?text=No%20Image%20Available'; ?>" alt="Product Image" width="200" height="200" loading="lazy">
                          </div>
                          <div class="col-md-6 col-sm-12">
                              <div class="download-software-description descr_64">
                                  <?php echo $data['description_64']; ?>
                              </div>
                              <div class="download-software-description descr_32">
                                  <?php echo $data['description_32']; ?>
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <div class="col-md-12 col-sm-12">
                              <p><?php echo $post->post_content; ?></p>
                          </div>
                      </div>
                      <div class="row">
                          <div class="col">
                            <?php if(!empty($data['note'])): ?>
                              <p><?php echo $data['note']; ?></p>
                            <?php endif; ?>
                          </div>
                      </div>
                  </div>
              <?php } ?>
              <div class="col-md-4 col-sm-12">
              <div class="row">
                    `<div class="col download-col">
                        <?php
                            $download_texts = [
                                'en' => ['64-bit' => 'Download 64-bit', '32-bit' => 'Download 32-bit'],
                                'de' => ['64-bit' => 'Herunterladen 64-bit', '32-bit' => 'Herunterladen 32-bit'],
                                'fr' => ['64-bit' => 'Téléchargement 64-bit', '32-bit' => 'Téléchargement 32-bit'],
                                'el' => ['64-bit' => 'Κατεβάστε 64-bit', '32-bit' => 'Κατεβάστε 32-bit'],
                                'it' => ['64-bit' => 'Scarica 64-bit', '32-bit' => 'Scarica 32-bit'],
                                'es' => ['64-bit' => 'Descargar 64-bit', '32-bit' => 'Descargar 32-bit'],
                                'pt-pt' => ['64-bit' => 'Download 64-bit', '32-bit' => 'Download 32-bit'],
                                'pt-br' => ['64-bit' => 'Download 64-bit', '32-bit' => 'Download 32-bit'],
                                'cs' => ['64-bit' => 'Stažení 64-bit', '32-bit' => 'Stažení 32-bit'],
                                'tr' => ['64-bit' => 'İndirmek 64-bit', '32-bit' => 'İndirmek 32-bit'],
                                'sk' => ['64-bit' => 'Stiahnite si 64-bit', '32-bit' => 'Stiahnite si 32-bit'],
                                'be' => ['64-bit' => 'Downloaden 64-bit', '32-bit' => 'Downloaden 32-bit']
                            ];

                        //   var_dump($data);

                            if (isset($download_texts[$lang])) {
                                $texts = $download_texts[$lang];
                            ?>

                            <?php if (!empty($data['download_link_64'])) { ?>
                                <a href="<?php echo $data['download_link_64']; ?>" target="_blank" class="download-btn whitespace--normal system-64-bit-button">
                                    <?php 
                                    if(!empty($data['download_label_2'])){
                                        echo esc_html($data['download_label_2']);
                                    }
                                    else{
                                        echo strip_tags($data['description_64']) . ' 64-bit';
                                    }
                                    ?>
                                </a>
                            <?php } ?>

                            <?php if (!empty($data['download_link_32'])) { ?>
                                <a href="<?php echo $data['download_link_32']; ?>" target="_blank" class="download-btn whitespace--normal system-32-bit-button">
                                    <?php 
                                    if(!empty($data['download_label_1'])){
                                        echo esc_html($data['download_label_1']);
                                    }
                                    else{
                                        echo strip_tags($data['description_32']) . ' 32-bit';
                                    }
                                    ?>
                                </a>
                            <?php } ?>

                            <?php if (!empty($data['additional_download_link'])) { ?>
                                <a href="<?php echo $data['additional_download_link']; ?>" target="_blank" class="download-btn whitespace--normal additional-download-button"><?php echo esc_html($data['additional_download_label']); ?></a>
                            <?php } ?>

                            <?php } ?>
                    </div>
                </div>
                <?php if (!empty($data['installation_guide'])) { ?>
                    <div id="desktop">
                        <div class="row">
                            <div class="col installation-col">
                                <a href="<?php echo home_url() . "installation-guide/?id=" . $data['installation_guide']; ?>" target="_blank" class="installation-btn whitespace--normal">
                                    <i aria-hidden="true" class="fas fa-file-alt"></i>&nbsp;<?php echo $installation_label; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div id="mobile">
                        <div class="row">
                            <div class="col installation-col-mobile">
                                <a href="<?php echo home_url() . "installation-guide/?id=" . $data['installation_guide']; ?>" target="_blank" class="installation-btn whitespace--normal">
                                    <i aria-hidden="true" class="fas fa-file-alt"></i>&nbsp;<?php echo $installation_label; ?>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col installation-col-mobile">
                                <a href="<?php echo $data['installation_video']; ?>" target="_blank" class="installation-btn whitespace--normal">
                                    <i aria-hidden="true" class="far fa-play-circle"></i>Installation video
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
              </div>
          </div>

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
</div><!--end of root element -->
<style>
        .wpml-ls-label {
            font-weight: bold;
            display: block;
            padding: 10px 0;
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

<?php if( (( $sku!="" && $lang!="" ) || ( !$found && !$product_found )) && $langSelected == false): ?>
<script>
/*document.addEventListener('DOMContentLoaded', function() {

    const ur_sc = window.location.href;
	let lang = ur_sc.match(/\/(en|de|el|fr|it|pt-pt|es|tr|cs|nl|sk|be)\//);

        var downloadCol = document.querySelector('.download-col');
        if (downloadCol) {
            downloadCol.style.display = 'none';
        }

        var langSwitcherContainer = document.querySelector('.wpml-ls-legacy-dropdown');
        var currentLanguageItem = document.querySelector('.wpml-ls-current-language');
        var firstAnchor = document.querySelector('.wpml-ls-legacy-dropdown > ul > li > a');
        
        if (firstAnchor) {
            var clonedAnchor = firstAnchor.cloneNode(true);
            clonedAnchor.setAttribute('href', '<?php echo get_permalink(); ?>');
            clonedAnchor.classList.remove('js-wpml-ls-item-toggle', 'wpml-ls-item-toggle');

            var newLi = document.createElement('li');
            newLi.className = 'wpml-ls-slot wpml-ls-item wpml-ls-item-' + lang;
            newLi.appendChild(clonedAnchor);

            var dropdown = document.querySelector('.wpml-ls-legacy-dropdown > ul > li');
            if (dropdown) {
                var label;

                switch (lang[1]) {
                    case 'de':
                        label = 'Sprache';
                        break;
                    case 'fr':
                        label = 'Langue';
                        break;
                    case 'it':
                        label = 'Lingua';
                        break;
                    case 'sk':
                        label = 'Jazyk';
                        break;
                    case 'cs':
                        label = 'Jazyk';
                        break;
                    case 'pt-pt':
                        label = 'Língua';
                        break;
                    case 'pt-br':
                        label = 'Língua';
                        break;  
                    case 'es':
                        label = 'Idioma';
                        break;
                    case 'be':
                        label = 'Taal';
                        break;
                    case 'el':
                        label = 'Γλώσσα';
                        break;
                    default:
                        label = 'Select Language';
                }
                var firstAnchor = dropdown.querySelector('a');
                firstAnchor.remove();
                
                var labelHTML = '<a href="#" class="js-wpml-ls-item-toggle wpml-ls-item-toggle"><span class="wpml-ls-native js-wpml-ls-item-toggle wpml-ls-item-toggle">' + label + '</span></a>';
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = labelHTML;
                var labelElement = tempDiv.firstChild;

                dropdown.insertBefore(labelElement, dropdown.firstChild);

                var submenu = document.querySelector('.wpml-ls-sub-menu');
                if (submenu) {
                    // submenu.appendChild(newLi);
                    submenu.insertBefore(newLi, submenu.firstChild);

                }
            }
        }
});*/
jQuery(document).ready(function($) {
    const ur_sc = window.location.href;
    let lang = ur_sc.match(/\/(en|de|el|fr|it|pt-pt|es|tr|cs|nl|sk|be)\//);

    var $downloadCol = $('.download-col');
    if ($downloadCol.length) {
        $downloadCol.hide();
    }

    var $langSwitcherContainer = $('.wpml-ls-legacy-dropdown');
    var $currentLanguageItem = $('.wpml-ls-current-language');
    var $firstAnchor = $('.wpml-ls-legacy-dropdown > ul > li > a').first();

    if ($firstAnchor.length) {
        var $clonedAnchor = $firstAnchor.clone();
        $clonedAnchor.attr('href', '<?php echo get_permalink(); ?>');
        $clonedAnchor.removeClass('js-wpml-ls-item-toggle wpml-ls-item-toggle');

        var $newLi = $('<li>', {
            class: 'wpml-ls-slot wpml-ls-item wpml-ls-item-' + lang
        }).append($clonedAnchor);

        var $dropdown = $('.wpml-ls-legacy-dropdown > ul > li').first();
        if ($dropdown.length) {
            var label;

            switch (lang ? lang[1] : '') {
                case 'de':
                    label = 'Sprache';
                    break;
                case 'fr':
                    label = 'Langue';
                    break;
                case 'it':
                    label = 'Lingua';
                    break;
                case 'sk':
                case 'cs':
                    label = 'Jazyk';
                    break;
                case 'pt-pt':
                case 'pt-br':
                    label = 'Língua';
                    break;
                case 'es':
                    label = 'Idioma';
                    break;
                case 'be':
                    label = 'Taal';
                    break;
                case 'el':
                    label = 'Γλώσσα';
                    break;
                default:
                    label = 'Select Language';
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
<?php elseif ($langSelected): ?>
<script>
jQuery(document).ready(function($) {
    const ur_sc = window.location.href;
    let lang = ur_sc.match(/\/(en|de|el|fr|it|pt-pt|es|tr|cs|nl|sk|be)\//);
    var $downloadCol = $('.download-col');
    if ($downloadCol.length) {
        $downloadCol.hide();
    }

    var $langSwitcherContainer = $('.wpml-ls-legacy-dropdown');
    var $currentLanguageItem = $('.wpml-ls-current-language');
    var $firstAnchor = $('.wpml-ls-legacy-dropdown > ul > li > a').first();

    if ($firstAnchor.length) {
        var $clonedAnchor = $firstAnchor.clone();
        $clonedAnchor.attr('href', '<?php echo get_permalink(); ?>');
        $clonedAnchor.removeClass('js-wpml-ls-item-toggle wpml-ls-item-toggle');

        var $newLi = $('<li>', {
            class: 'wpml-ls-slot wpml-ls-item wpml-ls-item-' + lang
        }).append($clonedAnchor);

        var $dropdown = $('.wpml-ls-legacy-dropdown > ul > li').first();
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
});
</script>
<?php get_footer(); ?>
