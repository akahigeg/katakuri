<?php
class KatakuriFormRendererSelect {
  public static function render($field_name, $saved_value, $options) {
    echo self::build($field_name, $saved_value, $options);
  }

  public static function build($field_name, $saved_value, $options) {
    $size = isset($options['size']) ? ' size="' . $options['size'] . '"' : '';
    $multiple = isset($options['multiple']) && $options['multiple'] == true ? ' multiple' : '';
    if (!empty($multiple)) {
      if (!isset($options['label_style']) && !isset($options['label_class'])) {
        $options['label_class'] = 'for-multiple-select';
      }
    }

    $html = KatakuriFormRendererLabel::build($field_name, $options);

    $saved_values = maybe_unserialize($saved_value);

    $style_and_class = KatakuriFormRendererHelperStyleAndClass::forInput($options);

    $html .= '<select name="' . $field_name . '[]"' . $size . ' ' . $style_and_class . $multiple . '>';
    $html .= self::buildOptions($saved_values, $options);
    $html .= '</select>';

    $html .= KatakuriFormRendererDescription::build($field_name, $options);

    return $html;
  }

  public static function buildOptions($saved_values, $options) {
    $html = '';
    foreach ($options['values'] as $value) {
      if (is_array($value)) {
        $option_value = array_keys($value)[0];
        $option_label = array_values($value)[0];
      } else {
        $option_value = $value;
        $option_label = $value;
      }
      if (is_array($saved_values) && in_array($option_value, $saved_values)) {
        $selected = ' selected';
      } else {
        $selected = '';
      }
      $html .= '<option value="' . $option_value . '"' . $selected . '>' . $option_label . '</option>';
    }

    return $html;
  }
}