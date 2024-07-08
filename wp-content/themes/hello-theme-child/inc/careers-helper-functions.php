<?php

//Option Page
add_action( 'admin_menu', 'kb_options_page' );
function kb_options_page() {

	add_options_page(
		'Company Details', // page <title>Title</title>
		'Company Details', // menu link text
		'manage_options', // capability to access the page
		'kscompany_settings', // page URL slug
		'company_details_content', // callback function with content
		2 // priority
	);
}

function company_details_content(){

	echo '<div class="wrap">
	<h1>Company Details</h1>
	<form method="post" action="">';
		settings_fields( 'kscompany_settings' ); // settings group name
		do_settings_sections( 'kscompany_settings' ); // just a page slug
		submit_button();

	echo '</form></div>';
	if(isset($_POST['kscompany_settings'])  ){
		$var2=$_POST['kscompany_settings'];
		update_option('kscompany_settings', $var2);
    }
}

add_action( 'admin_init',  'ksc_register_setting' );
function ksc_register_setting(){

    add_settings_section(
		'kscompany_section', // section ID
		__( '', 'ksc-company' ), // title (if needed)
		'kscompany_section_callback', // callback function (if needed)
		'kscompany_settings' // page slug
	);

	add_settings_field(
		'kscompany_name',
		__( 'Company Name', 'ksc-company' ),
		'kscompany_name_text_callback', // function which prints the field
		'kscompany_settings', // page slug
		'kscompany_section', // section ID
		array( 
			'label_for' => 'Company Name',
			'class' => 'ksc-company-name-class', // for <tr> element
		)
	);

	add_settings_field(
		'kscompany_description',
		__( 'Company Description', 'ksc-company' ),
		'kscompany_description_text_callback', // function which prints the field
		'kscompany_settings', // page slug
		'kscompany_section', // section ID
		array( 
			'label_for' => 'Company Description',
			'class' => 'ksc-company-details-class', // for <tr> element
		)
	);

	register_setting(
		'kscompany_settings', // settings group name
		'kscompany_settings' // option name
	);

}
/**
 * callback functions
 */
      
function kscompany_section_callback( ) {
	echo __( '', 'ksc-company' );
}

function kscompany_name_text_callback() {

	$options = get_option('kscompany_settings',array());
   
	if ( !isset( $options['kscompany_name'] ) ) {
		$options['kscompany_name'] = '';
	}

	?>
	<input type="text" id="kscompany_settings[kscompany_name]" name="kscompany_settings[kscompany_name]" value="<?php echo isset( $options['kscompany_name'] ) ?  $options['kscompany_name'] : false; ?>" />
	<?php

}
function kscompany_description_text_callback(  ) {

	$options = get_option( 'kscompany_settings',array());
    $content = isset( $options['kscompany_description'] ) ?  $options['kscompany_description'] : false;

	//printf("<textarea cols='100' rows='10' name='kscompany_settings[kscompany_description]' id='kscompany_description'>". echo isset( $options['kscompany_description'] ) ?  $options['kscompany_description'] : false; ."</textarea>");
	/*wp_editor(htmlentities(wpautop($content)), 'kscompany_description', array( 
        'textarea_name' => 'kscompany_settings[kscompany_description]',
        'media_buttons' => false) );*/
	?>
		<textarea id="kscompany_settings[kscompany_description]" name="kscompany_settings[kscompany_description]" rows="10" cols="150" type='textarea'><?php echo $options['kscompany_description']; ?></textarea>
	<?php	
}

