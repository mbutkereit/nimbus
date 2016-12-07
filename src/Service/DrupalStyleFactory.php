<?php

namespace Drupal\nimbus\Service;

use Drupal\Console\Style\DrupalStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DrupalStyleFactory.
 *
 * @package Drupal\nimbus\Service
 */
class DrupalStyleFactory implements DrupalStyleFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function create(InputInterface $input, OutputInterface $output) {
    return new DrupalStyle($input, $output);
  }

}
