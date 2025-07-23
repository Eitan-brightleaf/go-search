<?php
/**
 * Plugin Name: gfsearch
 * Description: A shortcode to search and display Gravity Forms entries based on specified criteria and attributes.
 * Version: 1.2.0
 * Author: BrightLeaf Digital
 * Author URI: https://digital.brightleaf.info/
 * License: GPL-2.0+
 */

add_action(
	'init',
	function () {
		add_shortcode( 'gfsearch', 'gfsearch_shortcode' );
	}
);

/**
 * Processes the gfsearch shortcode to perform searching and displaying Gravity Forms entries
 * based on specified criteria and attributes.
 *
 * @param array  $atts An associative array of attributes, or default values.
 * @param string $content Content of the shortcode, typically search values separated by '|'.
 *
 * @return string|false Formatted search results or false if search fails due to missing attributes or invalid setup.
 */
function gfsearch_shortcode( $atts, $content = null ) {

	$result = apply_filters( 'gogv_shortcode_process', $content );
	if ( $result !== $content ) {
		return $result;
	}

	$atts = shortcode_atts(
		[
			'target'                   => '0',
			'search'                   => '',
			'operators'                => '',
			'greater_than'             => false,
			'less_than'                => false,
			'display'                  => '',
			'sort_key'                 => 'id',
			'sort_direction'           => 'DESC',
			'sort_is_num'              => true,
			'secondary_sort_key'       => '',
			'secondary_sort_direction' => 'DESC',
			'unique'                   => false,
			'limit'                    => '1',
			'search_mode'              => 'all',
			'separator'                => '',
			'search_empty'             => false,
			'default'                  => '',
			'link'                     => false,
		],
		$atts,
		'gfsearch'
	);

	// Allow everything wp_kses_post allows plus <a> and its attributes
	$allowed_tags      = wp_kses_allowed_html( 'post' );
	$a_tags            = [
		'href'   => true,
		'title'  => true,
		'target' => true,
		'rel'    => true,
		'class'  => true,
		'id'     => true,
		'style'  => true,
	];
	$allowed_tags['a'] = $a_tags + ( $allowed_tags['a'] ?? [] );

	$content = html_entity_decode( $content, ENT_QUOTES );

	$form_id = array_map( 'intval', explode( ',', $atts['target'] ) );

	$search_criteria                          = [];
	$search_criteria['status']                = 'active';
	$search_criteria['field_filters']         = [];
	$search_criteria['field_filters']['mode'] = in_array( strtolower( $atts['search_mode'] ), [ 'all', 'any' ], true ) ? strtolower( $atts['search_mode'] ) : 'all';

	if ( ! empty( $atts['search'] ) && empty( $atts['display'] ) && ! $atts['search_empty'] ) {
		return '';
	}

	$search_ids = array_map( 'sanitize_text_field', explode( ',', $atts['search'] ) );
	$search_ids = array_map( 'trim', $search_ids );

	// Parse operators if provided
	$operators = [];
	if ( ! empty( $atts['operators'] ) ) {
		$operators = array_map( 'trim', explode( ',', $atts['operators'] ) );
		$operators = array_map( 'sanitize_text_field', $operators );
	}

	$content_values = array_map( 'trim', explode( '|', $content ) );

	foreach ( $search_ids as $index => $search_id ) {
		if ( empty( $search_id ) ) {
			continue;
		}
		$current_field = GFAPI::get_field( $form_id[0], $search_id );
		if ( $current_field && 'number' === $current_field['type'] ) {
			$content_values[ $index ] = str_replace( ',', '', $content_values[ $index ] );
		}

		// Add operator if provided for this field
		if ( ! empty( $operators[ $index ] ) ) {
            /*
             * Validate operator against supported operators
             * is, = (exact match)
             * isnot, isnot, != (not equal) (<> not supported due to sanitizing issues)
             * contains (Substring search-converted to LIKE %value%)
             * like: SQL like with wildcards
             * notin, not in (values not in array)
             * in (values in array)
             * lt, gt, lt=, gt=, (numeric operators)
             */
			$supported_operators = [
				'=',
				'is',
				'is not',
				'isnot',
                '!=',
                'contains',
				'like',
				'not in',
				'notin',
				'in',
                'lt',
				'gt',
				'gt=',
				'lt=',
			];
            if ( str_contains( $content_values[ $index ], 'array(' ) && in_array( $operators[ $index ], [ 'in', 'notin', 'not in' ], true ) ) {
                $json_string              = str_replace( [ 'array(', ')', "'" ], [ '[', ']', '"' ], $content_values[ $index ] );
                $content_values[ $index ] = json_decode( $json_string, true );
                $content_values[ $index ] = array_map(
                    fn( $value ) => GFCommon::replace_variables( $value, [], [] ),
                    $content_values[ $index ]
                );

                $field_filter = [
                    'key'   => $search_id,
                    'value' => $content_values[ $index ],
                ];
            } else {
                $field_filter = [
                    'key'   => $search_id,
                    'value' => GFCommon::replace_variables( $content_values[ $index ], [], [] ),
                ];
            }

			if ( in_array( $operators[ $index ], $supported_operators, true ) ) {
                $operators[ $index ]      = str_replace( 'gt', '>', $operators[ $index ] );
                $operators[ $index ]      = str_replace( 'lt', '<', $operators[ $index ] );
				$field_filter['operator'] = $operators[ $index ];
			}
		} else {
            $field_filter = [
                'key'   => $search_id,
                'value' => GFCommon::replace_variables( $content_values[ $index ], [], [] ),
            ];
        }

		$search_criteria['field_filters'][] = $field_filter;
	}

	// Process greater_than attribute
	if ( $atts['greater_than'] ) {
		$greater_than = array_map( 'trim', explode( ',', $atts['greater_than'] ) );
		if ( count( $greater_than ) >= 2 ) {
			$search_criteria['field_filters'][] = [
				'key'      => intval( $greater_than[0] ),
				'value'    => floatval( $greater_than[1] ),
				'operator' => '>',
			];
		}
	}

	// Process less_than attribute
	if ( $atts['less_than'] ) {
		$less_than = array_map( 'trim', explode( ',', $atts['less_than'] ) );
		if ( count( $less_than ) >= 2 ) {
			$search_criteria['field_filters'][] = [
				'key'      => intval( $less_than[0] ),
				'value'    => floatval( $less_than[1] ),
				'operator' => '<',
			];
		}
	}

	$sorting = [
		'key'        => sanitize_text_field( $atts['sort_key'] ),
		'direction'  => in_array( strtoupper( $atts['sort_direction'] ), [ 'ASC', 'DESC', 'RAND' ], true ) ? strtoupper( $atts['sort_direction'] ) : 'DESC',
		'is_numeric' => ! ( strtolower( $atts['sort_is_num'] ) === 'false' ) && $atts['sort_is_num'],
	];

	$secondary_sort_key       = sanitize_text_field( $atts['secondary_sort_key'] );
	$secondary_sort_direction = in_array( strtoupper( $atts['secondary_sort_direction'] ), [ 'ASC', 'DESC' ], true )
		? strtoupper( $atts['secondary_sort_direction'] )
		: 'DESC';

	$paging_offset = 0;
	$total_count   = 0;

	if ( 'all' !== strtolower( $atts['limit'] ) ) {
		$original_limit = empty( $atts['limit'] ) ? 1 : (int) $atts['limit'];

		if ( $secondary_sort_key ) {
			$atts['limit'] = 'all';
		}
	}

	if ( empty( $atts['limit'] ) ) {
		$page_size = 1;
	} elseif ( 'all' === strtolower( $atts['limit'] ) ) {
		$page_size = 25;
	} else {
		$page_size = min( intVal( $atts['limit'] ), 25 );
	}
	$paging = [
		'offset'    => $paging_offset,
		'page_size' => $page_size,
	];

	$entries = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

	if ( 'all' === $atts['limit'] || intVal( $atts['limit'] ) > 25 ) {
		$count = count( $entries );
		while ( $total_count > $count ) {
			$paging['offset'] += 25;
			$new_entries       = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );
			array_push( $entries, ...$new_entries ); // $entries = array_merge( $entries, $new_entries );
			if ( is_numeric( $atts['limit'] ) && count( $entries ) > $atts['limit'] ) {
				break;
			}
			$count = count( $entries );
		}
		if ( is_numeric( $atts['limit'] ) ) {
			$entries = array_slice( $entries, 0, intVal( $atts['limit'] ) );
		}
	}

	if ( empty( $entries ) ) {
		// If default contains multiple values, use the first one
		$default_values = array_map( 'trim', explode( '||', $atts['default'] ) );
		return wp_kses_post( $default_values[0] ?? '' );
	}

	if ( ! empty( $secondary_sort_key ) && 'RAND' !== $sorting['direction'] ) {
		$grouped_entries = [];
		foreach ( $entries as $entry ) {
			$primary_key_value                       = $entry[ $sorting['key'] ] ?? ''; // Use the primary sort key as the group key
			$grouped_entries[ $primary_key_value ][] = $entry;
		}

		// Sort each group based on the secondary sort key
		foreach ( $grouped_entries as &$group ) {
			usort(
				$group,
				function ( $entry1, $entry2 ) use ( $secondary_sort_key, $secondary_sort_direction ) {
					$value1 = $entry1[ $secondary_sort_key ] ?? '';
					$value2 = $entry2[ $secondary_sort_key ] ?? '';

					// For non-numeric values, use string comparison
					if ( ! is_numeric( $value1 ) || ! is_numeric( $value2 ) ) {
						if ( strtoupper( $secondary_sort_direction ) === 'ASC' ) {
							return strcasecmp( $value1, $value2 ); // Ascending order for strings
						}

						return strcasecmp( $value2, $value1 ); // Descending order for strings
					}

					// If numeric, compare numerically
					$value1 = (float) $value1;
					$value2 = (float) $value2;

					if ( strtoupper( $secondary_sort_direction ) === 'ASC' ) {
						return $value1 <=> $value2; // Ascending order for numbers
					}

					return $value2 <=> $value1; // Descending order for numbers
				}
			);
		}

		unset( $group ); // Clean up the reference variable to avoid potential bugs

		// Flatten groups back into a single array, retaining primary sort order
		$entries = [];
		foreach ( $grouped_entries as $group ) {
			$entries = array_merge( $entries, $group );
		}
	}

	if ( isset( $original_limit ) && $original_limit < count( $entries ) ) {
		$entries = array_slice( $entries, 0, $original_limit );
	}

	$results = [];

	$atts['display'] = convert_curly_shortcodes( $atts['display'] );

	// Mask nested gfsearch shortcodes [gfsearch ...]...[/gfsearch]
	// Mask only the display attribute value inside nested gfsearch shortcodes
	$nested_gfsearch_map = [];
	$masked_display      = $atts['display'];

	// Mask display attribute in [gfsearch ... display="..." or display='...']...[/gfsearch]
	$masked_display = preg_replace_callback(
    '/(\[gfsearch[^\]]*?\sdisplay=("|\')(.*?)(\2)[^\]]*\])/i',
    function ( $m ) use ( &$nested_gfsearch_map ) {
        $key                         = '__NESTED_GFSEARCH_DISPLAY_' . count( $nested_gfsearch_map ) . '__';
        $nested_gfsearch_map[ $key ] = $m[3];
        // Replace only the display value
        return str_replace( $m[3], $key, $m[0] );
    },
    $masked_display
	);

	// Updated regex: only match curly-brace {id}, {gfs:id}, {gfs:id;default} and plain gfs:id (not just numbers)
	$regex = '/{(gfs:)?([^{};]+)(;([^{}]+))?}|\bgfs:([0-9]+)\b/';
	preg_match_all( $regex, $masked_display, $matches, PREG_SET_ORDER );

	$display_ids  = [];
	$tag_defaults = [];

	if ( empty( $matches ) ) {
		$display_ids = array_map( 'sanitize_text_field', explode( ',', $masked_display ) );
		$display_ids = array_map( 'trim', $display_ids );
	} else {
		foreach ( $matches as $match ) {
			// If curly-brace format, use those capture groups
			if ( isset( $match[2] ) && '' !== $match[2] ) {
				$field_id = $match[2];
				if ( ! empty( $match[4] ) ) {
					$tag_defaults[ $field_id ] = $match[4];
				}
				$display_ids[] = sanitize_text_field( $field_id );
				// If plain gfs:id format
			} elseif ( isset( $match[5] ) && '' !== $match[5] ) {
				$field_id      = $match[5];
				$display_ids[] = sanitize_text_field( $field_id );
			}
		}
	}
	$display_ids = array_unique( $display_ids );

	$multi_input_present = false;

	// Parse default values
	$default_values = array_map( 'trim', explode( '||', $atts['default'] ) );
	$default_count  = count( $default_values );

	foreach ( $entries as $entry ) {
		$entry_results = [];
		foreach ( $display_ids as $index => $display_id ) {

			if ( 'meta' === $display_id ) {
				if ( ! empty( wp_kses_post( $atts['separator'] ) ) ) {
					$entry_results[ $display_id ] = implode( wp_kses_post( $atts['separator'] ), array_keys( $entry ) );
				} else {
					$entry_results[ $display_id ] = '<ul><li>' . implode( '</li><li>', array_keys( $entry ) ) . '</li></ul>';
				}
				continue;
			}
			if ( 'num_results' === $display_id ) {
				continue;
			}

			$field = GFAPI::get_field( $entry['form_id'], $display_id );
			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( $field && 'number' === $field->type ) {
				$field_value = GFCommon::format_number( $entry[ $display_id ], $field->numberFormat, $entry['currency'], true );
			} elseif ( $field && 'date' === $field->type ) {
				$field_value = GFCommon::date_display( $entry[ $display_id ], 'Y-m-d', $field->dateFormat );
			} elseif ( $field && is_multi_input_field( $field ) && ! str_contains( $display_id, '.' ) ) {
				$multi_input_present = true;
				$ids                 = array_column( $field['inputs'], 'id' );
				$field_results       = [];
				foreach ( $ids as $id ) {
					if ( ! empty( $entry[ $id ] ) ) {
						$field_results[] = $entry[ $id ];
					}
				}
					$field_value = implode( ' ', $field_results );
			} else {
				$field_value = $entry[ $display_id ] ?? '';
			}

			// Use default value if field value is empty
			if ( '' === $field_value || is_null( $field_value ) ) {
				// Check if there's a tag-specific default value for this field
				if ( isset( $tag_defaults[ $display_id ] ) ) {
					$field_value = wp_kses_post( $tag_defaults[ $display_id ] );
				} elseif ( 1 === $default_count ) { // Otherwise use the global default values
					// If there's only one default value, use it for all display values
					$field_value = wp_kses_post( $default_values[0] );
				} elseif ( $index < $default_count ) {
					// If there are multiple default values, use the corresponding one
					$field_value = wp_kses_post( $default_values[ $index ] );
				} else {
					$field_value = '';
				}
			}

			$entry_results[ $display_id ] = $field_value;
		}

		// We only need to filter if the default value is empty
		if ( '' === $atts['default'] || is_null( $atts['default'] ) ) {
			$entry_results = array_filter( $entry_results, fn( $value ) => '' !== $value && ! is_null( $value ) );
		}
		if ( ! empty( $matches ) ) {
			$display_format = wp_kses( $masked_display, $allowed_tags );
			foreach ( $display_ids as $index => $display_id ) {
				if ( 'num_results' === $display_id ) {
					continue;
				}

				$value = $entry_results[ $display_id ] ?? '';

				// If the value is empty and this is the first placeholder, use tag-specific default if available
				if ( ! $value && 0 === $index ) {
					if ( isset( $tag_defaults[ $display_id ] ) ) {
						$value = wp_kses_post( $tag_defaults[ $display_id ] );
					} else {
						$display_format = '';
						break;
					}
				}

				// Replace curly-brace formats first
				$display_format = str_replace( '{gfs:' . $display_id . '}', $value, $display_format );
				$display_format = str_replace( '{' . $display_id . '}', $value, $display_format );
				// Replace {gfs:id;default-value} format
				$pattern        = '/{gfs:' . preg_quote( $display_id, '/' ) . ';[^{}]+}/';
				$display_format = preg_replace( $pattern, $value, $display_format );
				$pattern        = '/{' . preg_quote( $display_id, '/' ) . ';[^{}]+}/';
				$display_format = preg_replace( $pattern, $value, $display_format );
				// Replace plain gfs:id only when not part of a larger word or attribute (not preceded/followed by [\w\.:])
				$display_format = preg_replace( '/(?<![\w\.:])gfs:' . preg_quote( $display_id, '/' ) . '(?![\w\.:])/', $value, $display_format );
			}
			// Restore masked display attributes in nested gfsearch
			if ( ! empty( $nested_gfsearch_map ) ) {
				$display_format = strtr( $display_format, $nested_gfsearch_map );
			}
			$result_text = $display_format;
			if ( $atts['link'] ) {
				$result_text = '<a target="_blank" href="' . admin_url( 'admin.php?page=gf_entries&view=entry&id=' . $entry['form_id'] . '&lid=' . $entry['id'] ) . '">' . $result_text . '</a>';
			}
			$results[] = $result_text;
		} else {
			$result_text = implode( ', ', $entry_results );
			if ( $atts['link'] ) {
				$result_text = '<a target="_blank"  href="' . admin_url( 'admin.php?page=gf_entries&view=entry&id=' . $entry['form_id'] . '&lid=' . $entry['id'] ) . '">' . $result_text . '</a>';
			}
			$results[] = $result_text;
		}
	}

	$results = array_map( 'trim', $results );
	$results = array_filter( $results, fn( $value ) => '' !== $value && ! is_null( $value ) );

	if ( empty( $results ) ) {
		// If default contains multiple values, use the first one
		$default_values = array_map( 'trim', explode( '||', $atts['default'] ) );
		return wp_kses_post( $default_values[0] ?? '' );
	}

	if ( empty( $atts['separator'] ) ) {
		$separator = ( count( $display_ids ) > 1 || $multi_input_present ) ? '; ' : ', ';
	} elseif ( strtolower( '__none__' ) === $atts['separator'] ) {
		$separator = '';
	} else {
		$separator = wp_kses_post( $atts['separator'] );
	}

	// Process shortcodes first, then apply uniqueness to the final output
	$final_results = array_map(
	function ( $result ) use ( $allowed_tags ) {
		return wp_kses( do_shortcode( $result ), $allowed_tags );
	},
	$results
	);

	if ( $atts['unique'] ) {
		$final_results = array_unique( $final_results );
	}

	$final_results = array_map(
	function ( $result ) use ( $final_results ) {
		return str_replace( '{gfs:num_results}', count( $final_results ), $result );
	},
	$final_results
        );

	return implode( $separator, $final_results );
}

