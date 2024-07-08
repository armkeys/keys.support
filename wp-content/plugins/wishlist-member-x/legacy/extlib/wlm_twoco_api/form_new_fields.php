<?php
$incremental_index = time();
$countries         = include wishlistmember_instance()->plugin_dir3 . '/helpers/countries.php';
$fields            = array(
	'nonce'              => array(
		'type'  => 'hidden',
		'name'  => 'nonce',
		'label' => '',
		'value' => wp_create_nonce( 'regform-do-charge' ),
		'class' => '',
	),
	'regform_action'     => array(
		'type'  => 'hidden',
		'name'  => 'regform_action',
		'label' => '',
		'value' => 'charge',
		'class' => '',
	),
	'charge_type'        => array(
		'type'  => 'hidden',
		'name'  => 'charge_type',
		'label' => '',
		'value' => is_user_logged_in() ? 'existing' : 'new',
		'class' => '',
	),
	'subscription'       => array(
		'type'  => 'hidden',
		'name'  => 'subscription',
		'label' => '',
		'value' => $settings['subscription'],
		'class' => '',
	),
	'redirect_to'        => array(
		'type'  => 'hidden',
		'name'  => 'redirect_to',
		'label' => '',
		'value' => get_permalink(),
		'class' => '',
	),
	'sku'                => array(
		'type'  => 'hidden',
		'name'  => 'sku',
		'label' => '',
		'value' => $sku,
		'class' => '',
	),
	'sellerId'           => array(
		'type'  => 'hidden',
		'name'  => 'sellerId',
		'label' => '',
		'value' => $twocheckoutapisettings['twocheckoutapi_seller_id'],
		'class' => '',
	),
	'publishableKey'     => array(
		'type'  => 'hidden',
		'name'  => 'publishableKey',
		'label' => '',
		'value' => $twocheckoutapisettings['twocheckoutapi_publishable_key'],
		'class' => '',
	),
	'token'              => array(
		'type'  => 'hidden',
		'name'  => 'token',
		'label' => '',
		'value' => '',
		'class' => '',
	),
	// Name fields.
	$incremental_index++ => array(
		'type' => 'heading',
		'text' => __( 'Personal Information', 'wishlist-member' ),
	),
	'first_name'         => array(
		'type'        => is_user_logged_in() ? 'hidden' : 'text',
		'name'        => 'first_name',
		'label'       => __( 'First Name', 'wishlist-member' ),
		'placeholder' => __( 'First Name', 'wishlist-member' ),
		'value'       => '',
		'col'         => 'col-6',
	),
	'last_name'          => array(
		'type'        => is_user_logged_in() ? 'hidden' : 'text',
		'name'        => 'last_name',
		'label'       => __( 'Last Name', 'wishlist-member' ),
		'placeholder' => __( 'Last Name', 'wishlist-member' ),
		'value'       => '',
		'col'         => 'col-6',
	),
	'email'              => array(
		'type'        => is_user_logged_in() ? 'hidden' : 'text',
		'name'        => 'email',
		'label'       => __( 'Email', 'wishlist-member' ),
		'placeholder' => __( 'Email', 'wishlist-member' ),
		'value'       => '',
		'col'         => 'col-12',
	),

	$incremental_index++ => array(
		'type' => 'heading',
		'text' => __( 'Billing Address', 'wishlist-member' ),
	),
	$incremental_index++ => array( 'type' => 'break' ),
	// Billing Address Fields.
	'address'            => array(
		'type'        => 'text',
		'name'        => 'address',
		'label'       => __( 'Address', 'wishlist-member' ),
		'placeholder' => __( 'Address', 'wishlist-member' ),
		'value'       => '',
		'col'         => 'col-10',
	),
	'zipCode'            => array(
		'type'        => 'text',
		'name'        => 'zipCode',
		'label'       => __( 'ZIP', 'wishlist-member' ),
		'placeholder' => __( 'ZIP', 'wishlist-member' ),
		'value'       => '',
		'col'         => 'col-2',
	),
	'city'               => array(
		'type'        => 'text',
		'name'        => 'city',
		'label'       => __( 'City', 'wishlist-member' ),
		'placeholder' => __( 'City', 'wishlist-member' ),
		'value'       => '',
		'col'         => 'col-3',
	),
	'state'              => array(
		'type'        => 'text',
		'name'        => 'state',
		'label'       => __( 'State', 'wishlist-member' ),
		'placeholder' => __( 'State', 'wishlist-member' ),
		'value'       => '',
		'col'         => 'col-3',
	),
	'country'            => array(
		'type'  => 'select',
		'name'  => 'country',
		'label' => __( 'Country', 'wishlist-member' ),
		'value' => $countries,
		'col'   => 'col-6',
	),
	$incremental_index++ => array(
		'type' => 'heading',
		'text' => __( 'Card Details', 'wishlist-member' ),
	),
	$incremental_index++ => array( 'type' => 'break' ),
	// Card fields.
	'cc_fields'          => array(
		'type' => 'cc_fields',
		'has'  => array( 'cc_cvc' ),
	),

	$incremental_index++ => array( 'type' => 'break' ),

);

$heading            = empty( $twocheckoutapisettings['formheading'] ) ? 'Register for %level' : $twocheckoutapisettings['formheading'];
$heading            = str_replace( '%level', $level_name, $heading );
$panel_button_label = str_replace( '%waiting', '<span class="regform-waiting">...</span> ', $panel_btn_label );
$panel_button_label = str_replace( '%currency', $currency, $panel_button_label );
$panel_button_label = str_replace( '%amount', $amt, $panel_button_label );

$data['fields']             = $fields;
$data['heading']            = $heading;
$data['panel_button_label'] = $panel_button_label;
$data['form_action']        = $thankyouurl;
$data['id']                 = $sku;
$data['logo']               = $logo;
$data['showlogin']          = true;

$currency = $twocheckoutapisettings['currency'] ? $twocheckoutapisettings['currency'] : 'USD';
if ( $settings['subscription'] ) {
	switch ( $settings['rebill_interval_type'] ) {
		case '1':
			// Translators: %d: Interval in Days.
			$interval = _n( '%d Day', '%d Days', $settings['rebill_interval'], 'wishlist-member' );
			break;
		case '2':
			// Translators: %d: Interval in Weeks.
			$interval = _n( '%d Week', '%d Weeks', $settings['rebill_interval'], 'wishlist-member' );
			break;
		case '3':
			// Translators: %d: Interval in d.
			$interval = _n( '%d Month', '%d Months', $settings['rebill_interval'], 'wishlist-member' );
			break;
		case '4':
			// Translators: %d: Interval in Years.
			$interval = _n( '%d Year', '%d Years', $settings['rebill_interval'], 'wishlist-member' );
			break;
	}
	$interval = sprintf( $interval, $settings['rebill_interval'] );

	if ( $settings['rebill_init_amount'] ) {
		$data['payment_description'] = sprintf(
			// Translators: 1: initial amount, 2: currency, 3: recurring amount, 4: interval 
			__( '%1$.2f %2$s then %3$.2f %2$s every %4$s', 'wishlist-member' ),
			$settings['rebill_init_amount'],
			$currency,
			$settings['rebill_recur_amount'],
			$interval
		);
	} else {
		// Translators: 1: recurring amount, 2: currency, 3: interval.
		$data['payment_description'] = sprintf(
			'%1$.2f %2$s every %3$s',
			$settings['rebill_recur_amount'],
			$currency,
			$interval
		);
	}
} else {
	$data['payment_description'] = sprintf( '%.2f %s', $settings['rebill_init_amount'], $currency );
}
