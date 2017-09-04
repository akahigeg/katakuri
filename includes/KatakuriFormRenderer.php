<?php
class KatakuriFormRenderer {
  public static function renderText($field_name, $saved_value, $options) {
    echo self::buildText($field_name, $saved_value, $options);
  }

  public static function buildText($field_name, $saved_value, $options) {
    $html = self::buildLabel($field_name, $options);

    $size = isset($options['size']) ? $options['size'] : '40';
    $html .= '<input name="' . $field_name . '" type="text" value="' . $saved_value . '" size="' . $size . '">';

    return $html;
  }

  public static function renderCheckbox($field_name, $saved_value, $options) {
    echo self::buildCheckbox($field_name, $saved_value, $options);
  }

  public static function buildCheckbox($field_name, $saved_value, $options) {
    $html = self::buildLabel($field_name, $options);

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
      $html .= '<label style="padding-right: 5px;">';
      $html .= '<input type="checkbox" name="' . $field_name . '[]" value="' . $option_value . '"' . $checked . '>';
      $html .= $option_label . '</label> ';
    }

    return $html;
  }

  public static function renderRadio($field_name, $saved_value, $options) {
    echo self::buildRadio($field_name, $saved_value, $options);
  }

  public static function buildRadio($field_name, $saved_value, $options) {
    $html = self::buildLabel($field_name, $options);

    $checks = array();
    foreach ($options['values'] as $value) {
      if ($saved_value == $value) {
        $checks[] = $value;
      }
    }
    foreach ($options['values'] as $value) {
      if ($saved_value == $value) {
        $checked = ' checked';
      } else {
        $checked = '';
      }
      $html .= '<label style="padding-right: 5px;">';
      $html .= '<input type="radio" name="' . $field_name . '" value="' . $value . '"' . $checked . '>';
      $html .= $value . '</label> ';
    }

    return $html;
  }

  public static function renderTextarea($field_name, $saved_value, $options) {
    echo self::buildTextarea($field_name, $saved_value, $options);
  }

  public static function buildTextarea($field_name, $saved_value, $options) {
    $html = self::buildLabel($field_name, $options);

    $rows = isset($options['rows']) ? $options['rows'] : '5';
    $cols = isset($options['cols']) ? $options['cols'] : '40';
    $html .= '<textarea name="' . $field_name . '" rows="' . $rows . '" cols="' . $cols . '" style="margin-top: 3px;">' . $saved_value . '</textarea>';

    return $html;
  }

  public static function renderSelect($field_name, $saved_value, $options) {
    echo self::buildSelect($field_name, $saved_value, $options);
  }

  public static function buildSelect($field_name, $saved_value, $options) {
    $html = self::buildLabel($field_name, $options);

    $saved_values = maybe_unserialize($saved_value);

    $size = isset($options['size']) ? 'size="' . $options['size'] . '"' : '';
    $width_style = isset($options['width']) ? 'style="width:' . $options['width'] . 'px;"' : '';
    $multiple = isset($options['multiple']) && $options['multiple'] == true ? 'multiple' : '';

    $html .= '<select name="' . $field_name . '[]" ' . $size . ' ' . $width_style . ' ' . $multiple . '>';
    $html .= self::buildOptions($saved_values, $options);
    $html .= '</select>';

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
      if (in_array($option_value, $saved_values)) {
        $selected = 'selected';
      } else {
        $selected = '';
      }
      $html .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_label . '</option>';
    }

    return $html;
  }

  public static function buildLabel($field_name, $options) {
    if (isset($options['label'])) {
      return '<label for="' . $field_name . '" style="padding-right: 8px; vertical-align: middle;">' . $options['label'] . '</label>';
    }
  }
}

