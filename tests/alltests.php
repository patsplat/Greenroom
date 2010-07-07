<?php
require_once dirname(__FILE__).'/../greenroom.inc.php';
require_once 'PHPUnit/Framework.php';
require_once 'linked_path.php';
require_once 'form.php';

config_set('greenroom_path', new LinkedPath('/path', '/url'));
 
class AllTests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite('blueline');
    $suite->addTestSuite('LinkedPathTest');
    $suite->addTestSuite('FormTest');
    return $suite;
  }
}