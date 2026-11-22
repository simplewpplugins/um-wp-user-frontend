<?php
namespace umwpuf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-tab card layout designer: rows -> columns -> elements.
 *
 * Every value is whitelisted or clamped in sanitize(); nothing from the saved
 * design is ever interpreted as markup, CSS selectors or arbitrary CSS.
 * The admin preview (assets/loop-builder.js) mirrors element_style()/row_style()
 * so the designer canvas matches the frontend.
 */
class Loop_Builder {

	const VERSION      = 2;
	const MAX_ROWS     = 12;
	const MAX_COLUMNS  = 4;
	const MAX_ELEMENTS = 40;

	/** @var array Per-request render context cache keyed by tab ID. */
	private static $context = array();

	public static function types() {
		return array( 'image', 'title', 'excerpt', 'date', 'modified', 'author', 'avatar', 'taxonomy', 'meta', 'status', 'comments', 'button', 'text', 'divider', 'spacer' );
	}

	private static function shadows( $kind = 'element' ) {
		if ( 'card' === $kind ) {
			return array( 'none' => 'none', 'soft' => '0 4px 16px rgba(15,23,42,.06)', 'medium' => '0 10px 28px rgba(15,23,42,.12)', 'strong' => '0 18px 40px rgba(15,23,42,.2)' );
		}
		return array( 'none' => 'none', 'soft' => '0 3px 12px rgba(0,0,0,.08)', 'medium' => '0 8px 22px rgba(0,0,0,.13)', 'strong' => '0 12px 30px rgba(0,0,0,.2)' );
	}

	public static function element_defaults( $type = 'text' ) {
		$style   = array( 'color' => '', 'background' => '', 'border_color' => '', 'size' => 16, 'weight' => 400, 'padding' => 0, 'margin' => 0, 'radius' => 0, 'border_width' => 0, 'gap' => 8, 'align' => 'left', 'shadow' => 'none' );
		$by_type = array(
			'title'    => array( 'size' => 19, 'weight' => 700 ),
			'date'     => array( 'size' => 13 ),
			'modified' => array( 'size' => 13 ),
			'excerpt'  => array( 'size' => 14 ),
			'author'   => array( 'size' => 14, 'weight' => 600 ),
			'avatar'   => array( 'size' => 40 ),
			'status'   => array( 'size' => 12 ),
			'comments' => array( 'size' => 13 ),
			'taxonomy' => array( 'size' => 13 ),
			'meta'     => array( 'size' => 14 ),
			'button'   => array( 'size' => 14, 'weight' => 600 ),
			'divider'  => array( 'border_width' => 1, 'border_color' => '#e5e7eb' ),
		);
		return array(
			'type'        => $type,
			'label'       => '',
			'key'         => '',
			'link'        => in_array( $type, array( 'image', 'title', 'button' ), true ) ? 'post' : 'none',
			'text'        => '',
			'words'       => 25,
			'tag'         => 'h3',
			'ratio'       => 'auto',
			'placeholder' => false,
			'height'      => 16,
			'style'       => array_merge( $style, $by_type[ $type ] ?? array() ),
		);
	}

	private static function el( $type, $overrides = array() ) {
		$el = self::element_defaults( $type );
		foreach ( $overrides as $key => $value ) {
			if ( 'style' === $key ) {
				$el['style'] = array_merge( $el['style'], $value );
			} else {
				$el[ $key ] = $value;
			}
		}
		return $el;
	}

	public static function row_defaults( $widths = array( 1 ) ) {
		return array(
			'widths'     => array_values( $widths ),
			'gap'        => 12,
			'valign'     => 'start',
			'stack'      => 'narrow',
			'padding'    => 0,
			'radius'     => 0,
			'background' => '',
			'columns'    => array_fill( 0, count( $widths ), array( 'elements' => array() ) ),
		);
	}

	public static function defaults() {
		$row                           = self::row_defaults( array( 1 ) );
		$row['columns'][0]['elements'] = array(
			self::el( 'image' ),
			self::el( 'title' ),
			self::el( 'date' ),
			self::el( 'excerpt' ),
			self::el( 'button', array( 'label' => __( 'View post', 'um-wp-user-frontend' ) ) ),
		);
		return array(
			'version' => self::VERSION,
			'card'    => array(
				'background'     => '#ffffff',
				'color'          => '#18212f',
				'border_color'   => '#e5e7eb',
				'border_width'   => 1,
				'radius'         => 14,
				'padding'        => 16,
				'gap'            => 12,
				'shadow'         => 'soft',
				'hover'          => 'none',
				'columns'        => 3,
				'columns_tablet' => 2,
				'columns_mobile' => 1,
				'grid_gap'       => 20,
			),
			'rows'    => array( $row ),
		);
	}

