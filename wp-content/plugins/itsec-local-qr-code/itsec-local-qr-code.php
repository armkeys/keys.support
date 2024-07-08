<?php

/*
 * Plugin Name: iThemes Security Pro – Local QR Codes
 * Plugin URI: https://ithemes.com/security
 * Description: Generate QR codes locally instead of relying on the iThemes Security API.
 * Author: iThemes
 * Author URI: https://ithemes.com
 * Version: 1.0.1
 * Text Domain: it-l10n-iitsec-local-qr-code
 * Domain Path: /lang
 * Network: True
 * License: GPLv2
 * iThemes Package: itsec-local-qr-code
 */

function itsec_local_qr_code_load_textdomain() {
	if ( function_exists( 'determine_locale' ) ) {
		$locale = determine_locale();
	} elseif ( function_exists( 'get_user_locale' ) && is_admin() ) {
		$locale = get_user_locale();
	} else {
		$locale = get_locale();
	}

	$locale = apply_filters( 'plugin_locale', $locale, 'it-l10n-iitsec-local-qr-code' );

	load_textdomain( 'it-l10n-iitsec-local-qr-code', WP_LANG_DIR . "/plugins/itsec-local-qr-code/it-l10n-iitsec-local-qr-code-$locale.mo" );
	load_plugin_textdomain( 'it-l10n-iitsec-local-qr-code', false, basename( dirname( __FILE__ ) ) . '/lang/' );
}

add_action( 'plugins_loaded', 'itsec_local_qr_code_load_textdomain' );

function ithemes_itsec_local_qr_code_updater_register( $updater ) {
	$updater->register( 'itsec-local-qr-code', __FILE__ );
}

add_action( 'ithemes_updater_register', 'ithemes_itsec_local_qr_code_updater_register' );
require( dirname( __FILE__ ) . '/lib/updater/load.php' );

function itsec_local_qr_mode_generate_two_factor_totp( $url, $payload ) {

	require_once dirname( __FILE__ ) . '/vendor/qr-code.php';

	try {
		$qr = ITSEC_QRCode::getMinimumQRCode( urldecode( $payload ), ITSEC_QR_ERROR_CORRECT_LEVEL_L );
		$image = $qr->createImage( 4, 0 );

		ob_start();
		imagepng( $image );
		$data = ob_get_contents();
		ob_end_clean();

		if ( ! $data ) {
			return $url;
		}

		if ( ! $b64 = base64_encode( $data ) ) {
			return $url;
		}

		return sprintf( 'data:image/png;base64,%s', $b64 );
	} catch ( Exception $e ) {
		return $url;
	}
}

add_filter( 'itsec_two_factor_qr_code_url', 'itsec_local_qr_mode_generate_two_factor_totp', 10, 2 );
