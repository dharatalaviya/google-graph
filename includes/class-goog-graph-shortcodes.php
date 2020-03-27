<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode Class
 * 
 * Handles adding Shortcode functionality to the front pages.
 * 
 * @package Google Graph
 * @since 1.0.0
 */
class Goog_Graph_Shortcodes {

    
	public function __construct() {

    }

	/**
	 * User Add Value of graph and getting graph Shortcode
	 * 
 	 * @package Google Graph
 	 * @since 1.0.0
	 */
	public function goog_graph_shortcode( $atts, $content ) {

		global $current_user;
        
        $current_user = wp_get_current_user();
        if ( 0 == $current_user->ID ) {
                // Not logged in.
                echo 'Please User  Login ';
        } 
        else {
            goog_graph_user_meta_form();
            goog_graph_display();
        }
        
    }
	
	/**
	 * Adding Hooks
	 * 
 	 * @package Google Graph
 	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		//Add shortcode of google editor
		add_shortcode( 'googlegraph', array( $this, 'goog_graph_shortcode') );	
		
	}
}