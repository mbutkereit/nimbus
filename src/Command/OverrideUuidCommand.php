<?php

namespace Drupal\nimbus\Command;

use Drupal\nimbus\Controller\OverrideUuidController;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Drupal\Console\Command\ContainerAwareCommand;

/**
 * Class OverrideUuidCommand.
 *
 * @package Drupal\nimbus\Controller
 */
class OverrideUuidCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nimbus:fuuid')->addArgument('accept', InputArgument::OPTIONAL, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $service = new OverrideUuidController($this->getService('nimbus.uuid_updater'));
    $service->uuidUpdateCommand($input, $output);
  }

}
