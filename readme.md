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
- **Comparison Filters:** Filter numeric fields by values (greater than or less than). 
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
| **`operators`**                | Comma-separated list of operators that correspond to each field in the `search` attribute. See documentation below for a list of supported operators.                                                                    | _(None)_        |
| **`display`**                  | Comma separated list of field IDs/entry properties to display. Also allows formating results with placeholders.                                                                                                          | _(Required)_    |
| **`search_mode`**              | Match all conditions (`all`, default) or any condition (`any`).                                                                                                                                                          | `all`           |
| **`sort_key`**                 | Field/property to sort entries (e.g., field ID or meta key).                                                                                                                                                             | `id` (entry ID) |
| **`sort_direction`**           | Sorting direction: `ASC`, `DESC`, or `RAND`.                                                                                                                                                                             | `DESC`          |
| **`sort_is_num`**              | Indicates if sorting is numeric (true/false).                                                                                                                                                                            | `true`          |
| **`secondary_sort_key`**       | Secondary sorting field (if needed).                                                                                                                                                                                     | (empty)         |
| **`secondary_sort_direction`** | Sorting direction for the secondary sort (ASC, DESC).                                                                                                                                                                    | `DESC`          |
| **`unique`**                   | Display only unique values in the results.                                                                                                                                                                               | `false`         |
| **`limit`**                    | Number of results to display. Use `limit="all"` to display all entries.                                                                                                                                                  | `1`             |
| **`separator`**                | Separator between **entry** results (supports HTML). Will only be used when there is more than one entry returned by the search. To configure a blank separator, enter `__none__`.                                       | _(Varies)_      |
| **`search_empty`**             | Search for fields with empty/blank values.                                                                                                                                                                               | `false`         |
| **`default`**                  | Default text to display if no results match search criteria. Can input multiple values corresponding to each `display` value, separated by a double pipe symbol.                                                         | _(Blank)_       |
| **`link`**                     | Makes results clickable links to admin entry details.                                                                                                                                                                    | `false`         |


### 🧩 Examples

#### Example 1: Display fields 16 and 17 (comma separated) when field 13 matches John and 14 matches john@example.com
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
[gfsearch target="0" display="<li>ID: {id}, Value: {13}</li>" separator="__none__"]
```
Wrap the above shortcode within `<ul>` tags:
```html
<ul>
    [gfsearch target="0" display="<li>ID: {id}, Value: {13}</li>" separator="__none__"]
</ul>
```

#### Example 5: Creating an HTML table with links
```html
  <table>
    <thead>
      <tr><th>Name</th><th>Email</th><th>Link</th></tr>
    </thead>
    <tbody>
    [gfsearch target="1" search="3,5" display="<tr><td>{1}</td><td><a href='mailto:{2}'>{2}</a></td><td><a href='{6}&query={10}'>{6}&query={10}</a></td></tr>" separator="__none__"]
    John Doe | john@example.com
    [/gfsearch]
    </tbody>
  </table>
```
You can create links to anywhere you want, including other views or parts of this view!

#### Example 6: Secondary Sort

```markdown
  [gfsearch target="2" display="13" sort_key="date_created" secondary_sort_key="name" secondary_sort_direction="ASC"]
