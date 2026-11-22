<?php
/**
 * Tab settings metabox.
 *
 * @var int $tab_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$umwpuf_helper = UMWPUF()->helper();
$form_options  = array( '' => __( '— Select a form —', 'um-wp-user-frontend' ) );
$forms         = get_posts( array( 'post_type' => 'wpuf_forms', 'posts_per_page' => -1, 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'orderby' => 'title', 'order' => 'ASC' ) );

// Per-form data used by the taxonomy picker and the layout designer.
$umwpuf_form_data = array();
foreach ( $forms as $umwpuf_form ) {
	$umwpuf_type = $umwpuf_helper->form_post_type( $umwpuf_form->ID );
	$umwpuf_obj  = $umwpuf_type ? get_post_type_object( $umwpuf_type ) : null;
	$form_options[ $umwpuf_form->ID ] = $umwpuf_form->post_title . ( $umwpuf_obj ? ' — ' . $umwpuf_obj->labels->singular_name : '' ) . ( 'publish' !== $umwpuf_form->post_status ? ' ' . __( '(not published)', 'um-wp-user-frontend' ) : '' );
	$umwpuf_fields = array();
	foreach ( $umwpuf_helper->form_meta_fields( $umwpuf_form->ID ) as $umwpuf_key => $umwpuf_field ) {
		$umwpuf_fields[] = array( 'key' => $umwpuf_key, 'label' => $umwpuf_field['label'], 'type' => $umwpuf_field['type'] );
	}
	$umwpuf_form_data[ $umwpuf_form->ID ] = array(
		'postType'   => $umwpuf_type,
		'taxonomies' => $umwpuf_helper->filterable_taxonomies( $umwpuf_type ),
		'fields'     => $umwpuf_fields,
	);
}

$umwpuf_saved_filters = get_post_meta( $tab_id, '_umwpuf_taxonomy_filters', true );
if ( ! is_array( $umwpuf_saved_filters ) ) {
	$umwpuf_saved_filters = '1' === get_post_meta( $tab_id, '_umwpuf_category_filter', true ) ? array( 'category' ) : array();
}

$all_roles = UM()->roles()->get_roles();
if ( ! is_array( $all_roles ) ) {
	$all_roles = array();
}
// Include native WordPress roles such as Author, Editor and Subscriber.
foreach ( wp_roles()->roles as $role_slug => $role_data ) {
	if ( ! isset( $all_roles[ $role_slug ] ) ) {
		$all_roles[ $role_slug ] = translate_user_role( $role_data['name'] );
	}
}

$icons_options = array();
foreach ( (array) UM()->fonticons()->all as $icon ) {
	$icons_options[ $icon ] = $icon;
}
$selected_icon = get_post_meta( $tab_id, 'umwpuf_icon', true ) ?: 'um-icon-android-list';
$icon_preview  = '<span id="umwpuf-icon-preview" style="font-size:30px;"><i class="' . esc_attr( $selected_icon ) . '"></i></span>';

$saved_mode = get_post_meta( $tab_id, '_umwpuf_mode', true );
// New tabs default to the recommended workspace; existing tabs without a mode stay legacy "submit".
$current_mode = $saved_mode ?: ( 'auto-draft' === get_post_status( $tab_id ) ? 'workspace' : 'submit' );

wp_nonce_field( 'umwpuf_save_tab_' . $tab_id, 'umwpuf_settings_nonce' );

UM()->admin_forms(
	array(
		'class'     => 'umwpuf-post-tab',
		'prefix_id' => 'umwpuf',
		'fields'    => array(
			array(
				'id'          => '_mode',
				'type'        => 'select',
				'label'       => __( 'Tab purpose', 'um-wp-user-frontend' ),
				'options'     => \umwpuf\admin\Admin::mode_labels(),
				'value'       => $current_mode,
				'description' => __( 'Workspace tabs list the member’s entries for the selected form and let the owner add and edit them in place.', 'um-wp-user-frontend' ),
			),
			array(
				'id'          => '_post_form',
				'type'        => 'select',
				'options'     => $form_options,
				'label'       => __( 'WP User Frontend form', 'um-wp-user-frontend' ),
				'value'       => get_post_meta( $tab_id, 'umwpuf_post_form', true ),
				'description' => __( 'Workspace entries use this form’s post type, including custom post types.', 'um-wp-user-frontend' ),
			),
			array(
				'id'      => '_layout',
				'type'    => 'select',
				'label'   => __( 'Entries layout', 'um-wp-user-frontend' ),
				'options' => \umwpuf\admin\Admin::layout_labels(),
				'value'   => get_post_meta( $tab_id, '_umwpuf_layout', true ) ?: 'grid',
			),
			array(
				'id'    => '_per_page',
				'type'  => 'number',
				'label' => __( 'Entries per page (1–50)', 'um-wp-user-frontend' ),
				'value' => get_post_meta( $tab_id, '_umwpuf_per_page', true ) ?: 9,
				'size'  => 'small',
			),
			array(
				'id'    => '_search',
				'type'  => 'checkbox',
				'label' => __( 'Enable live search', 'um-wp-user-frontend' ),
				'value' => get_post_meta( $tab_id, '_umwpuf_search', true ) ?: '0',
			),
			array(
				'id'          => '_tabicon',
				'type'        => 'select',
				'label'       => __( 'Tab icon', 'um-wp-user-frontend' ),
				'options'     => $icons_options,
				'value'       => $selected_icon,
				'description' => $icon_preview,
			),
			array(
				'id'          => '_description',
				'type'        => 'textarea',
				'label'       => __( 'Description', 'um-wp-user-frontend' ),
				'value'       => get_post_meta( $tab_id, 'umwpuf_description', true ),
				'description' => __( 'Shown above the entries in workspace mode, or above the form in legacy mode.', 'um-wp-user-frontend' ),
				'class'       => 'widefat',
				'args'        => array( 'textarea_rows' => 4 ),
			),
			array(
				'id'          => '_can_have_this_tab_roles',
				'type'        => 'select',
				'options'     => $all_roles,
				'label'       => __( 'Show on profiles with these roles', 'um-wp-user-frontend' ),
				'description' => __( 'Roles of the profile owner (WordPress or Ultimate Member). Leave empty for every role. Adding and editing are always limited to the profile owner.', 'um-wp-user-frontend' ),
				'multi'       => true,
				'value'       => get_post_meta( $tab_id, '_can_have_this_tab_roles', true ),
			),
		),
	)
)->render_form();
?>
<div class="umwpuf-taxonomy-admin" data-umwpuf-show="manage public workspace">
	<h4><?php esc_html_e( 'Taxonomy filters', 'um-wp-user-frontend' ); ?></h4>
	<p class="description"><?php esc_html_e( 'Visitors get a dropdown for each taxonomy you tick. Choices depend on the selected form’s post type.', 'um-wp-user-frontend' ); ?></p>
	<div id="umwpuf-taxonomy-options"></div>
	<p id="umwpuf-taxonomy-empty" class="description" hidden><?php esc_html_e( 'This post type has no public taxonomies to filter by.', 'um-wp-user-frontend' ); ?></p>
</div>
<script type="application/json" id="umwpuf-admin-data"><?php echo wp_json_encode( array( 'forms' => $umwpuf_form_data, 'savedFilters' => array_values( $umwpuf_saved_filters ), 'postTaxonomies' => $umwpuf_helper->filterable_taxonomies( 'post' ) ) ); ?></script>
