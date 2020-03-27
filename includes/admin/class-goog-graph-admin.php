<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 *
 * Handles generic Admin functionality.
 * 
 * @package WPWeb WooCommerce Collections
 * @since 1.0.0
 */

class Woo_Cl_Admin {
	
	public $model, $prefix;
	
	public function __construct(){
		
		global $woo_cl_model;
		
		$this->model = $woo_cl_model;
		
		//Get meta prefix
		$this->prefix = WOO_CL_META_PREFIX;
	}

	/**
	 * When user registers and has guest lists, remove token meta key so their lists are saved indefinately
	 *
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_new_user_registration( $user_id ) {

		//Get meta prefix
		$prefix = WOO_CL_META_PREFIX;

		// get user's token if present
		$lists 	= woo_cl_get_guest_lists( woo_cl_get_list_token() );
	
		// attribute posts to new author
		if ( $lists ) {

			// loop throgh each list and assign the new user ID to their list
			foreach ( $lists as $key => $list ) {

				//Get Collection id
				$list_id	= isset( $list['ID'] ) ? $list['ID'] : '';

				$args = array(
					'ID'          => $list_id,
					'post_author' => $user_id
				);
				wp_update_post( $args );

				// Update author in collection item post type
				$this->model->woo_cl_update_author_in_coll_items($list_id, $user_id);

				// delete token one each list
				delete_post_meta( $list_id, $prefix.'cl_token', woo_cl_get_list_token() );
			}
			
			// remove cookie
			setcookie( 'woo_cl_token', '', time()-3600, COOKIEPATH, COOKIE_DOMAIN );
		}
	}

	/**
	 * Custom column
	 *
	 * Handles the custom columns to collections listing page
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	
	public function woo_cl_manage_custom_columns( $column_name, $post_id ) {
		
		global $wpdb, $post, $current_user;
		
		//get prefix
		$prefix		= WOO_CL_META_PREFIX;
		
		//get user ID
		$user_ID	= isset( $current_user->ID ) ? $current_user->ID : '';
		
		switch( $column_name ) {
				
				case 'products' :
									$countargs = array(
														'author' 			=> $post->post_author,
														'post_parent' 		=> $post_id,
														'getcount'			=> 1,
													); 
									
									$total_items = $this->model->woo_cl_get_collection_items($countargs);
									echo $total_items;
									echo '<div id="inline_' . $post->ID . '_coll_downloads" class="hidden">' . $total_items . '</div>';
									break;
				case 'total' :
									$total_price = $this->model->woo_cl_collection_products_total_price($post_id);
									echo $total_price;					
									echo '<div id="inline_' . $post->ID . '_items_total" class="hidden">' . $total_price . '</div>';
									break;
									
				case 'featured' :
									// get Featured collection custom field
									$is_featured = get_post_meta( $post->ID, $prefix . 'featured_coll', true );
						            $html = "<input type='checkbox'  id='woocl_featuredbtn_".$post_id."' class='woocl_featuredbtn' ".(( $is_featured == 'yes' ) ? "checked='checked'" : "")."/>";							            
					            	echo $html;
									break;
									
				case 'follower':
									$follow_count	= 0;
									
									//check follow my blog post is activated or not
									if( woo_cl_is_follow_activate() ) {
										
										// get followers counts
										$follow_count 	= wpw_fp_get_post_followers_count( $post->ID );
									}
									
									echo $follow_count;
									echo '<div id="inline_' . $post->ID . '_coll_follower" class="hidden">' . $follow_count . '</div>';
									break;
			}
	}
	
	/**
	 * Add New Column to collections listing page
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	function woo_cl_add_new_collections_columns( $new_columns ) {
 		
 		unset( $new_columns['author'] );
 		unset( $new_columns['title'] );
 		unset( $new_columns['date'] );
 		
		$new_columns['title'] 		= __( 'Title', 'woocl' );
		$new_columns['products'] 	= __( 'Products', 'woocl' );
		$new_columns['featured'] 	= __( 'Featured?', 'woocl' );
		
		if( woo_cl_is_follow_activate() ) {
			
			$new_columns['follower'] = __( 'Followers', 'woocl' );
		}
		
		$new_columns['author'] 		= __( 'Author', 'woocl' );
 		$new_columns['total'] 		= __( 'Total', 'woocl' );
 		$new_columns['date'] 		= __( 'Date', 'woocl' );
 		
		return $new_columns;
	}
	
	/**
	 * Add New Action For Create Preview
	 * 
	 * Handles to add new action for
	 * Create Preview link of that collection
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_preview_action_new_link_add( $actions, $post ) {
		
		//check current user can have administrator rights
		//post type is collections post type
		if ( ! current_user_can( 'manage_options' ) || $post->post_type != WOO_CL_POST_TYPE_COLL ) 
			return $actions;
		
		//Get collection items URL
		$collection_items_url	= $this->model->woo_cl_get_collection_item_page_url( $post->ID );
		
		$actions['view'] = '<a href="' . $collection_items_url . '" title="' 
									. sprintf( __( 'Make a preview for this %s', 'woocl' ), woo_cl_get_label_singular() )
									. '" rel="permalink">' .  __( 'View', 'woocl' ) . '</a>';
		
		// return all actions
		return $actions ;
	}
	
	
	/**
	 * Pop Up On Editor
	 * 
	 * Includes the pop up on the WordPress editor
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_shortcode_popup_markup() {

		global $post;

		$coll_post_type	= WOO_CL_POST_TYPE_COLL;

		include_once( WOO_CL_ADMIN . '/forms/woo-cl-admin-popup.php' );

		if( isset( $post->post_type ) && $post->post_type == $coll_post_type ) {

			include_once( WOO_CL_ADMIN . '/forms/woo-cl-admin-add-product-popup.php' );
		}
	}
	
	/**
	 * Hide update notification checkbox
	 * 
	 * Handle to hide update notification checkbox
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_follow_notification_checkbox_display( $is_display ) {
		
		global $post;
		
		$coll_post_type	= WOO_CL_POST_TYPE_COLL;
		
		if( isset( $post->post_type ) && $post->post_type == $coll_post_type ) {
			
			$is_display	= false;
		}
		
		return $is_display;
	}
	
	/**
	 * Remove Follow Metabox
	 * 
	 * Handle to remove follow metabox in collection post type
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_remove_followm_metabox() {
		
		$post_type	= WOO_CL_POST_TYPE_COLL;
		
		//Remove follow post type from collection post type page
		remove_meta_box( 'wpw_fp_follow_me_metabox' , $post_type , 'normal' );
	}
	
	/**
	 * Add Email Class
	 * 
	 * Handle to add email class
	 * 
	 * @package WPWeb WooCommerce Collections
	 * @since 1.0.0
	 */
	public function woo_cl_add_email_classes( $email_classes ) {

		//Include Collection share email class file
		require_once ( WOO_CL_ADMIN . '/class-woo-cl-email-collection-share.php' );
		$email_classes['Woo_Cl_Email_Collection_Share'] = new Woo_Cl_Email_Collection_Share();
		
		return $email_classes;
	}
	