```

## 🖼️ Display Attribute

The `display` attribute controls what is shown for each matching entry. You can use it in two different formats:

---

### 🔹 1. Basic Comma-Separated Field List

You can pass a simple comma-separated list of field or property IDs, like:

```markdown
display="13,14,15"
```

* This will output the values of fields 13, 14, and 15 for each matching entry.
* By default:

  * **Single field per entry** → results are separated by commas
  * **Multiple fields per entry** → fields are separated by commas; entries are separated by semicolons
* You can override the entry separator with the `separator` attribute (supports HTML):
> **Note**: The behavior of the separator applies to both the basic comma-separated field list and the custom 
formatting with placeholders options. The separator is only applied when there is more than one entry returned by the 
search. To configure a blank separator, enter `__none__`.

---

### 🔹 2. Custom Display String with Placeholders

You can build a custom display using placeholders inside a string. This gives you full control over formatting, including HTML, text, and shortcodes.

```markdown
display="Name: {13}, Email: {14}"
```

---

### 🏷️ Placeholder Formats

You can use placeholders to insert entry values into the output:

* `{id}` – standard numeric field or entry property (e.g., `{13}`, `{id}`, `{form_id}`)
* `{gfs:id}` – for non-numeric properties when used in **contexts where merge tags may be parsed**, such as Gravity View custom content widgets, confirmations, or notifications
* `gfs:id` – used only in **nested shortcodes**

> **Tip**: Use `{id}` for most numeric fields, and `{gfs:id}` for text-based meta like `created_by`, `date_created`, etc.

---

### 🆘️ Default Values for Placeholders

You can include a fallback/default value inside a placeholder using `;`:

```markdown
{5;No Name Found}
{gfs:created_by;Current User}
```

* Only curly-brace formats (`{}`) support default values.
* Plain format (`gfs:id;default`) is **not supported**.

---

### ⚠️ Placeholder Behavior Notes

* If the **first placeholder in your display string** resolves to an empty value, the entire result will be treated as empty and skipped (unless a default value is configured).
* Always match placeholders to real field or entry property IDs in your form.
* Avoid nesting `"` inside the `display` string if you're already using double quotes to wrap it — prefer single quotes inside instead.

```markdown
display="<a href='mailto:{13}'>{13}</a>"  ✅
display="<a href="{13}">"                 ❌
```

---

### 🔢 Special Placeholders

- {num_results} or {gfs:num_results} will be replaced with the total number of results returned.
Useful when using limit="all" or for showing counts like: "{num_results} entries found."

- To see which keys are available for use in the display or search attributes:

  `[gfsearch display="meta"]`

  This will return a list of all meta keys for the matched entry. You can customize the layout with the `separator` attribute.

  > **Tip**: You can also find meta-keys by hovering or clicking on column headers in Forms → Entries in the WP admin. The meta key appears in the URL.



### 🧬 Nested Shortcodes

You can include shortcodes inside the `display` attribute using **double curly braces** (`{{ ... }}` syntax). This allows you to embed other shortcodes—like `gravitymath`, `gfsearch` or any other shortcode—within the output for each entry.

---

#### 🔧 Basic Syntax

Wrap any supported shortcode inside double curly braces:

```markdown
[gfsearch display="Sum: {{gravitymath}}2+2{{/gravitymath}}"]
```

* Works with both self-closing and wrapped shortcodes
* Supports all shortcode attributes
* Placeholders like `{13}` will be parsed **before** the nested shortcode is run

---

#### 🔁 Placeholder Behavior

When nesting a `gfsearch` shortcode:

* The **outer** `gfsearch` processes its own placeholders in the `display` string first
* The **nested** `gfsearch` processes its own `display` attribute separately after it runs
* Use the format `gfs:id` (no curly braces) inside nested shortcodes to refer to placeholder values
* Likewise, when referencing entry values inside formulas or shortcode attributes, you may need to use a custom merge tag
format. Using standard merge tags like `{8}` or `{gfs:8}` **will break** the shortcode. For example:

```markdown
{{gravitymath scope='view' id='1014' filter='filter_19=gfs:21'}}~gfs.8.sum~{{/gravitymath}}
```

This correctly filters by field 21 and calculates the sum of field 8 using special merge tag syntax. See our [snippet]() for more information.

```markdown
[gfsearch display="Lookup: {{gfsearch target='60' search='1' display='gfs:23'}}"]
John
[/gfsearch]
```

---

#### ⚠️ Best Practices & Caveats

* Don’t mix single and double quotes inside the `display` attribute—if the outer string uses double quotes, use single quotes inside:

```markdown
display="{{gravitymath scope='view' id='1014'}}2+2{{/gravitymath}}"
```

