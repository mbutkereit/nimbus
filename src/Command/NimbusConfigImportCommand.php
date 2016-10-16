<?php

namespace Drupal\nimbus\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Drupal\Console\Command\ContainerAwareCommand;

/**
 * Class NimbusConfigImportCommand.
 *
 * @package Drupal\nimbus\Controller
 */
class NimbusConfigImportCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nimbus:import')->addArgument('accept', InputArgument::OPTIONAL, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $service = $this->getService('nimbus.nimbus_import');
    $service->configurationImport($input, $output);
  }

}
