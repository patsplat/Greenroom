<?php

class BaseField {
  function __construct($name='__missing__', $label='', $options=array()) {
    $this->name = $name;
    $this->label = $label;
    $this->options = $options;
    
    if (!isset($this->options['widget'])) {
      $this->options['widget'] = preg_replace('/field/i', 'widget', strtolower(get_class($this)));
    }
  }
  function errors($value) {
    $errors = array();
    if (isset($this->options['required']) && $this->options['required']) {
      if (empty($value)) $errors[]= 'Required field.';
    }
    return $errors;
  }
  function normalize($value, $file=false) {
    return $value;
  }
  function normalize_from_db($value) {
    return $value;
  }
  function normalize_to_db($value) {
    return $value;
  }
  static function client_setup_html_head() { return false; }
  function widget($show_errors=false, $value="--missing--") {
    if ($value == "--missing--") $value = $this->submitted_value();
    $widget = sprintf(
      "<input type='text' name='%s' id='id_%s' value='%s' class='%s'>",
      $this->name, $this->name, htmlspecialchars($value), $this->cssclass()
    );
    if ($show_errors) {
      $errors = $this->errors($value);
      if (!empty($errors)) {
        $widget .= sprintf(
          "<div class='error'>%s</div>",
          implode(".  ", $this->errors)
        );
      }
    }
    return $widget;
  }
  function set_default_value($default) {
    if (!isset($_REQUEST[$this->name])) {
      $_REQUEST[$this->name] = $default;
    }
  }
  function submitted_value() {
    return isset($_REQUEST[$this->name]) ? $_REQUEST[$this->name] : false;
  }
  function cssclass() {
    $classes = array(
      preg_replace('/field/i', '', strtolower(get_class($this))),
      $this->name
    );
    return implode(' ', $classes);
  }
  function labelclass() {
    return isset($this->options['required']) && $this->options['required'] ?  'required' : '';
  }
  function html($show_errors, $wrapper="%s") {
    $row = sprintf(
      "<label class='%s'>%s</label>%s",
      $this->labelclass(),
      $this->label,
      $this->widget($show_errors)
    );
    return sprintf($wrapper, $row);
  }
  function requires_multipart_form() {
    return false;
  }
  function to_search_field() {
    $this->options['required'] = false;
    return $this;
  }
  function empty_value() { return false; }
  function display($value) { return $value; }
}



class HiddenField extends BaseField {
  /**
  Hidden fields ignore the wrapper, and just output the form field.
  */
  function html($show_errors=false, $wrapper="%s") {
    return sprintf(
      "<input type='hidden' name='%s' id='id_%s' value='%s' class='%s'>",
      $this->name, $this->name, htmlspecialchars($this->submitted_value()), $this->cssclass()
    );
  }
}



class CharField extends BaseField {
  function normalize($value) {
    return trim($value);
  }
  function errors($value) {
    $errors = parent::errors($value);
    if (isset($this->options['length']) && $this->options['length']) {
      if (stlen($value) > $this->options['length']) {
        $errors[] = 'Must be under '.$this->options['length'].' characters.';
      }
    }
    return $errors;
  }
}

class TitleField extends CharField {
  function html($show_errors, $wrapper="%s") {
    return sprintf($wrapper, $this->widget($show_errors));
  }
}

class ChoiceField extends BaseField {
  function __construct($name='--missing--', $label='', $options=array()) {
    parent::__construct($name, $label, $options);
    if (!isset($options['choices']) && !isset($options['static_instance']) && !$options['static_instance']) {
      throw new Exception('choices option is required');
    }
  }
  function errors($value) {
    $errors = parent::errors($value);
    if (!empty($value) && !in_array($value, array_keys($this->options['choices']))) {
      $errors[] = 'Invalid choice.';
    }
    return $errors;
  }
  function widget($show_errors=false, $value="--missing--") {
    if ($value == "--missing--") $value = $this->submitted_value();
    $widget = sprintf(
      "<select name='%s' id='id_%s' class='%s'>\n",
      $this->name, $this->name, $this->cssclass()
    );
    $widget .= "<option value=''></option>\n";

    foreach($this->options['choices'] as $option_value => $option_label) {
      $selected = $option_value == $value ? ' selected ' : '';
      $widget .= sprintf(
        "<option value='%s' %s>%s</option>\n",
        htmlspecialchars($option_value), $selected, htmlspecialchars($option_label)
      );
    }
    $widget .= "</select>";
    
    if ($show_errors) {
      $errors = $this->errors($value);
      if (!empty($errors)) {
        $widget .= sprintf(
          "<div class='error'>%s</div>",
          implode(".  ", $errors)
        );
      }
    }
    return $widget;
  }
}

