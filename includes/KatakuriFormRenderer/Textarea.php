<?php
class KatakuriFormRendererTextarea {
  public static function render($field_name, $saved_value, $options) {
    echo self::build($field_name, $saved_value, $options);
  }

  public static function build($field_name, $saved_value, $options) {
    $html = KatakuriFormRendererLabel::build($field_name, $options);

    $rows = isset($options['rows']) ? $options['rows'] : '5';
    $cols = isset($options['cols']) ? $options['cols'] : '40';
    $html .= '<textarea name="' . $field_name . '" rows="' . $rows . '" cols="' . $cols . '">' . $saved_value . '</textarea>';

    return $html;
  }
}