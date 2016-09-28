<?php

namespace Drupal\nimbus\Controller;

use Drupal\nimbus\UuidUpdaterInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 *
 */
class OverrideUuidController {

  /**
   * @var \Drupal\nimbus\UuidUpdaterInterface
   */
  private $uuidUpdater;

  /**
   *
   */
  public function __construct(UuidUpdaterInterface $uuid_updater) {
    $this->uuidUpdater = $uuid_updater;
  }

  /**
   *
   */
  public function uuidUpdateCommand(InputInterface $input, OutputInterface $output) {
    $result = $this->uuidUpdater->getEntries();
    $result = $this->uuidUpdater->filterEntries($result);
    if (!empty($result)) {
      $elements = [];
      foreach ($result as $config_name => $change_object) {
        $elements[] = [
          $config_name,
          $change_object->getActiveUuid(),
          $change_object->getStagingUuid(),
          $change_object->getStagingUuid(),
        ];
      }
      $table = new Table($output);
      $table
        ->setHeaders(['Config', 'Active', 'Staging', 'New'])
        ->setRows($elements);
      $table->render();
      $helper = new QuestionHelper();
      $question = new ConfirmationQuestion('You will reset the whole config, sure ?');
      try {
        $value = $input->getArgument('accept');
        $input->setInteractive(!$value);
      }
      catch (\Exception $e) {
        $input->setInteractive(TRUE);
      }

      if (!$helper->ask($input, $output, $question)) {
        $output->writeln('you canceled the override.');
        return FALSE;
      }

      foreach ($result as $key => $element) {
        $current_database_value = $element->getActiveConfig();
        $current_database_value['uuid'] = $element->getStagingUuid();
        $this->uuidUpdater->updateEntry($key, $current_database_value);
      }
      $output->writeln('Finished !');
    }
    else {
      $output->writeln('No wrong entries found.');
    }
  }

}
