<?php
global $mf_view; $mf_view = array();

function wrap_layout($template) {
  if ($template) {
    $template = realpath($template);
    if (!file_exists($template)) {
      trigger_error('Could not locate layout: ('.$template.')', E_USER_ERROR);
      return false;
    }
    view_set('layout', $template);  
    if (!in_array('layout_capture_content', ob_list_handlers())) {
      ob_start('layout_capture_content');
      register_shutdown_function('render_layout');
    }
  } else {
    // clear all pre-existing layout handling
    while(ob_list_handlers()) @ob_get_clean();
    view_set('layout', $template);
    view_set('content', '');
    ob_start();
    //echo "hi patrick [$template]"; global $mf_view; print_r($mf_view);
  }
  return true;
}
function layout_capture_content($content) {
  global $mf_view;
  $mf_view['content'] = $content;
}
function render_layout() {
  event('pre_render_layout');
  
  while (@ob_end_flush());
  $template = view('layout');
  //echo "hi patrick [$template]"; global $mf_view; print_r($mf_view);
  if ($template) {
    require($template);
  } else {
    echo view('content');
  }
}

function view($key, $default = '') {
  global $mf_view;
  if (isset($mf_view[$key]) && !empty($mf_view[$key])) {
    return $mf_view[$key];
  } else {
    $mf_view[$key] = $default;
    return $default;
  }
}
function view_set($key, $value) {
  global $mf_view;
  $mf_view[$key] = $value;
}