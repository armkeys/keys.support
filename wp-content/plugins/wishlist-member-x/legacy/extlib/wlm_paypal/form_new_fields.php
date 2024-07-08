<?php
global $current_user;
$incremental_index = time();
$fields            = array(
	'nonce' => array(
		'type'  => 'hidden',
		'name'  => 'nonce',
		'label' => '',
		'value' => wp_create_nonce('eway-do-charge'),
		'class' => ''
	),
	'regform_action' => array(
		'type'  => 'hidden',
		'name'  => 'regform_action',
		'label' => '',
		'value' => 'charge',
		'class' => ''
	),
	'charge_type' => array(
		'type'  => 'hidden',
		'name'  => 'charge_type',
		'label' => '',
		'value' => 'new',
		'class' => '',
	),
	'subscription' => array(
		'type'  => 'hidden',
		'name'  => 'subscription',
		'label' => '',
		'value' => $settings['subscription'],
		'class' => ''
	),
	'redirect_to' => array(
		'type'  => 'hidden',
		'name'  => 'redirect_to',
		'label' => '',
		'value' => home_url() . add_query_arg(),
		'class' => ''
	),
	'sku' => array(
		'type'  => 'hidden',
		'name'  => 'sku',
		'label' => '',
		'value' => $sku,
		'class' => ''
	),
	// Name fields.
  $incremental_index++ => array('type' => 'heading', 'text' => __( 'Personal Information', 'wishlist-member' ) ),
	'first_name' => array(
		'type'        => 'text',
		'name'        => 'first_name',
		'label'       => 'First Name',
		'placeholder' => 'First Name',
		'value'       => $current_user->first_name,
	'col'         => 'col-6',
	),
	'last_name' => array(
		'type'        => 'text',
		'name'        => 'last_name',
		'label'       => 'Last Name',
		'placeholder' => 'Last Name',
		'value'       => $current_user->last_name,
	'col'         => 'col-6',
	),
	'email' => array(
		'type'        => 'text',
		'name'        => 'email',
		'label'       => 'Email',
		'placeholder' => 'Email',
		'value'       => $current_user->user_email,
	'col'         => 'col-12',
	),
  $incremental_index++ => array('type' => 'break'),
  $incremental_index++ => array('type' => 'heading', 'text' => __( 'Card Details', 'wishlist-member' ) ),

	// Card fields.
	'cc_fields' => array(
	'type' => 'cc_fields',
	'has' => ['cc_type', 'cc_cvc']
	),

  $incremental_index++ => array('type' => 'break'),
  $incremental_index++ => array('type' => 'heading', 'text' => __( 'Address', 'wishlist-member' ) ),

	'street' => array(
		'type'        => 'text',
		'name'        => 'street',
		'label'       => 'Street',
		'placeholder' => 'Street',
		'value'       => '',
	'col'         => 'col-12',
	),
	'city_name' => array(
		'type'        => 'text',
		'name'        => 'city_name',
		'label'       => 'City',
		'placeholder' => 'City',
		'value'       => '',
	'col'         => 'col-6',
	),
	'state' => array(
		'type'        => 'text',
		'name'        => 'state',
		'label'       => 'State/Province',
		'placeholder' => 'State/Province',
		'value'       => '',
	'col'         => 'col-3',
	),
	'zip_code' => array(
		'type'        => 'text',
		'name'        => 'zip_code',
		'label'       => 'Zip/Postal',
		'placeholder' => 'Zip/Postal',
		'value'       => '',
	'col'         => 'col-3',
	),
  $incremental_index++ => array('type' => 'break'),
);

$level_name = $wpm_levels[$product['sku']]['name'];
$heading    = empty($settings['formheading']) ? 'Register for %level' : $settings['formheading'];
$heading    = str_replace('%level', $level_name, $heading);

$data['payment_description'] = $amt;
$data['fields']              = $fields;
$data['heading']             = $heading;
$data['panel_button_label']  = $panel_button_label;
$data['form_action']         = $paypalprothankyou_url . '?action=purchase-direct&id=' . $sku;
$data['id']                  = $sku;
$data['logo']                = $logo;
$data['showlogin']           = true;
