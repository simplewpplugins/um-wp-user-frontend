<?php
namespace umwpuf;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Render a WPUF edit form inside the Ultimate Member workspace without
 * letting WPUF's shortcode leave an output buffer open around the page.
 */
class Edit_Form {
    public function __construct() {
        add_shortcode( 'umwpuf_edit_form', array( $this, 'shortcode' ) );
    }

    public function shortcode( $atts = array() ) {
        $atts = shortcode_atts( array( 'tab_id' => 0, 'post_id' => 0 ), $atts, 'umwpuf_edit_form' );
        $tab_id = absint( $atts['tab_id'] );
        $post_id = absint( $atts['post_id'] );
        $post = $post_id ? get_post( $post_id ) : null;
        $tab = $tab_id ? get_post( $tab_id ) : null;
        $form_id = $tab_id ? absint( get_post_meta( $tab_id, 'umwpuf_post_form', true ) ) : 0;

        if ( ! is_user_logged_in() || ! $tab || 'umwpuf' !== $tab->post_type || 'publish' !== $tab->post_status ||
            'workspace' !== get_post_meta( $tab_id, '_umwpuf_mode', true ) || ! $post ||
            (int) $post->post_author !== get_current_user_id() || ! current_user_can( 'edit_post', $post_id ) ||
            ! $form_id || 'wpuf_forms' !== get_post_type( $form_id ) || 'publish' !== get_post_status( $form_id ) ||
            absint( get_post_meta( $post_id, '_wpuf_form_id', true ) ) !== $form_id ||
            ! function_exists( 'wpuf_get_form_settings' ) ) {
            return '<p class="umwpuf-empty">' . esc_html__( 'You cannot edit this entry.', 'um-wp-user-frontend' ) . '</p>';
        }
        $settings = wpuf_get_form_settings( $form_id );
        $post_type = is_array( $settings ) && ! empty( $settings['post_type'] ) ? sanitize_key( $settings['post_type'] ) : 'post';
        if ( $post->post_type !== $post_type ) {
            return '<p class="umwpuf-empty">' . esc_html__( 'You cannot edit this entry.', 'um-wp-user-frontend' ) . '</p>';
        }
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        $requested_post_id = isset( $_GET['pid'] ) ? absint( $_GET['pid'] ) : 0;
        if ( $requested_post_id !== $post_id || ! wp_verify_nonce( $nonce, 'wpuf_edit' ) ) {
            return '<p class="umwpuf-empty">' . esc_html__( 'Please reopen this entry to edit it.', 'um-wp-user-frontend' ) . '</p>';
        }

        // WPUF 4.3.15's edit_post_shortcode() calls ob_start() twice but
        // closes only one buffer. Without isolation, the rest of the profile
        // template can be captured and emitted as duplicate page markup.
        // Keep its native permission checks, form fields and save handlers.
        $initial_level = ob_get_level();
        ob_start();
        $fragment = '';
        try {
            $result = do_shortcode( '[wpuf_edit]' );
            $buffered = '';
            while ( ob_get_level() > $initial_level ) {
                $buffered = ob_get_clean() . $buffered;
            }
            $fragment = $buffered . ( is_string( $result ) ? $result : '' );
        } finally {
            while ( ob_get_level() > $initial_level ) {
                ob_end_clean();
            }
        }
        return $fragment;
    }
}
