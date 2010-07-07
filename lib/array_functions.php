<?php
if (!function_exists('array_default')) {
  function array_default( &$arr, $k, $v ) {
    if (!array_key_exists($k, $arr) || $arr[$k] == '') {
          $arr[$k] = $v;
    } 
  }
}

if (!function_exists('array_merge_low_keys')) {
  function array_merge_low_keys( &$target, &$source ) {
    foreach($source as $k => $v) {
      $target[strtolower($k)] = $v;
    }
  }
}

?>