class MultipleChoiceField extends BaseField {
  function __construct($name='--missing--', $label='', $options=array()) {
    parent::__construct($name, $label, $options);
    if (!isset($options['choices']) && !isset($options['static_instance']) && !$options['static_instance']) {
      throw new Exception('choices option is required');
    }
  }
  function errors($value) {
    $errors = parent::errors($value);
    foreach($value as $v) {
      if (!in_array($v, array_keys($this->options['choices']))) {
        $errors[] = 'Invalid choice ('.$v.').';
        break;
      }
    }
    return $errors;
  }
  function submitted_value() {
    if (isset($_REQUEST[$this->name])) {
      return $_REQUEST[$this->name];
    } else {
      return array();
    }
  }
  function set_default($value) {
    if (!is_array($value)) $value = array($value);
    if(!isset($_REQUEST[$this->name.'[]'])) {
      $_REQUEST[$this->name.'[]'] = $value;
    }
  }
  function widget($show_errors=false, $value="--missing--") {
    if ($value == "--missing--") $value = $this->submitted_value();
    if (empty($value)) $value = array();
    $widget = "";
    $i = 0;
    foreach($this->options['choices'] as $option_value => $option_label) {
      $i++;
      $checked = in_array($option_value, $value) ? ' checked ' : '';
      $widget .= sprintf(
        "<input type='checkbox' id='%s_%s_id' class='%s' name='%s[]' value='%s' %s>",
        $this->name, $i, $this->cssclass(), $this->name,
        htmlspecialchars($option_value), $checked
      );
      $widget .= sprintf(
        "<label for='%s_%s_id' class='%s'>%s</label>\n",
        $this->name, $i, $this->cssclass(), $option_label
      );
    }
    if ($show_errors) {
      $errors = $this->errors($value);
      if (!empty($errors)) {
        $widget .= sprintf(
          "<div class='error'>%s</div>",
          implode(".  ", $errors)
        );
      }
    }
    return $widget;
  }
  function empty_value() { return array(); }
}

// Time

class DateTimeField extends BaseField {
  function errors($value) {
    $errors = parent::errors($value);
    if (!empty($value) && !strtotime($value)) {
      $errors[] = 'Invalid Date.';
    }
    return $errors;
  }
  function normalize($value) {
    if (!empty($value)) return date('Y-m-d h:m', strtotime($value));
  }
  function normalize_from_db($value) {
    if (!empty($value)) {
      if (is_a($value, 'MongoDate')) $value = $value->sec;
      if (is_string($value)) $value = strtotime($value);
    }
    return date('Y-m-d h:m', $value);
  }
  function normalize_to_db($value) {
    return new MongoDate(strtotime($value));
  }
  static function client_setup_html_head() {
    ?>
    <!-- start calendar -->
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo config('greenroom_path')->url ?>/vendor/jscalendar-1.0/skins/aqua/theme.css" />
    <script type="text/javascript" src="<?php echo config('greenroom_path')->url ?>/vendor/jscalendar-1.0/calendar.js"></script>
    <script type="text/javascript" src="<?php echo config('greenroom_path')->url ?>/vendor/jscalendar-1.0/lang/calendar-en.js"></script>
    <script type="text/javascript" src="<?php echo config('greenroom_path')->url ?>/vendor/jscalendar-1.0/calendar-setup.js"></script>
    <script>
    jQuery(document).ready(function() {
      jQuery('input.datetime').each(function() {
        Calendar.setup({
          inputField      :    this.id,   // id of the input field
          ifFormat        :    "%Y-%m-%d %H:%M", // format of the input field
          showsTime       :    true,
          timeFormat      :    "24"
        });
      });
      jQuery('input.date').each(function() {
        Calendar.setup({
          inputField      :    this.id,   // id of the input field
          ifFormat        :    "%Y-%m-%d", // format of the input field
          showsTime       :    false
        });
      });
    });
    </script>
    <!-- end calendar -->
    <?php
  }
  function display($value) {
    return relative_dateformat($value);
  }
}

