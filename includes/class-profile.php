<?php 
namespace umwpuf;

if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists('umwpuf\Profile')){

	class Profile {

		var $tabs = [];

		function __construct(){
			add_action( 'um_core_loaded', array( $this,'add_posting_tabs' ) );
		}

		

		function add_posting_tabs(){

			$tabs = UMWPUF()->helper()->get_posting_tabs();

			if( $tabs && is_array( $tabs ) ){

				foreach( $tabs as $tab ){

					$key = $tab['slug'];
					$id = $tab['id'];

					if( ! UMWPUF()->helper()->can_have_tab( um_profile_id(), $tab['id'] ) ){
						continue;
					}
	
					if( um_profile_id() != get_current_user_id() ){
						continue;
					}

					add_filter( 'um_user_profile_tabs', function( $tabs ) use ( $tab, $key ) {

						$tabs[ $key ] = array(
							'name'   => esc_html( $tab['title'] ),
							'icon'   => esc_html( $tab['icon'] ),
							'is_custom_added' => true
						);
			
						UM()->options()->options[ 'profile_tab_' . esc_html( $key ) ] = true;
						$this->tabs = $tabs;
						return $tabs;

					}, 10, 1 );

					add_action( 'um_profile_content_'.$key,function(  $args ) use ( $tab,$key ){

						if( isset( $tab['content'] ) && trim($tab['content']) != ''){
							echo  '<div class="umwpuf-tab-description">'.esc_html( $tab['content']).'</div>';
						}

						if( isset( $tab['post_form'] ) && trim($tab['post_form']) != ''){
							$form = get_post(absint($tab['post_form']));
							if( $form && $form->post_type == 'wpuf_forms'){
								echo '<div class="umwpuf-tab-post_form">'.do_shortcode('[wpuf_form id="'.esc_html($form->ID).'"]').'</div>';
							}
						}
						
					} );

				}
			}
		}


	}

}