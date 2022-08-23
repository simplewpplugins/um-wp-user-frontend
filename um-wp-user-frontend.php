<?php
/*
 Plugin Name: WP User Frontend Integration for Ultimate Member
 Plugin URI: https://wordpress.org/plugins/um-wp-user-frontend
 Description: Integrates WP User Frontend to Ultimate member allowing you to add post forms to Ultimate member user profile.
 Version: 1.3
 Requires at least: 3.0
 Requires PHP: 7.0
 Author: Simple Plugins
 Author URI: https://aswin.com.np
 License: GPL v2 or later
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: um-wp-user-frontend
 Domain Path: /languages
 */

if( ! defined( 'UMWPUF_PLUGIN' )){
	define( 'UMWPUF_PLUGIN', __FILE__  );
}

if( ! defined( 'UMWPUF_URL' )){
	define( 'UMWPUF_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}


if( ! defined( 'UMWPUF_PATH' )){
	define( 'UMWPUF_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

add_action( 'plugins_loaded', 'umwpuf_check_dependencies', -20 );
function umwpuf_check_dependencies(){

	if( is_plugin_active('wp-user-frontend/wpuf.php') &&  is_plugin_active('ultimate-member/ultimate-member.php')  ){

	include UMWPUF_PATH.'includes/init.php';

}else{

	add_action( 'admin_notices', function(){

		echo '<div style="background:red;color:#fff;" class="error"><p>' .  __( 'The <strong>Ultimate member - WP User Frontend</strong> plugin requires the <a style="color:#fff;" href="https://wordpress.org/plugins/ultimate-member"><strong>Ultimate Member</strong></a> and <a style="color:#fff;" href="https://wordpress.org/plugins/wp-user-frontend/"><strong>WP User Frontend</strong></a> plugin to be activated to work properly.', 'um-wp-user-frontend' ) . '</p></div>';

	} );

}

}