class DateField extends DateTimeField {
  function normalize($value) {
    if (!empty($value)) return date('Y-m-d', strtotime($value));
  }
  function normalize_from_db($value) {
    if (!empty($value)) {
      if (is_a($value, 'MongoDate')) $value = $value->sec;
      if (is_string($value)) $value = strtotime($value);
    }
    return date('Y-m-d', $value);
  }
  static function client_setup_html_head() { return; }
}

// phone

define('EMAIL_REGEXP', '@^([-_\.a-zA-Z0-9]+)\@(([-_\.a-zA-Z0-9]+)\.)+[-_\.a-zA-Z0-9]+$@');
class EmailField extends BaseField {
  function errors($value) {
    $errors = parent::errors($value);
    if (!empty($value) && !preg_match(EMAIL_REGEXP, $value)) {
      $errors[] = 'Invalid Email Format.';
    }
    return $errors;
  }
}

class RichTextField extends BaseField {
  function widget($show_errors=false, $value="--missing--") {
    if ($value =="--missing--") $value = $this->submitted_value();
    $widget = "";
    if ($show_errors) {
      $errors = $this->errors($value);
      if (!empty($errors)) {
        $widget .= sprintf(
          "<div class='error'>%s</div>",
          implode(".  ", $this->errors)
        );
      }
    }
    $widget .= sprintf(
      "<textarea name='%s' id='id_%s' class='%s'>%s</textarea>",
      $this->name, $this->name, $this->cssclass(), htmlspecialchars($value)
    );
    return $widget;
  }
  static function client_setup_html_head() {
    ?>
    <!-- start tinymce -->    
    <script type="text/javascript" src="<?php echo config('greenroom_path')->url ?>/vendor/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
    <script type="text/javascript"> 
      $(document).ready(function() {
        $('textarea.richtext').tinymce({
          // Location of TinyMCE script
          script_url : '<?php echo config('greenroom_path')->url ?>/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js',
     
          // General options
          theme : "advanced",
          plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
     
          // Theme options
          theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,cut,copy,paste,pastetext,pasteword",
          theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,pagebreak,cleanup,help,code,fullscreen",
          theme_advanced_buttons3 : false,
          theme_advanced_buttons4 : false,
          theme_advanced_toolbar_location : "top",
          theme_advanced_toolbar_align : "left",
          theme_advanced_statusbar_location : "bottom",
          theme_advanced_resizing : true,
        });
      });
    </script> 
    <!-- end tinymce --> 
    <?php
  }
}

class ImageField extends BaseField {
  function __construct($name='--missing--', $label='', $options=array()) {
    if (!isset($options['static_instance'])) $options['static_instance'] = false;
    
    parent::__construct($name, $label, $options);
    
    if (!isset($options['action_url']) && !$options['static_instance']) {
      throw new Exception('action_url option is required');
    }
  }
  
  function widget($show_errors=false, $value="--missing--") {
    if ($value == "--missing--") $value = $this->submitted_value();
    $widget = sprintf(
      "<input type='file' name='%s_button' id='id_%s_button' class='%s'>",
      $this->name, $this->name, htmlspecialchars($value), $this->cssclass()
    );
    $widget .= sprintf(
      "<input type='hidden' name='%s' id='id_%s' value='%s'>",
      $this->name, $this->name, htmlspecialchars($value)
    );
    if ($show_errors) {
      $errors = $this->errors($value);
      if (!empty($errors)) {
        $widget .= sprintf(
          "<div class='error'>%s</div>",
          implode(".  ", $this->errors)
        );
      }
    }
    ob_start();
    ?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function(){
  var input = $('#id_<?php echo $this->name ?>_button');
  var parent = input.parent('div.form_row');
  var hidden = parent.find('input[type=hidden]')

  var add_thumbnail = function() {
    parent.find('img.thumb').remove()
    parent.find('a.remove').remove()
      
    thumburl = hidden.val()
    if (thumburl != '') {
      thumb = $('<a></a>').appendTo(parent).addClass('thumb').attr('target', '_blank').attr('href', thumburl)
      $('<img />').appendTo(thumb).addClass('thumb').attr('src', thumburl)
      $('<a></a>').appendTo(parent).addClass('remove').click(function() {
        var parent = $(this).parent('div.form_row')
        var hidden = parent.find('input[type=hidden]')
        var input =  parent.find('input[type=file]')
        hidden.val('')
        add_thumbnail()
      }).text('Remove')
    }
  }
  add_thumbnail(input, parent, hidden);
  
  new AjaxUpload(input, {
    action: '<?php echo $this->options["action_url"] ?>',
    name: input.attr('name'),
    onComplete : function(file, response) {
      hidden.val(response)
      add_thumbnail();
    }
  });
});/*]]>*/</script>
    <?php
    $widget .= ob_get_clean();
    
