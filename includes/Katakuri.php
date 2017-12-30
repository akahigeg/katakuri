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
    $yaml_path_on_theme = get_stylesheet_directory() . '/post-types.yml';
    $yaml_path_on_root = ABSPATH . '/post-types.yml';

    $yaml_path = '';

    if (file_exists($yaml_path_on_theme)) {
      $yaml_path = $yaml_path_on_theme;
    }

    if (empty($yaml_path) && file_exists($yaml_path_on_root)) {
      $yaml_path = $yaml_path_on_root;
    }

    if (empty($yaml_path)) {
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

    # 
    // if ($options['input'] == 'reference') {
    //   add_action('wp_ajax_rewrite_' . $post_type_name, );
    // }
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
    $current_post_type = get_query_var('post_type');
    if (isset($post_types[$current_post_type]) 
        && isset($post_types[$current_post_type]['columns_on_manage_screen'])) {
      if (isset($post_types[$current_post_type]['columns_on_manage_screen']['show'])) {
        foreach ($post_types[$current_post_type]['columns_on_manage_screen']['show'] as $field) {
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

      if (isset($post_types[$current_post_type]['columns_on_manage_screen']['hide'])) {
        foreach ($post_types[$current_post_type]['columns_on_manage_screen']['hide'] as $field) {
          unset($columns[$field]);
        }
      }
    }

    $columns['date'] = $date_escape;

    return $columns;
  }

  public static function manageCustomColumns($column_name, $post_id) {
    if (preg_match('/_category$/', $column_name)) {
      $saved_value = get_the_term_list($post_id, $column_name, '', ', ');
    } else {
      $saved_value = get_post_meta($post_id, $column_name, true);
    }
    if (is_array($saved_value)) {
      echo implode($saved_value, ',');
    } else {
      echo $saved_value;
    }
  }

  public static function enableManageCustomColumns() {
    $post_types = self::readConfig();

    foreach ($post_types as $post_type => $options) {
      if ($post_type == 'post') {
        $hook_name = 'manage_posts_custom_column';
      } elseif ($post_type == 'page') {
        $hook_name = 'manage_pages_custom_column';
      } else {
        $hook_name = 'manage_' . $current_post_type . '_posts_custom_column';
      }
      add_action($hook_name, 'Katakuri::manageCustomColumns', 10, 2);
    }
  }

  /**
   *  
   */
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

            # avoid double rendering
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

        if (count($include_fields) > 0) {
          add_meta_box($post_type_name. '_meta_box', 
                       'Custom Fields', 
                       'Katakuri::renderMetaBox', 
                       $post_type_name, 'normal', 'default', $include_fields);
        }
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

          echo '<div class="katakuri-meta-box">';
          if (isset($options['before'])) {
            echo $options['before'];
          }

          $method_name = 'render' . KatakuriUtil::pascalize($options['input']);
          KatakuriFormRenderer::$method_name($name, $saved_value, $options);

          if (isset($options['after'])) {
            echo $options['after'];
          }
          echo '</div>';
        }
      }
    }
  }

  public static function saveMeta($post_id) {
    $current_post_type = get_post_type($post_id);

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    $post_types = self::readConfig();

    if (array_key_exists($current_post_type, $post_types) 
        && isset($post_types[$current_post_type]['custom_fields'])) {
      $custom_fields = $post_types[$current_post_type]['custom_fields'];
      foreach ($custom_fields as $custom_field) {
        foreach ($custom_field as $name => $options) {
          self::saveMetaByFieldType($post_id, $name, $options);
        }
      }
    }
  }

  /**
   *
   */
  public static function addTaxonomyMetaBoxForEdit($term) {
    self::renderTaxonomyMetaBox($term->taxonomy, $term);
  }

  public static function addTaxonomyMetaBoxForAdd($current_taxonomy_name) {
    self::renderTaxonomyMetaBox($current_taxonomy_name);
  }

  public static function renderTaxonomyMetaBox($current_taxonomy_name, $term = null) {
    $taxonomy_config = self::readTaxonomyConfig($current_taxonomy_name);

    foreach ($taxonomy_config as $name => $options) {
      if (isset($options['custom_fields'])) {
        foreach ($options['custom_fields'] as $i => $custom_field) {
          foreach ($custom_field as $name => $field_options) {
            if (empty($term)) {
              $saved_value = '';
            } else {
              $saved_value = get_term_meta($term->term_id, $name, true);
            }
            echo '<div class="form-field term-' . $name . '-wrap">';
            $method_name = 'render' . KatakuriUtil::pascalize($field_options['input']);
            KatakuriFormRenderer::$method_name($name, $saved_value, $field_options);
            if (isset($field_options['description'])) {
              echo '<p>' . $field_options['description'] . '</p>';
            }
            echo '</div>';
          }
        }
      }
    }
  }

  public static function saveMetaByFieldType($item_id, $field_name, $options, $item_type = 'post') {
    if ($item_type == 'post') { 
      // include custom post type
      $add_meta_function = 'add_post_meta';
      $update_meta_function = 'update_post_meta';
    } else {
      // taxonomy
      $add_meta_function = 'add_term_meta';
      $update_meta_function = 'update_term_meta';
    }

    switch ($options['input']) {
      case 'text':
      case 'textarea':
      case 'radio':
      case 'image':
        if (isset($_POST[$field_name])) {
          $update_meta_function($item_id, $field_name, $_POST[$field_name]);
        } else {
          // $_POST is not exist 
          //   * new post is opened 
          //   * some plugins do something 
          if (isset($options['default'])) {
            $add_meta_function($item_id, $field_name, $options['default'], true);
          }
        }
        break;
      case 'checkbox':
      case 'select':
      case 'reference':
        if (isset($_POST[$field_name])) {
          $update_meta_function($item_id, $field_name, $_POST[$field_name]);
          continue;
        }

        // $_POST is not exist 
        //   * new post is opened 
        //   * nothing was selected on the form
        //   * some plugins do something 
        $v = get_term_meta($item_id, $field_name, true);
        if ($v == '' && isset($options['default'])) { // 
          $add_meta_function($item_id, $field_name, $options['default']);
        } else {
          $update_meta_function($item_id, $field_name, array());
        }
        break;
      default:
    }
  }

  public static function saveTermMeta($term_id) {
    $term = get_term($term_id);
    $taxonomy_config = self::readTaxonomyConfig($term->taxonomy);

    foreach ($taxonomy_config as $name => $taxonomy_options) {
      if (isset($taxonomy_options['custom_fields'])) {
        foreach ($taxonomy_options['custom_fields'] as $i => $custom_field) {
          foreach ($custom_field as $name => $options) {
            self::saveMetaByFieldType($term_id, $name, $options, 'taxonomy');
          }
        }
      }
    }
  }

  public static function readTaxonomyConfig($taxonomy_name) {
    $post_types = self::readConfig();

    foreach ($post_types as $post_type_name => $post_type_options) {
      if (array_key_exists('taxonomies', $post_type_options)) {
        foreach ($post_type_options['taxonomies'] as $taxonomy_config) {
          foreach ($taxonomy_config as $name => $options) {
            if ($name == $taxonomy_name) {
              return $taxonomy_config;
            }
          }
        }
      }
    }
  }

  public static function enableSortableColumns() {
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

  public static function enableTaxonomyCustomFields() {
    $post_types = self::readConfig();

    foreach ($post_types as $post_type_name => $post_type_options) {
      if (array_key_exists('taxonomies', $post_type_options)) {
        foreach ($post_type_options['taxonomies'] as $taxonomy_config) {
          foreach ($taxonomy_config as $name => $options) {
            if (array_key_exists('custom_fields', $options)) {
              add_action($name . '_add_form_fields', 'Katakuri::addTaxonomyMetaBoxForAdd');
              add_action($name . '_edit_form', 'Katakuri::addTaxonomyMetaBoxForEdit');
            }
          }
        }
      }
    }
  }

  public static function enqueueStyle() {
    wp_enqueue_style('katakuri-style' , plugins_url('../katakuri.css', __FILE__));
  }
  public static function enqueueScript() {
    wp_enqueue_media();
  }

  public static function ajax() {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'OK'));
    die();
  }

  public static function script() {
    wp_register_script(
        'katakuri',
        dirname( __FILE__ ) . '/js/update_reference.js',
        array('jquery'),
        false,
        true
    );
 
    wp_enqueue_script('katakuri_update_reference', plugins_url('../js/update_reference.js', __FILE__));
  }

  public static function addActions() {
    add_action('init', 'Katakuri::init');
    add_action('add_meta_boxes', 'Katakuri::addMetaBoxes');
    add_action('save_post', 'Katakuri::saveMeta');

    // TODO: testing for taxonomy custom field and uploading image

    add_action ('created_term', 'Katakuri::saveTermMeta');
    add_action ('edited_term', 'Katakuri::saveTermMeta');

    add_action('manage_posts_columns', 'Katakuri::manageColumns');
    add_action('manage_pages_columns', 'Katakuri::manageColumns');
    self::enableManageCustomColumns();

    add_action('admin_enqueue_scripts', 'Katakuri::enqueueStyle');
    add_action('admin_enqueue_scripts', 'Katakuri::enqueueScript');

    add_action('wp_ajax_katakuri', 'Katakuri::ajax');
    add_action('admin_enqueue_scripts', 'Katakuri::script');
  }
}
