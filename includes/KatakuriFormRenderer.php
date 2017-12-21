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

  public static function renderImage($field_name, $saved_value, $options) {
    echo self::buildImage($field_name, $saved_value, $options);
  }

  public static function buildImage($field_name, $saved_value, $options) {
    $html = self::buildLabel($field_name, $options);

    $html .= '<input type="hidden" id="' . $field_name . '-image" name="' . $field_name . '" class="custom_media_url" value="' . $saved_value . '">';
    if ($saved_value == '') {
      $html .= '<div id="' . $field_name . '-image-wrapper"></div>';
    } else {
      $html .= '<div id="' . $field_name . '-image-wrapper">' . wp_get_attachment_image($saved_value, 'large') . '</div>';
    }
    $html .= '<p>
       <input type="button" class="button button-secondary ' . $field_name . '-media-button" id="' . $field_name . '-media-button" name="media-button" value="Add" />
       <input type="button" class="button button-secondary ' . $field_name . '-media-remove" id="' . $field_name . '-media-remove" name="media-remove" value="Remove" />
    </p>';

    $html .= self::buildMediaJS($field_name, $saved_value, $options);

    return $html;
  }

  // ref: http://jeroensormani.com/how-to-include-the-wordpress-media-selector-in-your-plugin/
  public static function buildMediaJS($field_name, $saved_value, $options) {
    $script = <<<"EOM"
    <script>
     jQuery(document).ready( function($) {
       function katakuri_media_upload(button_class) {
         var _custom_media = true,
         _orig_send_attachment = wp.media.editor.send.attachment;
         $('body').on('click', button_class, function(e) {
           var button_id = '#'+$(this).attr('id');
           var send_attachment_bkp = wp.media.editor.send.attachment;
           var button = $(button_id);
           _custom_media = true;
           wp.media.editor.send.attachment = function(props, attachment){
             if ( _custom_media ) {
               $('#{$field_name}-image').val(attachment.id);
               $('#{$field_name}-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:150px;float:none;" />');
               $('#{$field_name}-image-wrapper .custom_media_image').attr('src',attachment.url).css('display','block');
             } else {
               return _orig_send_attachment.apply( button_id, [props, attachment] );
             }
            }
         wp.media.editor.open(button);
         return false;
       });
     }
     katakuri_media_upload('.{$field_name}-media-button.button'); 
     $('body').on('click','.{$field_name}-media-remove',function(){
       $('#{$field_name}-image').val('');
       $('#{$field_name}-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
     });
     // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-category-ajax-response
     $(document).ajaxComplete(function(event, xhr, settings) {
       var queryStringArr = settings.data.split('&');
       if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
         var xml = xhr.responseXML;
         \$response = $(xml).find('term_id').text();
         if(\$response!=""){
           // Clear the thumb image
           $('#{$field_name}-image-wrapper').html('');
         }
       }
     });
   });
   </script>
EOM;
    return $script;
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
      if (is_array($value)) {
        $option_value = array_keys($value)[0];
        $option_label = array_values($value)[0];
      } else {
        $option_value = $value;
        $option_label = $value;
      }
      if ($saved_value == $option_value) {
        $checked = ' checked';
      } else {
        $checked = '';
      }
      $html .= '<label style="padding-right: 5px;">';
      $html .= '<input type="radio" name="' . $field_name . '" value="' . $option_value . '"' . $checked . '>';
      $html .= $option_label . '</label> ';
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
    $html .= '<textarea name="' . $field_name . '" rows="' . $rows . '" cols="' . $cols . '">' . $saved_value . '</textarea>';

    return $html;
  }

  public static function renderSelect($field_name, $saved_value, $options) {
    echo self::buildSelect($field_name, $saved_value, $options);
  }

  public static function buildSelect($field_name, $saved_value, $options) {
    $html = self::buildLabel($field_name, $options);

    $saved_values = maybe_unserialize($saved_value);

    $size = isset($options['size']) ? ' size="' . $options['size'] . '"' : '';
    $width_style = isset($options['width']) ? ' style="width:' . $options['width'] . 'px;"' : '';
    $multiple = isset($options['multiple']) && $options['multiple'] == true ? ' multiple' : '';

    $html .= '<select name="' . $field_name . '[]"' . $size . $width_style . $multiple . '>';
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
      if (is_array($saved_values) && in_array($option_value, $saved_values)) {
        $selected = ' selected';
      } else {
        $selected = '';
      }
      $html .= '<option value="' . $option_value . '"' . $selected . '>' . $option_label . '</option>';
    }

    return $html;
  }

  public static function renderReference($field_name, $saved_value, $options) {
    echo self::buildReference($field_name, $saved_value, $options);
  }

  public static function buildReference($field_name, $saved_value, $options) {
    $relation_posts = get_posts($options['reference_query_options']);
    if (empty($relation_posts)) {
      $html .= 'nothing to select';
      return $html;
    }

    // select, radio, checkbox
    $values = array();
    foreach ($relation_posts as $post) {
      $values[$post->ID] = $post->post_title;
    }
    $options['values'] = $values;

    $html .= self::buildSelect($field_name, $saved_value, $options);

    return $html;
  }

  public static function buildLabel($field_name, $options) {
    if (isset($options['label'])) {
      return '<label for="' . $field_name . '" style="padding-right: 8px; vertical-align: middle;">' . $options['label'] . '</label>';
    }
  }
}

