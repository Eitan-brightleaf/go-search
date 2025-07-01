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
[gfsearch target="1" display="13"]
```
This displays the value of field ID 13 from the latest entry in form 1.
### Attributes Overview

| **Attribute**                  | **Description**                                                                                                                                                                                                          | **Default**     |
|--------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------|
| **`target`**                   | Specify forms to search: `0` for all forms, or comma-separated list of form IDs (e.g., `target="1,2"`).                                                                                                                  | (all forms) `0` |
| **`search`**                   | Field IDs/entry properties for filtering entries. Separate multiple IDs by a comma (`search="13,14"`). The corresponding values to search for should be placed in the shortcode content, separated by the pipe operator. | _(None)_        |
| **`display`**                  | Comma seperated list of field IDs/entry properties to display. Also allows formating results with placeholders.                                                                                                          | _(Required)_    |
| **`search_mode`**              | Match all conditions (`all`, default) or any condition (`any`).                                                                                                                                                          | `all`           |
| **`greater_than`**             | Filter numeric values greater than a threshold, e.g., `greater_than="4, 1000"` where 4 is the field ID and 1000 is the threshold.                                                                                        | _(None)_        |
| **`less_than`**                | Filter numeric values less than a threshold, e.g., `less_than="6, 50"` where 6 is the field ID and 50 is the threshold.                                                                                                  | _(None)_        |
| **`sort_key`**                 | Field/property to sort entries (e.g., field ID or meta key).                                                                                                                                                             | `id` (entry ID) |
| **`sort_direction`**           | Sorting direction: `ASC`, `DESC`, or `RAND`.                                                                                                                                                                             | `DESC`          |
| **`sort_is_num`**              | Indicates if sorting is numeric (true/false).                                                                                                                                                                            | `true`          |
| **`secondary_sort_key`**       | Secondary sorting field (if needed).                                                                                                                                                                                     | (empty)         |
| **`secondary_sort_direction`** | Sorting direction for the secondary sort (ASC, DESC).                                                                                                                                                                    | `DESC`          |
| **`unique`**                   | Display only unique values in the results.                                                                                                                                                                               | `false`         |
| **`limit`**                    | Number of results to display. Use `limit="all"` to display all entries.                                                                                                                                                  | `1`             |
| **`separator`**                | Separator between **entry** results (supports HTML).                                                                                                                                                                     | _(Varies)_      |
| **`search_empty`**             | Search for fields with empty/blank values.                                                                                                                                                                               | `false`         |
| **`default`**                  | Default text to display if no results match search criteria. Can input multiple values corresponding to each `display` value, seperated by a double pipe symbol.                                                         | _(Blank)_       |
| **`link`**                     | Makes results clickable links to admin entry details.                                                                                                                                                                    | `false`         |


### 🧩 Examples

#### Example 1: Display fields 16 and 17 (comma seperated) when field 13 matches John and 14 matches john@example.com
This will display the results from the five latest matching entries from form 1. The entry results will be each on a new line.
The shortcode will return 'No results found' for any blank entries or blank results in matching entries.
```markdown
[gfsearch target="1" search="13,14" display="16, 17" limit="5" separator="<br>" default="No results found"]
John|john@example.com
[/gfsearch]
```

#### Example 2: Search and Display Multiple Fields

```markdown
[gfsearch target="2" search="13,14" display="Name: {13}, Email: {14}" search_mode="all" limit="10"]
John|john@example.com
[/gfsearch]
```

#### Example 3: Display Unique Results with Links
This will return a list of unique created_by values from form 3. Each will link to its corresponding admin entry view.
```markdown
[gfsearch target="3" display="Unique Entry: {created_by}" unique="true" link="true"]
```

#### Example 4: Global Search with Custom HTML
This searches across all forms and fields and creates an HTML list showing the entry ID and the value of field 13.
```markdown
[gfsearch target="0" display="<li>ID: {id}, Value: {13}" separator="</li>"]
```
Wrap the above shortcode within `<ul>` tags:
```html
<ul>
  [gfsearch target="0" display="<li>ID: {id}, Value: {13}" separator="</li>"]
</ul>
```

#### Example 5: Creating an HTML table with links
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

#### Example 6: Secondary Search

```markdown
  [gfsearch target="2" display="13" sort_key="date_created" secondary_sort_key="name" secondary_sort_direction="ASC"]
