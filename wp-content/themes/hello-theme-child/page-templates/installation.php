<?php

/*
 Template Name: Installation
 Template Post Type: post, page, event, installation
*/
   
get_header();
global $wpdb;
$lang = apply_filters( 'wpml_current_language', NULL );
$table_posts = $wpdb->prefix."posts";
$table_postmeta = $wpdb->prefix."postmeta";
$table_icl_trans = $wpdb->prefix."icl_translations";
$installation_slug;
$status;


if($_GET['id'] != null){
  $get_id = intval($_GET['id']); 
  $trid = $wpdb->get_var("SELECT trid FROM {$table_icl_trans} WHERE element_id = {$get_id}");

   // Fetch all translations related to the trid
    $query = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table_icl_trans} WHERE trid = %d", 
        $trid
    ), ARRAY_A);

    // Initialize array to hold the slugs for each language
    $installation_slugs = [
        'de'    => null,
        'es'    => null,
        'fr'    => null,
        'it'    => null,
        'pt-pt' => null,
        'en'    => null,
        'sk'    => null,
        'be'    => null,
    ];

    // Iterate through the results and build the slug based on language_code
    foreach($query as $pi) {
        if(array_key_exists($pi['language_code'], $installation_slugs)) {
            $eid = $pi['element_id'];
            $installation_slugs[$pi['language_code']] = "installation-guide/?id={$eid}";
        }
    }
 
    $installation = get_post($get_id); 
    
    if ($installation) {
        $title = $installation->post_title;
        $external_url = get_field("installation_external_url", $get_id);
    
        if($external_url == null){
            $content = wpautop($installation->post_content);
            $video_url = get_field("installation_video_url", $_GET['id']);
        } else {
            $content = $external_url;
            $video_url = null;
        }
    } else {
        // Handle case where the installation is not found
        echo '
        <div class="modal fade" id="installationNotFoundModal" tabindex="-1" role="dialog" aria-labelledby="installationNotFoundModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installationNotFoundModalLabel">Notification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Installation Guide not found
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
        </div>

        <script type="text/javascript">
        // Trigger the modal to show
        jQuery(document).ready(function(){
            jQuery("#installationNotFoundModal").modal("show");
        });
        </script>
        ';

        // Delay the redirect to allow the user to see the modal
        echo '
        <script type="text/javascript">
        setTimeout(function(){
            window.location.href = "' . home_url() . '";
        }, 5000); // Redirects after 5 seconds
        </script>
        ';

        // Exit to ensure no further processing
        exit();
 
    }
}else{
    // Sanitize SKU input from URL, default to 0 if not provided
    $sku = isset($_GET['sku']) && !empty($_GET['sku']) ? sanitize_text_field($_GET['sku']) : 0;

    if ($sku > 0) {
        // First SQL query to search by 'sku'
        $query_id = $wpdb->get_var($wpdb->prepare(
            "SELECT sp.ID 
            FROM {$table_posts} as sp
            INNER JOIN {$table_postmeta} as spm ON sp.ID = spm.post_id
            INNER JOIN {$table_icl_trans} as sit ON sp.ID = sit.element_id
            WHERE sp.post_type = 'installation'
            AND spm.meta_key = 'sku'
            AND spm.meta_value = %s
            AND sit.language_code = %s
            LIMIT 1", 
            $sku, $lang
        ));
    
        if ($query_id) {
            // If SKU is found in the first query
            $post_id = $query_id;
            $installation = get_post($post_id); 
            $title = $installation->post_title;
        } else {
            // If no result, search within 'installation_repeater_group'
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT sp.ID, spm.meta_value 
                FROM {$table_posts} as sp
                INNER JOIN {$table_postmeta} as spm ON sp.ID = spm.post_id
                INNER JOIN {$table_icl_trans} as sit ON sp.ID = sit.element_id
                WHERE sp.post_type = 'installation'
                AND spm.meta_key = 'installation_repeater_group'
                AND sit.language_code = %s", 
                $lang
            ));
    
            $post_id = 0;
            foreach ($results as $result) {
                $meta_value = maybe_unserialize($result->meta_value);
                if (is_array($meta_value)) {
                    foreach ($meta_value as $group) {
                        if (isset($group['sku']) && $group['sku'] == $sku) {
                            $post_id = $result->ID;
                            break 2; // Exit both loops when found
                        }
                    }
                }
            }
    
            if ($post_id > 0) {
                $installation = get_post($post_id);
                $title = $installation->post_title;
            } else {              
                // If no results found
                echo '
                <div class="modal fade" id="skuNotFoundModal" tabindex="-1" role="dialog" aria-labelledby="skuNotFoundModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="skuNotFoundModalLabel">Notification</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Product SKU not found
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    </div>
                </div>
                </div>

                <script type="text/javascript">
                // Trigger the modal to show
                jQuery(document).ready(function(){
                    jQuery("#skuNotFoundModal").modal("show");
                });
                </script>
                ';

                // Delay the redirect to allow the user to see the modal
                echo '
                <script type="text/javascript">
                setTimeout(function(){
                    window.location.href = "' . home_url() . '";
                }, 5000); // Redirects after 5 seconds
                </script>
                ';

                // Exit to ensure no further processing
                exit();

            }
        }
    
        $external_url = get_field("installation_external_url", $post_id);
        $installation_slug = "installation-guide/?sku={$sku}";
    
        if ($external_url == null) {
            $content = wpautop(get_post_field('post_content', $post_id));
            $video_url = get_field("installation_video_url", $post_id);
        } else {
            $content = $external_url;
            $video_url = null;
        }
    } else {
           // Check if the request is via the post permalink without SKU or ID
           if (is_singular('installation')) {
                    global $post;

                    $installation = get_post($post->ID);

                    if ($installation) {
                        $title = $installation->post_title;
                        $content = wpautop($installation->post_content);

                        $external_url = get_field("installation_external_url", $post->ID);
                        $video_url = get_field("installation_video_url", $post->ID);

                        // Override content if an external URL exists
                        if ($external_url) {
                            $content = '<a href="' . esc_url($external_url) . '" class="btn btn-danger" target="_blank">Click here for details</a>';
                            $video_url = null; // Clear video URL if using external content
                        }
                    } else {
                        // Handle case where the post is not found
                        echo '<div class="modal fade" id="postNotFoundModal" tabindex="-1" role="dialog" aria-labelledby="postNotFoundModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="postNotFoundModalLabel">Notification</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Installation Guide not found.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script type="text/javascript">
                            jQuery(document).ready(function(){
                                jQuery("#postNotFoundModal").modal("show");
                            });
                            setTimeout(function(){
                                window.location.href = "' . home_url() . '";
                            }, 5000);
                        </script>';
                        exit();
                    }
        } else {
                    // Handle requests with missing or invalid SKU/ID and no permalink
                    echo '<script type="text/javascript">
                            alert("No valid SKU or ID provided.");
                            window.location.href = "' . home_url() . '";
                        </script>';
                    exit();
        }
    }
    

  }


  $options = get_option('ksinstallation_guide_settings', array());

  // Define default values for email and phone
  $email_default = "willkommen@keys.express";
  $phone_default = "+49 163 196 83 66";
  
  // Define translations for different languages
  $translations = [
      'en' => [
          'email_key' => 'kscompany_email',
          'phone_key' => 'kscontact_number',
          'contact_us' => 'Contact Form',
          'contact_details' => 'Contact Details'
      ],
      'de' => [
          'email_key' => 'kscompany_email_de',
          'phone_key' => 'kscontact_number_de',
          'contact_us' => 'Kontaktformular',
          'contact_details' => 'Kontaktdaten'
      ],
      'el' => [
          'email' => $email_default,
          'phone' => $phone_default,
          'contact_us' => 'Contact Form',
          'contact_details' => 'Contact Details'
      ],
      'fr' => [
          'email_key' => 'kscompany_email_fr',
          'phone_key' => 'kscontact_number_fr',
          'contact_us' => 'Contact Form',
          'contact_details' => 'Formulaire de contact'
      ],
      'it' => [
          'email_key' => 'kscompany_email_it',
          'phone_key' => 'kscontact_number_it',
          'contact_us' => 'Contact Form',
          'contact_details' => 'Dettagli del contatto'
      ],
      'pt-pt' => [
          'email_key' => 'kscompany_email_pt',
          'phone_key' => 'kscontact_number_pt',
          'contact_us' => 'Contact Form',
          'contact_details' => 'Detalhes do contato'
      ],
      'es' => [
          'email_key' => 'kscompany_email_es',
          'phone_key' => 'kscontact_number_es',
          'contact_us' => 'Contact Form',
          'contact_details' => 'Detalles de contacto'
      ],
      'tr' => [
          'email' => $email_default,
          'phone' => $phone_default,
          'contact_us' => 'Contact Form',
          'contact_details' => 'Contact Details'
      ],
      'cs' => [
          'email' => $email_default,
          'phone' => $phone_default,
          'contact_us' => 'Contact Form',
          'contact_details' => 'Contact Details'
      ],
      'sk' => [
          'email_key' => 'kscompany_email_sk',
          'phone_key' => 'kscontact_number_sk',
          'contact_us' => 'Kontaktný formulár',
          'contact_details' => 'Kontaktné údaje'
      ],
      'be' => [
          'email_key' => 'kscompany_email_be',
          'phone_key' => 'kscontact_number_be',
          'contact_us' => 'Kontaktný formulár',
          'contact_details' => 'Kontaktné údaje'
      ]
  ];
  
  // Get translation for the current language
  $translation = isset($translations[$lang]) ? $translations[$lang] : $translations['en'];
  
  // Retrieve email and phone from options if available, otherwise use defaults
  $email = isset($translation['email_key']) ? (!empty($options[$translation['email_key']]) ? $options[$translation['email_key']] : '') : $translation['email'];
  $phone = isset($translation['phone_key']) ? (!empty($options[$translation['phone_key']]) ? $options[$translation['phone_key']] : '') : $translation['phone'];
  
  // Assign contact form and details text
  $contact_us = $translation['contact_us'];
  $contact_details = $translation['contact_details'];
  
 ?>   
