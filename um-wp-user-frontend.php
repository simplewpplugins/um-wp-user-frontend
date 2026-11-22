<?php
/*
 Plugin Name: WP User Frontend Integration for Ultimate Member
 Plugin URI: https://wordpress.org/plugins/um-wp-user-frontend
 Description: Add WP User Frontend post submission forms to Ultimate Member profile tabs so members can submit posts directly from their profiles.
 Version: 1.6.0
 Requires at least: 6.5
 Requires PHP: 7.4
 Requires Plugins: ultimate-member, wp-user-frontend
 Author: Simple Plugins
 Author URI: https://github.com/simplewpplugins
 License: GPL v2 or later
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: um-wp-user-frontend
 Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'UMWPUF_VERSION' ) ) {
	define( 'UMWPUF_VERSION', '1.6.0' );
}

if( ! defined( 'UMWPUF_PLUGIN' )){
	define( 'UMWPUF_PLUGIN', __FILE__  );
}

if( ! defined( 'UMWPUF_URL' )){
	define( 'UMWPUF_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}


if( ! defined( 'UMWPUF_PATH' )){
	define( 'UMWPUF_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

/**
 * Block manual activation when either required plugin is unavailable.
 * WordPress 6.5+ also enforces the Requires Plugins header above.
 */
register_activation_hook( __FILE__, 'umwpuf_validate_activation_dependencies' );
function umwpuf_dependencies_ready() {
    return function_exists( 'UM' ) && defined( 'WPUF_VERSION' );
}
function umwpuf_validate_activation_dependencies() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $missing = array();
    foreach ( array( 'ultimate-member/ultimate-member.php' => 'Ultimate Member', 'wp-user-frontend/wpuf.php' => 'WP User Frontend' ) as $file => $name ) {
        if ( ! is_plugin_active( $file ) && ( ! function_exists( 'is_plugin_active_for_network' ) || ! is_plugin_active_for_network( $file ) ) ) {
            $missing[] = $name;
        }
    }
    if ( $missing ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die(
            esc_html( sprintf( __( 'Cannot activate this integration. Activate these required plugins first: %s.', 'um-wp-user-frontend' ), implode( ', ', $missing ) ) ),
            esc_html__( 'Missing required plugins', 'um-wp-user-frontend' ),
            array( 'back_link' => true )
        );
    }
}
add_action( 'plugins_loaded', 'umwpuf_check_dependencies', 30 );
function umwpuf_check_dependencies() {
    if ( umwpuf_dependencies_ready() ) {
        require_once UMWPUF_PATH . 'includes/init.php';
        return;
    }
    if ( is_admin() ) {
        add_action( 'admin_notices', 'umwpuf_dependency_notice' );
        add_action( 'network_admin_notices', 'umwpuf_dependency_notice' );
    }
}
function umwpuf_dependency_notice() {
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }
    echo '<div class="notice notice-error"><p>' . esc_html__( 'WP User Frontend Integration for Ultimate Member is inactive because Ultimate Member and WP User Frontend must both be active.', 'um-wp-user-frontend' ) . '</p></div>';
}
