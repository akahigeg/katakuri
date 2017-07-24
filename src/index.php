<?php
/*
Plugin Name: wp-cpt-json
Version: 0.1-alpha
Description: PLUGIN DESCRIPTION HERE
Author: akahigeg
Author URI: http://brassworks.jp/
Plugin URI: http://brassworks.jp/
Text Domain: wp-cpt-json
Domain Path: /languages
*/

if (!array_key_exists('wp-cpt-json', $GLOBALS)) {
  class WpCptJson{
  	static public function init() {
	  $post_type_name = 'same_type';
	  $options = array(
      'labels' => array(
        'name' => __('SameType')
      ),
      'public' => true,
      'has_archive' => true,
    );
	  register_post_type($post_type_name, $options);

  	}
  }
  $GLOBALS['wp-cpt-json'] = new WpCptJson();
  add_action('init', 'WpCptJson::init');
}