* Even when using the [Global Variables plugin](https://digital.brightleaf.info/global-variables-for-gravity-math/), use the double curly brace syntax for your formulas if they are meant to run inside a GFSearch display attribute.

* Shortcodes inside the `display` string must either:

  * Return a **value** that can be shown as part of a string alongside other placeholders or text
  * Or return a **field/property ID** when using the basic display format (e.g., `display="gfs:23"`), which GFSearch will interpret and replace with the actual entry value

#### 💡 Examples

##### Nested shortcode with computed math:

```markdown
[gfsearch display="Total: {{gravitymath}}~gfs.8+gfs.9~{{/gravitymath}}"]
```

##### Nested gfsearch to pull related field:

```markdown
[gfsearch target='60' search='1' sort_key='3' display="Submitted by {16} on {3}. Related: {{gfsearch target='61' search='2' display='gfs:23'}}"]
John
[/gfsearch]
```

This could output something like:

> Submitted by John Smith on 2024-07-15. Related: Completed
## ⚖️ Operators Attribute

The `operators` attribute allows you to define **how each search value is compared** to its corresponding field in the `search` attribute. It should be a **comma-separated list**, with each operator matching its position to the same-positioned field ID in the `search` attribute.

### ✅ Supported Operators

| Operator                | Meaning                                |
|-------------------------|----------------------------------------|
| `=` or `is`             | Equals                                 |
| `!=`, `isnot`, `is not` | Not equal to                           |
| `contains`              | Partial match                          |
| `like`                  | SQL-style `LIKE` with custom wildcards |
| `in`                    | Value is in array                      |
| `notin`, `not in`       | Value is NOT in array                  |
| `gt`                    | Greater than                           |
| `lt`                    | Less than                              |
| `gt=`                   | Greater than or equal to               |
| `lt=`                   | Less than or equal to                  |

> 💡 To compare against multiple values using `in` or `not in`, pass a PHP-style array in the shortcode content, like:
>
> ```markdown
> array('item one', 'item two', 'item three')
> ```

---

### 🔄 Operator Matching Behavior

Each operator in `operators` must match the position of a field in the `search` attribute:

* ✅ If you pass **fewer operators** than `search` fields:

    * The **remaining fields default to** `=` (exact match).
    * This lets you apply advanced filters only where needed.
* ⚠️ If you pass **more operators** than `search` fields:
    * **Extra operators are ignored.**
* ❌ If `operators` is omitted entirely:
    * **All search fields use `=` by default.**

---

### 🧪 Examples

#### Basic match with mixed operators

```markdown
[gfsearch search="3,5,8" operators="contains,=,gt"]
Smith|john@example.com|50
[/gfsearch]
```

* Field 3 must *contain* "Smith"
* Field 5 must *equal* "[john@example.com](mailto:john@example.com)"
* Field 8 must be *greater than* 50

#### Using array for `in`

```markdown
[gfsearch search="5" operators="in"]
array('yes','maybe')
[/gfsearch]
```

* Field 5 must match one of the given values

#### Mixing defaults and explicit operators

```markdown
[gfsearch search="3,5,8" operators="contains"]
Smith|john@example.com|50
[/gfsearch]
```

* Field 3 uses `contains`
* Field 5 and 8 default to `=`

---

### 📌 Tips & Gotchas

* **Array format:** Use `array('one','two')` exactly—do not just write comma-separated values.
* **Order matters:** Ensure your `operators` match the order of `search` fields.
* If a field is repeated in `search`, you can still assign distinct operators per instance.
* The `greater_than` and `less_than` attributes are deprecated in favor of the new `operators` attribute.

## ❗ Notes and Best Practices

- To search multiple fields pass comma separated IDs to the search attribute and separate the corresponding values in 
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
Copy the code from [here](https://digital.brightleaf.info/code/entry/44-gfsearch-shortcode/)
and install in your themes functions.php file or with your favorite code snippets plugin.

## 📜 License
This plugin is licensed under **GPLv2 or later**.

## 🔗 Links
- [Plugin Repo](https://github.com/Eitan-brightleaf/gfsearch)
- [In the BL Digital Snippet Directory](https://digital.brightleaf.info/code/entry/44-gfsearch-shortcode/)
