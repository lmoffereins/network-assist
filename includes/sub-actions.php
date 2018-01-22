<?php

/**
 * Network Assist Sub-action Functions
 *
 * @package Network Assist
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Run dedicated activation hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'network_assist_activation'
 */
function network_assist_activation() {
	do_action( 'network_assist_activation' );
}

/**
 * Run dedicated deactivation hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'network_assist_deactivation'
 */
function network_assist_deactivation() {
	do_action( 'network_assist_deactivation' );
}

/**
 * Run dedicated loaded hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'network_assist_loaded'
 */
function network_assist_loaded() {
	do_action( 'network_assist_loaded' );
}

/**
 * Run dedicated init hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'network_assist_init'
 */
function network_assist_init() {
	do_action( 'network_assist_init' );
}

/**
 * Run dedicated early registration hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'network_assist_register'
 */
function network_assist_register() {
	do_action( 'network_assist_register' );
}

/**
 * Run dedicated map meta caps filter for this plugin
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'network_assist_map_meta_caps'
 *
 * @param array $caps Mapped caps
 * @param string $cap Required capability name
 * @param int $user_id User ID
 * @param array $args Additional arguments
 * @return array Mapped caps
 */
function network_assist_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
	return apply_filters( 'network_assist_map_meta_caps', $caps, $cap, $user_id, $args );
}
