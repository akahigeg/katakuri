<?php
class KatakuriFormRendererLabel {
  public static function render($field_name, $options) {
    echo self::build($field_name, $options);
  }

  public static function build($field_name, $options) {
    if (isset($options['label']) == false) {
      return;
    }

    $style_and_class = KatakuriFormRendererHelperStyleAndClass::forLabel($options);

    return '<label for="' . $field_name . '" ' . $style_and_class . '>' . $options['label'] . '</label>';
  }
}