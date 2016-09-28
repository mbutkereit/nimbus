<?php
namespace Drupal\nimbus\Controller;
/**
 * @file
 */

use Symfony\Component\Console\Input\InputArgument;
use Drupal\nimbus\Controller\OverrideUuidController;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Drupal\Console\Command\ContainerAwareCommand;

/**
 *
 */
class OverrideUuidCommand extends ContainerAwareCommand {

  /**
   *
   */
  protected function configure() {
    $this->setName('nimbus:fuuid')->addArgument('accept', InputArgument::OPTIONAL, FALSE);
  }

  /**
   *
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $service = new OverrideUuidController($this->getService('nimbus.uuid_updater'));
    $service->uuidUpdateCommand($input, $output);
  }

}
