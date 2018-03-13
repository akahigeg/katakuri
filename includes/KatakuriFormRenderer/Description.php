<?php
class KatakuriFormRendererDescription {
  public static function render($field_name, $options) {
    echo self::build($field_name, $options);
  }

  public static function build($field_name, $options) {
    if (isset($options['description']) == false) {
      return;
    }

    $style_and_class = KatakuriFormRendererHelperStyleAndClass::forDescription($options);

    return '<p ' . $style_and_class . '>' . $options['description'] . '</p>';
  }
}