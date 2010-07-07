<?php
function an_expensive_operation($arg1, $arg2) {
	sleep(60);
	echo "$arg1, $arg2\n";
}

pm_cache_set_dir('./cache');

// this will either run the callback and cache
// the return value and output, or just spit
// out the output and return value
// function cache( $callback, $params, $unique_name, $flags ) {...};
pm_cache(
  'an_expensive_operation',
  array('Hello', 'World'),
  'run_an_expensive_operation',
  array('on_save', 'on_update', 'on_monday')
);


// any of these calls will cause the cache to be deleted,
// and force the cached function above to be re-run
pm_break_cache('run_an_expensive_operation');
pm_break_cache('on_save');
pm_break_cache('on_update');
pm_break_cache('on_monday');
pm_break_cache('on_tuesday', 'on_monday');
?>