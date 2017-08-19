<?php
/*
Plugin Name: PostTypeNote
Version: 0.1-alpha
Description: Define custom post types, taxonomies and custom fields by static YAML file
Author: akahigeg
Author URI: http://higelog.brassworks.jp/
Plugin URI: https://github.com/akahigeg/post-type-note
Text Domain: post-type-note
License: Apache License 2.0
Domain Path: /languages
*/

if (!array_key_exists('post-type-note', $GLOBALS)) {
  class PostTypeNote {
    public static function init() {
      $post_types = self::readConfig();

      # register each post types
      foreach ($post_types as $post_type_name => $options) {
        self::registerPostType($post_type_name, $options);
      }
    }

    public static function readConfig() {
      return yaml_parse_file(plugin_dir_path(__FILE__) . '/post-types.yml');
    }

    private static function registerPostType($post_type_name, $options) {
      $register_options = $options['register_options'];
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

      register_post_type($post_type_name, $register_options);

      # register taxonomies
      self::registerTaxonomies($taxonomies, $post_type_name);
    }

    private static function registerTaxonomies($taxonomies, $post_type_name) {
      foreach ($taxonomies as $i => $taxonomy_name_and_args) {
        foreach ($taxonomy_name_and_args as $name => $args) {
          register_taxonomy($name, $post_type_name, $args);
        }
      }
    }

    public static function manageColumns($columns) {
      $date_escape = $columns['date'];
      unset($columns['date']);

      $post_types = self::readConfig();
      $current_post_type = get_post_type();
      if (isset($post_types[$current_post_type]) 
          && isset($post_types[$current_post_type]['custom_fields'])) {
        foreach ($post_types[$current_post_type]['custom_fields'] as $custom_field) {
          foreach ($custom_field as $name => $options) {
            if ($options['list_column']) {
              $columns[$name] = $name;
            }
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
        # support one meta box each post type now.
        if (array_key_exists('custom_fields', $options)) {
          add_meta_box($post_type_name. '_meta_box', 
                       'Custom Fields', 
                       'PostTypeNote::renderMetaBox', 
                       $post_type_name, 'normal', 'core');
        }
      }
    }

    public static function renderMetaBox() {
      global $post;
      $post_type_name = get_post_type($post);
      $custom_field_values = get_post_custom();

      $post_types = self::readConfig();
      if (array_key_exists($post_type_name, $post_types)) {
        $custom_fields = $post_types[$post_type_name]['custom_fields'];
        foreach ($custom_fields as $custom_field) {
          foreach ($custom_field as $name => $options) {
            $input_type = isset($options['input']) ? $options['input'] : "text";
            $saved_value = isset($custom_field_values[$name]) ? $custom_field_values[$name][0] : "";

            echo '<div>';

            $method_name = 'render' . PostTypeNoteUtil::pascalize($options['input']);
            PostTypeNoteFormRenderer::$method_name($name, $saved_value, $options);

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

      foreach ($post_types as $post_type_name => $options) {
        if (isset($options['sortable_columns'])) {
          add_filter('manage_edit-' . $post_type_name . '_sortable_columns', 'PostTypeNote::sortableColumns');
        }
      }
    }

    public static function sortableColumns() {
      $post_types = self::readConfig();

      $sortable = $post_types[get_post_type()]['sortable_columns'];
      foreach ($sortable as $i => $column) {
        $sortable_columns[$column] = $column;
      }

      return $sortable_columns;
    }

    public static function addActions() {
      add_action('init', 'PostTypeNote::init');
      add_action('add_meta_boxes', 'PostTypeNote::addMetaBoxes');
      add_action('save_post', 'PostTypeNote::saveMeta');

      add_action('manage_posts_columns', 'PostTypeNote::manageColumns');
      add_action('manage_posts_custom_column', 'PostTypeNote::manageCustomColumns', 10, 2);
    }
  }

  $include_path = plugin_dir_path(__FILE__) . 'includes';
  require_once($include_path . '/PostTypeNoteFormRenderer.php');
  require_once($include_path . '/PostTypeNoteUtil.php');

  $GLOBALS['post-type-note'] = new PostTypeNote();
  PostTypeNote::addActions();
  PostTypeNote::manageSortableColumns();
  // add_filter('manage_edit-hoge_sortable_columns', 'PostTypeNote::hoge');
}

/*
TODO: show args in admin console
TODO: manage custom field. build forms and save input values
TODO: comment
TODO: add_action('save_post', save_meta_func)
TODO: add_filter('manage_edit-<post_type>_sortable_columns', )
TODO: add_filter('request', <order>)
*/
