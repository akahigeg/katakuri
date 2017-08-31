<?php
class Katakuri {
  public static function init() {
    $post_types = self::readConfig();

    # register each post types
    foreach ($post_types as $post_type_name => $options) {
      self::registerPostType($post_type_name, $options);
    }
  }

  public static function readConfig() {
    $yaml_path = ABSPATH . '/post-types.yml';
    if (!file_exists($yaml_path)) {
      $yaml_path = plugin_dir_path(__FILE__) . '/../post-types.yml-sample';
    }
    return yaml_parse_file($yaml_path);
  }

  private static function registerPostType($post_type_name, $options) {
    $register_options = isset($options['register_options']) ? $options['register_options'] : array();
    # taxonomies links a post type
    $taxonomies = array();

    if (array_key_exists('taxonomies', $options)) {
      $taxonomies = $options["taxonomies"];
      $taxonomy_names = array();
      foreach ($taxonomies as $i => $taxonomy_name_and_args) {
        # $taxonomy_name_and_args example.
        #   {'some_post_tag' => $args}
        foreach ($taxonomy_name_and_args as $name => $args) {
          $taxonomy_names[] = $name;
        }
      }
      # $options["taxonomies"] in only taxxonomy names for regsiter_post_type
      $register_options["taxonomies"] = $taxonomy_names;
    }

    if (!post_type_exists($post_type_name)) {
      register_post_type($post_type_name, $register_options);
    }

    # register taxonomies
    self::registerTaxonomies($taxonomies, $post_type_name);
  }

  private static function registerTaxonomies($taxonomies, $post_type_name) {
    foreach ($taxonomies as $i => $taxonomy_name_and_args) {
      foreach ($taxonomy_name_and_args as $name => $args) {
        if (!taxonomy_exists($name)) {
          register_taxonomy($name, $post_type_name, $args);
        }
      }
    }
  }

  public static function manageColumns($columns) {
    $date_escape = $columns['date'];
    unset($columns['date']);

    $post_types = self::readConfig();
    $current_post_type = get_post_type();
    if (isset($post_types[$current_post_type]) 
        && isset($post_types[$current_post_type]['list_columns'])) {
      foreach ($post_types[$current_post_type]['list_columns'] as $field) {
        if (is_array($field)) {
          foreach ($field as $name => $options) {
            $label = isset($options['label']) ? $options['label'] : $name;
            $columns[$name] = $label;
          }
        } else {
          $columns[$field] = $field;
        }
      }
    }

    $columns['date'] = $date_escape;

    return $columns;
  }

  public static function manageCustomColumns($column_name, $post_id) {
    $saved_value = get_post_meta($post_id, $column_name, true);
    if (is_array($saved_value)) {
      echo implode($saved_value, ',');
    } else {
      echo $saved_value;
    }
  }

  public static function addMetaBoxes() {
    $post_types = self::readConfig();

    foreach ($post_types as $post_type_name => $options) {
      $rendered_fields = array();

      if (array_key_exists('meta_boxes', $options)) {
        foreach ($options['meta_boxes'] as $index => $meta_box) {
          foreach ($meta_box as $name => $meta_box_options) {
            $context = isset($meta_box_options['context']) ? $meta_box_options['context'] : '';
            $priority = isset($meta_box_options['priority']) ? $meta_box_options['priority'] : 'default';

            $include_fields = $meta_box_options['fields'];
            add_meta_box($name . '_meta_box_options', 
                       $meta_box_options['label'], 
                       'Katakuri::renderMetaBox', 
                       $post_type_name, $context, $priority, $include_fields);
            $rendered_fields = array_merge($rendered_fields, $include_fields);
          }
        }
      }

      if (array_key_exists('custom_fields', $options)) {
        $include_fields = array();
        foreach ($options['custom_fields'] as $index => $custom_field) {
          foreach ($custom_field as $name => $options) {
            if (!in_array($name, $rendered_fields)) {
              $include_fields[] = $name;
            }
          }
        }
        add_meta_box($post_type_name. '_meta_box', 
                     'Custom Fields', 
                     'Katakuri::renderMetaBox', 
                     $post_type_name, 'normal', 'default', $include_fields);
      }
    }
  }

