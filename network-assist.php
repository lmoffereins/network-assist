<?php

/**
 * The Network Assist Plugin
 *
 * @package Network Assist
 * @subpackage Main
 */

/**
 * Plugin Name:       Network Assist
 * Description:       Supporting functions that help you manage your multisite network
 * Plugin URI:        https://github.com/lmoffereins/network-assist/
 * Version:           1.0.0
 * Author:            Laurens Offereins
 * Author URI:        https://github.com/lmoffereins/
 * Network:           true
 * Text Domain:       network-assist
 * Domain Path:       /languages/
 * GitHub Plugin URI: lmoffereins/network-assist
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Network_Assist' ) ) :
/**
 * The main plugin class
 *
 * @since 1.0.0
 */
final class Network_Assist {

	/**
	 * Setup and return the singleton pattern
	 *
	 * @since 1.0.0
	 *
	 * @uses Network_Assist::setup_globals()
	 * @uses Network_Assist::setup_actions()
	 * @return The single Network Assist
	 */
	public static function instance() {

		// Store instance locally
		static $instance = null;

		if ( null === $instance ) {
			$instance = new Network_Assist;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Prevent the plugin class from being loaded more than once
	 */
	private function __construct() { /* Nothing to do */ }

	/** Private methods *************************************************/

	/**
	 * Setup default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		/** Versions **********************************************************/

		$this->version      = '1.0.0';
		$this->dbversion    = 100;

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes' );

		// Assets
		$this->assets_dir   = trailingslashit( $this->plugin_dir . 'assets' );
		$this->assets_url   = trailingslashit( $this->plugin_url . 'assets' );

		// Languages
		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );

		/** Misc **************************************************************/

		$this->extend       = new stdClass();
		$this->domain       = 'network-assist';
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( $this->includes_dir . 'actions.php'      );
		require( $this->includes_dir . 'functions.php'    );
		require( $this->includes_dir . 'sub-actions.php'  );

		// Admin
		if ( is_admin() ) {
			require( $this->includes_dir . 'admin/admin.php' );
		}
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		/** Activation **************************************************/

		add_action( 'activate_'   . $this->basename, 'network_assist_activation'   );
		add_action( 'deactivate_' . $this->basename, 'network_assist_deactivation' );

		/** Textdomain **************************************************/

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 20 );

		/** Admin *******************************************************/

		if ( is_admin() ) {
			add_action( 'network_assist_init', 'network_assist_admin' );
		}
	}

	/** Plugin **********************************************************/

	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the plugin first, and then the default WordPress
	 * languages folder.
	 *
	 * Note that custom translation files inside the plugin folder will be
	 * removed on plugin updates. If you're creating custom translation
	 * files, please use the global language folder.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'plugin_locale' with {@link get_locale()} value
	 * @uses load_textdomain() To load the textdomain
	 * @uses load_plugin_textdomain() To load the textdomain
	 */
	public function load_textdomain() {

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/network-assist/' . $mofile;

		// Look in global /wp-content/languages/network-assist folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/network-assist/languages/ folder
		load_textdomain( $this->domain, $mofile_local );

		// Look in global /wp-content/languages/plugins/
		load_plugin_textdomain( $this->domain );
	}

	/** Public methods **************************************************/
}

/**
 * Return single instance of this main plugin class
 *
 * @since 1.0.0
 * 
 * @return Network Assist
 */
function network_assist() {
	return Network_Assist::instance();
}

// Initiate plugin on load
network_assist();

endif; // class_exists
