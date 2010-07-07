<?php

# start libraries
foreach(array('array_functions', 'config', 'formatting', 'layout', 'form', 'model', 'backend') as $library) {
  require_once(dirname(__FILE__).'/lib/'.$library.'.php');
}
# end libraries

config_set('greenroom_dir', dirname(__FILE__));

class Greenroom {
  static function admin($backend) {
    if (!isset($_REQUEST['_v'])) {
      if (isset($_REQUEST['_m'])) {
        $_REQUEST['_v'] = 'list';
      } else {
        $_REQUEST['_v'] = 'model';
      }
    }
    if (!in_array($_REQUEST['_v'], array('model', 'list', 'edit'))) return;
    if ($_REQUEST['_v'] != 'model' &&
        !in_array($_REQUEST['_m'], Model::defined())) return;
    
    require(dirname(__FILE__).'/admin/'.$_REQUEST['_v'].'.php');
  }
}