<style>
  .dbox .icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #BF3535;
    margin: 0 auto;
    margin-bottom: 20px;
}
span.fa  {
  color: #FFF;
}
.wpml-ls-statics-footer{
  display:none;
}
.embed-responsive-16by9::before {
    padding-top: 10% !important;
}

  </style>

<div class="container-fluid bg-3" style="padding:30px 30px 0 30px">
  <div class="row">
      <div class="col-sm-2"></div>
      <div class="col-sm-8 text-justify card border-danger mb-3 pl-0 pr-0">
        <div class="card-header"><h2 class="text-center card-title text-danger"><?php echo $title; ?></h2></div>
        <div class="card-body">
          <?php if ($external_url == null) {?>
          <p class="card-text"><?php echo $content; ?></p>
          <?php }else{ ?>
          <p class="text-center"><a href="<?php echo $content; ?>" class="btn btn-danger" target="_blank">Click here for details</a>
          <?php } ?>
          <br/>
            <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="<?php echo  $video_url; ?>"></iframe>
            </div>
        </div>
      </div>
      <div class="col-sm-2"></div>
  </div>
  <!--start: contact us-->
<div class="row">
  <div class="col-lg-12 col-12 p-0 text-center">
  <h4 class=""><?php echo $contact_details; ?></h4>
  </div>
