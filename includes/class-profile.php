<?php
namespace umwpuf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers integration tabs after Ultimate Member has filtered its own enabled tabs.
 * This avoids writing to private Ultimate Member options and preserves legacy URLs.
 */
class Profile {
	public function __construct() {
		add_filter( 'um_user_profile_tabs', array( $this, 'register_tabs' ), 30 );
		add_action( 'init', array( $this, 'register_content_hooks' ), 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_early' ) );
	}

	private function is_owner( $profile_id ) {
		return is_user_logged_in() && (int) $profile_id === get_current_user_id();
	}

	private function is_public_mode( $tab_id ) {
		return in_array( UMWPUF()->helper()->tab_mode( $tab_id ), array( 'public', 'workspace' ), true );
	}

	private function allowed( $tab ) {
		$profile_id = function_exists( 'um_profile_id' ) ? (int) um_profile_id() : 0;
		if ( ! $profile_id ) {
			return false;
		}
		// Existing tabs had no mode and were owner-only submission tabs.
		return ( $this->is_public_mode( $tab['id'] ) || $this->is_owner( $profile_id ) )
			&& UMWPUF()->helper()->can_have_tab( $profile_id, $tab['id'] );
	}

	/**
	 * Print styles in <head> on profile pages instead of late in the footer,
	 * which avoids a flash of unstyled cards. render() still enqueues as a fallback.
	 */
	public function enqueue_early() {
		if ( function_exists( 'um_is_core_page' ) && um_is_core_page( 'user' ) ) {
			UMWPUF()->dashboard()->enqueue_styles();
		}
	}

	public function register_tabs( $tabs ) {
		foreach ( UMWPUF()->helper()->get_posting_tabs() as $tab ) {
			if ( ! $this->allowed( $tab ) ) {
				continue;
			}
			$key          = UMWPUF()->helper()->tab_key( $tab );
			$tabs[ $key ] = array(
				'name'            => esc_html( $tab['title'] ),
				'icon'            => ! empty( $tab['icon'] ) ? sanitize_html_class( $tab['icon'] ) : 'um-icon-android-list',
				'is_custom_added' => true,
				'default_privacy' => $this->is_public_mode( $tab['id'] ) ? 0 : 3,
			);
		}
		return $tabs;
	}

	public function register_content_hooks() {
		foreach ( UMWPUF()->helper()->get_posting_tabs() as $tab ) {
			add_action(
				'um_profile_content_' . UMWPUF()->helper()->tab_key( $tab ),
				function () use ( $tab ) {
					$this->render_tab( $tab );
				}
			);
		}
	}

	private function render_tab( $tab ) {
		if ( ! $this->allowed( $tab ) ) {
			return;
		}
		$mode = UMWPUF()->helper()->tab_mode( $tab['id'] );
		// Workspace prints the description itself, below its sub-navigation.
		if ( ! empty( $tab['content'] ) && 'workspace' !== $mode ) {
			echo '<div class="umwpuf-tab-description">' . wp_kses_post( wpautop( $tab['content'] ) ) . '</div>';
		}
		if ( in_array( $mode, array( 'manage', 'public', 'workspace' ), true ) ) {
			UMWPUF()->dashboard()->render( $tab );
			return;
		}
		$form_id = absint( $tab['post_form'] );
		if ( $form_id && 'wpuf_forms' === get_post_type( $form_id ) && 'publish' === get_post_status( $form_id ) ) {
			echo '<div class="umwpuf-tab-post-form">' . do_shortcode( '[wpuf_form id="' . $form_id . '"]' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( current_user_can( 'manage_options' ) ) {
			echo '<p class="umwpuf-empty">' . esc_html__( 'This tab has no published WP User Frontend form selected.', 'um-wp-user-frontend' ) . '</p>';
		}
	}
}
