<?php

function sanitize_cssid($string) {
  $string = strtolower($string);
  $string = trim($string);
  $string = preg_replace('/[^a-zA-Z0-9]+/', '-', $string);
  return $string;
}

function relative_dateformat($date) {
  if (!is_int($date)) $date = strtotime((string)$date);
  $now = time();
  $diff = $now-$date;
  if ( $diff < 60) {
    return $now-$date.'s ago';
  }
  if ($diff < 60*60) {
    return round($diff/60).'m ago';
  }
  if ($diff < 12*60*60) {
    return date('g:ia', $date);
  }
  if ($diff < 31*24*60*60) {
    return date('M j', $date);
  }
  return date('d/m/Y', $date);
}