```

## Display Attribute

- For basic usage of the display attribute pass in a comma seperated list of entry meta to display.

- When displaying one entry field from multiple entries by default, the results will be separated by commas. If you are
displaying multiple entry fields from each entry, the entries will be separated by semicolons while the entry fields will
be separated by commas. The separator between entry results can be modified with the `separator` attribute.

- The format that the individual entry fields will be displayed in is highly customizable. It supports entering just about
any string, including HTML, with using placeholders for the entry meta you want filled in. Just take care not to enter
anything that will break the shortcode such as brackets `[]` or close the display attribute early by mixing single and
double quotes.

- The `display` attribute supports placeholders in the following formats:
  - `{id}` (curly braces, field/property ID)
  - `{gfs:id}` (curly braces, with `gfs:` prefix)
  - `gfs:id` (plain, with `gfs:` prefix, no curly braces)

- You can set a default value per merge tag basis by prefixing the merge tag with gfs and closing with semicolon and the default value. 
This is aside from the `default` attribute.
  For example `{gfs:Name 5;No Name Found}` or `{gfs:created_by;Current User}` or `{gfs:phone;Not Available}`

- **Default values for placeholders** are only supported in curly-brace formats:
  - `{id;default}`
  - `{gfs:id;default}`
  - Example: `display="Name: {gfs:5;No Name Found}"`
  - The plain format `gfs:id;default` is not supported.

- You generally should use the plain `{id}` format for the placeholders but there are situations where you need the 
other formats such as when using the `gfsearch` shortcode in a place that merge tags may be parsed such as a Gravity View custom content widget.
  - For numeric meta-properties (such as a field id) use the plain `{id}` format.
  - For non-numeric meta-properties (such as entry ID or date created or Gravity Flow workflow meta) you should use the `{gfs:id}` format.
  - The `gfs:id` format is primarily used when in a nested shortcode. See below for more information.

- Always ensure placeholders (e.g., `{13}`) match the field IDs or entry properties.

- Avoid using double quotes (`"`) inside the `display` attribute if your attribute is wrapped with double quotes—use single quotes (`'`) instead, and vice versa.
  <br> Avoid <br>
  `display="<a href="example.com">{3}</a>"`<br>
  Instead <br>
  `display="<a href='example.com'>{3}</a>"`

- If you are using the special syntax for the `display` attribute you can pass in a placeholder `{num_results}` or `{gfs:num_results}` which will be substituted
  with the number of results returned. This could be useful when using `limit="all"` and you need to know how results there are.

- When using the special placeholder syntax for the `display` attribute if the first placeholder is empty the whole result will be treated as empty.

- To see which fields are available for search or display, use:
`[gfsearch display="meta"]`.
  This will output a list of all meta keys for the matched entry. You can customize the formatting using the `separator` attribute.
**Tip**: You can also find meta-keys by checking the column headers in the Gravity Forms → Entries screen. Hover or click a column title to see the key in the URL.


### Nested Shortcodes

- You can use shortcodes in the `display` attribute and the value of the shortcode will become part of the display string.

- To use shortcodes in the `display` attribute by wrap them in double curly braces. For example:
  `[gfsearch display="This is an example: Entry ID: {gfs:id} Sum: {{gravitymath}}2+2{{/gravitymath}} Nested search:{{gfsearch display='23'}}"]`

- This works for self-closing shortcodes and shortcodes with a closing tag. Attributes can be used in the shortcodes.

- The shortcodes will be parsed after placeholders are replaced, so you can use placeholders in the shortcode. 

- Remember that the shortcode result should be used in conjunction with placeholders as part of a display string, or else
it should return a field ID/entry property that can be returned. For example, the following
`[gfsearch target=12 search=2 display="{{gravitymath}}2+2{{/gravitymath}}]John Doe[/gfsearch]`
will not display 4 but will instead display the value of field ID 4.

- When using nested `{{gfsearch ... display="..."}}` shortcodes inside the display attribute, only the outer display's 
placeholders are parsed in the parent shortcode. The display attribute of nested gfsearch shortcodes is 
ignored by the parent and will be parsed when the nested shortcode is processed.

- When using placeholders in a nested shortcode, you should use the gfs:id format without the curly braces. This is both
when using them in the shortcode content and attributes. Likewise, you may need to use a different merge tag format
for other parts of the shortcode. Notice the content of the first example below and see our [snippet]() which converts it back
to a regular merge tag for Gravity Math.
For example `{{gravitymath scope='view' id='1014' filter='filter_19=gfs:21' }}~gfs.8.sum~{{/gravitymath }}`
`{{gfsearch target='60' search='1' sort_key='3' display='{16} on {3}' }}gfs:21{{/gfsearch}}`

