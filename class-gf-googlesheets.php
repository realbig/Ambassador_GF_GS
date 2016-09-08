<?php
/**
 * Loads if determined compatible. The main plugin file.
 *
 * @since {{VERSION}}
 *
 * @package GravityFormsGoogleSheets
 */

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

defined( 'ABSPATH' ) || die();

GFForms::include_feed_addon_framework();

/**
 * GoogleSheets integration using the Add-On Framework.
 *
 * @see GFFeedAddOn
 */
class GFGoogleSheets extends GFFeedAddOn {

	/**
	 * Defines the version of the GoogleSheets Add-On.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_version Contains the version, defined in googlesheets.php
	 */
	protected $_version = GF_GOOGLESHEETS_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.14.26';

	/**
	 * Defines the plugin slug.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsgooglesheets';

	/**
	 * Defines the main plugin file.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsgooglesheets/googlesheets.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string
	 */
	protected $_url = 'http://realbigmarketing.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Google Sheets Add-On';

	/**
	 * Defines the short title of this Add-On.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_title The short title of the Add-On.
	 */
	protected $_short_title = 'Google Sheets';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since {{VERSION}}
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_googlesheets';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_googlesheets';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_googlesheets_uninstall';

	/**
	 * Defines the capabilities to add to roles by the Members plugin.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    array $_capabilities Capabilities to add to roles by the Members plugin.
	 */
	protected $_capabilities = array( 'gravityforms_googlesheets', 'gravityforms_googlesheets_uninstall' );

	/**
	 * Contains an instance of the Google Drive API libray, if available.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    Google_Service_Drive $api If available, contains an instance of the Google Drive API library.
	 */
	protected $api_drive = null;

	/**
	 * Contains an instance of the Google Sheets API libray, if available.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    Google_Service_Sheets $api If available, contains an instance of the Google Sheets API library.
	 */
	protected $api_sheets = null;

	/**
	 * Contains an instance of the Google Client.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    Google_Client $api If available, contains an instance of the Google Client.
	 */
	protected $api_client = null;

	/**
	 * Defines the GoogleSheets API client identifier.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $googlesheets_client_identifier GoogleSheets API client identifier.
	 */
	// TODO Check if used.
	protected $googlesheets_client_identifier = 'Gravity-Forms-GoogleSheets/1.0';

	/**
	 * Contains a queue of GoogleSheets feeds that need to be processed on shutdown.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    array $googlesheets_feeds_to_process A queue of GoogleSheets feeds that need to be processed on shutdown.
	 */
	// TODO Check if used.
	protected $googlesheets_feeds_to_process = array();

	/**
	 * Defines the nonce action used when processing GoogleSheets feeds.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    string $nonce_action Nonce action for processing GoogleSheets feeds.
	 */
	// TODO Check if used.
	protected $nonce_action = 'gform_googlesheets_upload';

	/**
	 * The notification events which should be triggered once the last feed has been processed.
	 *
	 * @since {{VERSION}}
	 * @access protected
	 * @var    array $_notification_events The notification events which should be triggered once the last feed has been processed.
	 */
	// TODO Check if used.
	protected $_notification_events = array();

	/**
	 * Get instance of this class.
	 *
	 * @access public
	 * @static
	 * @return $_instance
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Include the API and GoogleSheets Upload Field.
	 *
	 * @since {{VERSION}}
	 */
	public function pre_init() {

		parent::pre_init();

		if ( $this->is_gravityforms_supported() ) {

			// Load the GoogleSheets autoloader.
			if ( ! function_exists( '\GoogleSheets\autoload' ) ) {
//				require_once 'includes/api/autoload.php';
				require_once 'vendor/autoload.php';
			}

			// Get plugin settings.
			$settings = $this->get_plugin_settings();

			// Initialize the GoogleSheets field class.
			if ( ! rgar( $settings, 'defaultAppEnabled' ) && rgar( $settings, 'accessToken' ) ) {
//				require_once 'includes/class-gf-field-googlesheets.php';
			}
		}
	}

