<?php
class KatakuriUtil {
  public static function underscore($str) {
    return ltrim(strtolower(preg_replace('/[A-Z]/', '_\0', $str)), '_');
  }

  public static function camelize($str) {
    return lcfirst(strtr(ucwords(strtr($str, array('_' => ' '))), array(' ' => '')));
  }

  public static function pascalize($str) {
    return ucfirst(strtr(ucwords(strtr($str, array('_' => ' '))), array(' ' => '')));
  }

  public function __call($name, $args) {
    echo $name;
  }
}
