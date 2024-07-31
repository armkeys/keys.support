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
  $get_id = $_GET['id'];
  $trid = $wpdb->get_var("SELECT trid FROM {$table_icl_trans} WHERE element_id = {$get_id}");
   $sql = "SELECT * FROM {$table_icl_trans} WHERE trid = {$trid}";
     $query = $wpdb->get_results($sql,ARRAY_A);
     foreach($query as $pi){
       if($pi['language_code'] == 'de'){
         $eid_de =  $pi['element_id'];
         $installation_slug_de = "installation-guide/?id={$eid_de}";
       }elseif($pi['language_code'] == 'es'){
         $eid_es =  $pi['element_id'];
         $installation_slug_es = "installation-guide/?id={$eid_es}";
       }elseif($pi['language_code'] == 'fr'){
         $eid_fr =  $pi['element_id'];
         $installation_slug_fr = "installation-guide/?id={$eid_fr}";
       }elseif($pi['language_code'] == 'it'){
         $eid_it =  $pi['element_id'];
         $installation_slug_it = "installation-guide/?id={$eid_it}";
       }elseif($pi['language_code'] == 'pt-pt'){
         $eid_pt =  $pi['element_id'];
         $installation_slug_pt = "installation-guide/?id={$eid_pt}";
       }elseif($pi['language_code'] == 'en'){
        $eid_en =  $pi['element_id'];
        $installation_slug_en = "installation-guide/?id={$eid_en}";
      }elseif($pi['language_code'] == 'sk'){
        $eid_sk =  $pi['element_id'];
        $installation_slug_sk = "installation-guide/?id={$eid_sk}";
      }  
     }
 
    $installation = get_post( $_GET['id'] ); 
    $title = $installation->post_title;
    $external_url = get_field("installation_external_url", $_GET['id']);

    if($external_url == null){
      $content = wpautop($installation->post_content);
      $video_url = get_field("installation_video_url", $_GET['id']);
    }else{
      $content = $external_url;
      $video_url = null;
    }    
}else{
    //wp_redirect(home_url());
    //exit;
    if($_GET['sku'] != null){
      $sku = $_GET['sku'];
    }else{
      $sku = 0;
    }
    $query_id =  $wpdb->get_var("SELECT sp.ID FROM {$table_posts} as sp INNER JOIN {$table_postmeta} as spm ON sp.ID = spm.post_id INNER JOIN {$table_icl_trans} as sit ON sp.ID = sit.element_id WHERE sp.post_type = 'installation' AND spm.meta_key ='sku' AND spm.meta_value = {$sku} AND sit.language_code = '{$lang}' LIMIT 1");
    if($query_id == null){
          $query =  $wpdb->get_results("SELECT sp.ID, spm.meta_key,spm.meta_value, sit.trid FROM {$table_posts} as sp INNER JOIN {$table_postmeta} as spm ON sp.ID = spm.post_id INNER JOIN {$table_icl_trans} as sit ON sp.ID = sit.element_id WHERE sp.post_type = 'installation' AND spm.meta_key ='installation_repeater_group' AND sit.language_code = '{$lang}'", ARRAY_A);
          $count = count($query);

          for ( $i = 0; $i < $count; $i++ ) {
          $post_id =  $query[$i]['ID'];
          $trid = $query[$i]['trid'];     
          $installation_group = maybe_unserialize($query[$i]['meta_value']);

          for ($j = 0; $j < count($installation_group); $j++ ) {
              $is_sku_exists = in_array($sku,$installation_group[$j]);
              if($is_sku_exists){
                break;
              }
          }
              if($is_sku_exists){
                $title =  $wpdb->get_var("SELECT sp.post_title FROM {$table_posts} as sp INNER JOIN {$table_postmeta} as spm ON sp.ID = spm.post_id INNER JOIN {$table_icl_trans} as sit ON sp.ID = sit.element_id WHERE sp.post_type = 'product' AND spm.meta_key ='sku' AND spm.meta_value = {$sku} AND sit.language_code = '{$lang}'");
                break;
              }
          }
    
    }else{
      $post_id = $query_id;
      $installation = get_post($post_id); 
      $title = $installation->post_title;
    }
    $external_url = get_field("installation_external_url", $post_id);
    $installation_slug = "installation-guide/?sku={$sku}";

    if($external_url == null){
      $content = wpautop(get_post_field('post_content', $post_id));
      $video_url = get_field("installation_video_url", $post_id);
    }else{
      $content = $external_url;
      $video_url = null;
    }  
    $check_sku = get_post_meta($post_id,'installation_repeater_group');
    $sku_match = false;

    $count = count($check_sku[0]);
    for ( $i = 0; $i < $count; $i++ ) {
    if ($check_sku[0][$i]['sku'] == $sku ) {
      //var_dump($check_sku[0][$i]['sku']);
      //die();
        $sku_match = true;
      }
  	}

    if($sku_match == false){
    ?>
      <script>
       alert("PRODUCT SKU NOT FOUND")
     	 window.open("https://keys.support/en/download-center","_self");
       </script>
    <?php 
    }

  }


  $options = get_option('ksinstallation_guide_settings',array());
  
