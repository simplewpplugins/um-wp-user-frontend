<?php
/*
 Plugin Name: Ultimate member - WP User Frontend
 Plugin URI: https://wordpress.org/plugins/um-wp-user-frontend
 Description: This plugin integrates WP User Frontend to Ultimate member allowing you to add post forms to Ultimate member user profile.
 Version: 1.1
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


if( is_plugin_active('wp-user-frontend/wpuf.php') &&  is_plugin_active('ultimate-member/ultimate-member.php')  ){

	include UMWPUF_PATH.'includes/init.php';

}else{

	add_action( 'admin_notices', function(){

		echo '<div style="background:red;color:#fff;" class="error"><p>' .  __( 'The <strong>Ultimate member - WP User Frontend</strong> plugin requires the <a style="color:#fff;" href="https://wordpress.org/plugins/ultimate-member"><strong>Ultimate Member</strong></a> and <a style="color:#fff;" href="https://wordpress.org/plugins/wp-user-frontend/"><strong>WP User Frontend</strong></a> plugin to be activated to work properly.', 'um-wp-user-frontend' ) . '</p></div>';

	} );

}


/*
apply_filters( 'wpuf_front_post_edit_link', $url );
$form_fields[] = apply_filters( 'wpuf-get-form-fields', $field );
add_action( 'save_post', [ WPUF_Admin_Posting::init(), 'save_meta' ], 1 );
apply_filters( 'wpuf_register_url', $url, $page_id 
return apply_filters( 'wpuf_login_url', $url, $page_id );
WPUF dashboard [wpuf_account]
wpuf_subscription
Change role after subscription purchase
display subscription options: [wpuf_sub_pack]
do_action( 'wpuf_payment_received', $data, $recurring );
apply_filters( 'wpuf_new_subscription', $user_meta, $this->user->id, $pack_id, $recurring );

$key = '_wpuf_subscription_pack'
update_user_meta( $this->user->id, $key, $user_meta );
wpuf()->subscription->insert_free_pack_subscribers( $pack_id, $this->user );
delete_user_meta( $this->user->id, '_wpuf_subscription_pack' );
*/