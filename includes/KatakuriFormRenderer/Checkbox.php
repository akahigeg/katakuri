<?php
class KatakuriFormRendererCheckbox {
  public static function render($field_name, $saved_value, $options) {
    echo self::build($field_name, $saved_value, $options);
  }

  public static function build($field_name, $saved_value, $options) {
    $html = KatakuriFormRendererLabel::build($field_name, $options);

    $style_and_class = KatakuriFormRendererHelperStyleAndClass::forInput($options);

    $saved_values = maybe_unserialize($saved_value);
    foreach ($options['values'] as $value) {
      if (is_array($value)) {
        $option_value = array_keys($value)[0];
        $option_label = array_values($value)[0];
      } else {
        $option_value = $value;
        $option_label = $value;
      }
      if (is_array($saved_values) && in_array($option_value, $saved_values)) {
        $checked = ' checked';
      } else {
        $checked = '';
      }
      $html .= '<label class="for-checkbox-value">';
      $html .= '<input type="checkbox" name="' . $field_name . '[]" value="' . $option_value . '" ' . $style_and_class . $checked . '>';
      $html .= $option_label . '</label> ';
    }

    $html .= KatakuriFormRendererDescription::build($field_name, $options);

    return $html;
  }
}
