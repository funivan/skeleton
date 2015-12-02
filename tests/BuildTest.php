<?php

  namespace Tests\Funivan\Skeleton;

  /**
   *
   * @package Tests\Funivan\Skeleton
   */
  class TestBuild extends \PHPUnit_Framework_TestCase {


    /**
     *
     */
    public function testBuild() {
      passthru("./bin/build-phar.sh");
      $this->assertFileExists('./build/skeleton.phar');
      $result = shell_exec("./build/skeleton.phar");
      $this->assertContains('Create files', $result);
    }

  } 
  
  