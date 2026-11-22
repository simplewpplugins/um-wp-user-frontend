<?php
namespace umwpuf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'umwpuf\Helper' ) ) {

	class Helper {

		/** @var array|null Cached published tab definitions. */
		public $posting_forms = null;

		/** @var array Per-request cache of form field definitions. */
		private $form_fields_cache = array();

		/**
		 * WPUF field types that never hold displayable, user-facing values.
		 * Hidden fields are excluded because site owners use them for internal data.
		 */
		const EXCLUDED_FIELD_TYPES = array( 'hidden', 'password', 'recaptcha', 'really_simple_captcha', 'action_hook', 'toc', 'section_break', 'html', 'step_start', 'shortcode', 'custom_hidden_field', 'cloudflare_turnstile' );

		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_font_icons' ) );
			add_action( 'init', array( $this, 'plugin_i18n' ) );
			// Tab definitions change only when a tab is saved; drop the cache then.
			add_action( 'save_post_umwpuf', array( $this, 'flush_tab_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_tab_cache' ) );
		}

		public function plugin_i18n() {
			load_plugin_textdomain( 'um-wp-user-frontend', false, dirname( plugin_basename( UMWPUF_PLUGIN ) ) . '/languages/' );
		}

		public function flush_tab_cache() {
			$this->posting_forms = null;
		}

		public function load_font_icons() {
			global $current_screen;
			if ( $current_screen && 'umwpuf' === $current_screen->id && defined( 'um_url' ) ) {
				$version = defined( 'ultimatemember_version' ) ? constant( 'ultimatemember_version' ) : null;
				wp_enqueue_style( 'um_fonticons_ii', constant( 'um_url' ) . 'assets/css/um-fonticons-ii.css', array(), $version );
				wp_enqueue_style( 'um_fonticons_fa', constant( 'um_url' ) . 'assets/css/um-fonticons-fa.css', array(), $version );
			}
		}

		/**
		 * Collect every role slug the profile owner has, without switching
		 * Ultimate Member's globally "fetched" user (um_fetch_user() has side
		 * effects on whatever profile is currently being rendered).
		 */
		public function user_roles( $user_id ) {
			$user  = get_userdata( (int) $user_id );
			$roles = $user ? (array) $user->roles : array();
			if ( function_exists( 'UM' ) && method_exists( UM()->roles(), 'get_all_user_roles' ) ) {
				$um_roles = UM()->roles()->get_all_user_roles( (int) $user_id );
				if ( is_array( $um_roles ) ) {
					$roles = array_merge( $roles, $um_roles );
				}
			}
			$normalized = array();
			foreach ( $roles as $role ) {
				$role         = sanitize_key( $role );
				$normalized[] = $role;
				// UM 1.x stored custom roles without the "um_" prefix that UM 2.x uses.
				if ( 0 === strpos( $role, 'um_' ) ) {
					$normalized[] = substr( $role, 3 );
				}
			}
			return array_values( array_unique( array_filter( $normalized ) ) );
		}

		public function can_have_tab( $profile_id, $tab_id ) {
			$tab = get_post( $tab_id );
			if ( ! $tab || 'umwpuf' !== $tab->post_type ) {
				return false;
			}
			$tab_roles = get_post_meta( $tab->ID, '_can_have_this_tab_roles', true );
			if ( empty( $tab_roles ) ) {
				return true;
			}
			$wanted = array();
			foreach ( (array) $tab_roles as $role ) {
				$role     = sanitize_key( $role );
				$wanted[] = $role;
				if ( 0 === strpos( $role, 'um_' ) ) {
					$wanted[] = substr( $role, 3 );
				}
			}
			return (bool) array_intersect( $wanted, $this->user_roles( $profile_id ) );
		}

		/**
		 * Profile tab keys that belong to Ultimate Member or its extensions.
		 * A tab whose slug collides with one of these falls back to "umwpuf-{ID}",
		 * otherwise it would replace (and double-render inside) the core tab.
		 */
		public function reserved_tab_keys() {
			$keys = array( 'main', 'posts', 'comments', 'activity', 'messages', 'friends', 'followers', 'following', 'groups', 'forums', 'bbpress', 'reviews', 'notifications', 'photos', 'jobs', 'woocommerce', 'purchases', 'product-reviews', 'mycred', 'badges', 'favorites' );
			return (array) apply_filters( 'umwpuf_reserved_tab_keys', $keys );
		}