    return $widget;
  }
  
  static function client_setup_html_head() {
    ?>
<script type="text/javascript" src="<?php echo config('greenroom_path')->url ?>/vendor/valums-ajax-upload-6f977de/ajaxupload.js"></script>
<style type="text/css">
img.thumb { max-width:100px; max-height:100px; display:block;}
a.remove { cursor:pointer; }
</style>
    <?php
  }
}


/*
class Reference extends BaseField {
  function __construct($name='--missing--', $label='', $options=array()) {
    if (!isset($options['static_instance'])) $options['static_instance'] = false;
    if (!isset($options['multiple'])) $options['multiple'] == false;
    if (!isset($options['model']) && !$options['static_instance'])
      throw new Exception('model option is required');

    parent::__construct($name, $label, $options);
  }
  
  function widget($value) {
  
  }
}
*/


/*
+ initial form
+ form w/ submission
+ form w/ errors
+ clean form
*/
define('FORM_SUBMISSION_FLAG', '--form-submitted--');
class Form {
  function __construct($fields, $default_data=array()) {
    $this->fields = $fields;
    foreach($this->fields as $f) {
      if (isset($default_data[$f->name])) $f->set_default_value($default_data[$f->name]);
    }
    $this->is_multipart = false;
    foreach($this->fields as $f) {
      if ($f->requires_multipart_form()) {
        $this->is_multipart = true;
      }
    }
    $this->show_errors = isset($_REQUEST[FORM_SUBMISSION_FLAG]);
  }
  function open($action=false, $target=false) {
    $enctype = $this->is_multipart ? 'enctype="multipart/form-data"' : '';
    $target = $target ? 'target="'.$target.'"': '';
    $action = $action ? 'action="'.$action.'"': '';
    $open = sprintf('<form %s %s %s method="post">', $enctype, $target, $action);
    $open .= '<input type="hidden" name="'.FORM_SUBMISSION_FLAG.'" value="YES">';
    return $open;
  }
  function close() { return '</form>'; }
  function field2li($field) { return $field->html($this->show_errors, "<li class='form_row'>%s</li>\n"); }
  function as_li() { return implode("\n", array_map(array($this,'field2li'), $this->fields)); }
  function field2p($field) { return $field->html(false, "<p class='form_row'>%s</p>\n\n"); }
  function as_p() { return implode("\n", array_map(array($this, 'field2p'), $this->fields)); }
  function field2div($field) { return $field->html($this->show_errors, "<div class='form_row'>%s</div>\n"); }
  function as_div() { return implode("\n", array_map(array($this,'field2div'), $this->fields)); }
  
  function errors() {
    if (!$this->show_errors) return array();
  
    if (isset($this->errors)) return $this->errors;
    
    $errors = array();
    foreach($this->fields as $f) {
      $field_errors = $f->errors($f->submitted_value());
      if ($field_errors) {
        foreach($field_errors as $e) {
          $errors[] = $f->label . ': '.$e;
        }
      }
    }
    $this->errors = $errors;
    
    return $errors;
  }
  
  function is_valid() {
    $errors = $this->errors();
    return isset($_REQUEST[FORM_SUBMISSION_FLAG]) && empty($errors);
  }
  
  function cleaned_data() {
    if (isset($this->cleaned_data)) {
      return $this->cleaned_data;
    }
    if (!isset($_REQUEST[FORM_SUBMISSION_FLAG]) ||
        count($this->errors()) > 0) {
      $this->cleaned_data = false;
    } else {
      $this->cleaned_data = array();
      foreach($this->fields as $f) {
        $this->cleaned_data[$f->name] = $f->normalize($f->submitted_value());
      }
    }
    return $this->cleaned_data;
  }
  
  static function client_setup_html_head() {
    ?>
<link rel="stylesheet" href="<?php echo config('greenroom_path')->url ?>/css/form.css" type="text/css" media="screen, projection">
<script>
$(document).ready(function() {
  $('label.required').append('*');
});
</script>
    <?php
    foreach (get_declared_classes() as $klass) {
       if (is_subclass_of($klass, 'BaseField')) {
	 $instance = new $klass('', '', array('static_instance' => true));
        $instance->client_setup_html_head();
       }
    }
  }
}