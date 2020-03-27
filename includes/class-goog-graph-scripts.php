<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Scripts Class
 * 
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 * 
 * @package Google Graph
 * @since 1.0.0
 */
class Goog_Graph_Scripts {
	
	public function __construct() {
		
	}

	/**
	 * Enqueue Styles for public
	 * 
 	 * @package Google Graph
 	 * @since 1.0.0
	 */
	public function goog_graph_public_scripts() {
		
		global $post;
        
		
		//Get post content if exist
		$post_content = isset( $post->post_content ) ? $post->post_content : '';
		
		if( has_shortcode( $post_content, 'googlegraph' ) ) { // add script for only collection page
            
            global $current_user;

            $goog_graph_data = get_user_option( 'goog_graph_data', $current_user->ID);
          
            wp_register_script( 'goog_grap_googlegrapscript', 'https://www.gstatic.com/charts/loader.js',array( 'jquery' ), '1.0.0', true );
            wp_enqueue_script( 'goog_grap_googlegrapscript' );
            wp_register_script( 'goog_grap_general_js', GOOL_GRAPH_URL. 'includes/js/goog_graph_general.js',array( 'jquery' ), '1.0.0', true );
            wp_enqueue_script( 'goog_grap_general_js' );
            
            wp_localize_script( 'goog_grap_general_js', 'goog_graph_user_data', $goog_graph_data);
        }
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hoocks for the scripts.
	 * 
	 * @package Google Graph
 	 * @since 1.0.0
	 */
	public function add_hooks() {
		
	
		//script for public
		add_action( 'wp_enqueue_scripts', array( $this, 'goog_graph_public_scripts' ) );
		
		}
}