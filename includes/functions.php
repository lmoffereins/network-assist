<?php

/**
 * Network Assist Functions
 *
 * @package Network Assist
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the sites that have activated a given plugin
 *
 * @since 1.0.0
 *
 * @uses $wpdb WPDB
 * @uses apply_filters() Calls 'network_assist_plugin_site_query'
 *
 * @param  string $plugin Plugin slug
 * @param  array  $args   Query args for {@see WP_Site_Query}
 * @return array Sites where the plugin is activated
 */
function network_assist_plugin_site_query( $plugin, $args = array() ) {
	global $wpdb;

	$sql    = '';
	$_sites = get_sites( $args );
	$sites  = array();

	// Construct single query for all sites that have the plugin activated
	foreach ( $_sites as $site ) {
		$site_id = is_a( $site, 'WP_Site' ) ? $site->blog_id : $site;
		switch_to_blog( $site_id );

		// Union multiple sites
		if ( ! empty( $sql ) ) {
			$sql .= ' UNION';
		}

		// Define site query
		$sql .= $wpdb->prepare( " SELECT %d as site_id FROM {$wpdb->options} WHERE option_name = %s AND option_value LIKE %s", $site_id, 'active_plugins', '%' . $wpdb->esc_like( $plugin ) . '%' );

		restore_current_blog();
	}

	// Sites found
	if ( $query = $wpdb->get_col( $sql ) ) {
		$query = array_map( 'intval', $query );

		// Filter relevant sites
		$sites = array_filter( $_sites, function( $site ) use ( $query ) {
			return in_array( is_a( $site, 'WP_Site' ) ? $site->blog_id : $site, $query );
		});
	}

	return apply_filters( 'network_assist_plugin_site_query', $sites, $plugin, $args );
}

/**
 * Return the sites that have activated a given theme
 *
 * @since 1.0.0
 *
 * @uses $wpdb WPDB
 * @uses apply_filters() Calls 'network_assist_theme_site_query'
 *
 * @param  string $theme Plugin slug
 * @param  array  $args  Query args for {@see WP_Site_Query}
 * @return array Nested array of sites utilizing the theme as stylesheet, and sites
 *                utilizing the theme as template (parent theme).
 */
function network_assist_theme_site_query( $theme, $args = array() ) {
	global $wpdb;

	$ssql = $tsql = '';
	$_sites = get_sites( $args );
	$sites  = array( 'stylesheet' => array(), 'template' => array() );

	// Construct single query for all sites that have the theme activated
	foreach ( $_sites as $site ) {
		$site_id = is_a( $site, 'WP_Site' ) ? $site->blog_id : $site;
		switch_to_blog( $site_id );

		// Union multiple sites
		if ( ! empty( $ssql ) ) {
			$ssql .= ' UNION';
		}

		// Union multiple sites
		if ( ! empty( $tsql ) ) {
			$tsql .= ' UNION';
		}

		// Define site queries
		$sql   = " SELECT %d as site_id FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s";
		$ssql .= $wpdb->prepare( $sql, $site_id, 'stylesheet', $theme );
		$tsql .= $wpdb->prepare( $sql, $site_id, 'template',   $theme );

		restore_current_blog();
	}

	// Stylesheet sites found
	if ( $query = $wpdb->get_col( $ssql ) ) {
		$query = array_map( 'intval', $query );

		// Filter relevant sites
		$sites['stylesheet'] = array_filter( $_sites, function( $site ) use ( $query ) {
			return in_array( is_a( $site, 'WP_Site' ) ? $site->blog_id : $site, $query );
		});
	}

	// Template sites found
	if ( $query = $wpdb->get_col( $tsql ) ) {
		$query = array_map( 'intval', $query );

		// Filter relevant sites
		$sites['template'] = array_filter( $_sites, function( $site ) use ( $query ) {
			return in_array( is_a( $site, 'WP_Site' ) ? $site->blog_id : $site, $query );
		});
	}

	return apply_filters( 'network_assist_theme_site_query', $sites, $theme, $args );
}
