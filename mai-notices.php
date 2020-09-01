<?php

/**
 * Plugin Name:     Mai Notices
 * Plugin URI:      https://maitheme.com
 * Description:     Custom block for callout notices in your content.
 * Version:         0.1.1
 *
 * Author:          BizBudding, Mike Hemberger
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Mai_Notices Class.
 *
 * @since 0.1.0
 */
final class Mai_Notices {

	/**
	 * @var   Mai_Notices The one true Mai_Notices
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Mai_Notices Instance.
	 *
	 * Insures that only one instance of Mai_Notices exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_Notices::setup_constants() Setup the constants needed.
	 * @uses    Mai_Notices::includes() Include the required files.
	 * @uses    Mai_Notices::hooks() Activate, deactivate, etc.
	 * @see     Mai_Notices()
	 * @return  object | Mai_Notices The one true Mai_Notices
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new Mai_Notices;
			// Methods.
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'MAI_NOTICES_VERSION' ) ) {
			define( 'MAI_NOTICES_VERSION', '0.1.1' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_NOTICES_PLUGIN_DIR' ) ) {
			define( 'MAI_NOTICES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Includes Path.
		if ( ! defined( 'MAI_NOTICES_INCLUDES_DIR' ) ) {
			define( 'MAI_NOTICES_INCLUDES_DIR', MAI_NOTICES_PLUGIN_DIR . 'includes/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'MAI_NOTICES_PLUGIN_URL' ) ) {
			define( 'MAI_NOTICES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'MAI_NOTICES_PLUGIN_FILE' ) ) {
			define( 'MAI_NOTICES_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Base Name
		if ( ! defined( 'MAI_NOTICES_BASENAME' ) ) {
			define( 'MAI_NOTICES_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function includes() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';
		// Includes.
		foreach ( glob( MAI_NOTICES_INCLUDES_DIR . '*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run the hooks.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init', [ $this, 'updater' ] );
		// add_filter( 'acf/settings/load_json', array( $this, 'load_json' ) );
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @since 0.1.0
	 *
	 * @uses https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return void
	 */
	public function updater() {

		// Bail if current user cannot manage plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'Puc_v4_Factory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/maithemewp/mai-notices/', __FILE__, 'mai-notices' );
	}

	/**
	 * Add path to load acf json files.
	 *
	 * @since 0.1.0
	 *
	 * @param array The existing acf-json paths.
	 *
	 * @return array The modified paths.
	 */
	function load_json( $paths ) {
		$paths[] = untrailingslashit( MAI_NOTICES_PLUGIN_DIR ) . '/acf-json';
		return $paths;
	}
}

/**
 * The main function for that returns Mai_Notices
 *
 * The main function responsible for returning the one true Mai_Notices
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = Mai_Notices(); ?>
 *
 * @since 0.1.0
 *
 * @return object|Mai_Notices The one true Mai_Notices Instance.
 */
function Mai_Notices() {
	return Mai_Notices::instance();
}

// Get Mai_Notices Running.
Mai_Notices();
