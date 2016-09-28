<?php
namespace Drupal\nimbus\Command;
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
class NimbusConfigExportCommand extends ContainerAwareCommand {

  /**
   *
   */
  protected function configure() {
    $this->setName('nimbus:export')->addArgument('accept', InputArgument::OPTIONAL, FALSE);
  }

  /**
   *
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $service = $this->getService('nimbus.nimbus_export');
    $service->configurationExport($input, $output);
  }

}
