<?php
/* a micro caching api.  caches output and results. */
global $pm_cache_config, $pm_cache;
if (!is_array($pm_cache_config)) $pm_cache_config = array();

/*rhizome_set_config('discuss_cache_dir',
  rhizome_config('site_path').'/cache/discuss/');
rhizome_set_config('discuss_cache_index',
  rhizome_config('discuss_cache_dir').'/discuss_cache_index.txt');*/

$pm_cache = unserialize(@file_get_contents($pm_cache_config('pm_cache_index')));
//print_r($discuss_cache); exit;
if (!is_array($discuss_cache)) $discuss_cache = array();


function pm_cache_set_dir($cache_dir) {

}


function discuss_cache($callback_with_parameters, $unique_name, $flags) {
  global $discuss_cache;

  $discuss_cache[$unique_name] = array($unique_name);
  
  $return_value_file = rhizome_config('discuss_cache_dir') . '/' . $unique_name . '.serialized.txt';
  $output_file = rhizome_config('discuss_cache_dir') . '/' . $unique_name . '.output.txt';
  
  if (file_exists($output_file) &&
      file_exists($return_value_file) &&
      !isset($_REQUEST['force']) &&
      isset($discuss_cache[$unique_name])) {
    readfile($output_file);
    return unserialize(file_get_contents($return_value_file));
  } else {
    foreach($flags as $flag) {
    if (!isset($discuss_cache[$flag])) $discuss_cache[$flag] = array();
      if (!in_array($unique_name, $discuss_cache[$flag]))
        $discuss_cache[$flag][] = $unique_name;
    }
    
    $callback = array_shift($callback_with_parameters);
    ob_start();
    $result = call_user_func_array($callback, $callback_with_parameters);
    $serialized_result = serialize($result);
    $output = ob_get_flush();
    
    $fh = fopen($output_file, 'w');
    fwrite($fh, $output);
    fclose($fh);
    
    chmod($output_file, 0664);
    $fh = fopen($return_value_file, 'w');
    fwrite($fh, $serialized_result);
    fclose($fh);
    chmod($return_value_file, 0664);

    discuss_cache_save_index();

    
    return $result;
  }
  
}

function discuss_delete_cache() {
  $flags_or_names = func_get_args();
  
  global $discuss_cache;
  
  foreach($flags_or_names as $flag) {
    unlink(rhizome_config('discuss_cache_dir').'/'.$flag.'.serialized.txt');
    unlink(rhizome_config('discuss_cache_dir').'/'.$flag.'.output.txt');
    
    $deleted_names = array();
    
    if (is_array($discuss_cache[$flag])) {
      foreach($discuss_cache[$flag] as $unique_name) {
        @unlink(rhizome_config('discuss_cache_dir') . '/' . $unique_name . '.serialized.txt');
        @unlink(rhizome_config('discuss_cache_dir') . '/' . $unique_name . '.output.txt');
        $deleted_names[] = $unique_name;
      }
      unset($discuss_cache[$name]);
    }
    
    foreach($deleted_names as $name) {
      unset($discuss_cache[$name]);
    }
    
  }
  discuss_cache_save_index();
}

function discuss_cache_save_index() {
  global $discuss_cache;
  $serialized_discuss_cache = serialize($discuss_cache);
  $fh = fopen(rhizome_config('discuss_cache_index'), 'w');
	fwrite($fh, $serialized_discuss_cache);
	fclose($fh);
	chmod(rhizome_config('discuss_cache_index'), 0664);
}
?>