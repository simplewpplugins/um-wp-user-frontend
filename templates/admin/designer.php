<?php
/**
 * Custom layout designer metabox. The UI is built by assets/loop-builder.js.
 *
 * @var int $tab_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$umwpuf_config   = \umwpuf\Loop_Builder::config( $tab_id );
$umwpuf_defaults = \umwpuf\Loop_Builder::defaults();
// Generated from the t('…') calls in assets/loop-builder.js.
$umwpuf_strings = array(
	'Title' => __( 'Title', 'um-wp-user-frontend' ),
	'Excerpt' => __( 'Excerpt', 'um-wp-user-frontend' ),
	'Custom field' => __( 'Custom field', 'um-wp-user-frontend' ),
	'Taxonomy terms' => __( 'Taxonomy terms', 'um-wp-user-frontend' ),
	'Static text' => __( 'Static text', 'um-wp-user-frontend' ),
	'Featured image' => __( 'Featured image', 'um-wp-user-frontend' ),
	'Author avatar' => __( 'Author avatar', 'um-wp-user-frontend' ),
	'Publish date' => __( 'Publish date', 'um-wp-user-frontend' ),
	'Last updated' => __( 'Last updated', 'um-wp-user-frontend' ),
	'Author name' => __( 'Author name', 'um-wp-user-frontend' ),
	'Status (owner only)' => __( 'Status (owner only)', 'um-wp-user-frontend' ),
	'Comment count' => __( 'Comment count', 'um-wp-user-frontend' ),
	'Button' => __( 'Button', 'um-wp-user-frontend' ),
	'Divider' => __( 'Divider', 'um-wp-user-frontend' ),
	'Spacer' => __( 'Spacer', 'um-wp-user-frontend' ),
	'Content' => __( 'Content', 'um-wp-user-frontend' ),
	'Media' => __( 'Media', 'um-wp-user-frontend' ),
	'Details' => __( 'Details', 'um-wp-user-frontend' ),
	'Layout & actions' => __( 'Layout & actions', 'um-wp-user-frontend' ),
	'Desktop' => __( 'Desktop', 'um-wp-user-frontend' ),
	'Tablet' => __( 'Tablet', 'um-wp-user-frontend' ),
	'Phone' => __( 'Phone', 'um-wp-user-frontend' ),
	'Sunny two-bedroom apartment' => __( 'Sunny two-bedroom apartment', 'um-wp-user-frontend' ),
	'Quiet cottage with a garden' => __( 'Quiet cottage with a garden', 'um-wp-user-frontend' ),
	'Modern loft in the old town' => __( 'Modern loft in the old town', 'um-wp-user-frontend' ),
	'Family house near the lake' => __( 'Family house near the lake', 'um-wp-user-frontend' ),
	'Studio with mountain views' => __( 'Studio with mountain views', 'um-wp-user-frontend' ),
	'Renovated flat by the river' => __( 'Renovated flat by the river', 'um-wp-user-frontend' ),
	'Bright corner unit with a balcony, wooden floors and an open kitchen. Close to shops, schools and the park, with easy access to public transport and plenty of natural light throughout the day.' => __( 'Bright corner unit with a balcony, wooden floors and an open kitchen. Close to shops, schools and the park, with easy access to public transport and plenty of natural light throughout the day.', 'um-wp-user-frontend' ),
	'Published' => __( 'Published', 'um-wp-user-frontend' ),
	'comments' => __( 'comments', 'um-wp-user-frontend' ),
	'View post' => __( 'View post', 'um-wp-user-frontend' ),
	'Type some text' => __( 'Type some text', 'um-wp-user-frontend' ),
	'Choose a taxonomy' => __( 'Choose a taxonomy', 'um-wp-user-frontend' ),
	'Taxonomy not available for this form' => __( 'Taxonomy not available for this form', 'um-wp-user-frontend' ),
	'Choose a custom field' => __( 'Choose a custom field', 'um-wp-user-frontend' ),
	'Sample text' => __( 'Sample text', 'um-wp-user-frontend' ),
	'Drop elements here' => __( 'Drop elements here', 'um-wp-user-frontend' ),
	'Row' => __( 'Row', 'um-wp-user-frontend' ),
	'Add a row to start designing' => __( 'Add a row to start designing', 'um-wp-user-frontend' ),
	'Element limit reached (40).' => __( 'Element limit reached (40).', 'um-wp-user-frontend' ),
	'added' => __( 'added', 'um-wp-user-frontend' ),
	'Classic card' => __( 'Classic card', 'um-wp-user-frontend' ),
	'Horizontal (image beside text)' => __( 'Horizontal (image beside text)', 'um-wp-user-frontend' ),
	'Listing with details' => __( 'Listing with details', 'um-wp-user-frontend' ),
	'Author spotlight' => __( 'Author spotlight', 'um-wp-user-frontend' ),
	'Read more' => __( 'Read more', 'um-wp-user-frontend' ),
	'Compact list rows' => __( 'Compact list rows', 'um-wp-user-frontend' ),
	'Open' => __( 'Open', 'um-wp-user-frontend' ),
	'Minimal text' => __( 'Minimal text', 'um-wp-user-frontend' ),
	'Click to add, or drag onto the card' => __( 'Click to add, or drag onto the card', 'um-wp-user-frontend' ),
	'card per row' => __( 'card per row', 'um-wp-user-frontend' ),
	'cards per row' => __( 'cards per row', 'um-wp-user-frontend' ),
	'card width' => __( 'card width', 'um-wp-user-frontend' ),
	'auto' => __( 'auto', 'um-wp-user-frontend' ),
	'Clear' => __( 'Clear', 'um-wp-user-frontend' ),
	'Use inherited colour' => __( 'Use inherited colour', 'um-wp-user-frontend' ),
	'inherit' => __( 'inherit', 'um-wp-user-frontend' ),
	'None' => __( 'None', 'um-wp-user-frontend' ),
	'Soft' => __( 'Soft', 'um-wp-user-frontend' ),
	'Medium' => __( 'Medium', 'um-wp-user-frontend' ),
	'Strong' => __( 'Strong', 'um-wp-user-frontend' ),
	'Card' => __( 'Card', 'um-wp-user-frontend' ),
	'Column' => __( 'Column', 'um-wp-user-frontend' ),
	'Selection' => __( 'Selection', 'um-wp-user-frontend' ),
	'Grid' => __( 'Grid', 'um-wp-user-frontend' ),
	'Desktop columns' => __( 'Desktop columns', 'um-wp-user-frontend' ),
	'Tablet columns' => __( 'Tablet columns', 'um-wp-user-frontend' ),
	'Phone columns' => __( 'Phone columns', 'um-wp-user-frontend' ),
	'Space between cards' => __( 'Space between cards', 'um-wp-user-frontend' ),
	'Card style' => __( 'Card style', 'um-wp-user-frontend' ),
	'Background' => __( 'Background', 'um-wp-user-frontend' ),
	'Text colour' => __( 'Text colour', 'um-wp-user-frontend' ),
	'Border colour' => __( 'Border colour', 'um-wp-user-frontend' ),
	'Border width' => __( 'Border width', 'um-wp-user-frontend' ),
	'Corner radius' => __( 'Corner radius', 'um-wp-user-frontend' ),
	'Padding' => __( 'Padding', 'um-wp-user-frontend' ),
	'Space between rows' => __( 'Space between rows', 'um-wp-user-frontend' ),
	'Shadow' => __( 'Shadow', 'um-wp-user-frontend' ),
	'Hover effect' => __( 'Hover effect', 'um-wp-user-frontend' ),
	'Lift' => __( 'Lift', 'um-wp-user-frontend' ),
	'Glow' => __( 'Glow', 'um-wp-user-frontend' ),
	'Tip: click a row or element on the card to edit it. Drag elements from the left between columns.' => __( 'Tip: click a row or element on the card to edit it. Drag elements from the left between columns.', 'um-wp-user-frontend' ),
	'Column layout' => __( 'Column layout', 'um-wp-user-frontend' ),
	'Auto (fit content)' => __( 'Auto (fit content)', 'um-wp-user-frontend' ),
	'width' => __( 'width', 'um-wp-user-frontend' ),
	'Move row up' => __( 'Move row up', 'um-wp-user-frontend' ),
	'Move row down' => __( 'Move row down', 'um-wp-user-frontend' ),
	'Duplicate row' => __( 'Duplicate row', 'um-wp-user-frontend' ),
	'Delete row' => __( 'Delete row', 'um-wp-user-frontend' ),
	'Columns' => __( 'Columns', 'um-wp-user-frontend' ),
	'Stack columns' => __( 'Stack columns', 'um-wp-user-frontend' ),
	'When the card is narrow' => __( 'When the card is narrow', 'um-wp-user-frontend' ),
	'On phones only' => __( 'On phones only', 'um-wp-user-frontend' ),
	'Never' => __( 'Never', 'um-wp-user-frontend' ),
	'“When the card is narrow” (under 420px) adapts to any grid and theme, and is recommended.' => __( '“When the card is narrow” (under 420px) adapts to any grid and theme, and is recommended.', 'um-wp-user-frontend' ),
	'Vertical alignment' => __( 'Vertical alignment', 'um-wp-user-frontend' ),
	'Top' => __( 'Top', 'um-wp-user-frontend' ),
	'Middle' => __( 'Middle', 'um-wp-user-frontend' ),
	'Bottom' => __( 'Bottom', 'um-wp-user-frontend' ),
	'Stretch' => __( 'Stretch', 'um-wp-user-frontend' ),
	'Column gap' => __( 'Column gap', 'um-wp-user-frontend' ),
	'Row style' => __( 'Row style', 'um-wp-user-frontend' ),
	'Move up' => __( 'Move up', 'um-wp-user-frontend' ),
	'Move down' => __( 'Move down', 'um-wp-user-frontend' ),
	'Move to previous column' => __( 'Move to previous column', 'um-wp-user-frontend' ),
	'Move to next column' => __( 'Move to next column', 'um-wp-user-frontend' ),
	'Duplicate' => __( 'Duplicate', 'um-wp-user-frontend' ),
	'Delete' => __( 'Delete', 'um-wp-user-frontend' ),
	'Style' => __( 'Style', 'um-wp-user-frontend' ),
	'No link' => __( 'No link', 'um-wp-user-frontend' ),
	'Link to entry' => __( 'Link to entry', 'um-wp-user-frontend' ),
	'Label (shown before the value)' => __( 'Label (shown before the value)', 'um-wp-user-frontend' ),
	'Optional' => __( 'Optional', 'um-wp-user-frontend' ),
	'HTML tag' => __( 'HTML tag', 'um-wp-user-frontend' ),
	'Length (words)' => __( 'Length (words)', 'um-wp-user-frontend' ),
	'Text' => __( 'Text', 'um-wp-user-frontend' ),
	'e.g. Featured' => __( 'e.g. Featured', 'um-wp-user-frontend' ),
	'Button text' => __( 'Button text', 'um-wp-user-frontend' ),
	'Height' => __( 'Height', 'um-wp-user-frontend' ),
	'Aspect ratio' => __( 'Aspect ratio', 'um-wp-user-frontend' ),
	'Original' => __( 'Original', 'um-wp-user-frontend' ),
	'Show a placeholder when an entry has no image' => __( 'Show a placeholder when an entry has no image', 'um-wp-user-frontend' ),
	'Size' => __( 'Size', 'um-wp-user-frontend' ),
	'Taxonomy' => __( 'Taxonomy', 'um-wp-user-frontend' ),
	'— Choose —' => __( '— Choose —', 'um-wp-user-frontend' ),
	'The selected form’s post type has no public taxonomies.' => __( 'The selected form’s post type has no public taxonomies.', 'um-wp-user-frontend' ),
	'Terms link to' => __( 'Terms link to', 'um-wp-user-frontend' ),
	'Nothing' => __( 'Nothing', 'um-wp-user-frontend' ),
	'Term archive page' => __( 'Term archive page', 'um-wp-user-frontend' ),
	'Filter this tab’s entries' => __( 'Filter this tab’s entries', 'um-wp-user-frontend' ),
	'Link' => __( 'Link', 'um-wp-user-frontend' ),
	'Field (meta key)' => __( 'Field (meta key)', 'um-wp-user-frontend' ),
	'Pick a form field' => __( 'Pick a form field', 'um-wp-user-frontend' ),
	'meta_key' => __( 'meta_key', 'um-wp-user-frontend' ),
	'Suggestions come from the selected form. Hidden and password fields are never shown.' => __( 'Suggestions come from the selected form. Hidden and password fields are never shown.', 'um-wp-user-frontend' ),
	'Select a form in Tab Settings to list its custom fields.' => __( 'Select a form in Tab Settings to list its custom fields.', 'um-wp-user-frontend' ),
	'Only the entry’s owner sees the status.' => __( 'Only the entry’s owner sees the status.', 'um-wp-user-frontend' ),
	'Colour' => __( 'Colour', 'um-wp-user-frontend' ),
	'Thickness' => __( 'Thickness', 'um-wp-user-frontend' ),
	'Vertical spacing' => __( 'Vertical spacing', 'um-wp-user-frontend' ),
	'Font size' => __( 'Font size', 'um-wp-user-frontend' ),
	'Font weight' => __( 'Font weight', 'um-wp-user-frontend' ),
	'Alignment' => __( 'Alignment', 'um-wp-user-frontend' ),
	'Left' => __( 'Left', 'um-wp-user-frontend' ),
	'Center' => __( 'Center', 'um-wp-user-frontend' ),
	'Right' => __( 'Right', 'um-wp-user-frontend' ),
	'Button text colour' => __( 'Button text colour', 'um-wp-user-frontend' ),
	'Button colour' => __( 'Button colour', 'um-wp-user-frontend' ),
	'Margin' => __( 'Margin', 'um-wp-user-frontend' ),
	'Space between terms' => __( 'Space between terms', 'um-wp-user-frontend' ),
	'Nothing to configure here.' => __( 'Nothing to configure here.', 'um-wp-user-frontend' ),
	'Card & grid' => __( 'Card & grid', 'um-wp-user-frontend' ),
	'Select a form in Tab Settings to preview real entries.' => __( 'Select a form in Tab Settings to preview real entries.', 'um-wp-user-frontend' ),
	'Loading real entries…' => __( 'Loading real entries…', 'um-wp-user-frontend' ),
	'No published entries for this form yet. Showing sample content.' => __( 'No published entries for this form yet. Showing sample content.', 'um-wp-user-frontend' ),
	'Real published entries of the selected form, rendered by the frontend template (unsaved changes included).' => __( 'Real published entries of the selected form, rendered by the frontend template (unsaved changes included).', 'um-wp-user-frontend' ),
	'Could not load the preview.' => __( 'Could not load the preview.', 'um-wp-user-frontend' ),
	'Sample content. Switch to “Real entries” to check actual posts.' => __( 'Sample content. Switch to “Real entries” to check actual posts.', 'um-wp-user-frontend' ),
	'Row limit reached (12).' => __( 'Row limit reached (12).', 'um-wp-user-frontend' ),
	'Row added' => __( 'Row added', 'um-wp-user-frontend' ),
	'Replace the current design with this preset? You can undo this.' => __( 'Replace the current design with this preset? You can undo this.', 'um-wp-user-frontend' ),
	'applied' => __( 'applied', 'um-wp-user-frontend' ),
	'Delete this row and its elements?' => __( 'Delete this row and its elements?', 'um-wp-user-frontend' ),
	'Row deleted' => __( 'Row deleted', 'um-wp-user-frontend' ),
	'deleted' => __( 'deleted', 'um-wp-user-frontend' ),
	'Element moved' => __( 'Element moved', 'um-wp-user-frontend' ),
);
?>
<div id="umwpuf-designer" class="umwpuf-designer">
	<input type="hidden" name="umwpuf_loop_builder" value="<?php echo esc_attr( wp_json_encode( $umwpuf_config ) ); ?>">
	<script type="application/json" id="umwpuf-designer-data"><?php echo wp_json_encode( array( 'defaults' => $umwpuf_defaults, 'strings' => $umwpuf_strings, 'nonce' => wp_create_nonce( 'umwpuf_designer_preview' ), 'ajax' => admin_url( 'admin-ajax.php' ) ), JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
	<p class="umwpuf-d-intro"><?php esc_html_e( 'Build the card used for each entry. Add rows, split them into columns, then drag elements into place. Changes are saved when you update the tab.', 'um-wp-user-frontend' ); ?> <span class="umwpuf-d-dirty"><?php esc_html_e( 'Unsaved changes', 'um-wp-user-frontend' ); ?></span></p>

	<div class="umwpuf-d-toolbar">
		<div class="umwpuf-d-toolgroup">
			<label class="screen-reader-text" for="umwpuf-d-preset"><?php esc_html_e( 'Start from a preset', 'um-wp-user-frontend' ); ?></label>
			<select id="umwpuf-d-preset" class="umwpuf-d-preset"><option value=""><?php esc_html_e( 'Start from a preset…', 'um-wp-user-frontend' ); ?></option></select>
			<button type="button" class="screen-reader-text" data-act="preset"><?php esc_html_e( 'Apply preset', 'um-wp-user-frontend' ); ?></button>
		</div>
		<div class="umwpuf-d-toolgroup">
			<button type="button" class="button" data-act="undo" title="<?php esc_attr_e( 'Undo (Ctrl+Z)', 'um-wp-user-frontend' ); ?>" aria-label="<?php esc_attr_e( 'Undo', 'um-wp-user-frontend' ); ?>">&#8630;</button>
			<button type="button" class="button" data-act="redo" title="<?php esc_attr_e( 'Redo (Ctrl+Shift+Z)', 'um-wp-user-frontend' ); ?>" aria-label="<?php esc_attr_e( 'Redo', 'um-wp-user-frontend' ); ?>">&#8631;</button>
		</div>
		<div class="umwpuf-d-toolgroup umwpuf-d-devices" role="group" aria-label="<?php esc_attr_e( 'Preview device', 'um-wp-user-frontend' ); ?>">
			<button type="button" data-act="device" data-value="desktop" aria-pressed="true"><?php esc_html_e( 'Desktop', 'um-wp-user-frontend' ); ?></button>
			<button type="button" data-act="device" data-value="tablet" aria-pressed="false"><?php esc_html_e( 'Tablet', 'um-wp-user-frontend' ); ?></button>
			<button type="button" data-act="device" data-value="mobile" aria-pressed="false"><?php esc_html_e( 'Phone', 'um-wp-user-frontend' ); ?></button>
		</div>
	</div>

	<div class="umwpuf-d-workspace">
		<aside class="umwpuf-d-palette" aria-label="<?php esc_attr_e( 'Elements', 'um-wp-user-frontend' ); ?>">
			<h3><?php esc_html_e( 'Elements', 'um-wp-user-frontend' ); ?></h3>
			<div class="umwpuf-d-palette-items"></div>
		</aside>
		<div class="umwpuf-d-canvas">
			<div class="umwpuf-d-canvas-meta" aria-live="polite"></div>
			<div class="umwpuf-d-stage" tabindex="-1"></div>
			<div class="umwpuf-d-addrow">
				<button type="button" class="button" data-act="toggle-addrow" aria-haspopup="true">+ <?php esc_html_e( 'Add row', 'um-wp-user-frontend' ); ?></button>
				<div class="umwpuf-d-addrow-menu" hidden></div>
			</div>
		</div>
		<aside class="umwpuf-d-inspector" aria-label="<?php esc_attr_e( 'Settings for the selection', 'um-wp-user-frontend' ); ?>"></aside>
	</div>

	<section class="umwpuf-d-preview">
		<div class="umwpuf-d-preview-head">
			<h3><?php esc_html_e( 'Grid preview', 'um-wp-user-frontend' ); ?> <small class="umwpuf-d-scale"></small></h3>
			<div class="umwpuf-d-toolgroup" role="group" aria-label="<?php esc_attr_e( 'Preview content', 'um-wp-user-frontend' ); ?>">
				<button type="button" data-act="preview-mode" data-value="sample" aria-pressed="true"><?php esc_html_e( 'Sample content', 'um-wp-user-frontend' ); ?></button>
				<button type="button" data-act="preview-mode" data-value="real" aria-pressed="false"><?php esc_html_e( 'Real entries', 'um-wp-user-frontend' ); ?></button>
			</div>
		</div>
		<p class="umwpuf-d-preview-note description"></p>
		<div class="umwpuf-d-grid-viewport"><div class="umwpuf-d-grid-inner"></div></div>
	</section>
	<div class="umwpuf-d-live screen-reader-text" aria-live="polite"></div>
</div>
<p id="umwpuf-designer-off" class="description" hidden><?php esc_html_e( 'Choose “Custom layout (designer)” as the entries layout in Tab Settings to design your own cards.', 'um-wp-user-frontend' ); ?></p>
