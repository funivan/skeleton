#!/usr/bin/env php
<?php

  if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50306) {
    fwrite(STDERR, "PHP needs to be a minimum version of PHP 5.3.6\n");
    exit(1);
  }

  \Phar::mapPhar('skeleton.phar');
  require_once 'phar://skeleton.phar/vendor/autoload.php';
  $application = new \Funivan\Skeleton\Application();
  $application->run();
__HALT_COMPILER();