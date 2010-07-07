<?php
require_once 'PHPUnit/Framework.php';


class TestField extends BaseField {
  static function client_setup_html_head() {
    echo "<!-- HTML FOR HEAD HERE -->";
  }
}

class FormTest extends PHPUnit_Framework_TestCase
{
  public function testBaseField_client_setup_html_head() {
    ob_start();
    Form::client_setup_html_head();
    $head = ob_get_clean();    
    $this->assertContains('<!-- HTML FOR HEAD HERE -->', $head);
  }
}
