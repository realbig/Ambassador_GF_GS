<?php
/**
 * Plugin Name: Gravity Forms Google Sheets Add-On
 * Description: Allows Gravity Form submissions to be sent to a Google Sheet.
 * Version: 0.1.0
 * Author: Real Big Marketing
 * Author URI: http://realbigmarketing.com
 */

defined( 'ABSPATH' ) || die();

define( 'GF_GOOGLESHEETS_VERSION', '0.1.0' );

// If Gravity Forms is loaded, bootstrap the Google Sheets Add-On.
add_action( 'gform_loaded', array( 'GFGoogleSheets_Bootstrap', 'load' ), 5 );

/**
 * Class GFGoogleSheets_Bootstrap
 *
 * Handles the loading of the GoogleSheets Add-On and registers with the Add-On Framework.
 */
class GFGoogleSheets_Bootstrap {

	/**
	 * If the Feed Add-On Framework exists, GoogleSheets Add-On is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		if ( self::compatibility_test() ) {
			require_once 'class-gf-googlesheets.php';
		} else {
			require_once 'class-gf-googlesheets-incompatible.php';
		}

		GFAddOn::register( 'GFGoogleSheets' );
	}

	/**
	 * Determine if current server environment matches requirements to run the GoogleSheets Add-On.
	 *
	 * @access public
	 * @static
	 * @return bool
	 */
	public static function compatibility_test() {

		/* PHP must be version 5.3 or greater. */
		if ( version_compare( PHP_VERSION, '5.3.4', '<' ) ) {
			return false;
		}

		/* PHP must be 64-bit. */
		if ( PHP_INT_MAX !== 9223372036854775807 ) {
			return false;
		}

		/* openssl_random_pseudo_bytes or mcrypt_create_iv must exist. */
		if ( ! function_exists( 'openssl_random_pseudo_bytes' ) && ! function_exists( 'mcrypt_create_iv' ) ) {
			return false;
		}

		return true;
	}
}

/**
 * Returns an instance of the GFGoogleSheets class
 *
 * @see    GFGoogleSheets::get_instance()
 * @return object GFGoogleSheets
 */
function gf_googlesheets() {
	return GFGoogleSheets::get_instance();
}