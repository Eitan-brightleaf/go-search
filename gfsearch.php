<?php
/**
 * Plugin Name: gfsearch
 * Description: A shortcode to search and display Gravity Forms entries based on specified criteria and attributes.
 * Version: 1.3.0
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
 * The shortcode supports the following attributes:
 * - target: Specify forms to search (0 for all forms, or comma-separated list of form IDs)
 * - search: Field IDs/entry properties for filtering entries
 * - operators: Operators that correspond to each field in the search attribute
 * - ds: Display string with placeholders (gfs.1, gfs.2, etc.)
 * - dp: Display placeholders (field IDs to display)
 * - sort_key: Field/property to sort entries
 * - sort_direction: Sorting direction (ASC, DESC, or RAND)
 * - sort_is_num: Indicates if sorting is numeric (true/false)
 * - secondary_sort_key: Secondary sorting field
 * - secondary_sort_direction: Sorting direction for the secondary sort
 * - unique: Display only unique values in the results
 * - limit: Number of results to display
 * - search_mode: Match all conditions (all) or any condition (any)
 * - search_empty: Search for fields with empty/blank values
 * - default: Default text to display if no results match search criteria
 * - link: Makes results clickable links to admin entry details
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
			'ds'                       => '',  // display string with placeholders
			'dp'                       => '',  // display placeholders
			'sort_key'                 => 'id',
			'sort_direction'           => 'DESC',
			'sort_is_num'              => true,
			'secondary_sort_key'       => '',
			'secondary_sort_direction' => 'DESC',
			'unique'                   => false,
			'limit'                    => '1',
			'search_mode'              => 'all',
			'search_empty'             => false,
			'default'                  => '',
			'link'                     => false,
		],
		$atts,
		'gfsearch'
	);

	if ( ! empty( $atts['search'] ) && empty( $atts['ds'] ) && empty( $atts['dp'] ) && ! $atts['search_empty'] ) {
		return '';
	}

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

	$form_ids   = array_map( 'intval', explode( ',', $atts['target'] ) );
	$form_count = count( $form_ids );

	$search_criteria                  = [];
	$search_criteria['status']        = 'active';
	$search_criteria['field_filters'] = [];

	$search_modes = array_map( 'trim', explode( ',', $atts['search_mode'] ) );

	// convert search attribute to array of arrays
	if ( str_contains( $atts['search'], 'array(' ) ) {
		$json_string    = '[' . str_replace( [ 'array(', ')', "'" ], [ '[', ']', '"' ], $atts['search'] ) . ']';
		$atts['search'] = json_decode( $json_string, true );

	} else {
		$atts['search'] = [ explode( ',', $atts['search'] ) ];
	}

	// Validate that the number of search arrays matches the number of form IDs
	// Only check if there are multiple forms and search arrays
	if ( $form_count > 1 && count( $atts['search'] ) > 1 && count( $atts['search'] ) !== $form_count ) {
		return 'Error: The number of search arrays (' . count( $atts['search'] ) . ') does not match the number of form IDs (' . $form_count . ').';
	}

	$search_ids = array_map(
		fn( $search_id_group ) => array_map(
			fn( $search_id ) => trim( GFCommon::replace_variables( sanitize_text_field( $search_id ), [], [] ) ),
			$search_id_group
		),
		$atts['search']
	);

	// content
	if ( str_contains( $content, 'array(' ) ) {
		$json_string = '[' . str_replace( [ 'array(', ')', "'" ], [ '[', ']', '"' ], $content ) . ']';
	} else {
		$json_string = '[[' . str_replace( "'", '"', $content ) . ']]';
	}
	$content_values = json_decode( $json_string, true );

	// Validate that the number of content arrays matches the number of form IDs
	// Only check if there are multiple forms and content arrays
	if ( $form_count > 1 && count( $content_values ) > 1 && count( $content_values ) !== $form_count ) {
		return 'Error: The number of content arrays (' . count( $content_values ) . ') does not match the number of form IDs (' . $form_count . ').';
	}

	$content_values = array_map(
		fn( $value_group ) => array_map(
			fn ( $value ) => trim( GFCommon::replace_variables( sanitize_text_field( $value ), [], [] ) ),
			$value_group
		),
		$content_values
	);

	// Parse operators if provided
	$operators = [];
	if ( ! empty( $atts['operators'] ) ) {
		// convert operators attribute to array of arrays
		if ( str_contains( $atts['operators'], 'array(' ) ) {
			$json_string = '[' . str_replace( [ 'array(', ')', "'" ], [ '[', ']', '"' ], $atts['operators'] ) . ']';
			$operators   = json_decode( $json_string, true );
		} else {
			// Support non-array format by converting to array format
			$operators = [ array_map( 'trim', explode( ',', $atts['operators'] ) ) ];
			$operators = [ array_map( 'sanitize_text_field', $operators[0] ) ];
		}

		// Validate that the number of operator arrays matches the number of form IDs
		// Only check if there are multiple forms and operator arrays
		if ( $form_count > 1 && count( $operators ) > 1 && count( $operators ) !== $form_count ) {
			return 'Error: The number of operator arrays (' . count( $operators ) . ') does not match the number of form IDs (' . $form_count . ').';
		}
	}

	foreach ( $search_ids as $index => $search_id ) {
		if ( empty( $search_id ) ) {
			continue;
		}
		$current_field = GFAPI::get_field( $form_id[0], $search_id );
		if ( $current_field && 'number' === $current_field['type'] ) {
			$content_values[ $index ] = str_replace( ',', '', $content_values[ $index ] );
		}

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

			if ( in_array( $operators[ $index ], $supported_operators, true ) ) {
                $operators[ $index ]      = str_replace( 'gt', '>', $operators[ $index ] );
                $operators[ $index ]      = str_replace( 'lt', '<', $operators[ $index ] );
				$field_filter['operator'] = $operators[ $index ];
			}
		}

		$search_criteria['field_filters'][] = $field_filter;
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

	// Process display attributes (ds and dp)

	// Initialize display variables
	$display_ids     = [];
	$tag_defaults    = [];
	$display_strings = [];

	// Process ds (display string) attribute
	if ( ! empty( $atts['ds'] ) ) {
		// Convert ds attribute to array of strings if it's in array format
		if ( str_contains( $atts['ds'], 'array(' ) ) {
			$json_string = '[' . str_replace( [ 'array(', ')', "'" ], [ '[', ']', '"' ], $atts['ds'] ) . ']';
			$atts['ds']  = json_decode( $json_string, true );

			// Validate that the number of ds arrays matches the number of form IDs
			if ( $form_count > 1 && count( $atts['ds'] ) > 1 && count( $atts['ds'] ) !== $form_count ) {
				return 'Error: The number of display string arrays (' . count( $atts['ds'] ) . ') does not match the number of form IDs (' . $form_count . ').';
			}
		} else {
			// Support non-array format by converting to array format
			$atts['ds'] = [ $atts['ds'] ];
		}

		// Apply convert_curly_shortcodes to each ds string
		$atts['ds']      = array_map( 'convert_curly_shortcodes', $atts['ds'] );
		$display_strings = $atts['ds'];
	}

	// Process dp (display placeholders) attribute
	if ( ! empty( $atts['dp'] ) ) {
		// Convert dp attribute to array of arrays if it's in array format
		if ( str_contains( $atts['dp'], 'array(' ) ) {
			$json_string = '[' . str_replace( [ 'array(', ')', "'" ], [ '[', ']', '"' ], $atts['dp'] ) . ']';
			$atts['dp']  = json_decode( $json_string, true );

			// Validate that the number of dp arrays matches the number of form IDs
			if ( $form_count > 1 && count( $atts['dp'] ) > 1 && count( $atts['dp'] ) !== $form_count ) {
				return 'Error: The number of display placeholder arrays (' . count( $atts['dp'] ) . ') does not match the number of form IDs (' . $form_count . ').';
			}
		} else {
			// Support non-array format by converting to array format
			$atts['dp'] = [ explode( ',', $atts['dp'] ) ];
			$atts['dp'] = [ array_map( 'trim', $atts['dp'][0] ) ];
		}

		// Use the first form's placeholders by default
		$display_ids = array_map( 'sanitize_text_field', $atts['dp'][0] );

		// Extract any default values from placeholders with format {id;default}
		foreach ( $display_ids as $display_id ) {
			if ( preg_match( '/{(gfs:)?([^{};]+)(;([^{}]+))?}/', $display_id, $match ) ) {
				if ( ! empty( $match[4] ) ) {
					$tag_defaults[ $match[2] ] = $match[4];
				}
			}
		}
	}

	$multi_input_present = false;

	// Parse default values
	$default_values = array_map( 'trim', explode( '||', $atts['default'] ) );
	$default_count  = count( $default_values );

	foreach ( $entries as $entry ) {
		// Determine which form's display format to use
		$form_index = 0;
		if ( $form_count > 1 ) {
			// Find the index of the current entry's form_id in the form_id array
			$form_index = array_search( $entry['form_id'], $form_id, true );
			if ( false === $form_index ) {
				$form_index = 0; // Default to first form if not found
			}
		}

		// Get the display IDs for this form
		$current_display_ids = $display_ids;
		if ( ! empty( $atts['dp'] ) && isset( $atts['dp'][ $form_index ] ) ) {
			$current_display_ids = array_map( 'sanitize_text_field', $atts['dp'][ $form_index ] );
		}

		$entry_results = [];
		foreach ( $current_display_ids as $index => $display_id ) {
			// Handle special placeholders
			if ( 'meta' === $display_id ) {
				// Use default list format for meta data
				$entry_results[ $display_id ] = '<ul><li>' . implode( '</li><li>', array_keys( $entry ) ) . '</li></ul>';
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
                if ( '' === $field_value ) {
                    $temp = GFCommon::replace_variables( '{' . $display_id . '}', GFAPI::get_form( $entry['form_id'] ), $entry );
                    if ( '{' . $display_id . '}' !== $temp ) {
                        $field_value = $temp;
                    }
                }
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

		// Get the appropriate display string for this form
		$display_string = $display_strings[ $form_index ] ?? $display_strings[0];

		// Process the display string with the new placeholder format (gfs.1, gfs.2, etc.)
		$display_format = wp_kses( $display_string, $allowed_tags );

		// Replace placeholders in the format gfs.1, gfs.2, etc.
		foreach ( $current_display_ids as $index => $display_id ) {
			if ( 'num_results' === $display_id ) {
				continue;
			}

			$value = $entry_results[ $display_id ] ?? '';

			// If the value is empty and this is the first placeholder, use default if available
			if ( ! $value && 0 === $index ) {
				if ( isset( $tag_defaults[ $display_id ] ) ) {
					$value = wp_kses_post( $tag_defaults[ $display_id ] );
				} else {
					$display_format = '';
					break;
				}
			}

			// Replace new format placeholders (gfs.1, gfs.2, etc.)
			$placeholder_index = $index + 1; // 1-based indexing for placeholders
			$display_format    = preg_replace( '/([\'"])gfs\.' . $placeholder_index . '\\1/', $value, $display_format );

			// Also support the legacy format for backward compatibility
			$display_format = str_replace( '{gfs:' . $display_id . '}', $value, $display_format );
			$display_format = str_replace( '{' . $display_id . '}', $value, $display_format );
			$pattern        = '/{gfs:' . preg_quote( $display_id, '/' ) . ';[^{}]+}/';
			$display_format = preg_replace( $pattern, $value, $display_format );
			$pattern        = '/{' . preg_quote( $display_id, '/' ) . ';[^{}]+}/';
			$display_format = preg_replace( $pattern, $value, $display_format );
			$display_format = preg_replace( '/(?<![\w\.:])gfs:' . preg_quote( $display_id, '/' ) . '(?![\w\.:])/', $value, $display_format );
		}

		$result_text = $display_format;

		// Add link if requested
		if ( $atts['link'] ) {
			$result_text = '<a target="_blank" href="' . admin_url( 'admin.php?page=gf_entries&view=entry&id=' . $entry['form_id'] . '&lid=' . $entry['id'] ) . '">' . $result_text . '</a>';
		}

		$results[] = $result_text;
	}

	$results = array_map( 'trim', $results );
	$results = array_filter( $results, fn( $value ) => '' !== $value && ! is_null( $value ) );

	if ( empty( $results ) ) {
		// If default contains multiple values, use the first one
		$default_values = array_map( 'trim', explode( '||', $atts['default'] ) );
		return wp_kses_post( $default_values[0] ?? '' );
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

	return implode( '', $final_results );
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
