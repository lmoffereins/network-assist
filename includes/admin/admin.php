<?php

/**
 * Network Assist Admin Functions
 *
 * @package Network Assist
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Network_Assist_Admin' ) ) :
/**
 * The Network Assist Admin class
 *
 * @since 1.0.0
 */
class Network_Assist_Admin {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Define default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		/** Paths *************************************************************/

		$this->admin_dir = trailingslashit( network_assist()->includes_dir . 'admin' );
		$this->admin_url = trailingslashit( network_assist()->includes_url . 'admin' );
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {

	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Columns
		add_filter( 'manage_plugins-network_columns', array( $this, 'plugin_columns'        )        );
		add_filter( 'manage_themes-network_columns',  array( $this, 'theme_columns'         )        );
		add_filter( 'manage_plugins_custom_column',   array( $this, 'plugin_column_content' ), 10, 3 );
		add_filter( 'manage_themes_custom_column',    array( $this, 'theme_column_content'  ), 10, 3 );
	}

	/** Public methods ********************************************************/

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		// Register style
		wp_register_style( 'network-assist-admin', network_assist()->assets_url . 'css/admin.css', array( 'common' ) );

		// Plugins/Themes
		if ( in_array( $screen->id, array( 'plugins-network', 'themes-network' ) ) ) {
			wp_enqueue_style( 'network-assist-admin' );
		}
	}

	/**
	 * Modify the admin plugin columns
	 *
	 * @since 1.0.0
	 *
	 * @param  array $columns Admin columns
	 * @return array Admin columns
	 */
	public function plugin_columns( $columns ) {

		// Name position
		if ( $name_pos = array_search( 'name', array_keys( $columns ) ) ) {

			// Insert after name
			$columns = array_slice( $columns, 0, $name_pos + 1 ) + array(
				'nwas-sites' => esc_html__( 'Sites', 'network-assist' )
			) + array_slice( $columns, $name_pos + 1 );
		}

		return $columns;
	}

	/**
	 * Modify the admin theme columns
	 *
	 * @since 1.0.0
	 *
	 * @param  array $columns Admin columns
	 * @return array Admin columns
	 */
	public function theme_columns( $columns ) {

		// Name position
		if ( $name_pos = array_search( 'name', array_keys( $columns ) ) ) {

			// Insert after name
			$columns = array_slice( $columns, 0, $name_pos + 1 ) + array(
				'nwas-sites' => esc_html__( 'Sites', 'network-assist' )
			) + array_slice( $columns, $name_pos + 1 );
		}

		return $columns;
	}

	/**
	 * Display the content of the custom plugin admin column
	 *
	 * @since 1.0.0
	 *
	 * @param  string $column Column name
	 * @param  string $plugin Plugin file
	 * @param  array  $data   Plugin data
	 */
	public function plugin_column_content( $column, $plugin, $data ) {

		switch ( $column ) {

			// Sites
			case 'nwas-sites' :

				// Network activated
				if ( is_plugin_active_for_network( plugin_basename( $plugin ) ) ) {
					esc_html_e( 'All sites', 'network-assist' );

				// Sites activated
				} elseif ( $sites = network_assist_plugin_site_query( $plugin ) ) {

					// Display sites
					foreach ( $sites as $site ) {
						printf( '<span class="site-plugin"><a href="%s">%s</a></span>',
							get_admin_url( $site->blog_id, 'plugins.php' ),
							$site->domain
						);
					}

				// No activations
				} else {
					echo '&mdash;';
				}

				break;
		}
	}

	/**
	 * Display the content of the custom theme admin column
	 *
	 * @since 1.0.0
	 *
	 * @param  string   $column Column name
	 * @param  string   $theme  Theme slug
	 * @param  WP_Theme $data   Theme data
	 */
	public function theme_column_content( $column, $theme, $data ) {

		switch ( $column ) {

			// Sites
			case 'nwas-sites' :

				// Query sites
				$sites = network_assist_theme_site_query( $theme );

				// Sites activated
				if ( array_filter( $sites ) ) {

					// Keep a reference to all template themes to check for parent/child relationships
					$_templates = $sites['template'];

					// Remove sites using the theme both as stylesheet and template (standalone theme)
					$sites['template'] = array_udiff( $sites['template'], $sites['stylesheet'], function( $a, $b ) {
						return $a->blog_id !== $b->blog_id;
					});

					// Display sites
					foreach ( $sites as $type => $_sites ) {
						foreach ( $_sites as $site ) {

							// Check theme usage type
							$is_template    = $type === 'template';
							$is_child_theme = $type === 'stylesheet' && ! in_array( $site, $_templates );

							// Get related theme name
							if ( $is_template ) {
								$related_theme = wp_get_theme( get_blog_option( $site->blog_id, 'stylesheet' ) )->name;
							} elseif ( $is_child_theme ) {
								$related_theme = $data->parent_theme;
							} else {
								$related_theme = '';
							}

							// Setup link details
							$class  = $is_template ? 'site-template parent-theme' : 'site-stylesheet';
							$class .= $is_child_theme ? ' child-theme' : '';
							$link   = $is_template || $is_child_theme ? '<a href="%1$s" title="%2$s">%3$s</a>' : '<a href="%1$s">%3$s</a>';
							$title  = $is_template
								? esc_html__( 'The "%1$s" site uses "%2$s" as a parent theme for "%3$s".', 'network-assist' )
								: esc_html__( 'The "%1$s" site uses "%2$s" as a child theme of "%3$s".',   'network-assist' );

							// Output link
							printf( '<span class="' . $class . '">' . $link . '</span>',
								get_admin_url( $site->blog_id, 'themes.php' ),
								/* translators: 1. Site name, 2. Theme name, 3. Related theme name */
								esc_attr( sprintf( $title,
									$site->blogname,
									$data->name,
									$related_theme
								) ),
								$site->domain
							);
						}
					}

				// No activations
				} else {
					echo '&mdash;';
				}

				break;
		}
	}
}

/**
 * Setup the extension logic for BuddyPress
 *
 * @since 1.0.0
 *
 * @uses Network_Assist_Admin
 */
function network_assist_admin() {
	network_assist()->admin = new Network_Assist_Admin;
}

endif; // class_exists
