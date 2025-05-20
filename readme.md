# GFSearch
A powerful and flexible Gravity Forms search and display shortcode for WordPress.

## 📝 Description
The **GFSearch** shortcode enables advanced searching and displaying of Gravity Forms entries on your posts, pages, 
Gravity Views, or custom templates. It functions similarly to an Excel **VLOOKUP**, allows filtering results, sorting entries, custom formatting, and much more—all 
tailored to your exact needs.

## ✨ Key Features
- **Form Targeting:** Search entries across all forms, specific forms, or selected forms via IDs.
- **Field Filtering:** Search or display multiple fields simultaneously using field IDs and corresponding values.
- **Custom Formatting:** Customize output using placeholders and HTML formatting, supporting field data and meta-properties.
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
[gfsearch target="1" search="13,14" display="13, 14" limit="5" separator="<br>" default="No results found"]
John|john@example.com
[/gfsearch]
```
### Attributes Overview

| **Attribute**                  | **Description**                                                                                                                   | **Default**     |
|--------------------------------|-----------------------------------------------------------------------------------------------------------------------------------|-----------------|
| **`target`**                   | Specify forms to search: `0` for all forms, or comma-separated list of form IDs (e.g., `target="1,2"`).                           | (all forms) `0` |
| **`search`**                   | Field IDs/entry properties for filtering entries. Separate multiple IDs by a comma (`search="13,14"`).                            | _(None)_        |
| **`display`**                  | Comma seperated list of field IDs/entry properties to display. Also allows formating results with placeholders.                   | _(Required)_    |
| **`search_mode`**              | Match all conditions (`all`, default) or any condition (`any`).                                                                   | `all`           |
| **`greater_than`**             | Filter numeric values greater than a threshold, e.g., `greater_than="4, 1000"` where 4 is the field ID and 1000 is the threshold. | _(None)_        |
| **`less_than`**                | Filter numeric values less than a threshold, e.g., `less_than="6, 50"` where 6 is the field ID and 50 is the threshold.           | _(None)_        |
| **`sort_key`**                 | Field/property to sort entries (e.g., field ID or meta key).                                                                      | `id` (entry ID) |
| **`sort_direction`**           | Sorting direction: `ASC`, `DESC`, or `RAND`.                                                                                      | `DESC`          |
| **`sort_is_num`**              | Indicates if sorting is numeric (true/false).                                                                                     | `true`          |
| **`secondary_sort_key`**       | Secondary sorting field (if needed).                                                                                              | (empty)         |
| **`secondary_sort_direction`** | Sorting direction for the secondary sort (ASC, DESC).                                                                             | `DESC`          |
| **`unique`**                   | Display only unique values in the results.                                                                                        | `false`         |
| **`limit`**                    | Number of results to display. Use `limit="all"` to display all entries.                                                           | `1`             |
| **`separator`**                | Separator between results (supports HTML).                                                                                        | _(Varies)_      |
| **`search_empty`**             | Search for fields with empty/blank values.                                                                                        | `false`         |
| **`default`**                  | Default text to display if no results match search criteria.                                                                      | _(Blank)_       |
| **`link`**                     | Makes results clickable links to admin entry details.                                                                             | `false`         |


### 🧩 Examples
#### Example 1: Search and Display Multiple Fields
```markdown
[gfsearch target="2" search="name,email" display="Name: {13}, Email: {14}" search_mode="all" limit="10"]
John|john@example.com
[/gfsearch]
```
#### Example 2: Display Unique Results with Links
```markdown
[gfsearch target="3" display="Unique Entry: {created_by}" unique="true" link="true"]
```
#### Example 3: Global Search with Custom HTML
```markdown
[gfsearch target="0" display="<li>ID: {id}, Value: {13}" separator="</li>"]
```
Wrap the above shortcode within `<ul>` tags:
```html
<ul>
  [gfsearch target="0" display="<li>ID: {id}, Value: {13}" separator="</li>"]
