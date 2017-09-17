UNDER CONSTRACTION.

### Features

* Add custom post types
* Add custom fields
* Add taxonomies
* Show columns on a manage screen
* Create sortable columns on a manage screen

<details>
  <summary>hoge</summary>
```rb
a = 'hoge'
```
</details>

### Add custom post types

```yml 
some_post:
  register_options:
    labels:
      name: Some Post
    public: true
    has_archive: true
    supports: title
  taxonomies:
    - some_post_tag:
        hierarchical: false
        public: true
        show_ui: true
        label: SomePostTag
  custom_fields:
    - field1:
        label: FIELD1
        unique: true
        input: text
        size: 20
    - cb1:
        label: CheckBox1
        unique: false
        input: checkbox
        values:
          - apple
          - orange
  columns_on_manage_screen:
    show:
      - field1:
          label: FIELD1
      - field3
  sortable_columns:
    - field3
  meta_boxes:
    - box1:
        label: box
        context: side 
        fields:
          - field1
    - box2:
        label: box2
        context: normal
        fields:
          - cb1 

```

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