- Take extra care not to mix and match single and double quotes inside the display attribute if you are using nested
shortcodes and attributes inside those. This is doubly true if using our [Global Variables]() plugin to insert shortcodes.
**Note:** Even when using Global Variables use the double curly brace syntax in the formula.

## ❗ Notes and Best Practices

- To search multiple fields pass comma seperated IDs to the search attribute and separate the corresponding values in 
the shortcode content with the `|` symbol. Use the `search_mode` attribute to configure if any or all conditions must 
match. To search for multiple values for the same field, repeat the field ID in the search attribute with the corresponding values in the shortcode content.
  
- Custom Formatting: Use the display attribute with placeholders, enabling displays in a complex format.
You can create lists or tables, link to entries in a Gravity View, create `mailto` links, the possibilities are almost 
endless! You can define CSS classes allowing for even more customization! See above for details.

- The search and display attributes both support entry properties and field IDs. See [Gravity Forms Entry Object](https://docs.gravityforms.com/entry-object/).

- To perform a global search for any field with a specified value, leave the corresponding search ID blank.

- To display values from a field without searching, omit the search attribute and shortcode content.

- **Sorting:**  
    Use `sort_key` (field ID, entry property, or entry meta key), `sort_direction` (`ASC`, `DESC` (default), `RAND`), 
    and `sort_is_num` (`true`/`false`). For secondary sorting, use `secondary_sort_key` and `secondary_sort_direction`. 
    Secondary sorting is ignored if primary sort direction is `RAND`.
    Please note that dates are numeric regarding the `sort_is_num` attribute.

- Use the `unique` attribute with any non-empty value to return only unique results. **Please Note:** that if the results
are not exactly the same they will be treated as unique. So the results example.co**m** and example.co**n** as a typo
will be considered unique.

- To search for empty values, leave the shortcode content blank and use the `search_empty` attribute with any non-empty value.

- Use the `default` attribute to specify a value to display when no results are found or for blank values within entries.

- Use the `link` attribute with any non-empty value to wrap each result in a link to the entry view page in the WordPress admin.

- When using the shortcode content to pass in search values (separated by the | character), avoid using the pipe (|) 
symbol inside the actual values themselves. Escaping is not currently supported, so including a pipe within a value may 
result in incorrect or partial matches.

- The `[gfsearch]` shortcode does not restrict access by default. Anyone who can view the page can see the search results, including Gravity Forms entry data.
To protect sensitive information, place the shortcode inside pages with appropriate access controls (e.g., membership plugins, password protection, or role-based visibility).

- Each `[gfsearch]` shortcode runs a live database query. Using many shortcodes, large forms, or limit="all" can slow down page loads.
To improve speed:

  - Use limit to cap results

  - Minimize nested shortcodes

  - Consider caching the page output

- If there are more search IDs than values, extra fields will search for blank entries. Extra values beyond the number of IDs are ignored.

### 🧩 Multi-Input Field Support

Multi-input fields—such as **Name**, **Address**, and **Checkbox** fields—contain multiple inputs within a single field. 
This plugin supports both displaying and searching these fields, but the behavior differs slightly between display and search.

---

#### 📤 Displaying Multi-Input Fields

When using the `display` attribute with the **base field ID** (e.g., `{13}`), the plugin automatically detects if the field is multi-input and will:

- Fetch all of its sub-inputs (e.g., First Name, Last Name)
- Concatenate them into a single string separated by spaces

This allows for simple display of complete names or addresses without needing to reference each sub-input.

To target a specific subfield (e.g., just First Name), use its input ID directly, like `{13.3}`.

---

#### 🔍 Searching Multi-Input Fields

The correct way to search multi-input fields depends on the field type:

**Checkbox Fields**
- ✅ Use the **base field ID** (e.g., `search="2"`), not the input ID (`2.2`)
- ✅ This is the recommended method and ensures stability, especially if checkbox inputs are modified or dynamically generated

**Other Multi-Input Fields (e.g., Name, Address)**
- ✅ Use the **individual input IDs** (e.g., `13.3`, `13.6`)
- ❌ Searching by the base field ID (e.g., `13`) will not work for these fields

---

#### 🧪 Examples

Search by first and last name (multi-input Name field):
```markdown
[gfsearch target="2" search="13.3,13.6" display="Full Name: {13}"]
John|Smith
[/gfsearch]
```
Search for a selected checkbox value:
```
[gfsearch target="5" search="2" display="{2}"]
First Choice
[/gfsearch]
```

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