</div>
 
    <div class="row">
    <div class="col-md-8 offset-md-2">
          <div class="row bg-light" style="padding:30px;">
              <div class="col-md-3">
                <div class="dbox w-100 text-center">
                  <div class="icon d-flex align-items-center justify-content-center">
                  <span class="fa fa-pen"></span>
                  </div>
                  <div class="text">
                  <p><span><a href="https://keys.express/index.php/<?php echo strtoupper($lang); ?>/contact-us" target="_blank"><?php echo $contact_us; ?></a></span></p>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="dbox w-100 text-center">
                  <div class="icon d-flex align-items-center justify-content-center">
                  <span class="fa fa-phone"></span>
                  </div>
                  <div class="text">
                  <p><a href="tel://<?php echo $phone; ?>"><?php echo $phone; ?></a></p>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                  <div class="dbox w-100 text-center">
                  <div class="icon d-flex align-items-center justify-content-center">
                  <span class="fa fa-paper-plane"></span>
                  </div>
                  <div class="text">
                  <!--<p><a href="mailto:<?php echo !empty($email) ? $email : "welcome@keys.express"; ?>"><?php echo !empty($email) ? $email : "welcome@keys.express"; ?></a></p>-->
                  <p><a href="mailto:<?php echo $email; ?>"><?php echo  $email; ?></a></p>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="dbox w-100 text-center">
                  <div class="icon d-flex align-items-center justify-content-center">
                  <span class="fa fa-globe"></span>
                  </div>
                  <div class="text">
                  <p><a href="https://keys.express/<?php echo strtoupper($lang); ?>/" target="_blank">keys.express</a></p>
                  </div>
                </div>
              </div>
           </div>
        </div>
     </div>

<!--end: contact us-->
</div><!-- end container -->

<?php get_footer(); ?>
<div class="wpml-ls wpml-ls-legacy-list-horizontal text-center">
<ul>
        <?php
        $languages = [
            'de' => 'Deutsch',
            'fr' => 'Français',
            'it' => 'Italiano',
            'en' => 'English',
            'es' => 'Español',
            'pt-pt' => 'Português',
            'sk' => 'Slovenčina'
        ];

        // Get the SKU from the query parameters
        $sku = isset($_GET['sku']) ? $_GET['sku'] : 'not-found';

        if ($sku === 'not-found' && isset($_GET['id'])) {
            $get_id = intval($_GET['id']);
            $sku_field = get_field('sku', $get_id);

            // If the SKU field is found, use it; otherwise, keep 'not-found'
            if ($sku_field) {
                $sku = $sku_field;
            }
        }

        //$base_url = home_url();
        $base_url = "https://keys.support";

        foreach ($languages as $lang_code => $lang_name) {
               // Set the slug based on whether SKU was found or the permalink is used
                if ($sku === 'not-found' && !isset($_GET['id'])) {
                    $permalink = get_permalink();
                    $slug = "installation/".basename($permalink); 
                } else {
                    $slug = "installation-guide/?sku={$sku}";
                }
            
            $flag_url = "{$base_url}/wp-content/plugins/sitepress-multilingual-cms/res/flags/{$lang_code}.svg";
            $url = "{$base_url}/{$lang_code}/{$slug}";
            echo "<li class='wpml-ls-slot-footer wpml-ls-item wpml-ls-item-{$lang_code} wpml-ls-item-legacy-list-horizontal'>
                    <a href='{$url}' class='wpml-ls-link'>
                        <img class='wpml-ls-flag' src='{$flag_url}' alt='' width='18' height='12'>
                        <span class='wpml-ls-native' lang='{$lang_code}'>{$lang_name}</span>
                    </a>
                </li>";
        }
        ?>
    </ul>
</div>