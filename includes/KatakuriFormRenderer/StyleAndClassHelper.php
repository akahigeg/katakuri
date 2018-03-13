<?php
class KatakuriFormRendererHelperStyleAndClass {
  public static function forInput($options) {
    $attrs = array();
    if (isset($options['class'])) {
      $attrs[] = 'class="' . $options['class'] . '"';
    }
    if (isset($options['style'])) {
      $attrs[] = 'style="' . $options['style'] . '"';
    }
    return implode(' ', $attrs);
  }

  public static function forLabel($options) {
    $attrs = array();
    if (isset($options['label_class'])) {
      $attrs[] = 'class="' . $options['label_class'] . '"';
    }
    if (isset($options['label_style'])) {
      $attrs[] = 'style="' . $options['label_style'] . '"';
    }
    return implode(' ', $attrs);
  }

  public static function forDescription($options) {
    $attrs = array();
    if (isset($options['description_class'])) {
      $attrs[] = 'class="' . $options['description_class'] . '"';
    }
    if (isset($options['description_style'])) {
      $attrs[] = 'style="' . $options['description_style'] . '"';
    }
    if (empty($attrs)) {
      $attrs[] = 'class="katakuri-description"';
    }
    return implode(' ', $attrs);
  }
}