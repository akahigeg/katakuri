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

      $post_types = self::readConfig();
      if (array_key_exists($post_type_name, $post_types)) {
        $custom_fields = $post_types[$post_type_name]['custom_fields'];
        foreach ($custom_fields as $custom_field) {
          foreach ($custom_field as $name => $options) {
            echo $name;
          }
        } 
      }
    }

    public static function renderSomePostMetaBox() {
      echo "OK";
    }
  }
  $GLOBALS['post-type-note'] = new PostTypeNote();
  add_action('init', 'PostTypeNote::init');
  add_action('add_meta_boxes', 'PostTypeNote::addMetaBoxes');
}

class String {
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