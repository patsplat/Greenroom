<?php
$klass = $_REQUEST['_m'];

echo "<h3>Edit ".ucwords($klass)."</h3>";

if (!isset($_REQUEST['_id']) || empty($_REQUEST['_id'])) {
  $article = new $klass(array('title' => 'Title'));
} else {
  $article = $backend->get($klass, $_REQUEST['_id']);
}
$f = $article->form();

if (!isset($_REQUEST['submit'])) $_REQUEST['submit'] = false;
if ($_REQUEST['submit'] == 'Delete') {
  $backend->delete($klass, $_REQUEST['_id']);
  header("Location: ".$_REQUEST['_r'] );   
}
if ($_REQUEST['submit'] == 'Save') {
  $submission = $f->cleaned_data();
  if ($submission) {
    $article = $backend->save($klass, $submission);
    $f = $article->form();
    header("Location: ".$_REQUEST['_r'] );   
  }
}

echo $f->open();
if ($f->errors()) {
  echo "<div class='error'>Please correct the errors below.</div>";
}
echo $f->as_div(); ?>
<input type="hidden" name="_r" value="<?php echo htmlspecialchars($_REQUEST['_r']); ?>">
<div class='form_row submit'>
  <input type='submit' name='submit' value='Save'>
  <input type='submit' name='submit' value='Delete'>
</div>
<?php
echo $f->close();