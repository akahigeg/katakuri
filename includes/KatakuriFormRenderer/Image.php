<?php
class KatakuriFormRendererImage {
  public static function render($field_name, $saved_value, $options) {
    echo self::build($field_name, $saved_value, $options);
  }

  public static function build($field_name, $saved_value, $options) {
    $html = KatakuriFormRendererLabel::build($field_name, $options);

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

    $html .= KatakuriFormRendererDescription::build($field_name, $options);

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
}