  /**
   * @param $args => array
   *                 ["id"]=> string(18) "some_post_meta_box" 
   *                 ["title"]=> string(13) "Custom Fields" 
   *                 ["callback"]=> string(23) "Katakuri::renderMetaBox" 
   *                 ["args"]=> array(1) { [0]=> string(2) "OK" } 
   * 
   */
  public static function renderMetaBox($post, $args) {
    $post_type_name = get_post_type($post);
    $custom_field_values = get_post_custom();

    $post_types = self::readConfig();
    if (array_key_exists($post_type_name, $post_types)) {
      $custom_fields = $post_types[$post_type_name]['custom_fields'];
      $include_fields = $args['args'];
      foreach ($custom_fields as $custom_field) {
        foreach ($custom_field as $name => $options) {
          if (!in_array($name, $include_fields)) {
            continue;
          }
          $input_type = isset($options['input']) ? $options['input'] : "text";
          $saved_value = isset($custom_field_values[$name]) ? $custom_field_values[$name][0] : "";

          echo '<div style="padding:5px 0;">';

          $method_name = 'render' . KatakuriUtil::pascalize($options['input']);
          KatakuriFormRenderer::$method_name($name, $saved_value, $options);

          echo '</div>';
        }
      }
    }
  }

  public static function saveMeta($post_id) {
    $post_type_name = get_post_type($post_id);

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    $post_types = self::readConfig();
    if (array_key_exists($post_type_name, $post_types) && isset($post_types[$post_type_name]['custom_fields'])) {
      $custom_fields = $post_types[$post_type_name]['custom_fields'];
      foreach ($custom_fields as $custom_field) {
        foreach ($custom_field as $name => $options) {
          $input_type = isset($options['input']) ? $options['input'] : "text";

          switch ($options['input']) {
            case 'text':
            case 'textarea':
            case 'radio':
              if (isset($_POST[$name])) {
                update_post_meta($post_id, $name, sanitize_text_field($_POST[$name]));
              }
              break;
            case 'checkbox':
            case 'select':
              if (isset($_POST[$name])) {
                update_post_meta($post_id, $name, $_POST[$name]);
              } else {
                update_post_meta($post_id, $name, array());
              }
              break;
            default:
          }
        }
      }
    }
  }

  public static function manageSortableColumns() {
    $post_types = self::readConfig();

    global $wpdb;

    foreach ($post_types as $post_type_name => $options) {
      if (isset($options['sortable_columns'])) {
        $count_sql_base = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='$post_type_name'";

        $count_all = $wpdb->get_var($count_sql_base);
        $count_trash = $wpdb->get_var($count_sql_base . " AND post_status = 'trash'");
        $count_normal = $count_all - $count_trash; // for support unknown custom post status

        if (isset($_GET['post_status']) && $_GET['post_status'] == 'trash') {
          $count = $count_trash;
        } else {
          $count = $count_normal;
        }

        if ($count > 0) {
          // show sortable_columns when the list has at least one post.
          add_filter('manage_edit-' . $post_type_name . '_sortable_columns', 'Katakuri::sortableColumns');
        }
      }
    }
  }

  public static function sortableColumns() {
    $post_types = self::readConfig();

    global $sortable_columns;

    $sortable = $post_types[get_post_type()]['sortable_columns'];
    if (!empty($sortable)) {
      foreach ($sortable as $i => $column) {
        $sortable_columns[$column] = $column;
      }
    }

    return $sortable_columns;
  }

  public static function addActions() {
    add_action('init', 'Katakuri::init');
    add_action('add_meta_boxes', 'Katakuri::addMetaBoxes');
    add_action('save_post', 'Katakuri::saveMeta');

    add_action('manage_posts_columns', 'Katakuri::manageColumns');
    add_action('manage_posts_custom_column', 'Katakuri::manageCustomColumns', 10, 2);
    // TODO: manage_page_columns
    // add_action('manage_pages_columns', 'Katakuri::manageColumns');
  }
}