	/**
	 * Delete Collection Items
	 * 
	 * Handles to Delete Collection Items When Perticular Collection deleted OR
	 * delete product from Collection  when product deleted from the woocommerce
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_delete_all_collection_product( $post_id = '' ){
		
		global $woo_cl_model;
		
		$prefix	= WOO_CL_META_PREFIX;
		
		if( !empty( $post_id ) ) { // check if collection id is not empty
			
			$post_type = get_post_type( $post_id ); // get	post type from collection id
			
			// Delete Collection Items When Perticular Collection deleted
			if( $post_type == WOO_CL_POST_TYPE_COLL ){ // check if post type is Collection's post type
				
				//argument for get collection item based on collection id
				$args = array(
								'post_status'	=> 'any',
								'post_parent'	=> $post_id,
				 			 );
				
				//Get collection item
				$posts	= $woo_cl_model->woo_cl_get_collection_items( $args );
				
				if( !empty( $posts ) ){ // check if get any post
					
					foreach ( $posts as $post ) {
						
						// delete all collection items
						wp_delete_post( $post['ID'], true );
					}
				}
			}
			
			// Delete Product from Collection item when product is deleted from woocommerce
			if( $post_type == WOO_CL_PRODUCT_POSTTYPE ){ // check if post type is woocommerce product's post type
				
				//argument for get collection item based on product id
				$args = array(
								'meta_query'	=> array(
															'post_status'	=> 'any',
															 array( 
																	'key' 	=> $prefix.'coll_product_id',
																	'value' => $post_id
															 )
														)
							 );
				
				//Get collection item
				$posts	= $woo_cl_model->woo_cl_get_collection_items( $args );
				
				if( !empty( $posts ) ){ // check if get any post
					
					foreach ( $posts as $post ) {
						
						// delete all collection items
						wp_delete_post( $post['ID'], true );
					}
				}
			}
		}
	}
	
	/**
	 * Trash Collection Item
	 * 
	 * Handle to trash collection item
	 * when product move in trash
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_trash_collection_item( $product_id = '' ) {
		
		global $woo_cl_model;
		
		$prefix	= WOO_CL_META_PREFIX;
		
		if( !empty( $post_id ) ) { // check if product id is not empty
			
			// get post type from product id
			$post_type	= get_post_type( $post_id );
			
			// check if post type is woocommerce product's post type
			if( $post_type == WOO_CL_PRODUCT_POSTTYPE ) {
				
				//argument for get collection item based on product id
				$args = array(
								'meta_query'	=> array(
															'post_status'	=> 'any',
															 array( 
																	'key' 	=> $prefix.'coll_product_id',
																	'value' => $post_id
															 )
														)
							 );
				
				//Get collection item
				$posts	= $woo_cl_model->woo_cl_get_collection_items( $args );
				
				if( !empty( $posts ) ){ // check if get any post
					
					foreach ( $posts as $post ) {
						
						// delete all collection items
						wp_delete_post( $post['ID'] );
					}
				}
			}
		}
	}
	
	/**
	 * Untrash Collection Item
	 * 
	 * Handle to untrash collection item
	 * when product is restore
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_untrash_collection_item( $product_id = '' ) {
		
		global $woo_cl_model;
		
		$prefix	= WOO_CL_META_PREFIX;
		
		if( !empty( $post_id ) ) { // check if product id is not empty
			
			// get post type from product id
			$post_type = get_post_type( $post_id );
			
			// check if post type is woocommerce product's post type
			if( $post_type == WOO_CL_PRODUCT_POSTTYPE ) { 
				
				//argument for get collection item based on product id
				$args = array(
								'meta_query'	=> array(
															'post_status'	=> 'any',
															 array( 
																	'key' 	=> $prefix.'coll_product_id',
																	'value' => $post_id
															 )
														)
							 );
				
				//Get collection item
				$posts	= $woo_cl_model->woo_cl_get_collection_items( $args );
				
				if( !empty( $posts ) ){ // check if get any post
					
					foreach ( $posts as $post ) {
						
						// delete all collection items
						wp_untrash_post( $post['ID'] );
					}
				}
			}
		}
	}

	/**
	 * Collection featured / unfeatured update
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	function woo_cl_featured_ajax() {

 		$prefix = WOO_CL_META_PREFIX;

		if( isset( $_POST['post_id'],  $_POST['woo_cl_featured'] ) && !empty( $_POST['post_id'] ) ) {

			$featured =  $_POST['woo_cl_featured'];
			update_post_meta( $_POST['post_id'], $prefix.'featured_coll', $featured );
			echo $featured;
			exit;
		}
	}

	/**
	 * Rewrite Collection Parameter
	 * 
	 * Handle to rewrite collection parameter when cllection setting has been changed
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_woocommerce_settings_saved() {

		global $current_tab, $woo_cl_query;

		if( $current_tab == 'collection' ) {

			//add endpoints to query vars
			//Need to call before flush rewrite rules
			$woo_cl_query->woo_cl_init_query_vars();
			$woo_cl_query->woo_cl_add_endpoints();
		}
	}

	/**
	 * Reset Guest User
	 * 
	 * Handle to reset guest user
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_restore_guest_author( $postid, $post ) {

		$prefix	= WOO_CL_META_PREFIX;

		$post_type_object = get_post_type_object( $post->post_type );

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	// Check Autosave
			|| ( ! isset( $_POST['post_ID'] ) || $postid != $_POST['post_ID'] )
			|| ( ! current_user_can( $post_type_object->cap->edit_post, $postid ) )
			|| ( $post->post_type != WOO_CL_POST_TYPE_COLL ) )  {
			return $postid;
		}

		//if set collection tocken
		$coll_list_token = get_post_meta( $postid, $prefix.'cl_token', true );

		if( !empty( $coll_list_token ) ) { // if access token is not empty

			$user_id = 0;
			$this->woo_cl_update_collection_author( $postid, $user_id );
		}
	}

	/**
	 * Update Author In Collection
	 * 
	 * Handle to update author in collection
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function woo_cl_update_collection_author( $post_id, $user_id ) {

		global $wpdb;

		$wpdb->update( $wpdb->posts, array( 'post_author' => $user_id ), array( 'ID' => $post_id ) );
	}
	
	/**
	 * Add custom fields to customers
	 * 
	 * Manage custom meta fieds for,
	 * maximum collection per user and,
	 * maximum products per collection.
	 * 
	 * displays only admin user
	 * 
	 * @package WPWeb WooCommerce Collections
	 * @since 1.0.0
	 */
	public function woo_cl_add_customer_meta_fields( $user ) { 
		
		if ( !current_user_can( 'edit_user' ) ) {
			return false;
		} ?>
			
		<h3><?php echo __( 'Collection Settings', 'woocl' ); ?></h3>
		
		<table class="form-table">
			<tr>
				<th><label for="cl_max_collections_per_user"><?php echo __( 'Maximum collections per user', 'woocl' ); ?></label></th>
				
				<td>
					<input type="text" name="cl_max_collections_per_user" id="cl_max_collections_per_user" value="<?php echo esc_attr( get_the_author_meta( 'cl_max_collections_per_user', $user->ID ) ); ?>" /><br />
					<span class="description"><?php echo __( 'Enter the maximum collections allowed per user, Leave it empty to use this setting from the settings page.', 'woocl' ) ?></span>
				</td>
			</tr>
			
			<tr>
				<th><label for="cl_max_products_per_collection"><?php echo __( 'Maximum products per collection', 'woocl' ); ?></label></th>
				
				<td>
					<input type="text" name="cl_max_products_per_collection" id="cl_max_products_per_collection" value="<?php echo esc_attr( get_the_author_meta( 'cl_max_products_per_collection', $user->ID ) ); ?>" /><br />
					<span class="description"><?php echo __( 'Enter the maximum products allowed per collection, Leave it empty to use this setting from the settings page.', 'woocl' ) ?></span>
				</td>
			</tr>
		</table>
	<?php
	}
	