//Careers
function kb_careers_details ($atts){
	ob_start();
	$lang = apply_filters( 'wpml_current_language', NULL );

	$atts = shortcode_atts(
		array(
           'product_id' => 'none'
		),$atts,'category_filter');                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       

	$id = get_the_ID();
	$options = get_option('kscompany_settings',array());

	/*Label Trans*/
	if($lang == 'en'){
		$vacancies_label="Vacancies";$salary_label="Salary";$office_time_label="Office time";$location_label="Location";$job_type_label="Job Type";$deadline_label="Deadline";
		$as_a ="As a";$at="at";
		$interested ="Interested? Questions? Get in touch!";
		$apply ="Apply for this Job";
	}elseif($lang == 'de'){
		$vacancies_label="Anzahl gesuchte Personen";$salary_label="Gehalt";$office_time_label="Bürozeiten";$location_label="Standort";$job_type_label="Art der Anstellung";$deadline_label="Termin";
		$as_a ="Als ein";$at="bei";
		$interested ="Interessiert? Fragen? Gleich hier klicken!";
		$apply ="Für diesen Job bewerben";
	}elseif($lang == 'el'){
		$vacancies_label="Κενές θέσεις";$salary_label="Μισθός";$office_time_label="Ώρα γραφείου";$location_label="Τοποθεσία";$job_type_label="Τύπος εργασίας";$deadline_label="Προθεσμία";
		$as_a ="Σαν";$at="στο";
		$interested ="Ενδιαφερόμενος? Ερωτήσεις; Ερχομαι σε επαφή!";
		$apply ="Κάντε αίτηση για αυτήν την εργασία";
	}elseif($lang == 'fr'){
		$vacancies_label="Postes vacants";$salary_label="Un salaire";$office_time_label="Temps de bureau";$location_label="Lieu";$job_type_label="Type d'emploi";$deadline_label="Date limite";
		$as_a ="Comme un";$at="à";
		$interested ="Intéressé? Des questions? Entrer en contact!";
		$apply ="Postuler pour ce poste";
	}elseif($lang == 'it'){
		$vacancies_label="Posti vacanti";$salary_label="Stipendio";$office_time_label="Orario d'ufficio";$location_label="Posizione";$job_type_label="Tipo di lavoro";$deadline_label="Scadenza";
		$as_a ="Come un";$at="a";
		$interested ="Interessato? Domande? Mettiti in contatto!";
		$apply ="Candidati per questo lavoro";
	}elseif($lang == 'pt-pt'){
		$vacancies_label="Vagas";$salary_label="Salário";$office_time_label="Horário do escritório";$location_label="Localização";$job_type_label="Tipo de emprego";$deadline_label="Data limite";
		$as_a ="Como um";$at="no";
		$interested ="Interessado? Perguntas? Entrar em contato!";
		$apply ="Candidatar-se para esse trabalho";
	}elseif($lang == 'es'){
		$vacancies_label="Vacantes";$salary_label="Salario";$office_time_label="Tiempo de oficina";$location_label="Ubicación";$job_type_label="Tipo de empleo";$deadline_label="Fecha límite";
		$interested ="¿Interesado? ¿Preguntas? ¡Ponerse en contacto!";
		$apply ="Aplica para este trabajo";
	}elseif($lang == 'tr'){
		$vacancies_label="Vacancies";$salary_label="Salary";$office_time_label="Office time";$location_label="Location";$job_type_label="Job Type";$deadline_label="Son tarih";
		$as_a ="Olarak";$at="de";
		$interested ="Ilgilenen? sorular? Temasta olmak!";
		$apply ="Bu İşe Başvur";
	}elseif($lang == 'cs'){
		$vacancies_label="Volná místa";$salary_label="Plat";$office_time_label="Pracovní doba";$location_label="Umístění";$job_type_label="Typ práce";$deadline_label="Uzávěrka";
		$as_a ="Jako";$at="v";
		$interested ="Zájem? otázky? Být v kontaktu!";
		$apply ="Požádejte o tuto práci";
	}elseif($lang == 'be'){
		$vacancies_label="Postes vacants";$salary_label="Un salaire";$office_time_label="Kantoortijd";$location_label="Locatie";$job_type_label="Soort baan";$deadline_label="Date limite";
		$as_a ="Als";$at="bij";
		$interested ="Geïnteresseerd? Vragen? Neem contact op!";
		$apply ="Postuler pour ce poste";
	}
	?>
  <div class="container">
  <div class="row">
  <div class="col-md-12 content">
          <h2 class="title">
		  <?php echo esc_attr(get_the_title()); ?>
          </h2>
          <!--<h3 class="alted">
            Job description
          </h3>-->
          <div class="description">
			<table class="table table-borderless">
			<tbody>
			     <?php if(get_field("vacancies", $id)  != null): ?>
				<tr>
				<th scope="row"><?php echo $vacancies_label; ?> :</th>
				<td><?php echo get_field("vacancies", $id);?></td>
				</tr>
				<?php endif; ?>
				<?php if(get_field("salary", $id) != null): ?>
				<tr>
				<th scope="row"><?php echo $salary_label; ?> :</th>
				<td><?php echo get_field("salary", $id);?></td>
				</tr>
				<?php endif; ?>
				<?php if(get_field("office_time", $id) != null) : ?>
				<tr>
				<th scope="row"><?php echo $office_time_label; ?> :</th>
				<td><?php echo get_field("office_time", $id);?></td>
				</tr>
				<?php endif; ?>
				<?php if(get_field("location", $id) !=  null): ?>
				<tr>
				<th scope="row"><?php echo $location_label; ?> :</th>
				<td><?php echo get_field("location", $id);?></td>
				</tr>
				<?php endif; ?>
				<?php if(get_field("job_type", $id) != null): ?>
				<tr>
				<th scope="row"><?php echo $job_type_label; ?> :</th>
				<td><?php echo get_field("job_type", $id);?></td>
				</tr>
				<?php endif; ?>
				<?php if(get_field("deadline", $id) != null): ?>
				<tr>
				<th scope="row"><?php echo $deadline_label; ?> :</th>
				<td><?php echo get_field("deadline", $id);?></td>
				</tr>
				<?php endif; ?>
			</tbody>
			</table>

	<p><br></p>

<?php echo get_field("find_out_more", $id); ?> 
<h3> <strong><strong><br></strong><?php echo $as_a; ?> <?php echo esc_attr(get_the_title()); ?> <?php echo $at; ?> <?php echo $options['kscompany_name']; ?>:&nbsp;</strong><br>
</h3>
<p><?php echo get_field("job_description", $id);?></p>
<h3>
<strong><?php echo $interested; ?></strong> <br>
</h3>
          </div>
          <div class="apply hidden-xs hidden-sm">
            <a class="btn btn-lg btn-danger" href="<?php echo  esc_url(get_field('easy_job_link')); ?>" target="_blank"><?php echo $apply; ?></a>
          </div>
        </div>
  </div>
</div>

<?php return ob_get_clean(); 
}
add_shortcode('careers_details', 'kb_careers_details');

function kb_company_details ($atts){
	ob_start();
	$lang = apply_filters( 'wpml_current_language', NULL );                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              

	$options = get_option('kscompany_settings',array());
?>
  <div class="container">
  <div class="row">
  <div class="description">
	<?php echo $options['kscompany_description']; ?>
	</div>
</div>
</div>

	<?php
	return ob_get_clean(); 

}

add_shortcode('company_details', 'kb_company_details');






