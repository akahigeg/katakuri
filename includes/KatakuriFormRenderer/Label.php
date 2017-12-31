<?php
class KatakuriFormRendererLabel {
  public static function render($field_name, $options) {
    echo self::build($field_name, $options);
  }

  public static function build($field_name, $options) {
    if (isset($options['label'])) {
      return '<label for="' . $field_name . '" style="padding-right: 8px; vertical-align: middle;">' . $options['label'] . '</label>';
    }
  }
}