		/**
		 * Single source of truth for a tab's Ultimate Member key.
		 * Keeps the legacy slug-based key (so existing links still work) unless it collides.
		 */
		public function tab_key( $tab ) {
			$id   = absint( is_array( $tab ) ? $tab['id'] : $tab );
			$slug = is_array( $tab ) && isset( $tab['slug'] ) ? sanitize_title( $tab['slug'] ) : sanitize_title( get_post_field( 'post_name', $id ) );
			if ( '' === $slug || in_array( $slug, $this->reserved_tab_keys(), true ) ) {
				return 'umwpuf-' . $id;
			}
			return $slug;
		}

		public function tab_mode( $tab_id ) {
			$mode = get_post_meta( $tab_id, '_umwpuf_mode', true );
			return in_array( $mode, array( 'submit', 'manage', 'public', 'workspace' ), true ) ? $mode : 'submit';
		}

		public function get_posting_tabs() {
			if ( null === $this->posting_forms ) {
				$this->posting_forms = array();
				$tabs = get_posts(
					array(
						'post_type'      => 'umwpuf',
						'posts_per_page' => -1,
						'post_status'    => 'publish',
						'orderby'        => 'menu_order title',
						'order'          => 'ASC',
					)
				);
				foreach ( $tabs as $tab ) {
					$this->posting_forms[] = array(
						'id'        => $tab->ID,
						'slug'      => $tab->post_name,
						'title'     => $tab->post_title,
						'icon'      => get_post_meta( $tab->ID, 'umwpuf_icon', true ),
						'post_form' => get_post_meta( $tab->ID, 'umwpuf_post_form', true ),
						'content'   => get_post_meta( $tab->ID, 'umwpuf_description', true ),
					);
				}
			}
			return $this->posting_forms;
		}

		/**
		 * Displayable custom fields of a WPUF form: meta-backed fields only,
		 * keyed by meta key. Used by the loop designer's "Custom field" element.
		 *
		 * @return array<string,array{label:string,type:string}>
		 */
		public function form_meta_fields( $form_id ) {
			$form_id = absint( $form_id );
			if ( ! $form_id || 'wpuf_forms' !== get_post_type( $form_id ) ) {
				return array();
			}
			if ( isset( $this->form_fields_cache[ $form_id ] ) ) {
				return $this->form_fields_cache[ $form_id ];
			}
			$raw = array();
			if ( function_exists( 'wpuf_get_form_fields' ) ) {
				$raw = (array) wpuf_get_form_fields( $form_id );
			} else {
				foreach ( get_children( array( 'post_parent' => $form_id, 'post_type' => 'wpuf_input', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $child ) {
					$raw[] = (array) maybe_unserialize( $child->post_content );
				}
			}
			$fields = array();
			foreach ( $raw as $field ) {
				if ( ! is_array( $field ) || empty( $field['name'] ) || ( $field['is_meta'] ?? '' ) !== 'yes' ) {
					continue;
				}
				$type = sanitize_key( $field['input_type'] ?? '' );
				if ( in_array( $type, self::EXCLUDED_FIELD_TYPES, true ) || in_array( sanitize_key( $field['template'] ?? '' ), self::EXCLUDED_FIELD_TYPES, true ) ) {
					continue;
				}
				$key = sanitize_key( $field['name'] );
				// Protected (underscore) keys are never shown, matching WordPress conventions.
				if ( '' === $key || 0 === strpos( $key, '_' ) ) {
					continue;
				}
				$fields[ $key ] = array(
					'label' => wp_strip_all_tags( (string) ( $field['label'] ?? $key ) ),
					'type'  => $type,
				);
			}
			$this->form_fields_cache[ $form_id ] = (array) apply_filters( 'umwpuf_form_meta_fields', $fields, $form_id );
			return $this->form_fields_cache[ $form_id ];
		}

		/** Post type a WPUF form creates, or '' when the form/type is unusable. */
		public function form_post_type( $form_id ) {
			$form_id = absint( $form_id );
			if ( ! $form_id || 'wpuf_forms' !== get_post_type( $form_id ) || ! function_exists( 'wpuf_get_form_settings' ) ) {
				return '';
			}
			$settings  = wpuf_get_form_settings( $form_id );
			$post_type = is_array( $settings ) && ! empty( $settings['post_type'] ) ? sanitize_key( $settings['post_type'] ) : 'post';
			return post_type_exists( $post_type ) ? $post_type : '';
		}

		/** Public, UI-enabled taxonomies of a post type as name => label. */
		public function filterable_taxonomies( $post_type ) {
			$out = array();
			if ( ! $post_type || ! post_type_exists( $post_type ) ) {
				return $out;
			}
			foreach ( get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) {
				if ( $taxonomy->public && $taxonomy->show_ui ) {
					$out[ $taxonomy->name ] = $taxonomy->label;
				}
			}
			return $out;
		}
	}
}
