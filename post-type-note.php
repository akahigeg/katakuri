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
      $post_types = yaml_parse_file(plugin_dir_path(__FILE__) . '/post-types.yml');

      # register each post types
      foreach($post_types as $post_type_name => $options) {
        self::registerPostType($post_type_name, $options);
      }
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
        self::addCustomFieldsMetaBoxes($options['custom_fields']);

        # $options['custom_fields'] doesn't need from register post_type function
        unset($options['custom_fields']); 
      }

      register_post_type($post_type_name, $options);

      # register taxonomies
      self::registerTaxonomies($taxonomies, $post_type_name);
    }

    private static function addCustomFieldsMetaBoxes($custom_fields) {

    }

    private static function registerTaxonomies($taxonomies, $post_type_name) {
      foreach ($taxonomies as $i => $taxonomy_name_and_args) {
        foreach ($taxonomy_name_and_args as $name => $args) {
          register_taxonomy($name, $post_type_name, $args);
        }
      }
    }
  }
  $GLOBALS['post-type-note'] = new PostTypeNote();
  add_action('init', 'PostTypeNote::init');
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