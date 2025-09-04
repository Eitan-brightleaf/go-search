=== gfsearch ===
Contributors: brightleafdigital
Tags: gravity forms, search, shortcode, entries, form data
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.4
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A shortcode to search and display Gravity Forms entries based on specified criteria and attributes.

== Description ==

GFSearch provides a powerful shortcode `[gfsearch]` that allows you to search and display Gravity Forms entries based on custom criteria. This plugin extends the Gravity Forms functionality by enabling frontend display of form entries with advanced filtering, sorting, and formatting options.

= Key Features =

* Search Gravity Forms entries using custom criteria
* Display specific fields from entries
* Support for multiple search operators (is, contains, like, in, not in, etc.)
* Advanced sorting options with primary and secondary sort keys
* Customizable output formatting with placeholders
* Support for numeric comparison operators (greater than, less than)
* Unique result filtering
* Default values and fallback content
* Link entries directly to admin view

= Shortcode Usage =

Basic usage:
`[gfsearch target="1" search="2" display="1,2"]Search Value[/gfsearch]`

Advanced usage with operators:
`[gfsearch target="1" search="2,3" operators="contains,=" display="1,2,3" limit="10" sort_key="date_created" sort_direction="DESC"]Value1|Value2[/gfsearch]`

= Supported Attributes =

* `target` - Form ID(s) to search (comma-separated)
* `search` - Field ID(s) to search in (comma-separated)
* `operators` - Search operators (=, is, contains, like, in, not in, etc.)
* `display` - Field ID(s) to display (comma-separated)
* `limit` - Number of results to return (default: 1, use "all" for unlimited)
* `sort_key` - Field to sort by (default: id)
* `sort_direction` - Sort direction: ASC, DESC, or RAND (default: DESC)
* `unique` - Remove duplicate results (true/false)
* `search_mode` - Search mode: "all" or "any" (default: all)
* `default` - Default value when no results found
* `separator` - Custom separator for multiple values
* `link` - Add admin link to entries (true/false)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/gfsearch` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Make sure you have Gravity Forms installed and activated.
4. Use the `[gfsearch]` shortcode in your posts, pages, or widgets.

== Frequently Asked Questions ==

= Does this plugin require Gravity Forms? =

Yes, this plugin extends Gravity Forms functionality and requires Gravity Forms to be installed and activated.

= Can I search multiple forms at once? =

Yes, you can specify multiple form IDs in the `target` attribute separated by commas.

= What search operators are supported? =

The plugin supports: =, is, is not, isnot, !=, contains, like, not in, notin, in, lt (less than), gt (greater than), lt=, gt=

= Can I display the search results in a custom format? =

Yes, you can use field placeholders in the `display` attribute like `{1}`, `{gfs:2}`, or `{gfs:3;default-value}` for custom formatting.

= How do I limit the number of results? =

Use the `limit` attribute. Set it to a number for a specific limit, or "all" for unlimited results.