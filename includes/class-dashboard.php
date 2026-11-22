<?php
namespace umwpuf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	public function __construct() {
		add_action( 'wp_ajax_umwpuf_posts', array( $this, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_umwpuf_posts', array( $this, 'ajax' ) );
		add_filter( 'wpuf_add_post_redirect', array( $this, 'workspace_redirect' ), 20, 4 );
		add_filter( 'wpuf_edit_post_redirect', array( $this, 'workspace_redirect' ), 20, 4 );
	}

	public function enqueue_styles() {
		wp_enqueue_style( 'umwpuf-layout', UMWPUF_URL . 'assets/layout.css', array(), UMWPUF_VERSION );
		wp_enqueue_style( 'umwpuf-dashboard', UMWPUF_URL . 'assets/dashboard.css', array( 'umwpuf-layout' ), UMWPUF_VERSION );
	}

	private function enqueue_assets() {
		$this->enqueue_styles();
		wp_enqueue_script( 'umwpuf-dashboard', UMWPUF_URL . 'assets/dashboard.js', array(), UMWPUF_VERSION, true );
		if ( ! wp_script_is( 'umwpuf-dashboard', 'done' ) ) {
			wp_localize_script(
				'umwpuf-dashboard',
				'umwpufDashboard',
				array(
					'ajax'  => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'umwpuf_posts' ),
					'i18n'  => array(
						'error' => __( 'Unable to load entries. Please try again.', 'um-wp-user-frontend' ),
						'retry' => __( 'Retry', 'um-wp-user-frontend' ),
					),
				)
			);
		}
	}

	/** Canonical URL of a tab on a profile; never depends on the current request URL. */
	private function tab_url( $tab_id, $profile_id ) {
		$url = function_exists( 'um_user_profile_url' ) ? um_user_profile_url( $profile_id ) : '';
		if ( ! $url ) {
			$url = remove_query_arg( array( 'pid', '_wpnonce', 'umwpuf_view', 'umwpuf_notice' ) );
		}
		return add_query_arg( 'profiletab', UMWPUF()->helper()->tab_key( $tab_id ), $url );
	}