/**
 * Determines if a given field is a multi-input field.
 *
 * @param mixed $field The field configuration array. Expected to contain 'type' and optionally 'inputType' keys.
 *
 * @return bool True if the field is a multi-input field, false otherwise.
 */
function is_multi_input_field( $field ): bool {
	return 'name' === $field['type'] || 'address' === $field['type'] || 'checkbox' === $field['type'] || ( ( 'image_choice' === $field['type'] || 'multi_choice' === $field['type'] ) && 'checkbox' === $field['inputType'] );
}

/**
 * Converts custom curly bracket shortcodes into standard WordPress-style shortcodes.
 *
 * Converts content with shortcodes in the format `{{shortcode attributes}}content{{/shortcode}}`
 * to `[shortcode attributes]content[/shortcode]`. Handles standalone shortcodes and unmatched closing tags.
 *
 * @param string $content The content containing curly bracket shortcodes.
 *
 * @return string The converted content with standard WordPress-style shortcodes.
 */
function convert_curly_shortcodes( $content ) {
	while ( preg_match( '/\{\{(\w+)\b(.*?)\}\}/s', $content, $open_match, PREG_OFFSET_CAPTURE ) ) {
		$tag       = $open_match[1][0];
		$attrs     = $open_match[2][0];
		$start_pos = $open_match[0][1];
		$end_tag   = '{{/' . $tag . '}}';
		$end_pos   = strpos( $content, $end_tag, $start_pos );

		if ( false === $end_pos ) {
			break; // malformed shortcode
		}

		$open_len = strlen( $open_match[0][0] );
		$inner    = substr( $content, $start_pos + $open_len, $end_pos - $start_pos - $open_len );

		$replacement = '[' . $tag . $attrs . ']' . $inner . '[/' . $tag . ']';
		$content     = substr_replace( $content, $replacement, $start_pos, $end_pos + strlen( $end_tag ) - $start_pos );
	}

	// Handle standalone shortcodes like {{shortcode attr=...}} → [shortcode attr=...]
	$content = preg_replace_callback(
		'/\{\{(?!\/)([^\{\}\/]+?)\s*\}\}/',
		fn( $m ) => '[' . $m[1] . ']',
		$content
	);

	// Handle unmatched closing tags {{/shortcode}} → [/shortcode]
	return preg_replace( '/\{\{\/(\w+)\s*\}\}/', '[/$1]', $content );
}