</ul>
```
#### Example 4: Creating an HTML table with links
```html
  <table>
    <thead>
      <tr><th>Name</th><th>Email</th><th>Link</th></tr>
    </thead>
    <tbody>
    [gfsearch target="1" search="3,5" display="<tr><td>{1}</td><td><a href='mailto:{2}'>{2}</a></td><td><a href='{6}&query={10}'>{6}&query={10}</a></td>" separator="</tr>"]
    John Doe | john@example.com
    [/gfsearch]
    </tbody>
  </table>
```
You can create links to anywhere you want, including other views or parts of this view!
#### Example 5: Secondary Search
```markdown
  [gfsearch target="2" display="13" sort_key="date_created" secondary_sort_key="name" secondary_sort_direction="ASC"]
```

## ❗ Notes and Best Practices
- Always ensure placeholders (e.g., `{13}`) match the field IDs or entry properties.

- Use prefix for non-numeric keys when used alongside **Gravity View**. `{gfs:id}`

- To search multiple fields pass comma seperated IDs to the search attribute and separate the corresponding values in the shortcode content with the `|` symbol. Use the `search_mode` attribute to configure if any or all conditions must match. To search for multiple values for the same field, repeat the field ID in the search attribute with the corresponding values in the shortcode content.

- Custom Formatting: Use the display attribute with curly braces for placeholders (e.g., `display="Field: {13}, User: {created_by}"`). HTML is supported, enabling displays in a complex format. You can create lists or tables or link to entries in a Gravity View. You can define CSS classes allowing for even more customization!

- Avoid using double quotes (`"`) inside the `display` attribute if your attribute is wrapped with double quotes—use single quotes (`'`) instead, and vice versa.
<br> Avoid <br>
`display="<a href="example.com">{3}</a>"`<br>
Instead <br>
`display="<a href='example.com'>{3}</a>"`

- The search and display attributes both support entry properties and field IDs. See [Gravity Forms Entry Object](https://docs.gravityforms.com/entry-object/).

- Leave a search ID blank to search any field for the specified value.

- To perform a global search for any field with a specified value, leave the corresponding search ID blank.

- To display values from a field without searching, omit the search attribute and shortcode content.

- **Sorting:**  
    Use `sort_key` (field ID, entry property, or entry meta key), `sort_direction` (`ASC`, `DESC` (default), `RAND`), 
    and `sort_is_num` (`true`/`false`). For secondary sorting, use `secondary_sort_key` and `secondary_sort_direction`. 
    Secondary sorting is ignored if primary sort direction is `RAND`.
    Please note that dates are numeric regarding the `sort_is_num` attribute.

- Use the `unique` attribute with any non-empty value to return only unique results.

- To search for empty values, leave the shortcode content blank and use the `search_empty` attribute with any non-empty value.

- Use the `default` attribute to specify a value to display when no results are found or for blank values within entries.

- Use the `link` attribute with any non-empty value to wrap each result in a link to the entry view page in the WordPress admin.

## Installation
This shortcode can be installed as a snippet or as a plugin.

### As a Plugin
[Download the zip](https://github.com/Eitan-brightleaf/gfsearch/archive/refs/heads/main.zip) and install in the WP admin dashboard.

### As a snippet
Copy the code [here](https://raw.githubusercontent.com/Eitan-brightleaf/gfsearch/refs/heads/main/gfsearch.php?token=GHSAT0AAAAAADCYGWKL3KTFURKPP3WA2CCG2BMJXXQ)
and install in your themes functions.php file or with your favorite code snippets plugin.

## 📜 License
This plugin is licensed under **GPLv2 or later**.

## 🔗 Links
- [Plugin Repo](https://github.com/Eitan-brightleaf/gfsearch)
- [In the BL Digital Snippet Directory](https://digital.brightleaf.info/code/entry/44-gfsearch-shortcode/)
