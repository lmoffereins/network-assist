<?php

/**
 * Network Assist Actions
 *
 * @package Network Assist
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Sub-actions ***************************************************************/

add_action( 'plugins_loaded',               'network_assist_loaded',          10    );
add_action( 'init',                         'network_assist_init',            10    );
add_action( 'network_assist_init',          'network_assist_register',         0    );
add_filter( 'map_meta_cap',                 'network_assist_map_meta_caps',   10, 4 );
