<?php
if ( ! class_exists( 'WLMiSDK' ) ) {
	global $WishListMemberInstance;
	if ( file_exists( $WishListMemberInstance->plugin_dir . '/extlib/infusionsoft-sdk/isdk.php' ) ) {
		include_once $WishListMemberInstance->plugin_dir . '/extlib/infusionsoft-sdk/isdk.php';
	}
}
if ( ! class_exists( 'WLM_Infusionsoft_Connection' ) ) {
	class WLM_Infusionsoft_Connection {
		private static $services   = false;
		private static $appname    = '';
		private static $enckey     = '';
		private static $recon      = false;
		private static $enable_log = false;
		private static $log_path   = '';

		public function get_connection( $dbOn = 'off' ) {

			if ( false === self::$services || self::$recon ) {
				self::$services = new WLMiSDK();
				// Enable logging.
				if ( self::$enable_log ) {
					self::$services->enableLogging( 1 );
					if ( self::$log_path ) {
						self::$services->setLog( self::$log_path );
					}
				} else {
					self::$services->enableLogging( 0 );
				}

				$connection = self::$services->cfgCon( self::$appname, self::$enckey, $dbOn );
				if ( ! $connection ) {
					self::$services = false;
					self::$recon    = true;
				} else {
					self::$recon = false;
				}
			}

			return self::$services;
		}

		public function set_connection( $appname, $enckey ) {
			self::$appname = $appname;
			self::$enckey  = $enckey;
			self::$recon   = true;
		}

		public function enable_logging( $log_path = '' ) {
			self::$enable_log = true;
			if ( $log_path ) {
				self::$log_path = $log_path;
			}
		}
	}
}

