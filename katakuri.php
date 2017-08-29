<?php
/*
Plugin Name: Katakuri
Version: 0.1-alpha
Description: Define custom post types, taxonomies and custom fields by static YAML file
Author: akahigeg
Author URI: http://higelog.brassworks.jp/
Plugin URI: https://github.com/akahigeg/katakuri
Text Domain: katakuri
License: Apache License 2.0
Domain Path: /languages
*/

if (!array_key_exists('katakuri', $GLOBALS)) {
  $include_path = plugin_dir_path(__FILE__) . 'includes';
  require_once($include_path . '/Katakuri.php');
  require_once($include_path . '/KatakuriFormRenderer.php');
  require_once($include_path . '/KatakuriUtil.php');

  $GLOBALS['katakuri'] = new Katakuri();
  Katakuri::addActions();
  Katakuri::manageSortableColumns();
  // add_filter('manage_edit-hoge_sortable_columns', 'Katakuri::hoge');
}

/*
TODO: show args in admin console
TODO: manage custom field. build forms and save input values
TODO: comment
TODO: add_filter('request', <order>)
*/