	/**
	 * Save collection settings for user
	 * 
	 * @package WPWeb WooCommerce Collections
	 * @since 1.0.0
	 */
	public function woo_cl_save_customer_meta_fields( $user_id ) {
		
		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
		
		$user_max_collection = isset($_POST['cl_max_collections_per_user']) ? $this->model->woo_cl_escape_slashes_deep( trim($_POST['cl_max_collections_per_user']) ) : '';
		$user_max_products	 = isset($_POST['cl_max_products_per_collection']) ? $this->model->woo_cl_escape_slashes_deep( trim($_POST['cl_max_products_per_collection']) ) : '';
		
		update_user_meta( $user_id, 'cl_max_collections_per_user', $user_max_collection );
		update_user_meta( $user_id, 'cl_max_products_per_collection', $user_max_products );
	}
	
	/**
	 * Display product meta
	 * 
	 * Manage product meta for hide,
	 * collection button from perticular button
	 * 
	 * @package WPWeb WooCommerce Collections
	 * @since 1.0.3
	 */
	public function woo_cl_add_product_meta_settings() { 
		
		global $post;
		
		$prefix	= WOO_CL_META_PREFIX; ?>
		
		<div class="options_group woo_cl_collection hide_if_grouped"><?php
			woocommerce_wp_checkbox( array( 
										'id' => $prefix . 'hide_coll_btn',
										'label' => sprintf(__( 'Disable %s Button', 'woocl' ), woo_cl_get_label_singular()), 
										'cbvalue' => 'disable',
										'desc_tip' => true,
										'description' => sprintf(__( 'Check this box if you want to disable add to %s feature for this product.', 'woocl' ), woo_cl_get_label_singular( true )),
										'value' => esc_attr( $post->_woo_cl_hide_coll_btn ) 
									) );?>
		</div><?php
	}
	