	/**
	 * Add GoogleSheets feed processing hooks.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		parent::init();

		// Setup feed processing on shutdown.
//		add_action( 'shutdown', array( $this, 'maybe_process_feed_on_shutdown' ), 10 );

		// Process feeds upon admin POST request.
//		add_action( 'admin_init', array( $this, 'maybe_process_feed_on_post_request' ) );

		// Add GoogleSheets field settings.
//		add_action( 'gform_field_standard_settings', array( $this, 'add_field_settings' ), 10, 2 );

		// Save GoogleSheets auth token before rendering plugin settings page.
		add_action( 'admin_init', array( $this, 'save_auth_token' ) );

		// Potentiall logout of GoogleSheets before rendering plugin settings page.
		add_action( 'admin_init', array( $this, 'api_logout' ) );
	}

	/**
	 * Add AJAX callback for retrieving folder contents.
	 *
	 * @since {{VERSION}}
	 * @access public
	 * @return void
	 */
	public function init_ajax() {

		parent::init_ajax();

		add_action( 'wp_ajax_gform_googlesheets_get_sheet_choices', array( $this, 'ajax_get_sheet_choices' ) );
		add_action( 'wp_ajax_gform_googlesheets_get_field_map', array( $this, 'ajax_get_sheet_field_map' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @access public
	 * @return array $scripts
	 */
	public function scripts() {

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = array(
			array(
				'handle'  => 'gform_googlesheets_feedsettings',
				'deps'    => array( 'jquery' ),
				'src'     => $this->get_base_url() . "/assets/js/admin/feed_settings{$min}.js",
				'version' => $this->_version,
				'enqueue' => array( array( $this, 'maybe_enqueue_feed_settings_script' ) ),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Check if GoogleSheets plugin settings script should be enqueued.
	 *
	 * @access public
	 * @return bool
	 */
	public function maybe_enqueue_feed_settings_script() {
		return 'gf_edit_forms' === rgget( 'page' ) && 'gravityformsgooglesheets' === rgget( 'subview' );
	}

	/**
	 * Save GoogleSheets auth token before rendering plugin settings page.
	 *
	 * @access public
	 * @return void
	 */
	public function save_auth_token() {

		// Confirm we're on the GoogleSheets plugin settings page.
		if ( 'gf_settings' !== rgget( 'page' ) || 'gravityformsgooglesheets' !== rgget( 'subview' ) ) {
			return;
		}

		// Start the session.
		session_start();

		// Add message if just auth'd
		if ( get_transient( 'gform_googlesheets_auth_success' ) ) {

			GFCommon::add_message( __( 'Successfully authenticated Google Sheets', 'gravityformsgooglesheets' ) );
			delete_transient( 'gform_googlesheets_auth_success' );
		}

		// Save auth token.
		if ( rgget( 'code' ) ) {

			try {

				$client = $this->get_api_client();

				$token = $client->fetchAccessTokenWithAuthCode( $_GET['code'] );

				$settings                = $this->get_plugin_settings();
				$settings['accessToken'] = $token;
				$this->update_plugin_settings( $settings );

				set_transient( 'gform_googlesheets_auth_success', '1', 30 );

				wp_redirect( $client->getRedirectUri() );
				exit();

			} catch ( Exception $e ) {

				GFCommon::add_error_message( sprintf(
					esc_html__( 'Unable to authorize with GoogleSheets: %1$s', 'gravityformsgooglesheets' ),
					$e->getMessage()
				) );
			}
		}
	}

	/**
	 * Logs out of the Google Client.
	 *
	 * @since {{VERSION}}
	 */
	public function api_logout() {

		// Confirm we're on the GoogleSheets plugin settings page.
		if ( 'gf_settings' !== rgget( 'page' ) || 'gravityformsgooglesheets' !== rgget( 'subview' ) ) {
			return;
		}

		// Add message if just deauth'd
		if ( get_transient( 'gform_googlesheets_deauth_success' ) ) {

			GFCommon::add_message( __( 'Successfully de-authorized Google Sheets', 'gravityformsgooglesheets' ) );
			delete_transient( 'gform_googlesheets_deauth_success' );
		}

		// Revoke/remove auth token
		if ( rgget( 'gform_googlesheets_deauth' ) ) {

			$settings = $this->get_plugin_settings();

			$client = $this->get_api_client();

			$client->setAccessToken( $settings['accessToken'] );
			$client->revokeToken();

			$settings['accessToken'] = false;

			$this->update_plugin_settings( $settings );

			set_transient( 'gform_googlesheets_deauth_success', '1', 30 );

			wp_redirect( $client->getRedirectUri() );
			exit();
		}
	}

	/**
	 * Sets up the Google client.
	 *
	 * @since {{VERSION}}
	 *
	 * @return Google_Client The client.
	 */
	public function get_api_client() {

		if ( $this->api_client ) {
			return $this->api_client;
		}

		$settings = $this->get_plugin_settings();

		$redirect_uri = admin_url( 'admin.php' );
		$redirect_uri = add_query_arg( array(
			'page'    => 'gf_settings',
			'subview' => 'gravityformsgooglesheets'
		), $redirect_uri );

		$client = new Google_Client();
		$client->setClientId( rgar( $settings, 'clientID' ) );
		$client->setClientSecret( rgar( $settings, 'clientSecret' ) );
		$client->setRedirectUri( $redirect_uri );
		$client->addScope( Google_Service_Sheets::DRIVE );
		$client->setAccessType( 'offline' );

		$this->api_client = $client;

		return $client;
	}

	/**
	 * Initializes GoogleSheets API if credentials are valid.
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function initialize_api() {

		/* If API object is already setup, return true. */
		if ( ! is_null( $this->api ) ) {
			return true;
		}

		/* If access token parameter is null, set to the plugin setting. */
		$access_token = $this->get_plugin_setting( 'accessToken' );

		/* If access token is empty, return null. */
		if ( rgblank( $access_token ) ) {
			return null;
		}

		/* Log that were testing the API credentials. */
		$this->log_debug( __METHOD__ . '(): Testing API credentials.' );

		try {

			$client = $this->get_api_client();

			$client->setAccessToken( $access_token );

			// Refresh token if expired
			if ( $client->isAccessTokenExpired() ) {

				$client->fetchAccessTokenWithRefreshToken( $client->getRefreshToken() );

				$settings                = $this->get_plugin_settings();
				$settings['accessToken'] = $client->getAccessToken();
				$this->update_plugin_settings( $settings );
			}

			$serviceRequest = new DefaultServiceRequest( $access_token['access_token'] );
			ServiceRequestFactory::setInstance( $serviceRequest );

			$googledrive  = new Google_Service_Drive( $client );
			$googlesheets = new Google_Service_Sheets( $client );

			// TODO Test request
//			$googlesheets->getAccountInfo();

			$this->api_drive  = $googledrive;
			$this->api_sheets = $googlesheets;

			// Log that test passed.
			$this->log_debug( __METHOD__ . '(): API credentials are valid.' );

			return true;

		} catch ( Exception $e ) {

			// Log that test failed.
			$this->log_error( __METHOD__ . '(): API credentials are invalid; ' . $e->getMessage() );

			return false;
		}

		return false;
	}

	/**
	 * Setup plugin settings fields.
	 *
	 * @access public
	 * @return array $settings
	 */
	public function plugin_settings_fields() {

		$settings = $this->get_plugin_settings();

		return array(
			array(
				'description' => $this->plugin_settings_description(),
				'fields'      => array(
					array(
						'name'              => 'clientID',
						'label'             => esc_html__( 'Client ID', 'gravityformsgooglesheets' ),
						'type'              => 'text',
						'class'             => 'medium',
						'placeholder'       => '1' === rgar( $settings, 'defaultAppEnabled' ) ? $this->get_app_key() : '',
						'feedback_callback' => array( $this, 'is_valid_app_key_secret' ),
					),
					array(
						'name'              => 'clientSecret',
						'label'             => esc_html__( 'Client Secret', 'gravityformsgooglesheets' ),
						'type'              => 'text',
						'class'             => 'medium',
						'placeholder'       => '1' === rgar( $settings, 'defaultAppEnabled' ) ? $this->get_app_key() : '',
						'feedback_callback' => array( $this, 'is_valid_app_key_secret' ),
					),
					array(
						'name'  => 'authCode',
						'label' => esc_html__( 'Authentication Code', 'gravityformsgooglesheets' ),
						'type'  => 'auth_code',
					),
					array(
						'name' => 'accessToken',
						'type' => 'hidden',
					),
				),
			),
		);
	}

	/**
	 * Prepare custom app settings settings description.
	 *
	 * @access public
	 * @return string $description
	 */
	public function plugin_settings_description() {

		$html = '<p>';
		$html .= sprintf(
			__( 'In order to use Google Sheets, you need to first create a Google App and obtain the Client ID and Client Secret. You can do so by following %sthis guide%s.', 'gravityformsgooglesheets' ),
			'<a href="https://developers.google.com/sheets/quickstart/php#step_1_turn_on_the_api_name" target="_blank">',
			'</a>'
		);
		$html .= '</p>';

		return $html;
	}

	/**
	 * Create Generate Authentication Code settings field.
	 *
	 * @access public
	 *
	 * @param  array $field Field object.
	 * @param  bool $echo (default: true) Echo field contents.
	 *
	 * @return string $html
	 */
	public function settings_auth_code( $field, $echo = true ) {

		/* Get plugin settings. */
		$settings = $this->get_plugin_settings();

		if ( ! rgar( $settings, 'clientID' ) || ( rgar( $settings, 'clientID' ) && ! $this->initialize_api() ) ) {

			$html = sprintf(
				'<div style="%2$s" id="gform_googlesheets_auth_message">%1$s</div>',
				esc_html__( 'You must provide a valid app key and secret before authenticating with GoogleSheets.', 'gravityformsgooglesheets' ),
				! rgar( $settings, 'customAppKey' ) || ! rgar( $settings, 'customAppSecret' ) ? 'display:block' : 'display:none'
			);

			$html .= sprintf(
				'<a href="%3$s" class="button" id="gform_googlesheets_auth_button" style="%2$s">%1$s</a>',
				esc_html__( 'Click here to authenticate with Google Sheets.', 'gravityformsgooglesheets' ),
				! rgar( $settings, 'clientID' ) || ! rgar( $settings, 'clientSecret' ) ? 'display:none' : 'display:inline-block',
				rgar( $settings, 'clientID' ) && rgar( $settings, 'clientSecret' ) ? $this->get_api_client()->createAuthUrl() : '#'
			);

		} else {

			$html = esc_html__( 'GoogleSheets has been authenticated with your account.', 'gravityformsgooglesheets' );
			$html .= '&nbsp;&nbsp;<i class=\"fa icon-check fa-check gf_valid\"></i><br /><br />';
			$html .= sprintf(
				' <a href="%2$s" class="button" id="gform_googlesheets_deauth_button">%1$s</a>',
				esc_html__( 'Click here to de-authorize Google Sheets', 'gravityformsgooglesheets' ),
				add_query_arg( array(
					'gform_googlesheets_deauth' => '1',
					'page'                      => 'gf_settings',
					'subview'                   => 'gravityformsgooglesheets'
				), admin_url( 'admin.php' ) )
			);
		}

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Setup fields for feed settings.
	 *
	 * @access public
	 * @return array
	 */
	public function feed_settings_fields() {

		// Defaults
		$sheet_choices = array(
			array(
				'label' => __( '- Select a Sheet -', 'gravityformsgooglesheets' ),
				'value' => '',
			),
		);

		$field_map = array();

		// If saving, populate field choices for sake of validation
		if ( rgpost( 'gform-settings-save' ) ) {

			$sheet_choices = $this->get_sheet_choices();
			$field_map = $this->get_sheet_field_map( rgpost( '_gaddon_setting_sheet' ) );
		}

		// If on a current feed, populate choices before loading so values load properly
		if ( $this->get_current_feed_id() ) {

			$feed_values = $this->get_current_settings();

			$sheet_choices = $this->get_sheet_choices();
			$field_map = $this->get_sheet_field_map( $feed_values['sheet'] );
		}

		return array(
			array(
				'title'  => '',
				'fields' => array(
					array(
						'name'     => 'feedName',
						'type'     => 'text',
						'required' => true,
						'label'    => __( 'Name', 'gravityformsgooglesheets' ),
						'tooltip'  => '<h6>' . esc_html__( 'Name', 'gravityformsgooglesheets' ) . '</h6>' . __( 'Enter a feed name to uniquely identify this setup.', 'gravityformsgooglesheets' ),
					),
					array(
						'name'     => 'sheet',
						'type'     => 'select',
						'required' => true,
						'label'    => __( 'Sheet', 'gravityformsgooglesheets' ),
						'choices'  => $sheet_choices,
						'tooltip'  => '<h6>' . esc_html__( 'Google Sheet', 'gravityformsgooglesheets' ) . '</h6>' . __( 'Select the Google Sheet that you want to push form submissions to.', 'gravityformsgooglesheets' ),
					),
					array(
						'name'      => 'fields',
						'type'      => 'field_map',
						'hidden'    => empty( $field_map ) ? true : false,
						'required'  => true,
						'label'     => __( 'Fields', 'gravityformsgooglesheets' ),
						'field_map' => $field_map,
						'tooltip'   => '<h6>' . esc_html__( 'Field Map', 'gravityformsgooglesheets' ) . '</h6>' . __( 'This is where you setup each form field to submit to a specific column in the Google Sheet.', 'gravityformsgooglesheets' ),
					),
					array(
						'name'           => 'feedCondition',
						'type'           => 'feed_condition',
						'label'          => __( 'Upload Condition', 'gravityformsgooglesheets' ),
						'checkbox_label' => __( 'Enable Condition', 'gravityformsgooglesheets' ),
						'instructions'   => __( 'Upload to GoogleSheets if', 'gravityformsgooglesheets' ),
					),
				),
			),
		);

	}

	/**
	 * Set if feeds can be created.
	 *
	 * @access public
	 * @return bool
	 */
	public function can_create_feed() {

		return $this->initialize_api();
	}

	/**
	 * Enable feed duplication.
	 *
	 * @access public
	 *
	 * @param  string $id Feed ID requesting duplication.
	 *
	 * @return bool
	 */
	public function can_duplicate_feed( $id ) {

		return true;
	}

	/**
	 * Setup columns for feed list table.
	 *
	 * @access public
	 * @return array
	 */
	public function feed_list_columns() {

		return array(
			'feedName'    => esc_html__( 'Name', 'gravityformsgooglesheets' ),
			'googleSheet' => esc_html__( 'Google Sheet', 'gravityformsgooglesheets' ),
		);
	}

	/**
	 * Get fields for the Google Sheet setting.
	 *
	 * @since {{VERSION}}
	 */
	public function get_sheet_choices() {

		if ( ! $this->initialize_api() ) {
			return array();
		}

		// Log that were reaching out to the API.
		$this->log_debug( __METHOD__ . '(): Getting Spreadsheet Feed.' );

		$choices = array(
			array(
				'label' => __( '- Select a Sheet -', 'gravityformsgooglesheets' ),
				'value' => '',
			),
		);

		try {

			// Use the Drive service to list Sheet files
			$response = $this->api_drive->files->listFiles( array(
				'q' => "mimeType='application/vnd.google-apps.spreadsheet'",
			) );

			foreach ( $response->files as $file ) {
				$choices[] = array(
					'label' => $file->name,
					'value' => $file->id,
				);
			}

			// Log that test passed.
			$this->log_debug( __METHOD__ . '(): Spreadsheet Feed returned.' );

		} catch ( Exception $e ) {

			// Log that test failed.
			$this->log_error( __METHOD__ . '(): Could not get Spreadsheet Feed; ' . $e->getMessage() );
		}

		return $choices;
	}

	/**
	 * Get fields for feed setting.
	 *
	 * @since {{VERSION}}
	 * @access public
	 *
	 * @param string $sheet_ID The Google Sheet ID.
	 *
	 * @return array $choices
	 */
	public function get_sheet_field_map( $sheet_ID ) {

		if ( ! $this->initialize_api() ) {
			return array();
		}

		$choices = array();

		$sheet_values = $this->api_sheets->spreadsheets_values->get( $sheet_ID, '1:1', array(
			'majorDimension' => 'ROWS',
		) );

		if ( isset( $sheet_values->values[0] ) ) {
			foreach ( $sheet_values->values[0] as $i => $column ) {
				$choices[] = array(
					'label' => $column,
					'value' => $i,
					'name'  => $i,
				);
			}
		}

		return $choices;
	}

	/**
	 * AJAX call for getting and populating the Google Sheet field for the feed settings.
	 *
	 * @since {{VERSION}}
	 * @access public
	 */
	public function ajax_get_sheet_choices() {

		$choices = $this->get_sheet_choices();

		wp_send_json_success( array(
			'choices' => $choices,
		) );
	}

	/**
	 * AJAX call for getting and populating the fields field for the feed settings.
	 *
	 * @since {{VERSION}}
	 * @access public
	 */
	public function ajax_get_sheet_field_map() {

		if ( ! ( $sheet_ID = rgget( 'sheet_id' ) ) ) {
			wp_send_json_error( array(
				'error' => __( 'Could not get sheet ID', 'gravityformsgooglesheets' ),
			) );
		}

		$choices = $this->get_sheet_field_map( $sheet_ID );

		$html = $this->settings_field_map( array(
			'name'      => 'fields',
			'type'      => 'field_map',
			'hidden'    => true,
			'required'  => true,
			'label'     => __( 'Fields', 'gravityformsgooglesheets' ),
			'field_map' => $choices,
			'tooltip'   => '<h6>' . esc_html__( 'Field Map', 'gravityformsgooglesheets' ) . '</h6>' . __( 'This is where you setup each form field to submit to a specific column in the Google Sheet.', 'gravityformsgooglesheets' ),
		), false );

		wp_send_json_success( array(
			'choices' => $choices,
			'html'    => $html,
		) );
	}

	/**
	 * Add feed to processing queue.
	 *
	 * @access public
	 *
	 * @param  array $feed Feed object.
	 * @param  array $entry Entry object.
	 * @param  array $form Form object.
	 */
	public function process_feed( $feed, $entry, $form ) {

		// If the GoogleSheets instance isn't initialized, do not process the feed.
		if ( ! $this->initialize_api() ) {
			$this->add_feed_error( esc_html__( 'Feed was not processed because API was not initialized.', 'gravityformsgooglesheets' ), $feed, $entry, $form );

			return;
		}

		// Set flag for adding feed to processing queue.
		$process_feed = false;

		// Loop through the form fields and work with just the file upload fields.
		foreach ( $form['fields'] as $field ) {

			$input_type = $field->get_input_type();

			if ( ! in_array( $input_type, array( 'googlesheets', 'fileupload' ) ) ) {
				continue;
			}

			if ( 'all' !== rgars( $feed, 'meta/fileUploadField' ) && $field->id != rgars( $feed, 'meta/fileUploadField' ) ) {
				continue;
			}

			// If entry value is not empty, flag for processing.
			if ( ! rgblank( $entry[ $field->id ] ) ) {
				$process_feed = true;
			}

		}

		// If this feed is being added to the processing queue, add it and disable form notifications.
		if ( $process_feed ) {

			// Log that we're adding the feed to the queue.
			$this->log_debug( __METHOD__ . '(): Adding feed #' . $feed['id'] . ' to the processing queue.' );

			// Add the feed to the queue.
			$this->googlesheets_feeds_to_process[] = array( $feed, $entry['id'], $form['id'] );

			// Disable notifications.
			add_filter( 'gform_disable_notification_' . $form['id'], array( $this, 'disable_notification' ), 10, 2 );

		}

	}

	/**
	 * Disable the notification and stash the event for processing later.
	 *
	 * @param bool $is_disabled Is the notification disabled?
	 * @param array $notification The notification properties.
	 *
	 * @return bool
	 */
	public function disable_notification( $is_disabled, $notification ) {
		$event = rgar( $notification, 'event' );
		if ( ! $is_disabled && ! in_array( $event, $this->_notification_events ) ) {
			$this->_notification_events[] = $event;
		}

		return true;
	}

	/**
	 * Process queued feeds on shutdown.
	 *
	 * @access public
	 */
	public function maybe_process_feed_on_shutdown() {

		if ( ! empty( $this->googlesheets_feeds_to_process ) ) {

			foreach ( $this->googlesheets_feeds_to_process as $index => $feed_to_process ) {

				// Log that we're sending this feed to processing.
				$this->log_debug( __METHOD__ . '(): Sending processing request for feed #' . $feed_to_process[0]['id'] . '.' );

				/* Prepare the request. */
				$post_request = array(
					'action'       => $this->nonce_action,
					'feed'         => $feed_to_process,
					'is_last_feed' => ( $index === ( count( $this->googlesheets_feeds_to_process ) - 1 ) ),
					'_nonce'       => $this->create_nonce(),
				);

				if ( $post_request['is_last_feed'] ) {
					$post_request['notification_events'] = $this->_notification_events;
				}

				/* Execute. */
				$response = wp_remote_post( admin_url( 'admin-post.php', 'https' ), array(
					'timeout'   => 0.01,
					'blocking'  => false,
					'sslverify' => apply_filters( 'https_local_ssl_verify', true ),
					'body'      => $post_request,
				) );

				if ( is_wp_error( $response ) ) {
					$this->log_error( __METHOD__ . '(): Aborting. ' . $response->get_error_message() );
				}
			}

		}

	}

	/**
	 * Process queued feed.
	 *
	 * @access public
	 * @return void
	 */
	public function maybe_process_feed_on_post_request() {

		require_once( ABSPATH . 'wp-includes/pluggable.php' );

		global $_gfgooglesheets_delete_files, $_gfgooglesheets_update_entry_fields;
		$_gfgooglesheets_delete_files = $_gfgooglesheets_update_entry_fields = array();

		$nonce = rgpost( '_nonce' );

		if ( $this->nonce_action === rgpost( 'action' ) && false !== $this->verify_nonce( $nonce ) ) {

			$this->log_debug( __METHOD__ . '(): Nonce verified preparing to process request.' );

			$feed  = $_POST['feed'][0];
			$entry = GFAPI::get_entry( $_POST['feed'][1] );
			$form  = GFAPI::get_form( $_POST['feed'][2] );

			/* Process feed. */
			$entry = GFGoogleSheets::process_feed_files( $feed, $entry, $form );

			/* Update entry links and send notifications if last feed being processed. */
			if ( rgpost( 'is_last_feed' ) ) {
				$entry = $this->update_entry_links( $entry );

				$notification_events = rgpost( 'notification_events' );
				if ( is_array( $notification_events ) ) {
					foreach ( $notification_events as $event ) {
						GFAPI::send_notifications( $form, $entry, $event );
					}
				}
			}

			$this->maybe_delete_files();

			/* Run action. */
			gf_do_action( array( 'gform_googlesheets_post_upload', $form['id'] ), $feed, $entry, $form );

		}

	}

	/**
	 * Create nonce for GoogleSheets upload request.
	 *
	 * @access public
	 * @return string
	 */
	public function create_nonce() {

		$action = $this->nonce_action;
		$i      = wp_nonce_tick();

		return substr( wp_hash( $i . $action, 'nonce' ), - 12, 10 );

	}

	/**
	 * Verify nonce for GoogleSheets upload request.
	 *
	 * @access public
	 *
	 * @param  string $nonce Nonce to be verified.
	 *
	 * @return int|bool
	 */
	public function verify_nonce( $nonce ) {

		$action = $this->nonce_action;
		$i      = wp_nonce_tick();

		// Nonce generated 0-12 hours ago.
		if ( $nonce === substr( wp_hash( $i . $this->nonce_action, 'nonce' ), - 12, 10 ) ) {
			return 1;
		}

		// Nonce generated 12-24 hours ago.
		if ( $nonce === substr( wp_hash( ( $i - 1 ) . $this->nonce_action, 'nonce' ), - 12, 10 ) ) {
			return 2;
		}

		$this->log_error( __METHOD__ . '(): Aborting. Unable to verify nonce.' );

		return false;

	}

	/**
	 * Process feed.
	 *
	 * @access public
	 *
	 * @param  array $feed Feed object.
	 * @param  array $entry Entry object.
	 * @param  array $form Form object.
	 */
	public function process_feed_files( $feed, $entry, $form ) {

		/* If the GoogleSheets instance isn't initialized, do not process the feed. */
		if ( ! gf_googlesheets()->initialize_api() ) {
			$this->add_feed_error( esc_html__( 'Feed was not processed because API was not initialized.', 'gravityformsgooglesheets' ), $feed, $entry, $form );

			return $entry;
		}

		$this->log_debug( __METHOD__ . '(): Checking form fields for files to process.' );

		foreach ( $form['fields'] as $field ) {

			$input_type = $field->get_input_type();

			/* If feed is not a file upload or GoogleSheets field, skip it. */
			if ( ! in_array( $input_type, array( 'googlesheets', 'fileupload' ) ) ) {
				continue;
			}

			/* If feed is not uploading all file upload fields or this specifc field, skip it. */
			if ( rgars( $feed, 'meta/fileUploadField' ) !== 'all' && rgars( $feed, 'meta/fileUploadField' ) != $field->id ) {
				continue;
			}

			$this->log_debug( __METHOD__ . '(): Processing field: ' . print_r( $field, true ) );
			call_user_func_array( array( $this, 'process_' . $input_type . '_fields' ), array(
				$field,
				$feed,
				$entry,
				$form
			) );

		}

		return $entry;

	}

	/**
	 * Process GoogleSheets upload fields.
	 *
	 * @access public
	 *
	 * @param  array $field Field object.
	 * @param  array $feed Feed object.
	 * @param  array $entry Entry object.
	 * @param  array $form Form object.
	 */
	public function process_googlesheets_fields( $field, $feed, $entry, $form ) {

		global $_gfgooglesheets_update_entry_fields;

		// Get field value.
		$field_value = $entry[ $field->id ];

		// If field value is empty, return.
		if ( rgblank( $field_value ) ) {
			$this->log_debug( __METHOD__ . '(): Not uploading GoogleSheets Upload field #' . $field->id . ' because field value is empty.' );

			return;
		}

		$this->log_debug( __METHOD__ . '(): Beginning upload of GoogleSheets Upload field #' . $field->id . '.' );

		// Decode field value.
		$files = json_decode( stripslashes_deep( $field_value ), true );

		// Copy files to GoogleSheets.
		foreach ( $files as &$file ) {

			// Get destination path.
			$destination_path = gf_apply_filters( 'gform_googlesheets_folder_path', $form['id'], rgars( $feed, 'meta/destinationFolder' ), $form, $field->id, $entry, $feed );
			$destination_path = strpos( $destination_path, '/' ) !== 0 ? '/' . $destination_path : $destination_path;

			// Get destination folder metadata.
			$original_md = $this->api->getMetadataWithChildren( $destination_path );

			// Prepare file name.
			$file_name = basename( $file );
			$file_name = explode( '?dl=', $file_name );
			$file_name = $file_name[0];
			$file_name = gf_apply_filters( 'gform_googlesheets_file_name', $form['id'], $file_name, $form, $field->id, $entry, $feed );

			// Begin saving the URL to GoogleSheets.
			$save_url        = $this->save_url( $file, trailingslashit( $destination_path ) . $file_name );
			$save_url_id     = $save_url['job'];
			$save_url_status = $save_url['status'];

			// If save URL failed, log error.
			if ( 'FAILED' === $save_url_status ) {

				$this->add_feed_error( sprintf( esc_html__( 'Unable to upload file: %s', 'gravityformsgooglesheets' ), $upload_response['error'] ), $feed, $entry, $form );
				continue;

			}

			while ( ! in_array( $save_url_status, array( 'FAILED', 'COMPLETE' ) ) ) {

				$save_url_job    = $this->save_url_job( $save_url_id );
				$save_url_status = $save_url_job['status'];

				sleep( 2 );

			}

			if ( 'COMPLETE' === $save_url_status ) {

				list( $changed, $new_md ) = $this->api->getMetadataWithChildrenIfChanged( $destination_path, $original_md['hash'] );

				$new_file = $this->get_new_files( $original_md, $new_md );

				$file = $this->get_shareable_link( $new_file[0]['path'] );

				continue;

			}

		}

		/* Encode the files string for lead detail. */
		$files = json_encode( $files );

		/* Update lead detail */
		GFAPI::update_entry_field( $entry['id'], $field->id, $files );

		/* Add to array to update entry value for notification */
		$_gfgooglesheets_update_entry_fields[ $field->id ] = json_encode( $files );

	}

	/**
	 * Process file upload fields.
	 *
	 * @access public
	 *
	 * @param  array $field Field object.
	 * @param  array $feed Feed object.
	 * @param  array $entry Entry object.
	 * @param  array $form Form object.
	 */
	public function process_fileupload_fields( $field, $feed, $entry, $form ) {

		global $_gfgooglesheets_update_entry_fields;

		$field_value = rgar( $entry, $field->id );

		if ( rgblank( $field_value ) ) {
			$this->log_debug( __METHOD__ . '(): Not uploading File Upload field #' . $field->id . ' because field value is empty.' );

			return;
		}

		$this->log_debug( __METHOD__ . '(): Beginning upload of file upload field #' . $field->id . '.' );

		// Handle multiple files separately.
		if ( $field->multipleFiles ) {

			// Decode the string of files.
			$files = json_decode( stripslashes_deep( $field_value ), true );

			// Process each file separately.
			foreach ( $files as &$file ) {

				// Prepare file info.
				$file_info = array(
					'name'        => basename( $file ),
					'path'        => str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $file ),
					'url'         => $file,
					'destination' => rgars( $feed, 'meta/destinationFolder' ),
				);

				// Upload file.
				$file = gf_googlesheets()->upload_file( $file_info, $form, $field->id, $entry, $feed );

			}

			// Encode the files string for lead detail.
			$file_for_lead = json_encode( $files );

		} else {

			// Prepare file info.
			$file_info = array(
				'name'        => basename( $field_value ),
				'path'        => str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $field_value ),
				'url'         => $field_value,
				'destination' => rgars( $feed, 'meta/destinationFolder' ),
			);

			/* Upload file */
			$file_for_lead = gf_googlesheets()->upload_file( $file_info, $form, $field->id, $entry, $feed );

		}

		// Update lead detail.
		GFAPI::update_entry_field( $entry['id'], $field->id, $file_for_lead );

		// Add to array to update entry value for notification.
		$_gfgooglesheets_update_entry_fields[ $field->id ] = $file_for_lead;

	}

	/**
	 * Delete files that do not need a local version.
	 *
	 * @access public
	 */
	public function maybe_delete_files() {

		global $_gfgooglesheets_delete_files;

		if ( ! empty( $_gfgooglesheets_delete_files ) ) {
			$this->log_debug( __METHOD__ . '(): Deleting local files => ' . print_r( $_gfgooglesheets_delete_files, 1 ) );
			array_map( 'unlink', $_gfgooglesheets_delete_files );
		}

	}

	/**
	 * Update entry with GoogleSheets links.
	 *
	 * @access public
	 *
	 * @param  array $entry Entry object.
	 *
	 * @return array
	 */
	public function update_entry_links( $entry ) {

		global $_gfgooglesheets_update_entry_fields;

		if ( ! empty( $_gfgooglesheets_update_entry_fields ) ) {

			foreach ( $_gfgooglesheets_update_entry_fields as $field_id => $value ) {

				if ( strpos( $value, '"' ) === 0 ) {
					$value = stripslashes( substr( substr( $value, 0, - 1 ), 1 ) );
				}

				$entry[ $field_id ] = $value;

			}

		}

		return $entry;
	}

	/**
	 * Get GoogleSheets app key.
	 *
	 * @access public
	 * @return string - GoogleSheets app key
	 */
	public function get_app_key() {

		/* Get plugin settings. */
		$settings = $this->get_plugin_settings();

		return rgar( $settings, 'defaultAppEnabled' ) == '1' ? 'eylx4df4olbnm48' : rgar( $settings, 'customAppKey' );

	}

	/**
	 * Get GoogleSheets app secret.
	 *
	 * @access public
	 * @return string - GoogleSheets app secret
	 */
	public function get_app_secret() {

		/* Get plugin settings. */
		$settings = $this->get_plugin_settings();

		return rgar( $settings, 'defaultAppEnabled' ) == '1' ? '13w3fwg3k504onk' : rgar( $settings, 'customAppSecret' );

	}

	/**
	 * Get GoogleSheets authorization URL.
	 *
	 * @access public
	 * @return string
	 */
	public function setup_web_auth() {

		/* Setup API credentials array */
		$api_credentials = array(
			'key'    => $this->get_app_key(),
			'secret' => $this->get_app_secret(),
		);

		$application_info = GoogleSheets\AppInfo::loadFromJson( $api_credentials );
		$csrf_token_store = new GoogleSheets\ArrayEntryStore( $_SESSION, 'googlesheets-auth-csrf-token' );
		$redirect_uri     = admin_url( 'admin.php?page=gf_settings&subview=gravityformsgooglesheets', 'https' );

		return new GoogleSheets\WebAuth( $application_info, $this->googlesheets_client_identifier, $redirect_uri, $csrf_token_store );
	}

	/**
	 * Generate a GoogleSheets access token.
	 *
	 * @access public
	 *
	 * @param  string $auth_code GoogleSheets authentication code.
	 *
	 * @return array
	 */
	public function generate_access_token( $auth_code ) {

		/* Setup GoogleSheets web auth */
		$web_auth = $this->setup_web_auth();

		/* Get access token */
		try {

			list( $access_token, $googlesheets_user_id ) = $web_auth->finish( $auth_code );

			return array( 'access_token' => $access_token );

		} catch ( Exception $e ) {

			/* Get error message and strip down to just JSON data */
			$message = explode( '{', $e->getMessage() );
			$message = json_decode( '{' . $message[1], true );

			if ( 'invalid_grant' === $message['error'] ) {

				$this->log_error( __METHOD__ . '(): The authentication code provided does not exist or has expired.' );

				return array( 'error' => __( 'The authentication code you provided does not exist or has expired.', 'gravityformsgooglesheets' ) );

			}

		}

	}

	/**
	 * Get folder contents.
	 *
	 * @access public
	 *
	 * @param  string $path Folder path.
	 *
	 * @return array $folder
	 */
	public function get_folder( $path ) {

		/* If the GoogleSheets instance isn't configured, exit. */
		if ( ! $this->initialize_api() ) {
			$this->log_error( __METHOD__ . '(): Unable to get contents of folder (' . $path . ') because API was not initialized.' );

			return array();
		}

		$folder_metadata = $this->api->getMetadataWithChildren( $path );

		/* If folder no longer exists, set folder to root folder. */
		if ( is_null( $folder_metadata ) ) {
			$folder_metadata = $this->api->getMetadataWithChildren( '/' );
		}

		/* Explode the folder path. */
		$folder_metadata['exploded_path'] = explode( '/', $folder_metadata['path'] );

		/* Setup folder object. */
		$folder = array(
			'id'            => strtolower( $folder_metadata['path'] ),
			'text'          => end( $folder_metadata['exploded_path'] ),
			'parent'        => strtolower( dirname( $folder_metadata['path'] ) ),
			'children'      => false,
			'child_folders' => array(),
		);

		/* Set folder name to "Gravity Forms Add-On" if root folder. */
		if ( end( $folder_metadata['exploded_path'] ) == '' && $this->get_plugin_setting( 'defaultAppEnabled' ) == '1' ) {
			$folder['text'] = esc_html__( 'Gravity Forms Add-On', 'gravityformsgooglesheets' );
		}

		foreach ( $folder_metadata['contents'] as $item ) {

			if ( rgar( $item, 'is_dir' ) ) {

				$item                  = $this->api->getMetadataWithChildren( $item['path'] );
				$item['exploded_path'] = explode( '/', $item['path'] );
				$item['has_children']  = false;

				foreach ( $item['contents'] as $child_item ) {
					if ( rgar( $child_item, 'is_dir' ) ) {
						$item['has_children'] = true;
					}
				}

				$folder['child_folders'][] = array(
					'id'       => strtolower( $item['path'] ),
					'text'     => end( $item['exploded_path'] ),
					'parent'   => strtolower( dirname( $item['path'] ) ),
					'children' => rgar( $item, 'has_children' ),
				);
			}

		}

		if ( count( $folder['child_folders'] ) > 0 ) {
			$folder['children'] = true;
		}

		return $folder;

	}

	/**
	 * Get GoogleSheets link for file.
	 *
	 * @access public
	 *
	 * @param  string $file GoogleSheets file URL.
	 *
	 * @return string
	 */
	public function get_shareable_link( $file ) {

		return is_null( $this->api ) ? null : preg_replace( '/\?.*/', '', $this->api->createShareableLink( $file ) );

	}

	/**
	 * Create GoogleSheets folder for site.
	 *
	 * @access public
	 * @return string
	 */
	public function create_site_folder() {

		/* If the GoogleSheets instance doesn't exist, exit. */
		if ( ! $this->initialize_api() ) {
			$this->log_error( __METHOD__ . '(): Unable to create site folder because API is not initialized.' );

			return false;
		}

		/* Get site URL. */
		$site_url = parse_url( get_option( 'home' ) );

		/* Create folder. */

		return $this->api->createFolder( '/' . rgar( $site_url, 'host' ) );

	}

	/**
	 * Save GoogleSheets URL.
	 *
	 * @access public
	 *
	 * @param  string $url GoogleSheets URL to be copied.
	 * @param  string $destination Destination file path.
	 *
	 * @return array
	 */
	public function save_url( $url, $destination ) {

		/* Execute request. */
		$result = wp_remote_post( 'https://api.googlesheets.com/1/save_url/auto' . $destination, array(
			'body'    => array( 'url' => $url ),
			'headers' => array( 'Authorization' => 'Bearer ' . $this->get_plugin_setting( 'accessToken' ) ),
		) );

		/* If WP_Error, log feed error. */
		if ( is_wp_error( $result ) ) {
			$this->add_feed_error( sprintf( esc_html__( 'Unable to upload file: %s', 'gravityformsgooglesheets' ), $result->get_error_messages() ), $feed, $entry, $form );

			return null;
		}

		/* Decode JSON response. */
		$result = json_decode( $result['body'], true );

		/* If the result is an error, log it. */
		if ( isset( $result['error'] ) ) {
			$this->add_feed_error( sprintf( esc_html__( 'Unable to upload file: %s', 'gravityformsgooglesheets' ), $result['error'] ), $feed, $entry, $form );

			return null;
		}

		return $result;

	}

	/**
	 * Get GoogleSheets URL Save status.
	 *
	 * @access public
	 *
	 * @param  id $job_id GoogleSheets job ID.
	 *
	 * @return array
	 */
	public function save_url_job( $job_id ) {

		/* Execute request. */
		$result = wp_remote_get( 'https://api.googlesheets.com/1/save_url_job/' . $job_id, array(
			'headers' => array( 'Authorization' => 'Bearer ' . $this->get_plugin_setting( 'accessToken' ) ),
		) );

		/* If WP_Error, log feed error. */
		if ( is_wp_error( $result ) ) {
			$this->add_feed_error( sprintf( esc_html__( 'Unable to upload file: %s', 'gravityformsgooglesheets' ), $result->get_error_messages() ), $feed, $entry, $form );

			return null;
		}

		/* Decode JSON response. */
		$result = json_decode( $result['body'], true );

		/* If the result is an error, log it. */
		if ( isset( $result['error'] ) ) {
			$this->add_feed_error( sprintf( esc_html__( 'Unable to upload file: %s', 'gravityformsgooglesheets' ), $result['error'] ), $feed, $entry, $form );

			return null;
		}

		return $result;

	}

	/**
	 * Upload file to GoogleSheets.
	 *
	 * @access public
	 *
	 * @param array $file
	 * @param array $form
	 * @param int $field_id
	 * @param array $entry
	 * @param array $feed
	 *
	 * @return string
	 */
	public function upload_file( $file, $form, $field_id, $entry, $feed ) {

		global $_gfgooglesheets_delete_files;

		/* If the GoogleSheets instance isn't initialized, do not upload the file. */
		if ( ! $this->initialize_api() ) {
			return rgar( $file, 'url' );
		}

		/* Filter the folder folder path */
		$folder_path        = gf_apply_filters( 'gform_googlesheets_folder_path', $form['id'], $file['destination'], $form, $field_id, $entry, $feed );
		$destination_folder = $this->api->getMetadataWithChildren( $folder_path );

		/* If the folder path provided is not a folder, return the current file url. */
		if ( ! rgblank( $destination_folder ) && ! rgar( $destination_folder, 'is_dir' ) ) {
			$this->log_error( __METHOD__ . '(): Unable to upload file because destination is not a folder.' );

			return rgar( $file, 'url' );
		}

		/* If the folder doesn't exist, attempt to create it. If it can't be created, return the current file url. */
		if ( rgblank( $destination_folder ) ) {

			try {

				$this->api->createFolder( $folder_path );
				$destination_folder = $this->api->getMetadataWithChildren( $folder_path );

			} catch ( Exception $e ) {

				$this->log_error( __METHOD__ . '(): Unable to upload file because destination folder could not be created.' );

				return $file['url'];

			}

		}

		/* Filter the file name. */
		$file['name'] = gf_apply_filters( 'gform_googlesheets_file_name', $form['id'], $file['name'], $form, $field_id, $entry, $feed );
		if ( rgblank( $file['name'] ) ) {
			$file['name'] = basename( $file['path'] );
		}

		/* Upload the file */
		$file_handler  = fopen( $file['path'], 'rb' );
		$uploaded_file = $this->api->uploadFileChunked( trailingslashit( $folder_path ) . $file['name'], GoogleSheets\WriteMode::add(), $file_handler );
		$this->log_debug( __METHOD__ . '(): Result => ' . print_r( $uploaded_file, 1 ) );
		fclose( $file_handler );

		/* Check if we're storing a local version. */
		$store_local_version = gf_apply_filters( 'gform_googlesheets_store_local_version', array(
			$form['id'],
			$field_id
		), false, $file, $field_id, $form, $entry, $feed );

		/* If we're not saving a local copy, set this file for deletion. */
		if ( ! $store_local_version && ! in_array( $file['path'], $_gfgooglesheets_delete_files ) ) {
			$this->log_debug( __METHOD__ . '(): Registering local file for deletion.' );
			$_gfgooglesheets_delete_files[] = $file['path'];
		}

		/* If we are saving a local copy, remove the file from the deletion array. */
		if ( $store_local_version && ( $file_array_key = array_search( $file['path'], $_gfgooglesheets_delete_files ) ) !== false ) {
			unset( $_gfgooglesheets_delete_files[ $file_array_key ] );
		}

		/* Return the public link */

		return $this->get_shareable_link( trailingslashit( $folder_path ) . basename( $uploaded_file['path'] ) );

	}

	/**
	 * Get newly uploaded GoogleSheets files by comparing folder metadata.
	 *
	 * @access public
	 *
	 * @param array $metadata_a
	 * @param array $metadata_b
	 *
	 * @return array $new_files
	 */
	public function get_new_files( $metadata_a, $metadata_b ) {

		$file_revisions = array();
		$new_files      = array();

		foreach ( $metadata_a['contents'] as $content ) {

			$file_revisions[] = $content['rev'];

		}

		foreach ( $metadata_b['contents'] as $i => $content ) {

			if ( ! in_array( $content['rev'], $file_revisions ) ) {
				$new_files[] = $content;
			}

		}

		return $new_files;

	}

	/**
	 * Checks if a previous version was installed and enable default app flag if no custom app was used.
	 *
	 * @access public
	 *
	 * @param string $previous_version The version number of the previously installed version.
	 */
	public function upgrade( $previous_version ) {

		$previous_is_pre_custom_app_only = ! empty( $previous_version ) && version_compare( $previous_version, '1.0.6', '<' );

		if ( $previous_is_pre_custom_app_only ) {

			$settings = $this->get_plugin_settings();

			if ( ! rgar( $settings, 'customAppEnable' ) && $this->initialize_api() ) {
				$settings['defaultAppEnabled'] = '1';
			}

			unset( $settings['customAppEnable'] );

			$this->update_plugin_settings( $settings );

		}

	}

}

