<?php
class KatakuriFormRenderer {
  public static function renderText($field_name, $saved_value, $options) {
    self::renderLabel($field_name, $options);

    $size = isset($options['size']) ? $options['size'] : '40';
    echo '<input name="' . $field_name . '" type="text" value="' . $saved_value . '" size="' . $size . '">';
  }

  public static function renderCheckbox($field_name, $saved_value, $options) {
    self::renderLabel($field_name, $options);

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
        $checked = 'checked';
      } else {
        $checked = '';
      }
      echo '<label style="padding-right: 5px;">';
      echo '<input type="checkbox" name="' . $field_name . '[]" value="' . $option_value . '" ' . $checked . '>';
      echo $option_label . '</label> ';
    }
  }

  public static function renderRadio($field_name, $saved_value, $options) {
    self::renderLabel($field_name, $options);

    $checks = array();
    foreach ($options['values'] as $value) {
      if ($saved_value == $value) {
        $checks[] = $value;
      }
    }
    foreach ($options['values'] as $value) {
      if ($saved_value == $value || (count($checks) == 0 && $value == $options['default'])) {
        $checked = 'checked';
      } else {
        $checked = '';
      }
      echo '<label style="padding-right: 5px;">';
      echo '<input type="radio" name="' . $field_name . '" value="' . $value . '" ' . $checked . '>';
      echo $value . '</label> ';
    }
  }

  public static function renderTextarea($field_name, $saved_value, $options) {
    self::renderLabel($field_name, $options);

    $rows = isset($options['rows']) ? $options['rows'] : '5';
    $cols = isset($options['cols']) ? $options['cols'] : '40';
    echo '<textarea name="' . $field_name . '" rows="' . $rows . '" cols="' . $cols . '" style="margin-top: 3px;">' . $saved_value . '</textarea>';
  }

  public static function renderSelect($field_name, $saved_value, $options) {
    self::renderLabel($field_name, $options);

    $saved_values = maybe_unserialize($saved_value);

    $size = isset($options['size']) ? 'size="' . $options['size'] . '"' : '';
    $width_style = isset($options['width']) ? 'style="width:' . $options['width'] . 'px;"' : '';
    $multiple = isset($options['multiple']) && $options['multiple'] == true ? 'multiple' : '';

    echo '<select name="' . $field_name . '[]" ' . $size . ' ' . $width_style . ' ' . $multiple . '>';
    self::renderOptions($saved_values, $options);
    echo '</select>';
  }

  public static function renderOptions($saved_values, $options) {
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
      echo '<option value="' . $option_value . '" ' . $selected . '>' . $option_label . '</option>';
    }
  }

  public static function renderLabel($field_name, $options) {
    if (isset($options['label'])) {
      echo '<label for="' . $field_name . '" style="padding-right: 8px; vertical-align: middle;">' . $options['label'] . '</label>';
    }
  }
}

