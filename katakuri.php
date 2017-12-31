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
  require_once($include_path . '/KatakuriUtil.php');

foreach (scandir($include_path . '/KatakuriFormRenderer') as $filename) {
    $path = $include_path . '/KatakuriFormRenderer' . '/' . $filename;
    if (is_file($path)) {
        require_once $path;
    }
}


  $GLOBALS['katakuri'] = new Katakuri();
  Katakuri::addActions();
  Katakuri::enableSortableColumns();
  Katakuri::enableTaxonomyCustomFields();
}

/*
TODO: show args in admin console
TODO: comment
TODO: add_filter('request', <order>)
*/
