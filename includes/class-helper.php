<?php 
namespace umwpuf;

if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists('umwpuf\Helper')){

	class Helper {

        public $posting_forms = [];

        function __construct(){


			add_action( 'admin_enqueue_scripts', array( $this, 'load_font_icons' ) );

            add_action( 'plugins_loaded', array( $this, 'plugin_i18n' ) );

		}



        function plugin_i18n(){
            load_plugin_textdomain('um-wp-user-frontend', false, dirname( plugin_basename( UMWPUF_PLUGIN ) ).'/languages/');
        }


        function load_font_icons(){

            global $current_screen;
			if ( $current_screen->id == 'umwpuf' ) {

				wp_register_style( 'um_fonticons_ii', um_url . 'assets/css/um-fonticons-ii.css', array(), ultimatemember_version );
                wp_enqueue_style( 'um_fonticons_ii' );
    
                wp_register_style( 'um_fonticons_fa', um_url . 'assets/css/um-fonticons-fa.css', array(), ultimatemember_version );
                wp_enqueue_style( 'um_fonticons_fa' );

			}

		}


		function can_have_tab( $profile_id, $tab_id ){
            
            $tab = get_post( $tab_id );
            if( $tab && 'umwpuf' == $tab->post_type ){
                $tab_roles = get_post_meta( $tab->ID, '_can_have_this_tab_roles', true );

                if( ! $tab_roles || empty( $tab_roles )){
                    return true;
                }
                um_fetch_user( $profile_id );
                $user_role = UM()->user()->get_role();
                if( in_array( $user_role, $tab_roles ) ){
                    return true;
                }

                return false;
                
            }

            
            return false;
        }

        function can_view_tab( $tab_id, $user_id = null ){

            if( um_profile_id() == get_current_user_id() ){
                return true;
            }

            return false;
        }





        function get_posting_tabs(){

            if( empty( $this->posting_forms  )){

                $tabs = get_posts([
                    'post_type' => 'umwpuf',
                    'posts_per_page' => -1
                ]);

                if( $tabs ){
                    foreach( $tabs as $tab ){
                        $this->posting_forms[] = array(
                            'id' => $tab->ID,
                            'slug' => $tab->post_name,
                            'title' => $tab->post_title,
                            'icon' => get_post_meta( $tab->ID, 'umwpuf_icon',true),
                            'post_form' => get_post_meta( $tab->ID, 'umwpuf_post_form', true ),
                            'content' => get_post_meta( $tab->ID, 'umwpuf_description',true),
                        );
                    }
                }

            }

            return $this->posting_forms;

        }



	}

}