<?php

namespace Drupal\nimbus\Command;

use Drupal\Console\Command\Shared\ContainerAwareCommandTrait;
use Drupal\Console\Style\DrupalStyle;
use Drupal\Core\Config\StorageInterface;
use Drupal\nimbus\config\ProxyFileStorage;
use Drupal\nimbus\Service\DrupalStyleFactoryInterface;
use Drupal\nimbus\Service\NimbusConfigExporterInterface;
use Drupal\nimbus\Service\Question\QuestionFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NimbusConfigExportCommand.
 *
 * @package Drupal\nimbus\Command
 */
class NimbusConfigExportCommand extends Command {

  use ContainerAwareCommandTrait;

  /**
   * The question factory.
   *
   * @var QuestionFactoryInterface
   */
  private $questionFactory;

  /**
   * The drupal style factory.
   *
   * @var DrupalStyleFactoryInterface
   */
  private $drupalStyleFactory;

  /**
   * The configuration exporter service.
   *
   * @var NimbusConfigExporterInterface
   */
  private $configExporter;

  /**
   * The file storage.
   *
   * @var ProxyFileStorage
   */
  private $fileStorage;

  /**
   * The active config.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  private $configActive;

  /**
   * The target config.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  private $configTarget;

  /**
   * NimbusConfigExportCommand constructor.
   *
   * @param \Drupal\nimbus\Service\Question\QuestionFactoryInterface $question_factory
   *    The question factory.
   * @param \Drupal\nimbus\Service\DrupalStyleFactoryInterface $drupal_style_factory
   *    The drupal style factory.
   * @param \Drupal\nimbus\Service\NimbusConfigExporterInterface $config_exporter
   *    The configuration exporter service.
   * @param \Drupal\Core\Config\StorageInterface $config_active
   *    The active config storage.
   * @param \Drupal\Core\Config\StorageInterface $config_target
   *    The target config storage.
   */
  public function __construct(
    QuestionFactoryInterface $question_factory,
    DrupalStyleFactoryInterface $drupal_style_factory,
    NimbusConfigExporterInterface $config_exporter,
    StorageInterface $config_active,
    StorageInterface $config_target
  ) {
    $this->questionFactory = $question_factory;
    $this->drupalStyleFactory = $drupal_style_factory;
    $this->configExporter = $config_exporter;
    $this->configActive = $config_active;
    $this->configTarget = $config_target;

    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nimbus:export')->addArgument('accept', InputArgument::OPTIONAL, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('Override Export');

    if (!$this->configExporter->isExportMeaningful()) {
      $output->writeln('The active configuration is identical to the configuration in the export directories.');
      return TRUE;
    }

    $changed_configs = $this->configExporter->findChangedConfigurations();
    if ($changed_configs != []) {
      $output->writeln("Differences of the active config to the export directory:");

      $this->createTable($changed_configs, $output);

      if ($this->askToContinue($input, $output)) {
        $this->configExporter->exportConfiguration($this->configActive, $this->configTarget, $changed_configs);

        $output->writeln('Configuration successfully exported to ' . $this->configTarget->getWriteDirectories() . ". \n");
      }
    }
  }

  /**
   * Create a Table.
   *
   * @param array $rows
   *    The rows of the table.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *    The symfony console output.
   */
  private function createTable(array $rows, OutputInterface $output) {
    $headers = ['Collection', 'Config', 'Operation'];
    $elements = [];

    if ($this->fileStorage instanceof ProxyFileStorage) {
      $headers[] = 'Directory';
    }

    foreach ($rows as $collection => $row) {
      foreach ($row as $key => $config_names) {
        foreach ($config_names as $config_name) {
          $element = [
            $collection,
            $config_name,
            $key,
          ];
          if ($this->fileStorage instanceof ProxyFileStorage) {
            $path = ($key == 'delete')
              ? $this->fileStorage->getFilePath($config_name)
              : $this->fileStorage->getWriteDirectories();
            $element[] = $path;
          }
          $elements[] = $element;
        }
      }

    }
    $table = new Table($output);
    $table
      ->setHeaders($headers)
      ->setRows($elements);
    $table->render();
  }

  /**
   * Asks the user to continue.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *    An InputInterface instance.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *    An OutputInterface instance.
   *
   * @return bool
   *    Returns TRUE if the user wants to continue otherwise FALSE.
   */
  private function askToContinue(InputInterface $input, OutputInterface $output) {
    $question = $this->questionFactory
      ->createConfirmationQuestion('The .yml files in your export directory (' .
        $this->configTarget->getWriteDirectories() .
        ") will be deleted and replaced with the active config. \n(y/n) ",
        FALSE);
    try {
      $value = $input->getArgument('accept');
      if ($input->isInteractive()) {
        $input->setInteractive(!$value);
      }
    }
    catch (\Exception $e) {
      $input->setInteractive(FALSE);
    }

    if (!$input->getArgument('accept')) {
      // Create a DrupalStyle object to be able to ask for user input.
      $drupal_style = $this->drupalStyleFactory->create($input, $output);
      if (!$drupal_style->askQuestion($question)) {
        $output->writeln('Aborted !');
        return FALSE;
      }
    }
  }

}
