<?php

class Model {
  function __construct($data=array()) {
    foreach($this->fields() as $f) {
      $name = $f->name;
      $this->$name = $f->empty_value();
      if (isset($data[$name])) {
        $this->$name = $f->normalize($data[$name]);
      }
    }
  }
  
  function data() {
    $data = array();
    foreach($this->fields() as $f) {
      $name = $f->name;
      if (isset($this->$name)) {
        $data[$name] = $this->$name;
      }
    }
    return $data;
  }
  function fields() { return array(); }
  function form() {
    return new Form($this->fields(), $this->data());
  }
  
  function new_link($data) {
    if (false === $data) $data = array();
    $data = array_filter($data);
    $data['_r'] = $_REQUEST['_r'];
    $data['_v'] = 'edit';
    $data['_m'] = $_REQUEST['_m'];
    return config('greenroom_admin').'?'.http_build_query($data);
  }
  function edit_link() {
    $data = array();
    $data['_r'] = $_REQUEST['_r'];
    $data['_v'] = 'edit';
    $data['_m'] = $_REQUEST['_m'];
    $data['_id'] = (string) $this->_id;
    return config('greenroom_admin').'?'.http_build_query($data);
  }
  function defined() {
    $defined = array();
    foreach (get_declared_classes() as $klass) {
      if (is_subclass_of($klass, 'Model')) {
        $defined[] = $klass;
      }
    }
    return $defined;
  }
  function instance($klass) {
    $instance = new $klass(array('static_instance' => 'true'));
    return $instance;
  }
  
  function list_fields() {
    return array('_id');
  }
  
  function admin_table($documents) {
    $list_field_names = $this->list_fields();
    $list_fields = array();
    foreach($this->fields() as $f) {
      if (in_array($f->name, $list_field_names)) {
        $list_fields[$f->name] = $f;
      }
    }
    ?>
<table>
<tr>
<?php foreach($list_fields as $f): ?>
<th><?php echo $f->label ?></th>
<?php endforeach; ?>
</tr>
<?php
foreach( $documents as $d) {
  $edit_link = $d->edit_link();
  echo "<tr>\n";
  foreach($list_fields as $name => $f) {
    echo sprintf('<td><a href="%s"><div>%s</div></a></td>', $edit_link, $f->display($d->$name) );
  }
  echo "</tr>";
}
?>
</table>
    <?php
  }
  
  
}