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
    "download_link_32",
    "download_link_64",
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
    'es' => "Guía de instalación",
    'tr' => "Yükleme Rehberi",
    'cs' => "Průvodce instalací",
    'sk' => "Návod na inštaláciu",
    'be' => "Installatiegids"
];

$installation_label = $installation_labels[$lang] ?? $installation_labels['en'];
?>

<div class="container download-center-template"><!--start of root element -->
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
              <div class="col-md-6 col-sm-12">
                  <?php 
                  echo do_shortcode('[wpml_language_switcher type="widget" flags=1 native=1 translated=0][/wpml_language_switcher]');
                  echo do_shortcode('[product_category_filter product_id=' . $id . ']');
                  ?>
                  <br/>
              </div>

              <?php if (empty($data['sku']) && empty($data['description_32']) && empty($data['download_link_32']) && empty($data['installation_guide']) && empty($data['installation_video'])) { ?>
                  <div class="col-md-6 col-sm-12">
                      <div class="download-software-wrapper custom-success-bg">
                          <div class="row">
                              <div class="col-sm-12">
                                  <?php echo $data['download_page_content']; ?>
                              </div>
                          </div>
                      </div>
                  </div>
              <?php } else { ?>
                  <div class="col-md-6 col-sm-12 col-2-color">
                      <div class="row">
                          <div class="col-md-6 product-image">
                              <img src="<?php echo $data['image_url'] ?: 'https://via.placeholder.com/252/dc3545/FFFFFF/?text=No%20Image%20Available'; ?>" alt="Product Image" width="252" height="252" loading="lazy">
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
                          <div class="col download-col">
                              <?php
                                  $download_texts = [
                                      'en' => ['64-bit' => 'Download 64-bit', '32-bit' => 'Download 32-bit'],
                                      'de' => ['64-bit' => 'Herunterladen 64-bit', '32-bit' => 'Herunterladen 32-bit'],
                                      'fr' => ['64-bit' => 'Téléchargement 64-bit', '32-bit' => 'Téléchargement 32-bit'],
                                      'el' => ['64-bit' => 'Κατεβάστε 64-bit', '32-bit' => 'Κατεβάστε 32-bit'],
                                      'it' => ['64-bit' => 'Scarica 64-bit', '32-bit' => 'Scarica 32-bit'],
                                      'es' => ['64-bit' => 'Descargar 64-bit', '32-bit' => 'Descargar 32-bit'],
                                      'pt-pt' => ['64-bit' => 'Download 64-bit', '32-bit' => 'Download 32-bit'],
                                      'cs' => ['64-bit' => 'Stažení 64-bit', '32-bit' => 'Stažení 32-bit'],
                                      'tr' => ['64-bit' => 'İndirmek 64-bit', '32-bit' => 'İndirmek 32-bit'],
                                      'sk' => ['64-bit' => 'Stiahnite si 64-bit', '32-bit' => 'Stiahnite si 32-bit'],
                                      'be' => ['64-bit' => 'Downloaden 64-bit', '32-bit' => 'Downloaden 32-bit']
                                  ];

                                  if (isset($download_texts[$lang])) {
                                      $texts = $download_texts[$lang];
                                      if (!empty($data['download_link_64'])) { ?>
                                      <a href="<?php echo $data['download_link_64']; ?>" target="_blank" class="download-btn whitespace--normal system-64-bit-button"><?php echo esc_html($texts['64-bit']); ?></a>
                                  <?php } ?>
                                  <?php if (!empty($data['download_link_32'])) { ?>
                                      <a href="<?php echo $data['download_link_32']; ?>" target="_blank" class="download-btn whitespace--normal system-32-bit-button"><?php echo esc_html($texts['32-bit']); ?></a>
                                  <?php }} ?>
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
                      <br/>
                      <div class="row">
                          <div class="col">
                              <p><?php echo $data['note']; ?></p>
                          </div>
                      </div>
                  </div>
              <?php } ?>
          </div>

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

<?php get_footer(); ?>