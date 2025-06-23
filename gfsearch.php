<?php
/**
 * Plugin Name: gfsearch
 * Plugin URI: https://github.com/Eitan-brightleaf/gfsearch
 * Description: A shortcode to search and display Gravity Forms entries based on specified criteria and attributes.
 * Version: 1.0.2
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
	$allowed_tags['a'] = [
		'href'   => true,
		'title'  => true,
		'target' => true,
		'rel'    => true,
		'class'  => true,
		'id'     => true,
		'style'  => true,
	];

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

	$content_values = array_map( 'trim', explode( '|', $content ) );

	foreach ( $search_ids as $index => $search_id ) {
		if ( empty( $search_id ) ) {
			continue;
		}
		$current_field = GFAPI::get_field( $form_id[0], $search_id );
		if ( $current_field && 'number' === $current_field['type'] ) {
			$content_values[ $index ] = str_replace( ',', '', $content_values[ $index ] );
		}
		$search_criteria['field_filters'][] = [
			'key'   => $search_id,
			'value' => GFCommon::replace_variables( $content_values[ $index ], [], [] ),
		];
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
			$paging_offset += 25;
			$paging         = [
				'offset'    => $paging_offset,
				'page_size' => 25,
			];
			$new_entries    = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );
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

	if ( $atts['greater_than'] ) {
		$greater_than = array_map( 'trim', explode( ',', $atts['greater_than'] ) );
		$entries      = array_filter(
            $entries,
            function ( $entry ) use ( $greater_than ) {
				if ( $entry[ intval( $greater_than[0] ) ] > floatval( $greater_than[1] ) ) {
					return true;
				}
				return false;
			}
            );
	}
	if ( $atts['less_than'] ) {
		$less_than = array_map( 'trim', explode( ',', $atts['less_than'] ) );
		$entries   = array_filter(
            $entries,
            function ( $entry ) use ( $less_than ) {
				if ( $entry[ intval( $less_than[0] ) ] < floatval( $less_than[1] ) ) {
					return true;
				}
				return false;
			}
            );
	}

	$results = [];

	$regex = '/{(gfs:)?([^{};]+)(;([^{}]+))?}/';
	preg_match_all( $regex, $atts['display'], $matches );

	if ( empty( $matches[0] ) ) {
		$display_ids  = array_map( 'sanitize_text_field', explode( ',', $atts['display'] ) );
		$display_ids  = array_map( 'trim', $display_ids );
		$tag_defaults = [];
	} else {
		// Extract the actual IDs and default values, removing the prefix if present
		$display_ids  = [];
		$tag_defaults = [];

		foreach ( $matches[0] as $index => $match ) {
			// Get the field ID
			$field_id = $matches[2][ $index ];

			// Store the default value if present
			if ( ! empty( $matches[4][ $index ] ) ) {
				$tag_defaults[ $field_id ] = $matches[4][ $index ];
			}

			$display_ids[] = sanitize_text_field( $field_id );
		}
	}

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

			$field = GFAPI::get_field( $entry['form_id'], $display_id );
			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( 'number' === $field->type ) {
				$field_value = GFCommon::format_number( $entry[ $display_id ], $field->numberFormat, $entry['currency'], true );
			} elseif ( 'date' === $field->type ) {
				$field_value = GFCommon::date_display( $entry[ $display_id ], 'Y-m-d', $field->dateFormat );
			} elseif ( is_multi_input_field( $field ) ) {
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
				$field_value = $entry[ $display_id ];
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
		if ( ! empty( $matches[0] ) ) {
			$display_format = wp_kses( $atts['display'], $allowed_tags );
			foreach ( $display_ids as $display_id ) {
				// Replace all formats with the value: {id}, {gfs:id}, and {gfs:id;default-value}
				// If the field was filtered out (because default was empty), use empty string
				$value = $entry_results[ $display_id ] ?? '';

				// Replace simple {id} format
				$display_format = str_replace( '{' . $display_id . '}', $value, $display_format );

				// Replace {gfs:id} format
				$display_format = str_replace( '{gfs:' . $display_id . '}', $value, $display_format );

				// Replace {gfs:id;default-value} format
				$pattern        = '/{(gfs:)?' . preg_quote( $display_id, '/' ) . ';[^{}]+}/';
				$display_format = preg_replace( $pattern, $value, $display_format );
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

	if ( $atts['unique'] ) {
		$results = array_unique( $results );
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
	} else {
		$separator = wp_kses_post( $atts['separator'] );
	}

	return wp_kses( implode( $separator, $results ), $allowed_tags );
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