	private static function color( $value, $default = '' ) {
		return is_string( $value ) && sanitize_hex_color( $value ) ? sanitize_hex_color( $value ) : $default;
	}

	private static function integer( $value, $min, $max, $fallback ) {
		return is_numeric( $value ) ? max( $min, min( $max, (int) $value ) ) : $fallback;
	}

	private static function choice( $value, $allowed, $fallback ) {
		return is_string( $value ) && in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	public static function sanitize( $raw ) {
		if ( is_string( $raw ) ) {
			$raw = json_decode( wp_unslash( $raw ), true );
		}
		if ( ! is_array( $raw ) ) {
			return self::defaults();
		}
		$defaults = self::defaults();
		$card     = isset( $raw['card'] ) && is_array( $raw['card'] ) ? $raw['card'] : array();
		$safe     = array( 'version' => self::VERSION, 'card' => array(), 'rows' => array() );

		foreach ( array( 'background', 'color', 'border_color' ) as $key ) {
			$safe['card'][ $key ] = self::color( $card[ $key ] ?? '', $defaults['card'][ $key ] );
		}
		$ranges = array( 'border_width' => array( 0, 12 ), 'radius' => array( 0, 100 ), 'padding' => array( 0, 120 ), 'gap' => array( 0, 100 ), 'columns' => array( 1, 6 ), 'columns_tablet' => array( 1, 4 ), 'columns_mobile' => array( 1, 2 ), 'grid_gap' => array( 0, 80 ) );
		foreach ( $ranges as $key => $range ) {
			$safe['card'][ $key ] = self::integer( $card[ $key ] ?? null, $range[0], $range[1], $defaults['card'][ $key ] );
		}
		// Smaller screens never get more columns than larger ones.
		$safe['card']['columns_tablet'] = min( $safe['card']['columns_tablet'], $safe['card']['columns'] );
		$safe['card']['columns_mobile'] = min( $safe['card']['columns_mobile'], $safe['card']['columns_tablet'] );
		$safe['card']['shadow']         = self::choice( $card['shadow'] ?? '', array( 'none', 'soft', 'medium', 'strong' ), 'soft' );
		$safe['card']['hover']          = self::choice( $card['hover'] ?? '', array( 'none', 'lift', 'glow' ), 'none' );

		// Migrate v1 (flat element list) into a single one-column row.
		$rows = isset( $raw['rows'] ) && is_array( $raw['rows'] ) ? $raw['rows'] : null;
		if ( null === $rows ) {
			$rows = array( array( 'widths' => array( 1 ), 'columns' => array( array( 'elements' => is_array( $raw['elements'] ?? null ) ? $raw['elements'] : array() ) ) ) );
		}

		$budget = self::MAX_ELEMENTS;
		foreach ( array_slice( array_values( $rows ), 0, self::MAX_ROWS ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$widths = array();
			foreach ( array_slice( is_array( $row['widths'] ?? null ) ? array_values( $row['widths'] ) : array( 1 ), 0, self::MAX_COLUMNS ) as $width ) {
				$widths[] = self::integer( $width, 0, 12, 1 ); // 0 = size to content ("auto").
			}
			if ( ! $widths ) {
				$widths = array( 1 );
			}
			$safe_row = array(
				'widths'     => $widths,
				'gap'        => self::integer( $row['gap'] ?? null, 0, 80, 12 ),
				'valign'     => self::choice( $row['valign'] ?? '', array( 'start', 'center', 'end', 'stretch' ), 'start' ),
				'stack'      => self::choice( $row['stack'] ?? '', array( 'narrow', 'mobile', 'never' ), 'narrow' ),
				'padding'    => self::integer( $row['padding'] ?? null, 0, 80, 0 ),
				'radius'     => self::integer( $row['radius'] ?? null, 0, 60, 0 ),
				'background' => self::color( $row['background'] ?? '' ),
				'columns'    => array(),
			);
			$columns = is_array( $row['columns'] ?? null ) ? array_values( $row['columns'] ) : array();
			foreach ( array_keys( $widths ) as $index ) {
				$elements = array();
				$source   = isset( $columns[ $index ]['elements'] ) && is_array( $columns[ $index ]['elements'] ) ? $columns[ $index ]['elements'] : array();
				foreach ( $source as $item ) {
					if ( $budget <= 0 ) {
						break;
					}
					$element = self::sanitize_element( $item );
					if ( $element ) {
						$elements[] = $element;
						$budget--;
					}
				}
				$safe_row['columns'][] = array( 'elements' => $elements );
			}
			$safe['rows'][] = $safe_row;
		}
		return $safe;
	}

	private static function sanitize_element( $item ) {
		if ( ! is_array( $item ) || ! in_array( $item['type'] ?? '', self::types(), true ) ) {
			return null;
		}
		$type     = $item['type'];
		$defaults = self::element_defaults( $type );
		$style    = is_array( $item['style'] ?? null ) ? $item['style'] : array();
		$s        = array();
		foreach ( array( 'color', 'background', 'border_color' ) as $key ) {
			// A present-but-empty colour means "inherit"; a missing one takes the type default.
			$s[ $key ] = self::color( $style[ $key ] ?? '', array_key_exists( $key, $style ) ? '' : $defaults['style'][ $key ] );
		}
		$ranges = array( 'size' => array( 9, 72 ), 'weight' => array( 100, 900 ), 'padding' => array( 0, 100 ), 'margin' => array( 0, 100 ), 'radius' => array( 0, 100 ), 'border_width' => array( 0, 12 ), 'gap' => array( 0, 100 ) );
		foreach ( $ranges as $key => $range ) {
			$s[ $key ] = self::integer( $style[ $key ] ?? null, $range[0], $range[1], $defaults['style'][ $key ] );
		}
		$s['weight'] = (int) ( round( $s['weight'] / 100 ) * 100 );
		$s['align']  = self::choice( $style['align'] ?? '', array( 'left', 'center', 'right' ), 'left' );
		$s['shadow'] = self::choice( $style['shadow'] ?? '', array( 'none', 'soft', 'medium', 'strong' ), 'none' );

		return array(
			'type'        => $type,
			'label'       => mb_substr( sanitize_text_field( (string) ( $item['label'] ?? '' ) ), 0, 120 ),
			'key'         => sanitize_key( (string) ( $item['key'] ?? '' ) ),
			'link'        => self::choice( $item['link'] ?? '', array( 'none', 'post', 'archive', 'filter' ), $defaults['link'] ),
			'text'        => mb_substr( sanitize_text_field( (string) ( $item['text'] ?? '' ) ), 0, 200 ),
			'words'       => self::integer( $item['words'] ?? null, 5, 80, 25 ),
			'tag'         => self::choice( $item['tag'] ?? '', array( 'h2', 'h3', 'h4', 'p', 'div' ), 'h3' ),
			'ratio'       => self::choice( $item['ratio'] ?? '', array( 'auto', '16/9', '4/3', '1/1', '3/4', '21/9' ), 'auto' ),
			'placeholder' => ! empty( $item['placeholder'] ) && 'false' !== $item['placeholder'],
			'height'      => self::integer( $item['height'] ?? null, 0, 200, 16 ),
			'style'       => $s,
		);
	}

	public static function config( $tab_id ) {
		$raw = get_post_meta( $tab_id, '_umwpuf_loop_builder', true );
		return is_array( $raw ) ? self::sanitize( $raw ) : self::defaults();
	}

	/* ---------------------------------------------------------------------
	 * Style builders (mirrored in assets/loop-builder.js -> styles.*)
	 * ------------------------------------------------------------------ */

	public static function card_style( $c ) {
		$shadows = self::shadows( 'card' );
		return sprintf( 'background:%s;color:%s;border:%dpx solid %s;border-radius:%dpx;padding:%dpx;gap:%dpx;box-shadow:%s;', $c['background'], $c['color'], $c['border_width'], $c['border_color'], $c['radius'], $c['padding'], $c['gap'], $shadows[ $c['shadow'] ] );
	}

	public static function grid_style( $c ) {
		return sprintf( '--umwpuf-columns:%d;--umwpuf-columns-tablet:%d;--umwpuf-columns-mobile:%d;--umwpuf-grid-gap:%dpx;', $c['columns'], $c['columns_tablet'], $c['columns_mobile'], $c['grid_gap'] );
	}

	public static function row_template( $widths ) {
		return implode(
			' ',
			array_map(
				function ( $w ) {
					return $w > 0 ? 'minmax(0,' . (int) $w . 'fr)' : 'auto';
				},
				$widths
			)
		);
	}

	public static function row_style( $row ) {
		$out = '--umwpuf-row-cols:' . self::row_template( $row['widths'] ) . ';gap:' . (int) $row['gap'] . 'px;align-items:' . $row['valign'] . ';';
		if ( $row['padding'] ) {
			$out .= 'padding:' . (int) $row['padding'] . 'px;';
		}
		if ( $row['radius'] ) {
			$out .= 'border-radius:' . (int) $row['radius'] . 'px;';
		}
		if ( $row['background'] ) {
			$out .= 'background:' . $row['background'] . ';';
		}
		return $out;
	}

	public static function element_style( $e ) {
		$s    = $e['style'];
		$type = $e['type'];
		if ( 'spacer' === $type ) {
			return 'height:' . (int) $e['height'] . 'px;';
		}
		if ( 'divider' === $type ) {
			return 'border-top:' . max( 1, (int) $s['border_width'] ) . 'px solid ' . ( $s['border_color'] ? $s['border_color'] : 'currentColor' ) . ';margin:' . (int) $s['margin'] . 'px 0;';
		}
		$out = 'font-size:' . (int) $s['size'] . 'px;font-weight:' . (int) $s['weight'] . ';padding:' . (int) $s['padding'] . 'px;margin:' . (int) $s['margin'] . 'px;border-radius:' . (int) $s['radius'] . 'px;border:' . (int) $s['border_width'] . 'px solid ' . ( $s['border_color'] ? $s['border_color'] : 'transparent' ) . ';text-align:' . $s['align'] . ';gap:' . (int) $s['gap'] . 'px;';
		// Buttons paint their link, not the wrapper, so colours become custom properties.
		if ( 'button' === $type ) {
			if ( $s['color'] ) {
				$out .= '--umwpuf-btn-color:' . $s['color'] . ';';
			}
			if ( $s['background'] ) {
				$out .= '--umwpuf-btn-bg:' . $s['background'] . ';';
			}
		} else {
			if ( $s['color'] ) {
				$out .= 'color:' . $s['color'] . ';';
			}
			if ( $s['background'] ) {
				$out .= 'background:' . $s['background'] . ';';
			}
		}
		if ( 'image' === $type && 'auto' !== $e['ratio'] ) {
			$out .= '--umwpuf-ratio:' . str_replace( '/', ' / ', $e['ratio'] ) . ';';
		}
		$shadows = self::shadows();
		return $out . 'box-shadow:' . $shadows[ $s['shadow'] ] . ';';
	}

	/* ---------------------------------------------------------------------
	 * Rendering
	 * ------------------------------------------------------------------ */

	/** Design plus the custom fields this tab's form may display. */
	private static function context( $tab_id ) {
		if ( ! isset( self::$context[ $tab_id ] ) ) {
			$form_id                  = absint( get_post_meta( $tab_id, 'umwpuf_post_form', true ) );
			self::$context[ $tab_id ] = array(
				'config' => self::config( $tab_id ),
				'fields' => UMWPUF()->helper()->form_meta_fields( $form_id ),
			);
		}
		return self::$context[ $tab_id ];
	}

	public static function flush_context() {
		self::$context = array();
	}

	public static function render( $tab_id, $edit_url = '' ) {
		self::render_card( self::context( $tab_id ), $edit_url );
	}

	/**
	 * Server-rendered designer preview: an unsaved design applied to real
	 * published entries of a form, through exactly the same renderer as the frontend.
	 */
	public static function preview_html( $raw_config, $form_id, $count = 3 ) {
		$form_id   = absint( $form_id );
		$post_type = UMWPUF()->helper()->form_post_type( $form_id );
		if ( ! $post_type ) {
			return '';
		}
		$context = array(
			'config' => self::sanitize( $raw_config ),
			'fields' => UMWPUF()->helper()->form_meta_fields( $form_id ),
		);
		$query   = new \WP_Query(
			array(
				'post_type'           => $post_type,
				'post_status'         => 'publish',
				'posts_per_page'      => max( 1, min( 6, (int) $count ) ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'meta_query'          => array( array( 'key' => '_wpuf_form_id', 'value' => $form_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);
		ob_start();
		while ( $query->have_posts() ) {
			$query->the_post();
			self::render_card( $context, '' );
		}
		wp_reset_postdata();
		return ob_get_clean();
	}

	private static function render_card( $context, $edit_url ) {
		$card    = $context['config']['card'];
		$classes = 'umwpuf-post umwpuf-custom-card umwpuf-entry-card' . ( 'none' !== $card['hover'] ? ' umwpuf-hover-' . $card['hover'] : '' );
		echo '<article class="' . esc_attr( $classes ) . '" style="' . esc_attr( self::card_style( $card ) ) . '">';
		if ( $edit_url ) {
			/* translators: %s: entry title */
			$label = sprintf( __( 'Edit “%s”', 'um-wp-user-frontend' ), wp_strip_all_tags( get_the_title() ) );
			echo '<a class="umwpuf-card-edit" href="' . esc_url( $edit_url ) . '" aria-label="' . esc_attr( $label ) . '" title="' . esc_attr__( 'Edit entry', 'um-wp-user-frontend' ) . '"><span aria-hidden="true">&#9998;</span></a>';
		}
		foreach ( $context['config']['rows'] as $row ) {
			$columns = array();
			foreach ( $row['columns'] as $column ) {
				ob_start();
				foreach ( $column['elements'] as $element ) {
					self::element( $element, $context );
				}
				$columns[] = ob_get_clean();
			}
			// Skip rows where every element resolved to nothing for this entry.
			if ( '' === trim( implode( '', $columns ) ) ) {
				continue;
			}
			echo '<div class="umwpuf-row umwpuf-stack-' . esc_attr( $row['stack'] ) . ( count( $columns ) > 1 ? ' umwpuf-row-multi' : '' ) . '" style="' . esc_attr( self::row_style( $row ) ) . '">';
			foreach ( $columns as $html ) {
				echo '<div class="umwpuf-col">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped fragments.
			}
			echo '</div>';
		}
		echo '</article>';
	}

	private static function open( $e, $extra_class = '' ) {
		echo '<div class="umwpuf-loop-element umwpuf-loop-' . esc_attr( $e['type'] ) . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' ) . '" style="' . esc_attr( self::element_style( $e ) ) . '">';
	}

	private static function label( $e ) {
		if ( '' !== $e['label'] && 'button' !== $e['type'] ) {
			echo '<span class="umwpuf-loop-label">' . esc_html( $e['label'] ) . '</span> ';
		}
	}

	/**
	 * @return string|array String value, or array( 'attachment' => ID ) for upload fields.
	 */
	private static function meta_value( $id, $key, $context ) {
		$fields = $context['fields'];
		$type   = '';
		if ( isset( $fields[ $key ] ) ) {
			$type = $fields[ $key ]['type'];
			if ( in_array( $type, array( 'image_upload', 'file_upload' ), true ) ) {
				foreach ( (array) get_post_meta( $id, $key, false ) as $value ) {
					foreach ( (array) maybe_unserialize( $value ) as $attachment_id ) {
						$attachment_id = absint( $attachment_id );
						if ( $attachment_id && 'attachment' === get_post_type( $attachment_id ) ) {
							return array( 'attachment' => $attachment_id );
						}
					}
				}
				return '';
			}
		} else {
			// Not a field of this tab's form: allow public, explicitly registered scalar meta only.
			$registered = get_registered_meta_keys( 'post', get_post_type( $id ) );
			if ( empty( $registered[ $key ]['show_in_rest'] ) || ! in_array( $registered[ $key ]['type'] ?? '', array( 'string', 'integer', 'number', 'boolean' ), true ) ) {
				return '';
			}
		}
		$value = get_post_meta( $id, $key, true );
		if ( is_array( $value ) ) {
			$value = implode(
				', ',
				array_filter(
					array_map(
						function ( $v ) {
							return is_scalar( $v ) ? (string) $v : '';
						},
						$value
					),
					'strlen'
				)
			);
		}
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		// WPUF joins multi-choice answers with "| ".
		$value = str_replace( '| ', ', ', (string) $value );
		if ( 'numeric_text' === $type && is_numeric( $value ) ) {
			$decimals = false !== strpos( $value, '.' ) ? strlen( substr( strrchr( $value, '.' ), 1 ) ) : 0;
			$value    = number_format_i18n( (float) $value, $decimals );
		}
		return $value;
	}

	private static function element( $e, $context ) {
		$id    = get_the_ID();
		$type  = $e['type'];
		$key   = $e['key'];
		$value = '';

		switch ( $type ) {
			case 'spacer':
				echo '<div class="umwpuf-loop-element umwpuf-loop-spacer" aria-hidden="true" style="' . esc_attr( self::element_style( $e ) ) . '"></div>';
				return;
			case 'divider':
				echo '<hr class="umwpuf-loop-element umwpuf-loop-divider" style="' . esc_attr( self::element_style( $e ) ) . '">';
				return;
			case 'image':
				if ( has_post_thumbnail( $id ) ) {
					$img = get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy' ) );
				} elseif ( $e['placeholder'] ) {
					$img = '<span class="umwpuf-thumb-placeholder" aria-hidden="true">&#10022;</span>';
				} else {
					return;
				}
				self::open( $e, 'auto' !== $e['ratio'] ? 'umwpuf-has-ratio' : '' );
				echo 'post' === $e['link'] ? '<a href="' . esc_url( get_permalink( $id ) ) . '" tabindex="-1" aria-hidden="true">' . wp_kses_post( $img ) . '</a>' : wp_kses_post( $img );
				echo '</div>';
				return;
			case 'avatar':
				$size = (int) $e['style']['size'];
				self::open( $e );
				echo get_avatar( (int) get_post_field( 'post_author', $id ), $size * 2, '', '', array( 'width' => $size, 'height' => $size, 'class' => 'umwpuf-avatar' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core.
				echo '</div>';
				return;
			case 'taxonomy':
				if ( ! $key || ! taxonomy_exists( $key ) || ! is_object_in_taxonomy( get_post_type( $id ), $key ) ) {
					return;
				}
				$tax = get_taxonomy( $key );
				if ( ! $tax || ! $tax->public ) {
					return;
				}
				$terms = get_the_terms( $id, $key );
				if ( ! $terms || is_wp_error( $terms ) ) {
					return;
				}
				self::open( $e );
				self::label( $e );
				foreach ( $terms as $term ) {
					if ( 'filter' === $e['link'] ) {
						echo '<a href="#" data-umwpuf-taxonomy="' . esc_attr( $key ) . '" data-umwpuf-term="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</a>';
						continue;
					}
					$term_url = 'archive' === $e['link'] ? get_term_link( $term ) : '';
					echo $term_url && ! is_wp_error( $term_url ) ? '<a href="' . esc_url( $term_url ) . '">' . esc_html( $term->name ) . '</a>' : '<span>' . esc_html( $term->name ) . '</span>';
				}
				echo '</div>';
				return;
			case 'meta':
				if ( ! $key || 0 === strpos( $key, '_' ) ) {
					return;
				}
				$value = self::meta_value( $id, $key, $context );
				if ( is_array( $value ) ) {
					$attachment = $value['attachment'];
					self::open( $e );
					self::label( $e );
					echo wp_attachment_is_image( $attachment )
						? wp_get_attachment_image( $attachment, 'medium', false, array( 'loading' => 'lazy' ) )
						: '<a href="' . esc_url( wp_get_attachment_url( $attachment ) ) . '">' . esc_html( get_the_title( $attachment ) ) . '</a>';
					echo '</div>';
					return;
				}
				break;
			case 'title':
				$value = get_the_title( $id );
				break;
			case 'excerpt':
				$value = wp_trim_words( get_the_excerpt( $id ), (int) $e['words'] );
				break;
			case 'date':
				$value = get_the_date( '', $id );
				break;
			case 'modified':
				$value = get_the_modified_date( '', $id );
				break;
			case 'author':
				$value = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $id ) );
				break;
			case 'status':
				if ( ! current_user_can( 'edit_post', $id ) ) {
					return;
				}
				$status = get_post_status_object( get_post_status( $id ) );
				$value  = $status ? $status->label : get_post_status( $id );
				break;
			case 'comments':
				$count = (int) get_comments_number( $id );
				if ( ! $count && ! comments_open( $id ) ) {
					return;
				}
				/* translators: %s: number of comments */
				$value = sprintf( _n( '%s comment', '%s comments', $count, 'um-wp-user-frontend' ), number_format_i18n( $count ) );
				break;
			case 'button':
				$value = '' !== $e['label'] ? $e['label'] : __( 'View post', 'um-wp-user-frontend' );
				break;
			case 'text':
				$value = $e['text'];
				break;
			default:
				return;
		}

		if ( '' === (string) $value ) {
			return;
		}
		$url = 'post' === $e['link'] ? get_permalink( $id ) : '';
		self::open( $e );
		self::label( $e );
		$inner = $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $value ) . '</a>' : esc_html( $value );
		if ( 'title' === $type ) {
			$tag = tag_escape( $e['tag'] );
			echo '<' . $tag . ' class="umwpuf-loop-heading">' . $inner . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted tag, escaped content.
		} else {
			echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
		echo '</div>';
	}
}