	/**
	 * Keep successful WPUF AJAX submissions inside their originating profile tab.
	 * Only override redirects for a verified workspace request on this site.
	 */
	public function workspace_redirect( $response, $post_id, $form_id, $form_settings ) {
		if ( ! is_array( $response ) || empty( $response['success'] ) || ! is_user_logged_in() ) {
			return $response;
		}
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? wp_unslash( $_SERVER['HTTP_REFERER'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated below.
		$referer = wp_validate_redirect( esc_url_raw( $referer ), '' );
		if ( ! $referer ) {
			return $response;
		}
		$parts = wp_parse_url( $referer );
		if ( ! is_array( $parts ) || empty( $parts['query'] ) ) {
			return $response;
		}
		parse_str( $parts['query'], $query );
		$view = isset( $query['umwpuf_view'] ) && is_string( $query['umwpuf_view'] ) ? sanitize_key( $query['umwpuf_view'] ) : '';
		if ( ! in_array( $view, array( 'add', 'edit' ), true ) ) {
			return $response;
		}
		$post = get_post( absint( $post_id ) );
		if ( ! $post || (int) $post->post_author !== get_current_user_id() || absint( get_post_meta( $post->ID, '_wpuf_form_id', true ) ) !== absint( $form_id ) ) {
			return $response;
		}
		// Match the referring URL to a real workspace tab: UM's ?profiletab=key, or a /key/ path suffix.
		$path       = isset( $parts['path'] ) ? trailingslashit( $parts['path'] ) : '';
		$profiletab = isset( $query['profiletab'] ) && is_string( $query['profiletab'] ) ? sanitize_title( $query['profiletab'] ) : '';
		$matched    = false;
		foreach ( UMWPUF()->helper()->get_posting_tabs() as $tab ) {
			$tab_id = absint( $tab['id'] );
			if ( ! $this->is_workspace( $tab_id ) || absint( get_post_meta( $tab_id, 'umwpuf_post_form', true ) ) !== absint( $form_id ) ) {
				continue;
			}
			$key = UMWPUF()->helper()->tab_key( $tab );
			if ( $profiletab === $key || ( $path && substr( $path, -strlen( '/' . $key . '/' ) ) === '/' . $key . '/' ) ) {
				$matched = true;
				break;
			}
		}
		if ( ! $matched ) {
			return $response;
		}
		$return_url                = remove_query_arg( array( 'umwpuf_view', 'pid', '_wpnonce', 'msg' ), $referer );
		$response['redirect_to']   = add_query_arg( array( 'umwpuf_view' => 'entries', 'umwpuf_notice' => 'add' === $view ? 'added' : 'saved' ), $return_url );
		$response['show_message']  = false;
		return $response;
	}

	private function settings( $tab_id ) {
		$layout = get_post_meta( $tab_id, '_umwpuf_layout', true );
		return array(
			'layout'     => in_array( $layout, array( 'grid', 'list', 'table', 'custom' ), true ) ? $layout : 'grid',
			'per_page'   => max( 1, min( 50, absint( get_post_meta( $tab_id, '_umwpuf_per_page', true ) ?: 9 ) ) ),
			'search'     => '1' === get_post_meta( $tab_id, '_umwpuf_search', true ),
			'taxonomies' => $this->selected_taxonomies( $tab_id ),
		);
	}

	private function selected_taxonomies( $tab_id ) {
		list( $form_id, $post_type ) = $this->form_details( $tab_id );
		if ( ! $this->is_workspace( $tab_id ) ) {
			$post_type = 'post'; // Legacy modes always list standard posts.
		} elseif ( ! $form_id ) {
			return array();
		}
		$available = UMWPUF()->helper()->filterable_taxonomies( $post_type );
		$saved     = get_post_meta( $tab_id, '_umwpuf_taxonomy_filters', true );
		// Existing category-filter tabs retain their selection after upgrading.
		if ( ! is_array( $saved ) ) {
			$saved = '1' === get_post_meta( $tab_id, '_umwpuf_category_filter', true ) ? array( 'category' ) : array();
		}
		return array_values( array_intersect( array_map( 'sanitize_key', $saved ), array_keys( $available ) ) );
	}

	private function filter_controls( $tab_id, $profile_id ) {
		foreach ( $this->selected_taxonomies( $tab_id ) as $taxonomy ) {
			$object = get_taxonomy( $taxonomy );
			if ( ! $object ) {
				continue;
			}
			$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
			if ( is_wp_error( $terms ) || ! $terms ) {
				continue;
			}
			echo '<label class="umwpuf-taxonomy-filter-wrap"><span class="screen-reader-text">' . esc_html( $object->labels->singular_name ) . '</span><select class="umwpuf-taxonomy-filter" data-taxonomy="' . esc_attr( $taxonomy ) . '">';
			/* translators: %s: taxonomy label, e.g. "Categories" */
			echo '<option value="0">' . esc_html( sprintf( __( 'All %s', 'um-wp-user-frontend' ), $object->label ) ) . '</option>';
			foreach ( $terms as $term ) {
				echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
			}
			echo '</select></label>';
		}
	}

	private function permitted( $tab_id, $profile_id ) {
		$tab = get_post( $tab_id );
		if ( ! $tab || 'umwpuf' !== $tab->post_type || 'publish' !== $tab->post_status || ! get_userdata( $profile_id ) ) {
			return false;
		}
		$mode = get_post_meta( $tab_id, '_umwpuf_mode', true );
		if ( ! in_array( $mode, array( 'manage', 'public', 'workspace' ), true ) ) {
			return false;
		}
		$owner = is_user_logged_in() && get_current_user_id() === (int) $profile_id;
		if ( 'manage' === $mode && ! $owner ) {
			return false;
		}
		// Respect Ultimate Member profile privacy ("Only me", members-only, etc.).
		if ( ! $owner && function_exists( 'um_can_view_profile' ) && ! um_can_view_profile( $profile_id ) ) {
			return false;
		}
		return UMWPUF()->helper()->can_have_tab( $profile_id, $tab_id );
	}

	private function form_details( $tab_id ) {
		$form_id = absint( get_post_meta( $tab_id, 'umwpuf_post_form', true ) );
		if ( ! $form_id || 'publish' !== get_post_status( $form_id ) ) {
			return array( 0, '' );
		}
		$post_type = UMWPUF()->helper()->form_post_type( $form_id );
		return $post_type ? array( $form_id, $post_type ) : array( 0, '' );
	}

	private function is_workspace( $tab_id ) {
		return 'workspace' === get_post_meta( $tab_id, '_umwpuf_mode', true );
	}

	private function edit_url( $tab_id, $profile_id, $post_id ) {
		if ( ! is_user_logged_in() || get_current_user_id() !== (int) $profile_id || ! current_user_can( 'edit_post', $post_id ) ) {
			return '';
		}
		return add_query_arg(
			array( 'umwpuf_view' => 'edit', 'pid' => $post_id, '_wpnonce' => wp_create_nonce( 'wpuf_edit' ) ),
			$this->tab_url( $tab_id, $profile_id )
		);
	}

	private function search_box( $placeholder ) {
		echo '<label class="umwpuf-search-wrap"><span class="screen-reader-text">' . esc_html( $placeholder ) . '</span><input class="umwpuf-search" type="search" placeholder="' . esc_attr( $placeholder ) . '" autocomplete="off" enterkeyhint="search"></label>';
	}

	public function render( $tab ) {
		$tab_id     = absint( $tab['id'] );
		$profile_id = function_exists( 'um_profile_id' ) ? absint( um_profile_id() ) : 0;
		if ( ! $this->permitted( $tab_id, $profile_id ) ) {
			return;
		}
		$this->enqueue_assets();
		$settings = $this->settings( $tab_id );
		if ( $this->is_workspace( $tab_id ) ) {
			$this->render_workspace( $tab_id, $profile_id );
			return;
		}
		$mode = get_post_meta( $tab_id, '_umwpuf_mode', true );
		echo '<section class="umwpuf-dashboard" data-tab="' . esc_attr( $tab_id ) . '" data-user="' . esc_attr( $profile_id ) . '">';
		echo '<div class="umwpuf-toolbar"><div class="umwpuf-toolbar-title"><h2>' . esc_html( 'manage' === $mode ? __( 'My Posts', 'um-wp-user-frontend' ) : __( 'Published Posts', 'um-wp-user-frontend' ) ) . '</h2></div>';
		if ( $settings['search'] ) {
			$this->search_box( __( 'Search posts…', 'um-wp-user-frontend' ) );
		}
		$this->filter_controls( $tab_id, $profile_id );
		$this->reset_button();
		echo '</div><div class="umwpuf-results" aria-live="polite" aria-busy="false">';
		$this->results( $tab_id, $profile_id, '', array(), 1 );
		echo '</div></section>';
	}

	private function reset_button() {
		echo '<button type="button" class="umwpuf-reset" hidden>' . esc_html__( 'Clear filters', 'um-wp-user-frontend' ) . '</button>';
	}

	private function render_workspace( $tab_id, $profile_id ) {
		list( $form_id, $post_type ) = $this->form_details( $tab_id );
		if ( ! $form_id ) {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<p class="umwpuf-empty">' . esc_html__( 'Select a published WP User Frontend form with a valid post type in this tab’s settings.', 'um-wp-user-frontend' ) . '</p>';
			}
			return;
		}
		$owner = is_user_logged_in() && get_current_user_id() === (int) $profile_id;
		$view  = isset( $_GET['umwpuf_view'] ) ? sanitize_key( wp_unslash( $_GET['umwpuf_view'] ) ) : 'entries'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.
		if ( ! in_array( $view, array( 'entries', 'add', 'edit' ), true ) || ( ! $owner && 'entries' !== $view ) ) {
			$view = 'entries';
		}
		$base        = $this->tab_url( $tab_id, $profile_id );
		$entries_url = add_query_arg( 'umwpuf_view', 'entries', $base );
		$add_url     = add_query_arg( 'umwpuf_view', 'add', $base );
		echo '<section class="umwpuf-dashboard umwpuf-workspace" data-tab="' . esc_attr( $tab_id ) . '" data-user="' . esc_attr( $profile_id ) . '">';

		$notice = isset( $_GET['umwpuf_notice'] ) ? sanitize_key( wp_unslash( $_GET['umwpuf_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $owner && in_array( $notice, array( 'saved', 'added' ), true ) ) {
			$message = 'added' === $notice ? __( 'Entry submitted successfully.', 'um-wp-user-frontend' ) : __( 'Changes saved.', 'um-wp-user-frontend' );
			echo '<div class="umwpuf-success" role="status"><span>' . esc_html( $message ) . '</span><button type="button" class="umwpuf-dismiss" aria-label="' . esc_attr__( 'Dismiss', 'um-wp-user-frontend' ) . '">&times;</button></div>';
		}
		// Visitors only ever see Entries, so a one-item sub-navigation is noise.
		if ( $owner ) {
			echo '<nav class="umwpuf-subtabs" aria-label="' . esc_attr__( 'Content management', 'um-wp-user-frontend' ) . '">';
			echo '<a class="' . ( 'add' !== $view ? 'is-active' : '' ) . '" href="' . esc_url( $entries_url ) . '"' . ( 'entries' === $view ? ' aria-current="page"' : '' ) . '>' . esc_html__( 'Entries', 'um-wp-user-frontend' ) . '</a>';
			echo '<a class="' . ( 'add' === $view ? 'is-active' : '' ) . '" href="' . esc_url( $add_url ) . '"' . ( 'add' === $view ? ' aria-current="page"' : '' ) . '>' . esc_html__( 'Add New', 'um-wp-user-frontend' ) . '</a>';
			echo '</nav>';
		}
		$description = get_post_meta( $tab_id, 'umwpuf_description', true );
		if ( is_string( $description ) && '' !== trim( $description ) && 'entries' === $view ) {
			echo '<div class="umwpuf-tab-description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
		}

		if ( 'add' === $view ) {
			echo '<div class="umwpuf-workspace-form">' . do_shortcode( '[wpuf_form id="' . $form_id . '"]' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( 'edit' === $view ) {
			$post_id = isset( $_GET['pid'] ) ? absint( $_GET['pid'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified below.
			$post    = $post_id ? get_post( $post_id ) : null;
			$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			echo '<p class="umwpuf-back"><a href="' . esc_url( $entries_url ) . '">&larr; ' . esc_html__( 'Back to entries', 'um-wp-user-frontend' ) . '</a></p>';
			if ( ! $post || (int) $post->post_author !== (int) $profile_id || $post->post_type !== $post_type || absint( get_post_meta( $post_id, '_wpuf_form_id', true ) ) !== $form_id || ! wp_verify_nonce( $nonce, 'wpuf_edit' ) || ! current_user_can( 'edit_post', $post_id ) ) {
				echo '<p class="umwpuf-empty">' . esc_html__( 'You cannot edit this entry. The link may have expired; open it again from your entries.', 'um-wp-user-frontend' ) . '</p>';
			} else {
				echo '<h2 class="umwpuf-edit-title">' . esc_html( sprintf( /* translators: %s: entry title */ __( 'Editing: %s', 'um-wp-user-frontend' ), get_the_title( $post ) ?: __( '(Untitled)', 'um-wp-user-frontend' ) ) ) . '</h2>';
				echo '<div class="umwpuf-workspace-form umwpuf-edit-form">' . do_shortcode( '[umwpuf_edit_form tab_id="' . $tab_id . '" post_id="' . $post_id . '"]' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		} else {
			$settings = $this->settings( $tab_id );
			echo '<div class="umwpuf-toolbar">';
			if ( $settings['search'] ) {
				$this->search_box( __( 'Search entries…', 'um-wp-user-frontend' ) );
			}
			$this->filter_controls( $tab_id, $profile_id );
			$this->reset_button();
			if ( $owner ) {
				echo '<a class="umwpuf-add-button" href="' . esc_url( $add_url ) . '"><span aria-hidden="true">+</span> ' . esc_html__( 'Add New', 'um-wp-user-frontend' ) . '</a>';
			}
			echo '</div><div class="umwpuf-results" aria-live="polite" aria-busy="false">';
			$this->results( $tab_id, $profile_id, '', array(), 1 );
			echo '</div>';
		}
		echo '</section>';
	}

	public function ajax() {
		if ( ! check_ajax_referer( 'umwpuf_posts', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your session expired. Reload the page and try again.', 'um-wp-user-frontend' ) ), 403 );
		}
		$tab_id  = isset( $_POST['tab'] ) ? absint( $_POST['tab'] ) : 0;
		$user_id = isset( $_POST['user'] ) ? absint( $_POST['user'] ) : 0;
		if ( ! $this->permitted( $tab_id, $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access denied.', 'um-wp-user-frontend' ) ), 403 );
		}
		$settings = $this->settings( $tab_id );
		$search   = $settings['search'] && isset( $_POST['search'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST['search'] ) ), 0, 100 ) : '';
		$filters  = isset( $_POST['filters'] ) && is_array( $_POST['filters'] ) ? wp_unslash( $_POST['filters'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each value absint()-ed against whitelisted taxonomies in results().
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$term     = isset( $_POST['term'] ) ? absint( $_POST['term'] ) : 0;
		$page     = isset( $_POST['page'] ) ? max( 1, min( 10000, absint( $_POST['page'] ) ) ) : 1;
		ob_start();
		$this->results( $tab_id, $user_id, $search, $filters, $page, $taxonomy, $term );
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	private function status_badge( $post_id ) {
		$status = get_post_status( $post_id );
		$object = get_post_status_object( $status );
		return '<span class="umwpuf-status umwpuf-status-' . esc_attr( $status ) . '">' . esc_html( $object ? $object->label : $status ) . '</span>';
	}

	private function results( $tab_id, $profile_id, $search, $filters, $page, $taxonomy = '', $term = 0 ) {
		$settings  = $this->settings( $tab_id );
		$workspace = $this->is_workspace( $tab_id );
		$is_owner  = is_user_logged_in() && get_current_user_id() === (int) $profile_id;
		$owner     = ( 'manage' === get_post_meta( $tab_id, '_umwpuf_mode', true ) || $workspace ) && $is_owner;
		list( $form_id, $post_type ) = $workspace ? $this->form_details( $tab_id ) : array( 0, 'post' );
		if ( $workspace && ! $form_id ) {
			return;
		}
		$args = array(
			'post_type'           => $post_type,
			'author'              => $profile_id,
			'post_status'         => $owner ? array( 'publish', 'pending', 'draft', 'future' ) : 'publish',
			'posts_per_page'      => $settings['per_page'],
			'paged'               => $page,
			's'                   => $search,
			'ignore_sticky_posts' => true,
		);
		if ( $workspace ) {
			$args['meta_query'] = array( array( 'key' => '_wpuf_form_id', 'value' => $form_id, 'compare' => '=' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}
		$allowed   = $settings['taxonomies'];
		$tax_query = array( 'relation' => 'AND' );
		foreach ( $allowed as $allowed_taxonomy ) {
			$selected_term = isset( $filters[ $allowed_taxonomy ] ) && is_scalar( $filters[ $allowed_taxonomy ] ) ? absint( $filters[ $allowed_taxonomy ] ) : 0;
			if ( $selected_term && term_exists( $selected_term, $allowed_taxonomy ) ) {
				$tax_query[] = array( 'taxonomy' => $allowed_taxonomy, 'field' => 'term_id', 'terms' => array( $selected_term ) );
			}
		}
		// Taxonomy badges in custom cards may activate a filter for an enabled taxonomy.
		if ( $taxonomy && $term && in_array( $taxonomy, $allowed, true ) && term_exists( $term, $taxonomy ) ) {
			$tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => array( $term ) );
		}
		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}
		$args     = apply_filters( 'umwpuf_results_query_args', $args, $tab_id, $profile_id );
		$query    = new \WP_Query( $args );
		$filtered = '' !== $search || count( $tax_query ) > 1;

		if ( ! $query->have_posts() ) {
			echo '<div class="umwpuf-empty"><span aria-hidden="true">' . ( $filtered ? '&#8981;' : '&#10022;' ) . '</span>';
			if ( $filtered ) {
				echo '<h3>' . esc_html__( 'No matching entries', 'um-wp-user-frontend' ) . '</h3><p>' . esc_html__( 'Try a different search or clear the filters.', 'um-wp-user-frontend' ) . '</p>';
			} elseif ( $owner && $workspace ) {
				echo '<h3>' . esc_html__( 'No entries yet', 'um-wp-user-frontend' ) . '</h3><p>' . esc_html__( 'Your submissions will appear here.', 'um-wp-user-frontend' ) . '</p>';
				echo '<a class="umwpuf-add-button" href="' . esc_url( add_query_arg( 'umwpuf_view', 'add', $this->tab_url( $tab_id, $profile_id ) ) ) . '">' . esc_html__( 'Create your first entry', 'um-wp-user-frontend' ) . '</a>';
			} else {
				echo '<h3>' . esc_html__( 'Nothing here yet', 'um-wp-user-frontend' ) . '</h3>';
			}
			echo '</div>';
			return;
		}

		$from = ( $page - 1 ) * $settings['per_page'] + 1;
		$to   = min( $query->found_posts, $from + $query->post_count - 1 );
		/* translators: 1: first item number, 2: last item number, 3: total items */
		echo '<div class="umwpuf-result-count">' . esc_html( sprintf( __( 'Showing %1$s–%2$s of %3$s', 'um-wp-user-frontend' ), number_format_i18n( $from ), number_format_i18n( $to ), number_format_i18n( $query->found_posts ) ) ) . '</div>';

		$layout     = $settings['layout'];
		$grid_style = '';
		if ( 'custom' === $layout ) {
			$config     = Loop_Builder::config( $tab_id );
			$grid_style = Loop_Builder::grid_style( $config['card'] );
		}
		echo '<div class="umwpuf-posts umwpuf-layout-' . esc_attr( $layout ) . '"' . ( $grid_style ? ' style="' . esc_attr( $grid_style ) . '"' : '' ) . '>';
		while ( $query->have_posts() ) {
			$query->the_post();
			$id       = get_the_ID();
			$edit_url = $workspace && $owner ? $this->edit_url( $tab_id, $profile_id, $id ) : '';
			if ( 'custom' === $layout ) {
				Loop_Builder::render( $tab_id, $edit_url );
				continue;
			}
			$title = get_the_title() ?: __( '(Untitled)', 'um-wp-user-frontend' );
			echo '<article class="umwpuf-post umwpuf-entry-card">';
			if ( $edit_url ) {
				/* translators: %s: entry title */
				echo '<a class="umwpuf-card-edit" href="' . esc_url( $edit_url ) . '" aria-label="' . esc_attr( sprintf( __( 'Edit “%s”', 'um-wp-user-frontend' ), wp_strip_all_tags( $title ) ) ) . '" title="' . esc_attr__( 'Edit entry', 'um-wp-user-frontend' ) . '"><span aria-hidden="true">&#9998;</span></a>';
			}
			if ( 'table' !== $layout ) {
				echo '<a class="umwpuf-thumb" href="' . esc_url( get_permalink() ) . '" tabindex="-1" aria-hidden="true">';
				if ( has_post_thumbnail() ) {
					echo get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy' ) );
				} else {
					echo '<span class="umwpuf-thumb-placeholder" aria-hidden="true">&#10022;</span>';
				}
				echo '</a>';
			}
			echo '<div class="umwpuf-post-body"><div class="umwpuf-post-meta"><span>' . esc_html( get_the_date() ) . '</span>';
			if ( $owner ) {
				echo $this->status_badge( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in status_badge().
			}
			echo '</div><h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( $title ) . '</a></h3>';
			if ( 'table' !== $layout ) {
				echo '<p class="umwpuf-excerpt">' . esc_html( wp_trim_words( get_the_excerpt(), 18 ) ) . '</p>';
			}
			echo '<div class="umwpuf-post-actions"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( 'publish' === get_post_status() ? __( 'View post', 'um-wp-user-frontend' ) : __( 'Preview', 'um-wp-user-frontend' ) ) . '</a>';
			// Only show a native edit link if WordPress itself authorizes editing.
			if ( $owner && ! $workspace && current_user_can( 'edit_post', $id ) ) {
				$native = get_edit_post_link( $id );
				if ( $native ) {
					echo '<a href="' . esc_url( $native ) . '">' . esc_html__( 'Edit', 'um-wp-user-frontend' ) . '</a>';
				}
			}
			echo '</div></div></article>';
		}
		wp_reset_postdata();
		echo '</div>';
		$this->pagination( $page, (int) $query->max_num_pages );
	}

	/** Prev / numbered (with ellipses) / Next. Buttons are wired up by dashboard.js. */
	private function pagination( $page, $total ) {
		if ( $total < 2 ) {
			return;
		}
		$pages = array_unique( array_merge( array( 1, $total ), range( max( 1, $page - 1 ), min( $total, $page + 1 ) ) ) );
		sort( $pages );
		echo '<nav class="umwpuf-pagination" aria-label="' . esc_attr__( 'Entries pages', 'um-wp-user-frontend' ) . '">';
		echo '<button type="button" class="umwpuf-page-prev" data-page="' . esc_attr( max( 1, $page - 1 ) ) . '"' . disabled( $page <= 1, true, false ) . '><span aria-hidden="true">&lsaquo;</span><span class="screen-reader-text">' . esc_html__( 'Previous page', 'um-wp-user-frontend' ) . '</span></button>';
		$previous = 0;
		foreach ( $pages as $i ) {
			if ( $previous && $i - $previous > 1 ) {
				echo '<span class="umwpuf-page-gap" aria-hidden="true">&hellip;</span>';
			}
			/* translators: %s: page number */
			echo '<button type="button" data-page="' . esc_attr( $i ) . '" aria-label="' . esc_attr( sprintf( __( 'Page %s', 'um-wp-user-frontend' ), $i ) ) . '"' . ( $i === $page ? ' aria-current="page"' : '' ) . '>' . esc_html( number_format_i18n( $i ) ) . '</button>';
			$previous = $i;
		}
		echo '<button type="button" class="umwpuf-page-next" data-page="' . esc_attr( min( $total, $page + 1 ) ) . '"' . disabled( $page >= $total, true, false ) . '><span aria-hidden="true">&rsaquo;</span><span class="screen-reader-text">' . esc_html__( 'Next page', 'um-wp-user-frontend' ) . '</span></button>';
		echo '</nav>';
	}
}
