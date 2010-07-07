<?php

$model = Model::instance($_REQUEST['_m']);

$criteria = array();
foreach($model->fields() as $f) {
  if (is_a($f, 'ChoiceField') || is_a($f, 'MultipleChoiceField')) {
    $criteria[] = $f->to_search_field();
  }
}
$search = new Form($criteria, $_REQUEST);

$data = array();
$data['_v'] = 'list';
$data['_m'] = $_REQUEST['_m'];
if ($search->is_valid()) {
  $data = array_merge($data, $search->cleaned_data());
    
}
$_REQUEST['_r'] = config('greenroom_admin').'?'.http_build_query($data);


$documents = $backend->find($_REQUEST['_m'])->sort(array('publish_date' => -1));
if ($search->is_valid()) {
  $documents = $model->find($search->cleaned_data());
}
$new_link = $model->new_link($search->cleaned_data());

echo "<h3>List ".ucwords($_REQUEST['_m'])."</h3>";

echo sprintf('<a href="%s">New</a>', $new_link);

if (!empty($criteria)) {
  echo $search->open();
  echo $search->as_div();
  ?><div><input type="submit" name="submit" value="Search"></div><?php
  echo $search->close();
}

$model->admin_table($documents);