# GFSearch
A powerful and flexible Gravity Forms search and display shortcode for WordPress.

## 📝 Description
The **GFSearch** shortcode enables advanced searching and displaying of Gravity Forms entries on your posts, pages, 
Gravity Views, or custom templates. It allows filtering results, sorting entries, custom formatting, and much more—all 
tailored to your exact needs.

## ✨ Key Features
- **Form Targeting:** Search entries across all forms, specific forms, or selected forms via IDs.
- **Field Filtering:** Search or display multiple fields simultaneously using field IDs and corresponding values.
- **Custom Formatting:** Customize output using placeholders and HTML formatting, supporting field data and meta properties.
- **Sorting Options:** Primary and secondary sorting by ascending, descending, or random order.
- **Comparison Filters:** Filter numeric fields by values (`greater_than`, `less_than`). 
- **Global Search:** Search all form fields for specific values easily.
- **Unique Results:** Eliminate duplicate entries by enabling the `unique` option.
- **Advanced Search Modes:** Match any condition (`search_mode="any"`) or all conditions (default).
- **HTML Supported in Display:** Fully supports HTML in `display` and `separator` attributes, e.g., `<li>` for lists.
- **Search for Empty Fields:** Identify and display entries with missing field data.
- **Entry Linking:** Convert results into links to entry admin panel views.

## 🚀 Usage
To use the shortcode, embed in your content areas with relevant attributes, e.g.: `[gfsearch]`
``` markdown
[gfsearch target="1" search="13,14" display="Name: {13}, Email: {14}" limit="5" separator="<br>" default="No results found"]
John|john@example.com
[/gfsearch]
```
### Attributes Overview

| **Attribute**                  | **Description**                                                                                                                                                                              | **Default**     |
|--------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------|
| **`target`**                   | Specify forms to search: `0` for all forms, or comma-separated list of form IDs (e.g., `target="1,2"`).                                                                                      | (all forms) `0` |
| **`search`**                   | Field IDs for filtering entries. Separate multiple IDs by a comma (`search="13,14"`).                                                                                                        | _(None)_        |
| **`display`**                  | Format results with placeholders, e.g., `display="Result: {13}"`. Supports HTML tags such as `<a>`, `<ul>`, `<li>`, `<table>`. **Warning:** Avoid mixing quotes to prevent shortcode errors. | _(Required)_    |
| **`search_mode`**              | Match all conditions (`all`, default) or any condition (`any`).                                                                                                                              | `all`           |
| **`greater_than`**             | Filter numeric values greater than a threshold, e.g., `greater_than="4, 1000"`.                                                                                                              | _(None)_        |
| **`less_than`**                | Filter numeric values less than a threshold, e.g., `less_than="6, 50"`.                                                                                                                      | _(None)_        |
| **`sort_key`**                 | Field/property to sort entries (e.g., field ID or meta key).                                                                                                                                 | `id` (entry ID) |
| **`sort_direction`**           | Sorting direction: `ASC`, `DESC`, or `RAND`.                                                                                                                                                 | `DESC`          |
| **`sort_is_num`**              | Indicates if sorting is numeric (true/false).                                                                                                                                                | `true`          |
| **`secondary_sort_key`**       | Secondary sorting field (if needed).                                                                                                                                                         | (empty)         |
| **`secondary_sort_direction`** | Sorting direction for the secondary sort (ASC, DESC).                                                                                                                                        | `DESC`          |
| **`unique`**                   | Display only unique values in the results.                                                                                                                                                   | `false`         |
| **`limit`**                    | Number of results to display. Use `limit="all"` to display all entries.                                                                                                                      | `1`             |
| **`separator`**                | Separator between results (supports HTML).                                                                                                                                                   | _(Varies)_      |
| **`search_empty`**             | Search for fields with empty/blank values.                                                                                                                                                   | `false`         |
| **`default`**                  | Default text to display if no results match search criteria.                                                                                                                                 | _(Blank)_       |
| **`link`**                     | Makes results clickable links to admin entry details.                                                                                                                                        | `false`         |


### 🧩 Examples
#### Example 1: Search and Display Multiple Fields
``` markdown
[gfsearch target="2" search="name,email" display="Name: {13}, Email: {14}" search_mode="all" limit="10"]
John|john@example.com
[/gfsearch]
```
#### Example 2: Display Unique Results with Links
``` markdown
[gfsearch target="3" display="Unique Entry: {created_by}" unique="true" link="true"]
```
#### Example 3: Global Search with Custom HTML
``` markdown
[gfsearch target="0" display="<li>ID: {id}, Value: {13}" separator="</li>"]
```
Wrap the above shortcode within `<ul>` tags:
``` html
<ul>
  [gfsearch target="0" display="<li>ID: {id}, Value: {13}" separator="</li>"]
</ul>
```
## ❗ Notes and Best Practices
- Always ensure placeholders (e.g., `{13}`) match the field IDs or entry properties.
- Use prefix for non-numeric keys when used alongside **Gravity View**. `{gfs:id}`
- Avoid using double quotes (`"`) inside the `display` attribute if your shortcode is wrapped with double quotes—use single quotes (`'`) instead, and vice versa.

## 📜 License
This plugin is licensed under **GPLv2 or later**.
## 🔗 Links
- Plugin Repo: [GitHub - Eitan-brightleaf/gfsearch](https://github.com/Eitan-brightleaf/gfsearch)
- Gravity Forms Documentation: [Gravity Forms Entry Object](https://docs.gravityforms.com/entry-object/)
