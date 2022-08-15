<?php 
if ( ! defined( 'ABSPATH' ) ) exit; 
$form_options = [];
$forms = get_posts( [ 'post_type' => 'wpuf_forms','posts_per_page' => -1 ] ); 
if( $forms ):
    foreach( $forms as $form ):
        $form_options[$form->ID] = esc_html( $form->post_title );
    endforeach;
endif;
$icons_options = [];
$selected_form = get_post_meta( $tab_id, 'umwpuf_post_form', true );
$all_roles = UM()->roles()->get_roles();
foreach( UM()->fonticons()->all as $icon ) {
    $icons_options[$icon] = $icon;
}

$selected_icon = get_post_meta( $tab_id, 'umwpuf_icon', true );
if( ! $selected_icon ){
    $selected_icon = 'um-icon-android-list';
}
if( $selected_icon ){
    $icon_preview = '<span id="umwpuf-icon-preview" style="font-size:30px;"><i class="'.esc_html( $selected_icon ).'"></i></span>';
}

UM()->admin_forms( [
	'class'     => 'umwpuf-post-tab',
	'prefix_id' => 'umwpuf',
	'fields'    => [
        [
			'id'    => '_tabicon',
			'type'  => 'select',
			'label' => __( 'Tab Icon', 'um-wp-user-frontend' ),
            'options'   => $icons_options,
            'value' => esc_html( $selected_icon ),
            'description' => $icon_preview,
		],
        [
			'id'    => '_description',
			'type'  => 'textarea',
			'label' => __( 'Description', 'um-wp-user-frontend' ),
			'value' => get_post_meta( $tab_id, 'umwpuf_description', true ),
            'description'   => __( 'Description is displayed just before post form.', 'um-wp-user-frontend' ),
            'class'      => 'widefat ',
            'args'      => array(
                'textarea_rows' => 5
            )
		],
        [
			'id'        => '_post_form',
			'type'      => 'select',
			'options'   => $form_options,
			'label'     => __( 'Post Form', 'um-wp-user-frontend' ),
			'value'     => get_post_meta( $tab_id, 'umwpuf_post_form', true ),
		],
        [
			'id'        => '_can_have_this_tab_roles',
			'type'      => 'select',
			'options'   => $all_roles,
			'label'     => __( 'Show on these roles profiles', 'um-wp-user-frontend' ),
			'desctiption'   => __( 'You could select the roles which have the current profile tab at their form. If empty, profile tab is visible for all roles at their forms.', 'um-wp-user-frontend' ),
			'multi'     => true,
			'value'     => get_post_meta( $tab_id, '_can_have_this_tab_roles', true ),
		]
	],
] )->render_form();
?>
<script>
    (function($){
        $(document).on('change','#umwpuf__tabicon',function(e){
            var select = $(this);
            var preview_div = $('#umwpuf-icon-preview');
            var selected_option_value = select.find('option:selected').val();
            preview_div.find('i').attr('class','');
            preview_div.find('i').addClass(selected_option_value);
        });
    })(jQuery);
</script>