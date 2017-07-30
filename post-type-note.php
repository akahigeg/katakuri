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
  	static public function init() {
      $post_types = yaml_parse_file(plugin_dir_path(__FILE__) . '/post-types.yml');

      foreach($post_types as $post_type_name => $options) {
      	# taxonomies
      	$taxonomies = array();
      	if (array_key_exists('taxonomies', $options)) {
          $taxonomies = $options["taxonomies"];
          $taxonomy_names = array();
          foreach ($taxonomies as $i => $taxonomy_name_and_args) {
          	foreach ($taxonomy_name_and_args as $name => $args) {
              $taxonomy_names[] = $name;
          	}
          }
          $options["taxonomies"] = $taxonomy_names;
      	}

    	register_post_type($post_type_name, $options);

    	foreach ($taxonomies as $i => $taxonomy_name_and_args) {
          foreach ($taxonomy_name_and_args as $name => $args) {
            register_taxonomy($name, $post_type_name, $args);
          }
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
*/