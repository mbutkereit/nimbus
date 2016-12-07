<?php

namespace Drupal\nimbus\Service;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface DrupalStyleFactoryInterface.
 *
 * @package Drupal\nimbus\Service
 */
interface DrupalStyleFactoryInterface {

  /**
   * Creates a new DrupalStyle object.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *    The console input.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *    The console output.
   *
   * @return \Drupal\Console\Style\DrupalStyle
   *    The DrupalStyle object.
   */
  public function create(InputInterface $input, OutputInterface $output);

}
