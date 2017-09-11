UNDER CONSTRACTION.

### Custom Field

post:
  custom_fields:
    - field1:
        label: FIELD1
        unique: true
        input: text
        size: 20
  taxonomies:
    - post_additional_tag:
        hierarchical: false
        public: true
        show_ui: true
        label: SomePostTag
  columns_on_manage_screen:
    show:
      - field1:
          label: F1
    hide:
      - author
  sortable_columns:
    - tags

### Taxsonomy

post:
  taxonomies:
    - post_additional_tag:
        hierarchical: false
        public: true
        show_ui: true
        label: SomePostTag

