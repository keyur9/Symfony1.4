<?php

require_once '/Users/keyurdoshi/lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';

// In this file register the autoloader of composer using the following line:
require_once __DIR__.'/../vendor/autoload.php';

sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfDoctrinePlugin');
  }
}
