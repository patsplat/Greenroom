<?php
/* start config */
global $mf_config; $mf_config = array();
function config($value, $default=false) {
  global $mf_config;
  if (array_key_exists($value, $mf_config))
    return $mf_config[$value];
  else
    return $default;
}
function config_set($key, $value) {
  global $mf_config;
  return $mf_config[$key] = $value;
}
/* end config */

/* start event listeners */
global $mf_listeners; $mf_listeners = array();
function listen($event_name, $callback) {
  global $mf_listeners;
  return $mf_listeners[$event_name][] = $callback;
}
function filter($event_name, $callback) {
  listen($event_name, $callback);
}
function event($event_name, $parameters=array()) {
  global $mf_listeners;
  if (array_key_exists($event_name, $mf_listeners) &&
      is_array($mf_listeners[$event_name])) {
    foreach( $mf_listeners[$event_name] as $callback ) {
      /*echo "[Event: $event_name]";
      print_r($callback);
      echo '['.function_exists($callback) .']';*/
      call_user_func_array($callback, $parameters);
    }
  }
}
function filter_event($event_name, $parameter) {
  global $mf_listeners;
  if (is_array($mf_listeners[$event_name])) {
    foreach( $mf_listeners[$event_name] as $callback ) {
      $parameter = call_user_func($callback, $parameter);
    }
  }
  return $parameter;
}
/* end event listeners */

class LinkedPath {
  function __construct($url, $path) {
    $this->url = $url;
    $this->path = $path;
  }
  function cd($relative_path) {
    $this->url = $this->_cd($this->url, $relative_path);
    $this->path = $this->_cd($this->path, $relative_path);
    return $this;
  }
  function _cd($path, $relative_path) {
    $absolute = explode('/', $path);
    $relative = explode('/', $relative_path);
    foreach($relative as $dir) {
      switch ($dir) {
        case '..':
          array_pop($absolute);
        case '.':
          continue;
        default:
          array_push($absolute, $dir);
      }
    }
    return implode('/', $absolute);
  }
}