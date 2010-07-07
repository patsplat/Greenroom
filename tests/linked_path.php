<?php
require_once 'PHPUnit/Framework.php';

class LinkedPathTest extends PHPUnit_Framework_TestCase
{
  public function testLinkedPath() {
    $path = new LinkedPath('http://localhost/~patsplat', '/Users/patsplat/Sites');
    $this->assertEquals('http://localhost/~patsplat', $path->url);
    $this->assertEquals('/Users/patsplat/Sites', $path->path);
    $path->cd('media');
    $this->assertEquals('http://localhost/~patsplat/media', $path->url);
    $this->assertEquals('/Users/patsplat/Sites/media', $path->path);
    $path->cd('.');
    $this->assertEquals('http://localhost/~patsplat/media', $path->url);
    $this->assertEquals('/Users/patsplat/Sites/media', $path->path);
    $path->cd('./..');
    $this->assertEquals('http://localhost/~patsplat', $path->url);
    $this->assertEquals('/Users/patsplat/Sites', $path->path);
    $path->cd('./media');
    $this->assertEquals('http://localhost/~patsplat/media', $path->url);
    $this->assertEquals('/Users/patsplat/Sites/media', $path->path);
    $path->cd('..');
    $this->assertEquals('http://localhost/~patsplat', $path->url);
    $this->assertEquals('/Users/patsplat/Sites', $path->path);
    
    $path2 = clone($path);
    $path2->cd('media');
    $this->assertEquals('http://localhost/~patsplat/media', $path2->url);
    $this->assertEquals('/Users/patsplat/Sites/media', $path2->path);
    $this->assertEquals('http://localhost/~patsplat', $path->url);
    $this->assertEquals('/Users/patsplat/Sites', $path->path);
    
  }
}
