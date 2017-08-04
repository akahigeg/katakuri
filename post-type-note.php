<?php
/*
Plugin Name: post-type-note
Version: 0.1-alpha
Description: PLUGIN DESCRIPTION HERE
Author: akahigeg
Author URI: http://brassworks.jp/
Plugin URI: http://brassworks.jp/
Text Domain: post-type-note
Domain Path: /languages
*/

if (!array_key_exists('post-type-note', $GLOBALS)) {
  class PostTypeNote{
  	public static function init() {
      $post_types = self::readConfig();

      # register each post types
      foreach($post_types as $post_type_name => $options) {
        self::registerPostType($post_type_name, $options);
      }
    }

    public static function readConfig() {
      return yaml_parse_file(plugin_dir_path(__FILE__) . '/post-types.yml');
    }

    private static function registerPostType($post_type_name, $options) {
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
        $options["taxonomies"] = $taxonomy_names;
      }

      # add meta box in admin console for custom fields
      if (array_key_exists('custom_fields', $options)) {
        # $options['custom_fields'] doesn't need from register post_type function
        unset($options['custom_fields']); 
      }

      register_post_type($post_type_name, $options);

      # register taxonomies
      self::registerTaxonomies($taxonomies, $post_type_name);
    }

    # indent
    private static function registerTaxonomies($taxonomies, $post_type_name) {
      foreach ($taxonomies as $i => $taxonomy_name_and_args) {
        foreach ($taxonomy_name_and_args as $name => $args) {
          register_taxonomy($name, $post_type_name, $args);
        }
      }
    }

    public static function addMetaBoxes() {
      $post_types = self::readConfig();
      foreach($post_types as $post_type_name => $options) {
        # support one meta box each post type now.
        if (array_key_exists('custom_fields', $options)) {
          $custom_fields = $options['custom_fields'];
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

            switch ($options['input']) {
              case 'text':
                self::renderTextField($name, $options);
                break;
              case 'multiple-checkbox':
                self::renderMultipleCheckbox($name, $options);
                break;
              default:
            }

            echo '</div>';
          }
        } 
      }
    }

    public static function renderTextField($field_name, $options) {
      if (isset($options['label'])) {
        echo '<label for="' . $field_name . '">' . $options['label'] . '</label>';
      }
      $size = isset($options['size']) ? $options['size'] : '40';
      echo '<input name="' . $name . '" type="text" value="' . $saved_value . '" size="' . $size . '">';
    }

    public static function renderMultipleCheckbox($field_name, $options) {
                foreach ($options['values'] as $value) {
                  $checked = '';
                  echo '<label>';
                  echo '<input type="checkbox" name="' . $field_name . '[]" value="' . $value . '" ' . $checked . '>';
                  echo $value . '</label> ';
                }
    }
  }
  $GLOBALS['post-type-note'] = new PostTypeNote();
  add_action('init', 'PostTypeNote::init');
  add_action('add_meta_boxes', 'PostTypeNote::addMetaBoxes');
}

/*
TODO: show args in admin console
TODO: manage custom field. build forms and save input values
TODO: comment
TODO: add_action('save_post', save_meta_func)
TODO: add_filter('manage_<post_type>_posts_columns', )
TODO: add_action('manage_<post_type>_posts_custom_columns', )
TODO: add_filter('manage_edit-<post_type>_sortable_columns', )
TODO: add_filter('request', <order>)
*/