/*Label Trans*/
if($lang == 'en'){
  $options = get_option('ksinstallation_guide_settings',array());

  $email= !empty($options['kscompany_email']) ? $options['kscompany_email']: '';
  $phone = !empty($options['kscontact_number']) ? $options['kscontact_number']: '';
  $contact_us = "Contact Form";
  $contact_details = "Contact Details";
}elseif($lang == 'de'){
  $options = get_option('ksinstallation_guide_settings',array());

  $email = !empty($options['kscompany_email_de']) ? $options['kscompany_email_de'] : '';
  $phone = !empty($options['kscontact_number_de']) ? $options['kscontact_number_de'] : '';
  $contact_us = "Kontaktformular";
  $contact_details = "Kontaktdaten";
}elseif($lang == 'el'){
  $email= "willkommen@keys.express";
  $phone = "+49 163 196 83 66";
  $contact_us = "Contact Form";
  $contact_details = "Contact Details";
}elseif($lang == 'fr'){
  $options = get_option('ksinstallation_guide_settings',array());

  $email=  !empty($options['kscompany_email_fr']) ? $options['kscompany_email_fr']: '';
  $phone = !empty($options['kscontact_number_fr']) ? $options['kscontact_number_fr']: '';
  $contact_us = "Contact Form";
  $contact_details = "Formulaire de contact";
}elseif($lang == 'it'){
  $options = get_option('ksinstallation_guide_settings',array());

  $email= !empty($options['kscompany_email_it']) ? $options['kscompany_email_it']: '';
  $phone = !empty($options['kscontact_number_it']) ? $options['kscontact_number_it']: '';
  $contact_us = "Contact Form";
  $contact_details = "Dettagli del contatto";
}elseif($lang == 'pt-pt'){
  $options = get_option('ksinstallation_guide_settings',array());

  $email= !empty($options['kscompany_email_pt']) ? $options['kscompany_email_pt']: ''; 
  $phone =  !empty($options['kscontact_number_pt']) ? $options['kscontact_number_pt']: '';
  $contact_us = "Contact Form";
  $contact_details = "Detalhes do contato";
}elseif($lang == 'es'){
  $options = get_option('ksinstallation_guide_settings',array());

  $email= !empty($options['kscompany_email_es']) ? $options['kscompany_email_es']: '';
  $phone = !empty($options['kscontact_number_es']) ? $options['kscontact_number_es']: '';
  $contact_us = "Contact Form";
  $contact_details = "Detalles de contacto";
}elseif($lang == 'tr'){
  $email= "willkommen@keys.express";
  $phone = "+49 163 196 83 66";
  $contact_us = "Contact Form";
  $contact_details = "Contact Details";
}elseif($lang == 'cs'){
  $email= "willkommen@keys.express";
  $phone = "+49 163 196 83 66";
  $contact_us = "Contact Form";
  $contact_details = "Contact Details";
}elseif($lang == 'sk'){
  $options = get_option('ksinstallation_guide_settings',array());

  $email= !empty($options['kscompany_email_sk']) ? $options['kscompany_email_sk']: '';
  $phone = !empty($options['kscontact_number_sk']) ? $options['kscontact_number_sk']: '';
  $contact_us = "Kontaktný formulár";
  $contact_details = "Kontaktné údaje";
}
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
        
        $installation_slugs = [
            'de' => $installation_slug_de,
            'fr' => $installation_slug_fr,
            'it' => $installation_slug_it,
            'en' => $installation_slug_en,
            'es' => $installation_slug_es,
            'pt-pt' => $installation_slug_pt,
            'sk' => $installation_slug_sk
        ];

        foreach ($languages as $lang_code => $lang_name) {
            $slug = $installation_slugs[$lang_code];
            $flag_url = "https://keys.support/wp-content/plugins/sitepress-multilingual-cms/res/flags/{$lang_code}.png";
            $url = "https://keys.support/{$lang_code}/{$slug}";
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