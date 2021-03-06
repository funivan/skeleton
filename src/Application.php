<?php

  namespace Funivan\Skeleton;

  use Symfony\Component\Console\Application as BaseApplication;

  /**
   *
   * @package Funivan\Skeleton
   */
  class Application extends BaseApplication {


    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands() {
      // Keep the core default commands to have the HelpCommand
      // which is used when using the --help option
      $defaultCommands = parent::getDefaultCommands();
      
      $defaultCommands[] = new CreateCommand();

      return $defaultCommands;
    }

  }