<?php
/**
 * Loads if determined compatible. The main plugin file.
 *
 * @since {{VERSION}}
 *
 * @package GravityFormsGoogleSheets
 */

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
	 * @access public
	 * @var    Google_Service_Drive $api If available, contains an instance of the Google Drive API library.
	 */
	public $api_drive = null;

	/**
	 * Contains an instance of the Google Sheets API libray, if available.
	 *
	 * @since {{VERSION}}
	 * @access public
	 * @var    Google_Service_Sheets $api If available, contains an instance of the Google Sheets API library.
	 */
	public $api_sheets = null;

	/**
	 * Contains an instance of the Google Client.
	 *
	 * @since {{VERSION}}
	 * @access public
	 * @var    Google_Client $api If available, contains an instance of the Google Client.
	 */
	public $api_client = null;

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
				require_once 'vendor/autoload.php';
			}

			// Get plugin settings.
			$settings = $this->get_plugin_settings();
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

				$token_info = $client->fetchAccessTokenWithAuthCode( $_GET['code'] );

				$settings = $this->get_plugin_settings();

				if ( isset( $token_info['refresh_token'] ) ) {
					$settings['refreshToken'] = $token_info['refresh_token'];
				}

				$settings['accessToken'] = $token_info['access_token'];
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
			$settings['refreshToken'] = false;

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

		/* If API objects are already setup, return true. */
		if ( ! is_null( $this->api_sheets ) && ! is_null( $this->api_drive ) ) {
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

				$client->fetchAccessTokenWithRefreshToken( $this->get_plugin_setting( 'refreshToken' ) );
			}

			$googledrive  = new Google_Service_Drive( $client );
			$googlesheets = new Google_Service_Sheets( $client );

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
						'feedback_callback' => array( $this, 'is_valid_app_key_secret' ),
					),
					array(
						'name'              => 'clientSecret',
						'label'             => esc_html__( 'Client Secret', 'gravityformsgooglesheets' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_app_key_secret' ),
					),
					array(
						'name'  => 'authCode',
						'label' => esc_html__( 'Authenticate', 'gravityformsgooglesheets' ),
						'type'  => 'auth_code',
					),
					array(
						'name' => 'accessToken',
						'type' => 'hidden',
					),
					array(
						'name' => 'refreshToken',
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
			$field_map     = $this->get_sheet_field_map( rgpost( '_gaddon_setting_sheet' ) );
		}

		// If on a current feed, populate choices before loading so values load properly
		if ( $this->get_current_feed_id() ) {

			$feed_values = $this->get_current_settings();

			$sheet_choices = $this->get_sheet_choices();
			$field_map     = $this->get_sheet_field_map( $feed_values['sheet'] );
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
						'label'          => __( 'Condition', 'gravityformsgooglesheets' ),
						'checkbox_label' => __( 'Enable Condition', 'gravityformsgooglesheets' ),
						'instructions'   => __( 'Send data to Google Sheet if', 'gravityformsgooglesheets' ),
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

		// If the Google Sheets instance isn't initialized, do not process the feed.
		if ( ! $this->initialize_api() ) {

			$this->add_feed_error( esc_html__( 'Feed was not processed because API was not initialized.', 'gravityformsgooglesheets' ), $feed, $entry, $form );

			return;
		}

		// Log that we're sending this feed to processing.
		$this->log_debug( __METHOD__ . '(): Sending processing request for feed #' . $feed['id'] . '.' );

		// Begin building row
		$row = array();

		foreach ( $feed['meta'] as $meta_name => $meta_value ) {

			// Make sure we're on a field
			if ( ! preg_match( '/fields_(\d+)/', $meta_name, $matches ) ) {
				continue;
			}

			$column_i = $matches[1];

			// Get entry value
			$row[ $column_i ] = $this->get_field_value( $form, $entry, $meta_value );
		}

		try {

			$body = new Google_Service_Sheets_ValueRange();
			$body->setValues( array( $row ) );

			$this->api_sheets->spreadsheets_values->append(
				$feed['meta']['sheet'],
				'1:1',
				$body,
				array(
					'valueInputOption' => 'RAW',
				)
			);

		} catch ( Exception $e ) {

			// Log that test failed.
			$this->log_error( __METHOD__ . '(): Could not update Sheet; ' . $e->getMessage() );
		}
	}
}

