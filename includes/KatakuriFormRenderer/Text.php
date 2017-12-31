<?php
class KatakuriFormRendererText {
  public static function render($field_name, $saved_value, $options) {
    echo self::build($field_name, $saved_value, $options);
  }

  public static function build($field_name, $saved_value, $options) {
    $html = KatakuriFormRendererLabel::build($field_name, $options);

    $size = isset($options['size']) ? $options['size'] : '40';
    $html .= '<input name="' . $field_name . '" type="text" value="' . $saved_value . '" size="' . $size . '">';

    return $html;
  }
}