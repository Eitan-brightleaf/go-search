# gfsearch Plugin Refactor & Cleanup Checklist

Use this checklist to guide your improvements to the `gfsearch` plugin code. Check off each item as you complete it.

---

- [ ] **1. Split Large Functions**
  - Break up `gfsearch_shortcode` into smaller, well-named helper functions (e.g., entry fetching, sorting, formatting, default value resolution, result rendering).

- [ ] **2. Standardize Data Access**
  - Use either arrays or objects consistently for field data (e.g., `$field['type']` vs `$field->type`).

- [ ] **3. Sanitize and Escape Properly**
  - Escape only on output (e.g., use `esc_url()` for URLs, `esc_attr()` for attributes).
  - Avoid unnecessary escaping for internal logic.

- [ ] **4. Simplify Default Value Logic**
  - Extract default value selection into a helper function to avoid repetition.

- [ ] **5. Use Early Returns**
  - Refactor to use early returns for edge cases and errors, reducing nesting.

- [ ] **6. Extract Separator Logic**
  - Move separator selection into a helper function for clarity and reuse.

- [ ] **7. Optimize Filtering**
  - Filter results only once, after all processing is complete.

- [ ] **8. Add Inline Documentation**
  - Add docblocks and inline comments to all helper functions and complex logic blocks.

- [ ] **9. Refactor Result Formatting**
  - Move entry result formatting into its own function for clarity and reuse.

- [ ] **10. Consider OOP**
  - If the plugin grows, consider refactoring into a class-based structure for better encapsulation and extensibility.

---

Feel free to add more items as you discover other improvements!
