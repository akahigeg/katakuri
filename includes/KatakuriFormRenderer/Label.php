<?php
class KatakuriFormRendererLabel {
  public static function render($field_name, $options) {
    echo self::build($field_name, $options);
  }

  public static function build($field_name, $options) {
    if (isset($options['label']) == false) {
      return;
    }

    $attrs = array();
    if (isset($options['label_class'])) {
      $attrs[] = 'class="' . $options['label_class'] . '"';
    }
    if (isset($options['label_style'])) {
      $attrs[] = 'style="' . $options['label_style'] . '"';
    }

    return '<label for="' . $field_name . '" ' . implode(' ', $attrs) . '>' . $options['label'] . '</label>';
  }
}