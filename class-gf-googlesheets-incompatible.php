<?php
/**
 * Loads if determined incompatible.
 *
 * @since {{VERSION}}
 *
 * @package GravityFormsGoogleSheets
 */

defined( 'ABSPATH' ) || die();

GFForms::include_feed_addon_framework();

class GFGoogleSheets extends GFFeedAddOn {

	protected $_version = GF_GOOGLESHEETS_VERSION;
	protected $_min_gravityforms_version = '1.9.14.26';
	protected $_slug = 'gravityformsgooglesheets';
	protected $_path = 'gravityformsgooglesheets/googlesheets.php';
	protected $_full_path = __FILE__;
	protected $_url = 'http://realbigmarketing.com';
	protected $_title = 'Gravity Forms Google Sheets Add-On';
	protected $_short_title = 'Google Sheets';
	private static $_instance = null;

	/* Permissions */
	protected $_capabilities_settings_page = 'gravityforms_googlesheets';
	protected $_capabilities_form_settings = 'gravityforms_googlesheets';
	protected $_capabilities_uninstall = 'gravityforms_googlesheets_uninstall';
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Get instance of this class.
	 * 
	 * @access public
	 * @static
	 * @return $_instance
	 */
	public static function get_instance() {
		
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}
	
	/**
	 * Checks whether the current Add-On has a settings page.
	 * 
	 * @access public
	 * @return bool true
	 */
	public function has_plugin_settings_page() {
		return true;
	}
	
	/**
	 * Setup plugin settings page.
	 * 
	 * @access public
	 * @return void
	 */
	public function plugin_settings_page() {
		
		/* Setup plugin icon .*/
		$icon = $this->plugin_settings_icon();
		if ( empty( $icon ) ) {
			$icon = '<i class="fa fa-cogs"></i>';
		}

		/* Setup page title. */
		$html = sprintf( '<h3><span>%s %s</span></h3>', $icon, $this->plugin_settings_title() );
		
		if ( ! function_exists( 'openssl_random_pseudo_bytes' ) && ! function_exists( 'mcrypt_create_iv' ) ) {
			$html .= '<p>' . esc_html__( 'Gravity Forms Dropbox Add-On requires either the "openssl_random_pseudo_bytes" or "mcrypt_create_iv" PHP function to be available. To continue using this Add-On, please configure your PHP installation to support one of those functions.', 'gravityformsgooglesheets' ) . '</p>';
		}

		if ( PHP_INT_MAX !== 9223372036854775807 ) {
			$html .= '<p>' . esc_html__( 'Gravity Forms Dropbox Add-On requires a version of PHP that supports 64-bit integers. To continue using this Add-On, please upgrade PHP to a version that supports 64-bit integers.', 'gravityformsgooglesheets' ) . '</p>';
		}
		
		if ( version_compare( PHP_VERSION, '5.3.4', '<' ) ) {
			$html .= '<p>' . esc_html__( 'Gravity Forms Dropbox Add-On requires PHP 5.3.4 or greater to run. To continue using this Add-On, please upgrade PHP.', 'gravityformsgooglesheets' ) . '</p>';
		}
		
		echo $html;
	}
}