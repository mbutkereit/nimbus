<?php
namespace Drupal\nimbus\Controller;
/**
 * @file
 */

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Drupal\Console\Command\ContainerAwareCommand;

/**
 *
 */
class NimbusConfigImportCommand extends ContainerAwareCommand {

  /**
   *
   */
  protected function configure() {
    $this->setName('nimbus:import')->addArgument('accept', InputArgument::OPTIONAL, FALSE);
  }

  /**
   *
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $service = $this->getService('nimbus.nimbus_import');
    $service->configurationImport($input, $output);
  }

}
