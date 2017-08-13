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

    private static function registerTaxonomies($taxonomies, $post_type_name) {
      foreach ($taxonomies as $i => $taxonomy_name_and_args) {
        foreach ($taxonomy_name_and_args as $name => $args) {
          register_taxonomy($name, $post_type_name, $args);
        }
      }
    }

    public static function addMetaBoxes() {
      $post_types = self::readConfig();
      foreach ($post_types as $post_type_name => $options) {
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

            $method_name = 'render' . PostTypeNoteUtil::pascalize(str_replace('-', '_', $options['input']));
            PostTypeNoteFormRenderer::$method_name($name, $saved_value, $options);

            echo '</div>';
          }
        }
      }
    }

    public static function saveMeta($post_id) {
      $post_type_name = get_post_type($post_id);

      // var_dump($_POST);

      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
      }

      $post_types = self::readConfig();
      if (array_key_exists($post_type_name, $post_types)) {
        $custom_fields = $post_types[$post_type_name]['custom_fields'];
        foreach ($custom_fields as $custom_field) {
          foreach ($custom_field as $name => $options) {
            $input_type = isset($options['input']) ? $options['input'] : "text";

            switch ($options['input']) {
              case 'text':
              case 'textarea':
              case 'radio':
              case 'select':
                if (isset($_POST[$name])) {
                  update_post_meta($post_id, $name, sanitize_text_field($_POST[$name]));
                }
                break;
              case 'checkbox':
              case 'multiple-select':
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
  }

  class PostTypeNoteFormRenderer {
    public static function renderText($field_name, $saved_value, $options) {
      if (isset($options['label'])) {
        echo '<label for="' . $field_name . '">' . $options['label'] . '</label>';
      }
      $size = isset($options['size']) ? $options['size'] : '40';
      echo '<input name="' . $field_name . '" type="text" value="' . $saved_value . '" size="' . $size . '">';
    }

    public static function renderCheckbox($field_name, $saved_value, $options) {
      $saved_values = maybe_unserialize($saved_value);
      foreach ($options['values'] as $value) {
        if (is_array($value)) {
          $option_value = array_keys($value)[0];
          $option_label = array_values($value)[0];
        } else {
          $option_value = $value;
          $option_label = $value;
        }
        if (is_array($saved_values) && in_array($option_value, $saved_values)) {
          $checked = 'checked';
        } else {
          $checked = '';
        }
        echo '<label>';
        echo '<input type="checkbox" name="' . $field_name . '[]" value="' . $option_value . '" ' . $checked . '>';
        echo $option_label . '</label> ';
      }
    }

    public static function renderRadio($field_name, $saved_value, $options) {
      $checks = array();
      foreach ($options['values'] as $value) {
        if ($saved_value == $value) {
          $checks[] = $value;
        }
      }
      foreach ($options['values'] as $value) {
        if ($saved_value == $value || (count($checks) == 0 && $value == $options['default'])) {
          $checked = 'checked';
        } else {
          $checked = '';
        }
        echo '<label>';
        echo '<input type="radio" name="' . $field_name . '" value="' . $value . '" ' . $checked . '>';
        echo $value . '</label> ';
      }
    }

    public static function renderTextarea($field_name, $saved_value, $options) {
      if (isset($options['label'])) {
        echo '<label for="' . $field_name . '">' . $options['label'] . '</label>';
      }
      $rows = isset($options['rows']) ? $options['rows'] : '5';
      $cols = isset($options['cols']) ? $options['cols'] : '40';
      echo '<textarea name="' . $field_name . '" rows="' . $rows . '" cols="' . $cols . '">' . $saved_value . '</textarea>';
    }

    public static function renderCheckboxs($field_name, $saved_value, $options) {
      $checked = $saved_value == '1' ? 'checked' : '';

      echo '<label>';
      echo '<input type="checkbox" name="' . $field_name . '" value="1" ' . $checked . '>';
      echo $options['value'] . '</label> ';
    }

    public static function renderSelect($field_name, $saved_value, $options) {
      $saved_values = array($saved_value);
      if (isset($options['label'])) {
        echo '<label for="' . $field_name . '">' . $options['label'] . '</label>';
      }

      echo '<select name=' . $field_name . '>';
      self::renderOptions($saved_values, $options);
      echo '</select>';
    }

    public static function renderMultipleSelect($field_name, $saved_value, $options) {
      $saved_values = maybe_unserialize($saved_value);

      if (isset($options['label'])) {
        echo '<label for="' . $field_name . '">' . $options['label'] . '</label>';
      }

      $size = isset($options['size']) ? $options['size'] : '3';
      $width_style = isset($options['width']) ? 'style="width:' . $options['width'] . 'px;' : '';

      echo '<select name=' . $field_name . '[] size="' . $size . '" ' . $width_style . '" multiple>';
      self::renderOptions($saved_values, $options);
      echo '</select>';
    }

    public static function renderOptions($saved_values, $options) {
      foreach ($options['values'] as $value) {
        if (is_array($value)) {
          $option_value = array_keys($value)[0];
          $option_label = array_values($value)[0];
        } else {
          $option_value = $value;
          $option_label = $value;
        }
        if (in_array($option_value, $saved_values)) {
          $selected = 'selected';
        } else {
          $selected = '';
        }
        echo '<option value="' . $option_value . '" ' . $selected . '>' . $option_label . '</option>';
      }
    }
  }
  
  class PostTypeNoteUtil {
    public static function underscore($str) {
      return ltrim(strtolower(preg_replace('/[A-Z]/', '_\0', $str)), '_');
    }

    public static function camelize($str) {
      return lcfirst(strtr(ucwords(strtr($str, array('_' => ' '))), array(' ' => '')));
    }

    public static function pascalize($str) {
      return ucfirst(strtr(ucwords(strtr($str, array('_' => ' '))), array(' ' => '')));
    }
  }

  $GLOBALS['post-type-note'] = new PostTypeNote();
  add_action('init', 'PostTypeNote::init');
  add_action('add_meta_boxes', 'PostTypeNote::addMetaBoxes');
  add_action('save_post', 'PostTypeNote::saveMeta');
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