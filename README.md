UNDER CONSTRACTION.

### Features

* Add custom post types
* Add custom fields
* Add taxonomies
* Show columns on a manage screen
* Create sortable columns on a manage screen

### Add custom post types

    some_post:
      custom_fields:
        - field1:
            label: FIELD1
            unique: true
            input: text
            size: 20

### Add custom fields

    post:
      custom_fields:
        - field1:
            label: FIELD1
            unique: true
            input: text
            size: 20

### Add taxonomies

    post:
      taxonomies:
        - post_additional_tag:
            hierarchical: false
            public: true
            show_ui: true
            label: SomePostTag

### Show columns on a manage screen

    post:
      custom_fields:
        - field1:
            label: FIELD1
            unique: true
            input: text
            size: 20
      columns_on_manage_screen:
        show:
          - field1:
              label: F1
        hide:
          - author

### Create sortable columns on a manage screen

    post:
      sortable_columns:
        - tags
