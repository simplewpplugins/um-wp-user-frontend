<?php
namespace umwpuf\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'umwpuf\admin\Admin' ) ) {

	class Admin {

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'create_admin_submenu' ), 1001 );
			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
			add_action( 'save_post', array( $this, 'save_meta_data' ), 10, 3 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
			add_filter( 'manage_umwpuf_posts_columns', array( $this, 'list_columns' ) );
			add_action( 'manage_umwpuf_posts_custom_column', array( $this, 'list_column_content' ), 10, 2 );
			add_action( 'wp_ajax_umwpuf_designer_preview', array( $this, 'designer_preview' ) );
		}

		/** Renders an unsaved design against real entries for the designer's preview grid. */
		public function designer_preview() {
			if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'umwpuf_designer_preview', 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => __( 'Access denied.', 'um-wp-user-frontend' ) ), 403 );
			}
			$form_id = isset( $_POST['form'] ) ? absint( $_POST['form'] ) : 0;
			$config  = isset( $_POST['config'] ) ? (string) $_POST['config'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- whitelisted by Loop_Builder::sanitize().
			$html    = \umwpuf\Loop_Builder::preview_html( $config, $form_id, 6 );
			wp_send_json_success( array( 'html' => $html ) );
		}

		public static function mode_labels() {
			return array(
				'workspace' => __( 'Entries + Add New workspace (recommended)', 'um-wp-user-frontend' ),
				'submit'    => __( 'Submission form only (legacy)', 'um-wp-user-frontend' ),
				'manage'    => __( 'Owner’s posts dashboard (legacy)', 'um-wp-user-frontend' ),
				'public'    => __( 'Public published posts (legacy)', 'um-wp-user-frontend' ),
			);
		}

		public static function layout_labels() {
			return array(
				'grid'   => __( 'Grid', 'um-wp-user-frontend' ),
				'list'   => __( 'List', 'um-wp-user-frontend' ),
				'table'  => __( 'Compact rows', 'um-wp-user-frontend' ),
				'custom' => __( 'Custom layout (designer)', 'um-wp-user-frontend' ),
			);
		}

		private function is_tab_screen() {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			return $screen && 'umwpuf' === $screen->id;
		}

		public function admin_enqueue() {
			if ( ! $this->is_tab_screen() ) {
				return;
			}
			wp_enqueue_style( 'umwpuf-layout', UMWPUF_URL . 'assets/layout.css', array(), UMWPUF_VERSION );
			wp_enqueue_style( 'umwpuf-loop-builder', UMWPUF_URL . 'assets/loop-builder.css', array( 'umwpuf-layout' ), UMWPUF_VERSION );
			wp_enqueue_script( 'umwpuf-loop-builder', UMWPUF_URL . 'assets/loop-builder.js', array(), UMWPUF_VERSION, true );
			wp_enqueue_script( 'umwpuf-admin-visibility', UMWPUF_URL . 'assets/admin-visibility.js', array(), UMWPUF_VERSION, true );
		}

		public function create_admin_submenu() {
			add_submenu_page( 'ultimatemember', __( 'Frontend Post Tabs', 'um-wp-user-frontend' ), __( 'Frontend Post Tabs', 'um-wp-user-frontend' ), 'manage_options', 'edit.php?post_type=umwpuf' );
		}

		public function register_post_type() {
			$labels = array(
				'name'               => _x( 'Frontend Post Tabs', 'Post Type General Name', 'um-wp-user-frontend' ),
				'singular_name'      => _x( 'Frontend post tab', 'Post Type Singular Name', 'um-wp-user-frontend' ),
				'menu_name'          => __( 'Frontend Post Tabs', 'um-wp-user-frontend' ),
				'name_admin_bar'     => __( 'Profile Tab', 'um-wp-user-frontend' ),
				'all_items'          => __( 'All Tabs', 'um-wp-user-frontend' ),
				'add_new_item'       => __( 'Add New Profile Tab', 'um-wp-user-frontend' ),
				'add_new'            => __( 'Add New Tab', 'um-wp-user-frontend' ),
				'new_item'           => __( 'New Tab', 'um-wp-user-frontend' ),
				'edit_item'          => __( 'Edit Profile Tab', 'um-wp-user-frontend' ),
				'update_item'        => __( 'Update Tab', 'um-wp-user-frontend' ),
				'search_items'       => __( 'Search Tabs', 'um-wp-user-frontend' ),
				'not_found'          => __( 'No tabs yet. Add one to give members a frontend workspace.', 'um-wp-user-frontend' ),
				'not_found_in_trash' => __( 'No tabs in Trash.', 'um-wp-user-frontend' ),
			);
			// Tab configuration is site-wide; restrict every capability to administrators
			// (previously "page" capabilities let Editors create and edit tabs directly).
			$cap = 'manage_options';
			register_post_type(
				'umwpuf',
				array(
					'label'               => __( 'Frontend Post Tabs', 'um-wp-user-frontend' ),
					'description'         => __( 'Ultimate Member tab for frontend posting', 'um-wp-user-frontend' ),
					'labels'              => $labels,
					'supports'            => array( 'title', 'page-attributes' ),
					'hierarchical'        => false,
					'public'              => false,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'show_in_admin_bar'   => false,
					'show_in_nav_menus'   => false,
					'show_in_rest'        => false,
					'can_export'          => true,
					'has_archive'         => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => false,
					'map_meta_cap'        => false,
					'capabilities'        => array(
						'edit_post'              => $cap,
						'read_post'              => $cap,
						'delete_post'            => $cap,
						'edit_posts'             => $cap,
						'edit_others_posts'      => $cap,
						'delete_posts'           => $cap,
						'publish_posts'          => $cap,
						'read_private_posts'     => $cap,
						'create_posts'           => $cap,
						'delete_private_posts'   => $cap,
						'delete_published_posts' => $cap,
						'delete_others_posts'    => $cap,
						'edit_private_posts'     => $cap,
						'edit_published_posts'   => $cap,
					),
				)
			);
		}

		public function list_columns( $columns ) {
			$date = $columns['date'] ?? null;
			unset( $columns['date'] );
			$columns['umwpuf_mode']   = __( 'Purpose', 'um-wp-user-frontend' );
			$columns['umwpuf_form']   = __( 'Form', 'um-wp-user-frontend' );
			$columns['umwpuf_layout'] = __( 'Layout', 'um-wp-user-frontend' );
			$columns['umwpuf_key']    = __( 'Tab key', 'um-wp-user-frontend' );
			if ( $date ) {
				$columns['date'] = $date;
			}
			return $columns;
		}

		public function list_column_content( $column, $post_id ) {
			switch ( $column ) {
				case 'umwpuf_mode':
					$labels = self::mode_labels();
					echo esc_html( $labels[ UMWPUF()->helper()->tab_mode( $post_id ) ] );
					break;
				case 'umwpuf_form':
					$form_id = absint( get_post_meta( $post_id, 'umwpuf_post_form', true ) );
					if ( $form_id && 'wpuf_forms' === get_post_type( $form_id ) ) {
						$type = UMWPUF()->helper()->form_post_type( $form_id );
						echo esc_html( get_the_title( $form_id ) ) . ( $type ? ' <code>' . esc_html( $type ) . '</code>' : '' );
						if ( 'publish' !== get_post_status( $form_id ) ) {
							echo ' <em>' . esc_html__( '(form not published)', 'um-wp-user-frontend' ) . '</em>';
						}
					} else {
						echo '<span style="color:#b32d2e">' . esc_html__( 'No form selected', 'um-wp-user-frontend' ) . '</span>';
					}
					break;
				case 'umwpuf_layout':
					if ( 'submit' !== UMWPUF()->helper()->tab_mode( $post_id ) ) {
						$labels = self::layout_labels();
						$layout = get_post_meta( $post_id, '_umwpuf_layout', true );
						echo esc_html( $labels[ $layout ] ?? $labels['grid'] );
					} else {
						echo '&mdash;';
					}
					break;
				case 'umwpuf_key':
					echo '<code>' . esc_html( UMWPUF()->helper()->tab_key( $post_id ) ) . '</code>';
					break;
			}
		}

		public function add_metaboxes() {
			if ( $this->is_tab_screen() && defined( 'um_url' ) ) {
				add_meta_box( 'umwpuf-posting-form', __( 'Tab Settings', 'um-wp-user-frontend' ), array( $this, 'tab_metabox_ui' ), 'umwpuf', 'normal', 'high' );
				add_meta_box( 'umwpuf-layout-designer', __( 'Custom Layout Designer', 'um-wp-user-frontend' ), array( $this, 'designer_metabox_ui' ), 'umwpuf', 'normal', 'default' );
			}
		}

		public function tab_metabox_ui( $post ) {
			UMWPUF()->get_template_part( 'admin/metabox', array( 'tab_id' => $post->ID ) );
		}

		public function designer_metabox_ui( $post ) {
			UMWPUF()->get_template_part( 'admin/designer', array( 'tab_id' => $post->ID ) );
		}

		/**
		 * Save Profile Tab metabox settings.
		 *
		 * @param int      $post_id
		 * @param \WP_Post $post
		 * @param bool     $update
		 */
		public function save_meta_data( $post_id, $post, $update ) {
			if ( ! $post || 'umwpuf' !== $post->post_type || ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( wp_doing_ajax() || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
				return;
			}
			if ( empty( $_POST['umwpuf_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['umwpuf_settings_nonce'] ) ), 'umwpuf_save_tab_' . $post_id ) ) {
				return;
			}
			if ( empty( $_POST['umwpuf'] ) || ! is_array( $_POST['umwpuf'] ) ) {
				return;
			}
			$input = wp_unslash( $_POST['umwpuf'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each field sanitized below.

			$mode = isset( $input['_mode'] ) ? sanitize_key( $input['_mode'] ) : 'submit';
			update_post_meta( $post_id, '_umwpuf_mode', array_key_exists( $mode, self::mode_labels() ) ? $mode : 'submit' );

			$layout = isset( $input['_layout'] ) ? sanitize_key( $input['_layout'] ) : 'grid';
			update_post_meta( $post_id, '_umwpuf_layout', array_key_exists( $layout, self::layout_labels() ) ? $layout : 'grid' );

			if ( isset( $_POST['umwpuf_loop_builder'] ) ) {
				// Loop_Builder::sanitize() unslashes and whitelists the JSON payload.
				update_post_meta( $post_id, '_umwpuf_loop_builder', \umwpuf\Loop_Builder::sanitize( (string) $_POST['umwpuf_loop_builder'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			}

			$per_page = isset( $input['_per_page'] ) ? absint( $input['_per_page'] ) : 9;
			update_post_meta( $post_id, '_umwpuf_per_page', max( 1, min( 50, $per_page ?: 9 ) ) );
			update_post_meta( $post_id, '_umwpuf_search', ! empty( $input['_search'] ) ? '1' : '0' );

			$post_form = isset( $input['_post_form'] ) ? absint( $input['_post_form'] ) : 0;
			update_post_meta( $post_id, 'umwpuf_post_form', $post_form );

			// Validate selected filters against the taxonomies the listing will actually query.
			$query_type = 'workspace' === $mode ? UMWPUF()->helper()->form_post_type( $post_form ) : 'post';
			$available  = array_keys( UMWPUF()->helper()->filterable_taxonomies( $query_type ) );
			$requested  = isset( $_POST['umwpuf_taxonomy_filters'] ) && is_array( $_POST['umwpuf_taxonomy_filters'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['umwpuf_taxonomy_filters'] ) ) : array();
			update_post_meta( $post_id, '_umwpuf_taxonomy_filters', array_values( array_intersect( $requested, $available ) ) );

			update_post_meta( $post_id, 'umwpuf_description', isset( $input['_description'] ) ? sanitize_textarea_field( $input['_description'] ) : '' );

			$icon = isset( $input['_tabicon'] ) ? sanitize_html_class( $input['_tabicon'] ) : '';
			update_post_meta( $post_id, 'umwpuf_icon', '' !== trim( $icon ) ? $icon : 'um-icon-android-list' );

			$tab_roles = ! empty( $input['_can_have_this_tab_roles'] ) ? array_values( array_filter( array_map( 'sanitize_key', (array) $input['_can_have_this_tab_roles'] ) ) ) : array();
			update_post_meta( $post_id, '_can_have_this_tab_roles', $tab_roles );

			UMWPUF()->helper()->flush_tab_cache();
		}
	}
}
