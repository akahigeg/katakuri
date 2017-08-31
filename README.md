UNDER CONSTRACTION.

### Custom Field

post:
  custom_fields:
    - field1:
        label: FIELD1
        unique: true
        type: text
        input: text
        size: 20
        list_column: true
    - field2:
        label: FIELD2
        unique: false
        input: checkbox
        values:
          - apple
          - orange
        list_column: false

### Taxsonomy

post:
  taxonomies:
    - post_additional_tag:
        hierarchical: false
        public: true
        show_ui: true
        label: SomePostTag

### 