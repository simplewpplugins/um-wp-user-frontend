<?php 
namespace umwpuf\admin;

if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists('umwpuf\admin\Admin')){

	class Admin {


		function __construct(){

			add_action( 'admin_menu', array( $this, 'create_admin_submenu' ), 1001 );

			add_action( 'init', array( $this, 'register_post_type' ) );

			add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );

			add_filter( 'um_profile_tabs', array( $this, 'filter_profile_tabs_arr') );

			add_action( 'save_post', array( $this, 'save_meta_data' ), 10, 3 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue') );

		}



		function admin_enqueue(){

			global $current_screen;

			if ( $current_screen->id == 'umwpuf' ) {
				wp_register_style( 'um_fonticons_ii', um_url . 'assets/css/um-fonticons-ii.css', array(), ultimatemember_version );
				wp_enqueue_style( 'um_fonticons_ii' );

				wp_register_style( 'um_fonticons_fa', um_url . 'assets/css/um-fonticons-fa.css', array(), ultimatemember_version );
				wp_enqueue_style( 'um_fonticons_fa' );
			}
		}




		function filter_profile_tabs_arr( $tabs_arr ){


			$tabs = UMWPUF()->helper()->get_posting_tabs();
			
			foreach( $tabs as $tab ){

				if( ! UMWPUF()->helper()->can_have_tab( um_profile_id(), $tab['id'] ) ){
					continue;
				}

				if( ! UMWPUF()->helper()->can_view_tab( $tab['slug'] ) ){
					continue;
				}

				$tabs_arr[$tab['slug']] = array(
					'name' => esc_html( $tab['title'] ),
					'icon' => esc_html( $tab['icon'] )
				);
			}

			return $tabs_arr;

		}


		/**
		 * Add UM submenu for Profile Tabs
		 */
		function create_admin_submenu() {
			add_submenu_page( 'ultimatemember', __( 'Frontend post Tabs', 'um-wp-user-frontend' ), __( 'Frontend Post Tabs', 'um-frontend-posting' ), 'manage_options', 'edit.php?post_type=umwpuf' );
		}


		function register_post_type(){

				$labels = [
				'name'              => _x( 'Posting Tabs', 'Post Type General Name', 'um-wp-user-frontend' ),
				'singular_name'     => _x( 'Posting tab', 'Post Type Singular Name', 'um-wp-user-frontend' ),
				'menu_name'         => __( 'Posting Tabs', 'um-wp-user-frontend' ),
				'name_admin_bar'    => __( 'Profile Tabs', 'um-wp-user-frontend' ),
				'archives'          => __( 'Item Archives', 'um-wp-user-frontend' ),
				'attributes'        => __( 'Item Attributes', 'um-wp-user-frontend' ),
				'parent_item_colon' => __( 'Parent Item:', 'um-wp-user-frontend' ),
				'all_items'         => __( 'All Items', 'um-wp-user-frontend' ),
				'add_new_item'      => __( 'Add New Item', 'um-wp-user-frontend' ),
				'add_new'           => __( 'Add New', 'um-wp-user-frontend' ),
				'new_item'          => __( 'New Item', 'um-wp-user-frontend' ),
				'edit_item'         => __( 'Edit Item', 'um-wp-user-frontend' ),
				'update_item'       => __( 'Update Item', 'um-wp-user-frontend' ),
				'view_item'         => __( 'View Item', 'um-wp-user-frontend' ),
				'view_items'        => __( 'View Items', 'um-wp-user-frontend' ),
				'search_items'      => __( 'Search Item', 'um-wp-user-frontend' ),
				'not_found'         => __( 'Not found', 'um-wp-user-frontend' ),
			];
			
			$args = [
				'label'                 => __( 'Frontend Post Tabs', 'um-wp-user-frontend' ),
				'description'           => __( 'Ultimate member tab for frontend posting', 'um-wp-user-frontend' ),
				'labels'                => $labels,
				'supports'              => [ 'title','editor '],
				'hierarchical'          => false,
				'public'                => false,
				'show_ui'               => true,
				'show_in_menu'          => false,
				'menu_position'         => 5,
				'show_in_admin_bar'     => false,
				'show_in_nav_menus'     => false,
				'can_export'            => false,
				'has_archive'           => false,
				'exclude_from_search'   => true,
				'publicly_queryable'    => true,
				'capability_type'       => 'page',
			];

			register_post_type( 'umwpuf', $args );

		}


		function add_metaboxes(){


			global $current_screen;

			if ( $current_screen->id == 'umwpuf' ) {

				add_meta_box('umwpuf-posting-form', __( 'Tab Settings', 'um-wp-user-frontend' ), array( $this, 'tab_metabox_ui'), 'umwpuf', 'normal', 'default' );
			}
		}

		function tab_metabox_ui( $post ){

			$tab_id = $post->ID;

			UMWPUF()->get_template_part('admin/metabox',['tab_id' => $post->ID ]);

		}


		/**
		 * Save Profile Tab metabox settings
		 *
		 * @param int $post_id
		 * @param \WP_Post $post
		 * @param bool $update
		 */

		function save_meta_data( $post_id, $post, $update ){

			global $current_screen;
			if ( $current_screen->id != 'umwpuf' ) {
				return;
			}

			//make this handler only on product form submit
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'update-post_' . $post_id ) ) {
				return;
			}

			if ( empty( $post->post_type ) || 'umwpuf' != $post->post_type ) {
				return;
			}

			if ( empty( $_POST['umwpuf'] ) ) {
				return;
			}

			$description = '';
			if ( isset( $_POST['umwpuf']['_description'] ) ) {
				$description =  sanitize_text_field( $_POST['umwpuf']['_description'] );
			}
			update_post_meta( $post_id, 'umwpuf_description', $description );

			$icon = 'um-icon-android-list';
			if ( isset( $_POST['umwpuf']['_tabicon'] ) ) {
				$icon =   sanitize_text_field( $_POST['umwpuf']['_tabicon'] );
				if( trim( $icon) == '' ){
					$icon = 'um-icon-android-list';
				}
			}
			update_post_meta( $post_id, 'umwpuf_icon', $icon );


			$post_form = '';
			if ( isset( $_POST['umwpuf']['_post_form'] ) ) {
				$post_form = sanitize_key( $_POST['umwpuf']['_post_form'] );
			}
			update_post_meta( $post_id, 'umwpuf_post_form', $post_form );


			if ( ! empty( $_POST['umwpuf']['_can_have_this_tab_roles'] ) ) {

				$tab_roles = array_map( 'sanitize_text_field', $_POST['umwpuf']['_can_have_this_tab_roles'] );
				update_post_meta( $post_id, '_can_have_this_tab_roles', $tab_roles );

			} else {

				update_post_meta( $post_id, '_can_have_this_tab_roles', [] );
				
			}


		}


	}

}