	/**
	 * Save product meta
	 * 
	 * Save product meta for hide,
	 * collection button in product
	 * 
	 * @package WPWeb WooCommerce Collections
	 * @since 1.0.3
	 */
	public function woo_cl_save_product_meta_settings( $post_id, $post ) {
		
		$prefix	= WOO_CL_META_PREFIX;
		
		if( isset( $_POST[$this->prefix.'hide_coll_btn'] ) ) {
			update_post_meta( $post_id, $prefix . 'hide_coll_btn', $_POST[$this->prefix.'hide_coll_btn'] );
		} else {
			update_post_meta( $post_id, $prefix . 'hide_coll_btn', '' );
		}
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the admin post list.
	 * 
	 * @package WPWeb WooCommerce Collections
 	 * @since 1.0.0
	 */
	public function add_hooks() {

		//add action for guest user collection update
		add_action( 'user_register', array( $this, 'woo_cl_new_user_registration' ), 10, 1 );
		add_action( 'wpmu_new_user', array( $this, 'woo_cl_new_user_registration' ), 10, 1 );

		//add new field to post listing page
		add_action('manage_'.WOO_CL_POST_TYPE_COLL.'_posts_custom_column', array($this,'woo_cl_manage_custom_columns'), 10, 2);
		add_filter('manage_edit-'.WOO_CL_POST_TYPE_COLL.'_columns', array($this,'woo_cl_add_new_collections_columns'));

		//add filter to add new action "view preview" on admin Collections page
		add_filter( 'post_row_actions', array( $this , 'woo_cl_preview_action_new_link_add' ), 10, 2 );

		//Rewrite urls when settings has been changed
		// this action called after settings saved in database
		add_action( 'woocommerce_update_options', array( $this, 'woo_cl_woocommerce_settings_saved' ), 100 );

		// mark up for popup
		add_action( 'admin_footer-post.php', array( $this,'woo_cl_shortcode_popup_markup' ) );
		add_action( 'admin_footer-post-new.php', array( $this,'woo_cl_shortcode_popup_markup' ) );

		//Hide follow my blog post  metaboxes from collections post type
		add_filter( 'wpw_fp_check_post_update_notification', array($this, 'woo_cl_follow_notification_checkbox_display' ) );
		add_action( 'do_meta_boxes', array( $this, 'woo_cl_remove_followm_metabox' ) );

		//add action for email templates classes for woo collection
		add_filter( 'woocommerce_email_classes', array( $this, 'woo_cl_add_email_classes' ) );

		// delete collection item when delete collection or delete product
		add_action('before_delete_post', array( $this, 'woo_cl_delete_all_collection_product' ) );

		//add action for update featured collection
		add_action( 'admin_init', array( $this, 'woo_cl_featured_ajax' ) );

		// trash collection item when product are moved to trash
		//add_action( 'wp_trash_post', array( $this, 'woo_cl_trash_collection_item' ) );
		
		// restore collection item when product are restore
		//add_action( 'untrash_post', array( $this, 'woo_cl_untrash_collection_item' ) );
		
		add_action( 'save_post', array( $this, 'woo_cl_restore_guest_author' ), 10, 2 );
		
		// Add custom fields for maximum collection per user and maximum products per collection
		add_action( 'show_user_profile', array($this, 'woo_cl_add_customer_meta_fields'), 20 );
		add_action( 'edit_user_profile', array($this, 'woo_cl_add_customer_meta_fields'), 20 );
		
		// Save customer collection meta fields
		add_action( 'personal_options_update', array($this, 'woo_cl_save_customer_meta_fields') );
		add_action( 'edit_user_profile_update', array($this, 'woo_cl_save_customer_meta_fields') );
		
		// Add product meta in general tab for show/hide collection button
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'woo_cl_add_product_meta_settings' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'woo_cl_save_product_meta_settings' ), 10, 2 );
	}
}