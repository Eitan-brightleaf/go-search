# GFSearch

A flexible Gravity Forms search and display shortcode for WordPress.

## Description

GFSearch provides a powerful shortcode for searching and displaying Gravity Forms entries. It supports searching across all forms or specific forms, multiple fields, custom formatting, sorting, filtering, and more.

## Usage

Add the `[gfsearch]` shortcode to your posts or pages with various attributes to customize search and display behavior.

### Key Features

- **Target Forms:** Search all forms (`target="0"`), a specific form ID, or a comma-separated list of form IDs.
- **Multiple Fields:** Search or display multiple fields by passing comma-separated IDs to `search` and `display` attributes. Provide corresponding values separated by `|` in the shortcode content.
- **Custom Formatting:** Use the `display` attribute with curly braces for placeholders (e.g., `display="Field: {13}, User: {created_by}"`). Supports entry properties and field IDs. See [Gravity Forms Entry Object](https://docs.gravityforms.com/entry-object/).
- **Gravity View Compatibility:** Prefix non-numeric keys with `gfs:` (e.g., `{gfs:id}`) to avoid merge tag conflicts.
- **Search Modes:** Use `search_mode="any"` to match any condition, or default to all conditions.
- **Global Search:** Leave a search ID blank to search any field for the specified value.
- **Multiple Values for One Field:** Repeat the field in `search`, separate values by comma in content, and set `search_mode="any"`.
- **Comparison Filters:** Use `greater_than` or `less_than` (e.g., `greater_than="4, 500"`).
- **Sorting:** Use `sort_key`, `sort_direction` (`ASC`, `DESC`, `RAND`), and `sort_is_num` (`true`/`false`). Secondary sorting available.
- **Unique Results:** Use `unique` attribute to return only unique values.
- **Limit Results:** Use `limit` (number or `all`).
- **Separator:** Use `separator` (e.g., `separator="<br>"`).
- **Search for Empty Values:** Use `search_empty` with a blank shortcode content.
- **Default Value:** Use `default` to display when no results are found or for blank values.
- **Link to Entries:** Use `link` to wrap results in a link to the entry in the admin panel.

## Instructions

- **Targeting Forms:**  
    Use `target="0"` to search all forms, a specific form ID, or a comma-separated list of form IDs to search specific forms.

- **Multiple Fields:**  
    Pass multiple IDs to the `search` and `display` attributes, separated by commas, to search or display multiple fields. When searching for multiple fields, provide the corresponding values as the shortcode content, separated by a `|` symbol. Ensure the number and order of values matches the fields being searched.

- **Custom Formatting:**  
    For custom display formatting, configure the `display` attribute with your desired format and wrap each entry property in curly braces (e.g., `display="Text before: {13}, more: {14}, last: {15}"`). Each placeholder (e.g., `{13}`) will be replaced by the correct value. Avoid characters that could break the shortcode, such as `"` or `[]`. Limited HTML is allowed. Any entry property key can be used as a placeholder (e.g., `{id}`, `{created_by}`, `{13}`). See [Gravity Forms Entry Object](https://docs.gravityforms.com/entry-object/).

- **Gravity View Compatibility:**  
    When using with Gravity View, prefix non-numeric keys with `gfs:` (e.g., `{gfs:id}`) to prevent Gravity View from parsing them as merge tags. Both `{id}` and `{gfs:id}` formats are supported.

- **Search and Display Fields:**  
    The `search` and `display` fields can be a field ID, entry property, or entry meta key.

- **Search Modes:**  
    To search for multiple values in the same entry, use the `search_mode` attribute. Default is all conditions; use `search_mode="any"` to match any condition.

- **Global Search:**  
    To perform a global search for any field with a specified value, leave the corresponding search ID blank.

- **Display Only:**  
    To display values from a field without searching, omit the `search` attribute and shortcode content.

- **Multiple Values for One Field:**  
    To search for multiple values for one field, enter the field multiple times in the `search` attribute, separate values by commas in the shortcode content, and set `search_mode="any"`.

- **Comparison Filters:**  
    Use `greater_than` or `less_than` attributes to filter by numeric value. Format: `greater_than="4, 500"` filters out entries where field 4 is less than 500.

- **Sorting:**  
    Use `sort_key`, `sort_direction` (`ASC`, `DESC`, `RAND`), and `sort_is_num` (`true`/`false`). For secondary sorting, use `secondary_sort_key` and `secondary_sort_direction`. Secondary sorting is ignored if primary sort direction is `RAND`.

- **Unique Results:**  
    Use the `unique` attribute with any non-empty value (except `0` or empty string) to return only unique values.

- **Limit Results:**  
    Use the `limit` attribute to specify the number of results. Use `limit="all"` to display all results. If the number exceeds available results, all are returned. Default is one result.

- **Separator:**  
    Specify the separator between results with the `separator` attribute (e.g., `separator="<br>"`). Limited HTML is allowed.

- **Search for Empty Values:**  
    To search for empty values, leave the shortcode content blank and use the `search_empty` attribute with any non-empty value.

- **Default Value:**  
    Use the `default` attribute to specify a value to display when no results are found or for blank values within entries.

- **Link to Entries:**  
    Use the `link` attribute with any non-empty value to wrap each result in a link to the entry view page in the WordPress admin.

## Example

```markdown
[gfsearch target="1" search="13,14" display="Name: {13}, Email: {14}" search_mode="all" limit="5" separator="<br>" default="No results found"]
John|john@example.com
[/gfsearch]
```

## Notes

- Field IDs, entry properties, or meta keys can be used in `search` and `display`.
- Limited HTML is allowed in `display` and `separator`.
- For Gravity View, use `{gfs:key}` format for non-numeric keys.

## License

GPLv2 or later.