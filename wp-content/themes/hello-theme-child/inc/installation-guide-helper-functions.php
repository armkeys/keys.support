<?php

//Option Page
add_action( 'admin_menu', 'ks_installation_guide_options_page' );
function ks_installation_guide_options_page() {
        add_options_page(
            __('Installation Guide Settings', 'ksig-installation-guide'), // page <title>Title</title>
            __('Installation Guide Settings', 'ksig-installation-guide'), // menu link text
            'manage_options', // capability to access the page
            'ksinstallation_guide_settings', // page URL slug
            'installation_guide_settings_content', // callback function with content
            2 // priority
        );


}

function installation_guide_settings_content(){

        echo '<div class="wrap">
        <h1>' . esc_html__('Installation Guide Settings', 'ksig-installation-guide') . '</h1>
        <form method="post" action="">';
            settings_fields( 'ksinstallation_guide_settings' ); // settings group name
            do_settings_sections( 'ksinstallation_guide_settings' ); // just a page slug
            submit_button();

        echo '</form></div>';
        if(isset($_POST['ksinstallation_guide_settings'])  ){
            $var2=$_POST['ksinstallation_guide_settings'];
            update_option('ksinstallation_guide_settings', $var2);
        }
   
}

add_action( 'admin_init',  'ksig_register_setting' );
function ksig_register_setting(){

        //EN
        add_settings_section(
            'ksinstallation_guide_section', // section ID
            __( '', 'ksig-installation-guide' ), // title (if needed)
            'ksinstallation_guide_section_callback', // callback function (if needed)
            'ksinstallation_guide_settings' // page slug
        );
        add_settings_field(
            'kscontact_number',
            __('Contact Number', 'ksig-installation-guide' ),
            'kscontact_number_text_callback', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section', // section ID
            array( 
                'label_for' =>'Contact Number' ,
                'class' => 'ksig-installation-guide-name-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_email',
            __( 'Company Email', 'ksig-installation-guide' ),
            'kscompany_email_text_callback', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section', // section ID
            array( 
                'label_for' => 'Company Email',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_website',
            __( 'Company Website', 'ksig-installation-guide' ),
            'kscompany_website_text_callback', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section', // section ID
            array( 
                'label_for' => 'Company Website',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

         //DE
         add_settings_section(
            'ksinstallation_guide_section_de', // section ID
            __( '', 'ksig-installation-guide' ), // title (if needed)
            'ksinstallation_guide_section_callback', // callback function (if needed)
            'ksinstallation_guide_settings' // page slug
        );

         add_settings_field(
            'kscontact_number_de',
            'Kontakt Nummer (DE)',
            'kscontact_number_text_callback_de', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_de', // section ID
            array( 
                'label_for' =>'Contact Number DE' ,
                'class' => 'ksig-installation-guide-contact-number', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_email_de',
            'Firmen-E-Mail (DE)',
            'kscompany_email_text_callback_de', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_de', // section ID
            array( 
                'label_for' => 'Company Email DE',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_website_de',
            'Unternehmenswebseite (DE)',
            'kscompany_website_text_callback_de', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_de', // section ID
            array( 
                'label_for' => 'Company Website DE' ,
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        //FR
        add_settings_section(
            'ksinstallation_guide_section_fr', // section ID
            __( '', 'ksig-installation-guide' ), // title (if needed)
            'ksinstallation_guide_section_callback', // callback function (if needed)
            'ksinstallation_guide_settings' // page slug
        );

         add_settings_field(
            'kscontact_number_fr',
            'Numéro de contact (FR)',
            'kscontact_number_text_callback_fr', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_fr', // section ID
            array( 
                'label_for' =>'Contact Number FR' ,
                'class' => 'ksig-installation-guide-contact-number', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_email_fr',
            "E-mail de l'entreprise (FR)",
            'kscompany_email_text_callback_fr', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_fr', // section ID
            array( 
                'label_for' => 'Company Email FR',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_website_fr',
            "Site Web d'entreprise (FR)",
            'kscompany_website_text_callback_fr', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_fr', // section ID
            array( 
                'label_for' => 'Company Website FR' ,
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        //IT
        add_settings_section(
            'ksinstallation_guide_section_it', // section ID
            __( '', 'ksig-installation-guide' ), // title (if needed)
            'ksinstallation_guide_section_callback', // callback function (if needed)
            'ksinstallation_guide_settings' // page slug
        );

         add_settings_field(
            'kscontact_number_it',
            'Numero di contatto (IT)',
            'kscontact_number_text_callback_it', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_it', // section ID
            array( 
                'label_for' =>'Contact Number IT' ,
                'class' => 'ksig-installation-guide-contact-number', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_email_it',
            "E-mail aziendale (IT)",
            'kscompany_email_text_callback_it', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_it', // section ID
            array( 
                'label_for' => 'Company Email IT',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_website_it',
            "Sito web aziendale (IT)",
            'kscompany_website_text_callback_it', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_it', // section ID
            array( 
                'label_for' => 'Company Website IT' ,
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        //ES
        add_settings_section(
            'ksinstallation_guide_section_es', // section ID
            __( '', 'ksig-installation-guide' ), // title (if needed)
            'ksinstallation_guide_section_callback', // callback function (if needed)
            'ksinstallation_guide_settings' // page slug
        );

         add_settings_field(
            'kscontact_number_es',
            'Numéro de contacto (ES)',
            'kscontact_number_text_callback_es', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_es', // section ID
            array( 
                'label_for' =>'Contact Number ES' ,
                'class' => 'ksig-installation-guide-contact-number', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_email_es',
            "Correo electrónico de la empresa (ES)",
            'kscompany_email_text_callback_es', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_es', // section ID
            array( 
                'label_for' => 'Company Email ES',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_website_es',
            "Sitio web de la empresa (ES)",
            'kscompany_website_text_callback_es', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_es', // section ID
            array( 
                'label_for' => 'Company Website ES' ,
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

         //PT
         add_settings_section(
            'ksinstallation_guide_section_pt', // section ID
            __( '', 'ksig-installation-guide' ), // title (if needed)
            'ksinstallation_guide_section_callback', // callback function (if needed)
            'ksinstallation_guide_settings' // page slug
        );

         add_settings_field(
            'kscontact_number_pt',
            'Numero de contact (PT)',
            'kscontact_number_text_callback_pt', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_pt', // section ID
            array( 
                'label_for' =>'Contact Number PT' ,
                'class' => 'ksig-installation-guide-contact-number', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_email_pt',
            "E-mail de l'entreprise (PT)",
            'kscompany_email_text_callback_pt', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_pt', // section ID
            array( 
                'label_for' => 'Company Email PT',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_website_pt',
            "Site da empresa (PT)",
            'kscompany_website_text_callback_pt', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_pt', // section ID
            array( 
                'label_for' => 'Company Website PT' ,
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        //SK
        add_settings_section(
            'ksinstallation_guide_section_sk', // section ID
            __( '', 'ksig-installation-guide' ), // title (if needed)
            'ksinstallation_guide_section_callback', // callback function (if needed)
            'ksinstallation_guide_settings' // page slug
        );

         add_settings_field(
            'kscontact_number_sk',
            'Kontaktné číslo (SK)',
            'kscontact_number_text_callback_sk', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_sk', // section ID
            array( 
                'label_for' =>'Contact Number SK' ,
                'class' => 'ksig-installation-guide-contact-number', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_email_sk',
            "E-mail de l'entreprise (SK)",
            'kscompany_email_text_callback_sk', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_sk', // section ID
            array( 
                'label_for' => 'Company Email SK',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_website_sk',
            "Site Web d'entreprise (SK)",
            'kscompany_website_text_callback_sk', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_sk', // section ID
            array( 
                'label_for' => 'Company Website SK' ,
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        //BE
        add_settings_section(
            'ksinstallation_guide_section_be', // section ID
            __( '', 'ksig-installation-guide' ), // title (if needed)
            'ksinstallation_guide_section_callback', // callback function (if needed)
            'ksinstallation_guide_settings' // page slug
        );

         add_settings_field(
            'kscontact_number_be',
            'Numéro de contact (BE)',
            'kscontact_number_text_callback_be', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_be', // section ID
            array( 
                'label_for' =>'Contact Number BE' ,
                'class' => 'ksig-installation-guide-contact-number', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_email_be',
            "E-mail de l'entreprise (BE)",
            'kscompany_email_text_callback_be', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_be', // section ID
            array( 
                'label_for' => 'Company Email BE',
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        add_settings_field(
            'kscompany_website_be',
            "Site Web d'entreprise (BE)",
            'kscompany_website_text_callback_be', // function which prints the field
            'ksinstallation_guide_settings', // page slug
            'ksinstallation_guide_section_be', // section ID
            array( 
                'label_for' => 'Company Website BE' ,
                'class' => 'ksig-installation-guide-details-class', // for <tr> element
            )
        );

        register_setting(
            'ksinstallation_guide_settings', // settings group name
            'ksinstallation_guide_settings' // option name
        );


}
/**
 * callback functions
 */
      
function ksinstallation_guide_section_callback( ) {
	echo __( '', 'ksig-installation-guide' );
}

//EN
function kscontact_number_text_callback() {

        $options = get_option('ksinstallation_guide_settings',array());
  
        if ( !isset( $options['kscontact_number'] ) ) {
            $options['kscontact_number'] = '';
        }
      echo '<input type="text" id="ksinstallation_guide_settings[kscontact_number]" name="ksinstallation_guide_settings[kscontact_number]" value="'.  $options["kscontact_number"]  .'" />';
    

}

function kscompany_email_text_callback() {
 
        $options = get_option('ksinstallation_guide_settings',array());

        if ( !isset( $options['kscompany_email'] ) ) {
            $options['kscompany_email'] = '';
        }
        echo '<input type="text" id="ksinstallation_guide_settings[kscompany_email]" name="ksinstallation_guide_settings[kscompany_email]" value="' . $options['kscompany_email'] .'" />';

    
}

function kscompany_website_text_callback() {

        $options = get_option('ksinstallation_guide_settings',array());

        if ( !isset( $options['kscompany_website'] ) ) {
            $options['kscompany_website'] = '';
        }
        echo '<input type="text" id="ksinstallation_guide_settings[kscompany_website]" name="ksinstallation_guide_settings[kscompany_website]" value="'. $options['kscompany_website'] . '" />';
    


}


//DE
function kscontact_number_text_callback_de() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscontact_number_de'] ) ) {
        $options['kscontact_number_de'] = '';
    }
  echo '<style>  tr.ksig-installation-guide-contact-number {border-top: 1px solid lightgray;}</style>';
  echo '<input type="text" id="ksinstallation_guide_settings[kscontact_number_de]" name="ksinstallation_guide_settings[kscontact_number_de]" value="'.  $options["kscontact_number_de"]  .'" />';
}

function kscompany_email_text_callback_de() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_email_de'] ) ) {
        $options['kscompany_email_de'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_email_de]" name="ksinstallation_guide_settings[kscompany_email_de]" value="' . $options['kscompany_email_de'] .'" />';


}

function kscompany_website_text_callback_de() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_website_de'] ) ) {
        $options['kscompany_website_de'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_website_de]" name="ksinstallation_guide_settings[kscompany_website_de]" value="'. $options['kscompany_website_de'] . '" />';
}

//FR
function kscontact_number_text_callback_fr() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscontact_number_fr'] ) ) {
        $options['kscontact_numbe_fr'] = '';
    }
  echo '<style>  tr.ksig-installation-guide-contact-number {border-top: 1px solid lightgray;}</style>';
  echo '<input type="text" id="ksinstallation_guide_settings[kscontact_number_fr]" name="ksinstallation_guide_settings[kscontact_number_fr]" value="'.  $options["kscontact_number_fr"]  .'" />';
}

function kscompany_email_text_callback_fr() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_email_fr'] ) ) {
        $options['kscompany_email_fr'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_email_fr]" name="ksinstallation_guide_settings[kscompany_email_fr]" value="' . $options['kscompany_email_fr'] .'" />';


}

function kscompany_website_text_callback_fr() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_website_fr'] ) ) {
        $options['kscompany_website_fr'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_website_fr]" name="ksinstallation_guide_settings[kscompany_website_fr]" value="'. $options['kscompany_website_fr'] . '" />';
}

//IT
function kscontact_number_text_callback_it() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscontact_number_it'] ) ) {
        $options['kscontact_numbe_it'] = '';
    }
  echo '<style>  tr.ksig-installation-guide-contact-number {border-top: 1px solid lightgray;}</style>';
  echo '<input type="text" id="ksinstallation_guide_settings[kscontact_numbe_it]" name="ksinstallation_guide_settings[kscontact_number_it]" value="'.  $options["kscontact_number_it"]  .'" />';
}

function kscompany_email_text_callback_it() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_email_it'] ) ) {
        $options['kscompany_email_it'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_email_it]" name="ksinstallation_guide_settings[kscompany_email_it]" value="' . $options['kscompany_email_it'] .'" />';


}

function kscompany_website_text_callback_it() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_website_it'] ) ) {
        $options['kscompany_website_it'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_website_it]" name="ksinstallation_guide_settings[kscompany_website_it]" value="'. $options['kscompany_website_it'] . '" />';
}

//ES
function kscontact_number_text_callback_es() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscontact_number_es'] ) ) {
        $options['kscontact_numbe_es'] = '';
    }
  echo '<style>  tr.ksig-installation-guide-contact-number {border-top: 1px solid lightgray;}</style>';
  echo '<input type="text" id="ksinstallation_guide_settings[kscontact_numbe_es]" name="ksinstallation_guide_settings[kscontact_number_es]" value="'.  $options["kscontact_number_es"]  .'" />';
}

function kscompany_email_text_callback_es() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_email_es'] ) ) {
        $options['kscompany_email_es'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_email_es]" name="ksinstallation_guide_settings[kscompany_email_es]" value="' . $options['kscompany_email_es'] .'" />';


}

function kscompany_website_text_callback_es() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_website_es'] ) ) {
        $options['kscompany_website_es'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_website_es]" name="ksinstallation_guide_settings[kscompany_website_es]" value="'. $options['kscompany_website_es'] . '" />';
}

//PT
function kscontact_number_text_callback_pt() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscontact_number_pt'] ) ) {
        $options['kscontact_numbe_pt'] = '';
    }
  echo '<style>  tr.ksig-installation-guide-contact-number {border-top: 1px solid lightgray;}</style>';
  echo '<input type="text" id="ksinstallation_guide_settings[kscontact_numbe_pt]" name="ksinstallation_guide_settings[kscontact_number_pt]" value="'.  $options["kscontact_number_pt"]  .'" />';
}

function kscompany_email_text_callback_pt() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_email_pt'] ) ) {
        $options['kscompany_email_pt'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_email_pt]" name="ksinstallation_guide_settings[kscompany_email_pt]" value="' . $options['kscompany_email_pt'] .'" />';


}

function kscompany_website_text_callback_pt() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_website_pt'] ) ) {
        $options['kscompany_website_pt'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_website_pt]" name="ksinstallation_guide_settings[kscompany_website_pt]" value="'. $options['kscompany_website_pt'] . '" />';
}

//SK
function kscontact_number_text_callback_sk() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscontact_number_sk'] ) ) {
        $options['kscontact_numbe_sk'] = '';
    }
  echo '<style>  tr.ksig-installation-guide-contact-number {border-top: 1px solid lightgray;}</style>';
  echo '<input type="text" id="ksinstallation_guide_settings[kscontact_numbe_sk]" name="ksinstallation_guide_settings[kscontact_number_sk]" value="'.  $options["kscontact_number_sk"]  .'" />';
}

function kscompany_email_text_callback_sk() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_email_sk'] ) ) {
        $options['kscompany_email_sk'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_email_sk]" name="ksinstallation_guide_settings[kscompany_email_sk]" value="' . $options['kscompany_email_sk'] .'" />';


}

function kscompany_website_text_callback_sk() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_website_sk'] ) ) {
        $options['kscompany_website_sk'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_website_sk]" name="ksinstallation_guide_settings[kscompany_website_sk]" value="'. $options['kscompany_website_sk'] . '" />';
}

//BE
function kscontact_number_text_callback_be() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscontact_number_be'] ) ) {
        $options['kscontact_numbe_be'] = '';
    }
  echo '<style>  tr.ksig-installation-guide-contact-number {border-top: 1px solid lightgray;}</style>';
  echo '<input type="text" id="ksinstallation_guide_settings[kscontact_numbe_be]" name="ksinstallation_guide_settings[kscontact_number_be]" value="'.  $options["kscontact_number_be"]  .'" />';
}

function kscompany_email_text_callback_be() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_email_be'] ) ) {
        $options['kscompany_email_be'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_email_be]" name="ksinstallation_guide_settings[kscompany_email_be]" value="' . $options['kscompany_email_be'] .'" />';


}

function kscompany_website_text_callback_be() {

    $options = get_option('ksinstallation_guide_settings',array());

    if ( !isset( $options['kscompany_website_be'] ) ) {
        $options['kscompany_website_be'] = '';
    }
    echo '<input type="text" id="ksinstallation_guide_settings[kscompany_website_be]" name="ksinstallation_guide_settings[kscompany_website_be]" value="'. $options['kscompany_website_be'] . '" />';
}