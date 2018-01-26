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
		add_filter( 'theme_row_meta',                 array( $this, 'theme_row_meta'        ), 10, 4 );
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
					$limit = 5;

					// Display sites
					foreach ( array_values( $sites ) as $k => $site ) {

						// Limit list of sites
						if ( $k === $limit ) {
							echo '<span class="sites-more">' . esc_html( sprintf( _n( 'And %d more site', 'And %d more sites', count( $sites ) - $limit, 'network-assist' ), count( $sites ) - $limit ) ) . '</span>';
							break;
						}

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
	 * @param  string   $column     Column name
	 * @param  string   $stylesheet Theme slug
	 * @param  WP_Theme $theme      Theme data
	 */
	public function theme_column_content( $column, $stylesheet, $theme ) {

		switch ( $column ) {

			// Sites
			case 'nwas-sites' :

				// Query sites
				$sites = network_assist_theme_site_query( $stylesheet );

				// Sites activated
				if ( array_filter( $sites ) ) {

					// Keep a reference to all template themes to check for parent/child relationships
					$_templates = $sites['template'];

					// Remove sites using the theme both as stylesheet and template (standalone theme)
					$sites['template'] = array_udiff( $sites['template'], $sites['stylesheet'], function( $a, $b ) {
						return $a->blog_id !== $b->blog_id;
					});

					// Counts for limiting displayed sites
					$limit   = 5;
					$total   = array_reduce( $sites, function( $a, $b ) {
						return $a + count( $b );
					}, 0 );
					$counter = -1;

					// Display sites
					foreach ( $sites as $type => $_sites ) {
						foreach ( $_sites as $site ) {
							$counter++;

							// Limit list of sites
							if ( $counter === $limit ) {
								echo '<span class="sites-more">' . esc_html( sprintf( _n( 'And %d more site', 'And %d more sites', $total - $limit, 'network-assist' ), $total - $limit ) ) . '</span>';
								break;
							}

							// Check theme usage type
							$is_template    = $type === 'template';
							$is_child_theme = $type === 'stylesheet' && ! in_array( $site, $_templates );

							// Get related theme name
							if ( $is_template ) {
								$related_theme = wp_get_theme( get_blog_option( $site->blog_id, 'stylesheet' ) )->name;
							} elseif ( $is_child_theme ) {
								$related_theme = $theme->parent_theme;
							} else {
								$related_theme = '';
							}

							// Setup link details
							$class  = $is_template ? 'site-template parent-theme' : 'site-stylesheet';
							$class .= $is_child_theme ? ' child-theme' : '';
							$link   = $is_template || $is_child_theme ? '<a href="%1$s" title="%2$s">%3$s</a>' : '<a href="%1$s">%3$s</a>';
							$title  = $is_template
								/* translators: 1. Site name, 2. Theme name, 3. Related theme name */
								? esc_html__( 'The %1$s site uses %2$s as a parent theme for %3$s.', 'network-assist' )
								: esc_html__( 'The %1$s site uses %2$s as a child theme of %3$s.',   'network-assist' );

							// Output link
							printf( '<span class="' . $class . '">' . $link . '</span>',
								get_admin_url( $site->blog_id, 'themes.php' ),
								esc_attr( sprintf( $title,
									$site->blogname,
									$theme->name,
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

	/**
	 * Modify the theme meta in the list table
	 *
	 * @since 1.0.0
	 *
	 * @param  array    $meta       Theme's metadata
	 * @param  string   $stylesheet Theme slug
	 * @param  WP_Theme $theme      Theme data
	 * @param  string   $status     Theme status
	 * @return array Theme row meta
	 */
	public function theme_row_meta( $meta, $stylesheet, $theme, $status ) {

		// Mention child's parent theme
		if ( $theme->parent_theme ) {
			/* translators: %s: theme name */
			$aria_label = sprintf( __( 'Visit %s homepage' ), $theme->parent_theme );

			$item = sprintf(
				esc_html__( 'Child theme of %s', 'network-assist' ),
				sprintf( '<a href="%s" aria-label="%s">%s</a>',
					$theme->parent()->display( 'ThemeURI' ),
					esc_attr( $aria_label ),
					$theme->parent_theme
				)
			);

			// Insert after first item
			array_splice( $meta, 1, 0, $item );
		}

		return $meta;
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
