<?php
class KatakuriFormRendererText {
  public static function render($field_name, $saved_value, $options) {
    echo self::build($field_name, $saved_value, $options);
  }

  public static function build($field_name, $saved_value, $options) {
    $html = KatakuriFormRendererLabel::build($field_name, $options);

    $style_and_class = KatakuriFormRendererHelperStyleAndClass::forInput($options);

    $size = isset($options['size']) ? $options['size'] : '40';
    $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
    $html .= '<input name="' . $field_name . '" type="text" value="' . $saved_value . '" size="' . $size . '" placeholder="' . $placeholder . '" ' . $style_and_class . '>';

    $html .= KatakuriFormRendererDescription::build($field_name, $options);

    return $html;
  }
}