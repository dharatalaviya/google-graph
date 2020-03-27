<?php
/**
 * Plugin Name: Google Grap
 * Plugin URI:  
 * Description: After user login in front end. submit data of percentage and year. after that user getting line grap.   
 * Version: 1.0.0
 * Author: Dhara Talaviya
 * Author URI: 
 * Text Domain: gool
 * Domain Path: languages
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 * 
 * @package Googlegrap
 * @since 1.0.0
 */

if( !defined( 'GOOL_GRAPH_DIR' ) ) {
	define( 'GOOL_GRAPH_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'GOOL_GRAPH_URL' ) ) {
	define( 'GOOL_GRAPH_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}
if( !defined( 'GOOL_GRAPH_ADMIN' ) ) {
	define( 'GOOL_GRAPH_ADMIN', GOOL_GRAPH_DIR . '/includes/admin' ); // plugin admin dir
}

if( !defined( 'GOOL_GRAPH_BASENAME' ) ) {
	define( 'GOOL_GRAPH_BASENAME', basename( GOOL_GRAPH_DIR ) ); // base name
}

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package Googlegrap
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'goog_graph_install' );

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * 
 * @package Googlegrap
 * @since 1.0.0
 */
function goog_graph_install() {
	
	global $wpdb, $current_user;
	
	//Get User ID
	$user_ID = $current_user->ID;
    
}

//add action to load plugin
add_action( 'plugins_loaded', 'goog_graph_plugin_loaded' );

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package Googlegrap
 * @since 1.0.0
 */
function goog_graph_plugin_loaded() {
	
		//global variables
		 global  $goog_graph_admin,$goog_graph_scripts,$goog_graph_shortcode, $goog_graph_public;
		
		
		// Script Class to manage all scripts and styles
		include_once( GOOL_GRAPH_DIR . '/includes/class-goog-graph-scripts.php' );
		$goog_graph_scripts = new Goog_Graph_Scripts();
		$goog_graph_scripts->add_hooks();
		
		/*//collection public class for handling
		require_once( GOOL_GRAPH_DIR . '/includes/class-goog-grap-public.php' );
		$goog_graph_public = new Goog_Graph_Public();
		$goog_graph_public->add_hooks();
		
		//Admin Pages Class for admin side
		require_once( GOOL_GRAPH_ADMIN . '/class-goog-grap-admin.php' );
		$goog_graph_admin = new Goog_Graph_Admin();
		$goog_graph_admin->add_hooks(); */
		
		//shortcode class for handling shortcode content
		require_once( GOOL_GRAPH_DIR . '/includes/class-goog-graph-shortcodes.php' );
		$goog_graph_shortcode = new Goog_Graph_Shortcodes();
		$goog_graph_shortcode->add_hooks();
    
        // loads the Templates Functions file
		require_once ( GOOL_GRAPH_DIR . '/includes/goog-graph-template-functions.php' );
		
		
}//end if to check plugin loaded is called or not