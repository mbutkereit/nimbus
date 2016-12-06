<?php

namespace Drupal\nimbus\Service;

use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\StorageInterface;

/**
 * Class NimbusConfigExporter.
 *
 * @package Drupal\nimbus\Service
 */
class NimbusConfigExporter implements NimbusConfigExporterInterface {

  /**
   * The configuration comparer.
   *
   * @var StorageComparer
   */
  private $configComparer;

  /**
   * NimbusConfigExporter constructor.
   *
   * Creates a new instance of the nimbus configuration exporter service.
   *
   * @param \Drupal\Core\Config\StorageComparer $config_comparer
   *    The configuration comparer.
   */
  public function __construct(StorageComparer $config_comparer) {
    $this->configComparer = $config_comparer;
  }

  /**
   * {@inheritdoc}
   */
  public function exportConfiguration(
    StorageInterface $source_storage,
    StorageInterface $destination_storage,
    array $change_list
  ) {
    $this->writeConfigurationFiles($source_storage, $destination_storage, $change_list);
    $this->writeConfigurationCollections($source_storage, $destination_storage, $change_list);
  }

  /**
   * {@inheritdoc}
   */
  public function isExportMeaningful() {
    if ($this->configComparer->createChangelist()->hasChanges()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function findChangedConfigurations() {
    $change_list = [];
    foreach ($this->configComparer->getAllCollectionNames() as $collection) {
      $change_list[$collection] = $this->configComparer->getChangelist(NULL, $collection);
    }

    return $change_list;
  }

  /**
   * Writes all configuration files that have changed.
   *
   * @param \Drupal\Core\Config\StorageInterface $source_storage
   *    The source configuration storage.
   * @param \Drupal\Core\Config\StorageInterface $destination_storage
   *    The destination configuration storage.
   * @param array $change_list
   *    The array of changed configuration files.
   */
  private function writeConfigurationFiles(
    StorageInterface $source_storage,
    StorageInterface $destination_storage,
    array $change_list
  ) {
    // Write all .yml files.
    if (isset($change_list[''])) {

      foreach ($change_list['']['delete'] as $name) {
        if (is_string($name)) {
          $destination_storage->delete($name);
        }
      }
      unset($change_list['']['delete']);

      foreach ($change_list[''] as $update_categories) {
        foreach ($update_categories as $name) {
          if (is_string($name)) {
            $destination_storage->write($name, $source_storage->read($name));
          }
        }
      }
    }
  }

  /**
   * Writes the configuration collections.
   *
   * @param \Drupal\Core\Config\StorageInterface $source_storage
   *    The source configuration storage.
   * @param \Drupal\Core\Config\StorageInterface $destination_storage
   *    The destination configuration storage.
   * @param array $change_list
   *    The array of changed configuration files.
   */
  private function writeConfigurationCollections(
    StorageInterface $source_storage,
    StorageInterface $destination_storage,
    array $change_list
  ) {
    $live_collection = $source_storage->getAllCollectionNames();
    $collections_iteration = array_merge($live_collection, $destination_storage->getAllCollectionNames());
    array_unique($collections_iteration);

    foreach ($collections_iteration as $collection) {
      $source_storage = $source_storage->createCollection($collection);
      $destination_storage = $destination_storage->createCollection($collection);
      if (isset($change_list[$collection])) {
        if (isset($change_list[$collection]['delete'])) {
          foreach ($change_list[$collection]['delete'] as $name) {
            if (is_string($name)) {
              $destination_storage->delete($name);
            }
          }
          unset($change_list[$collection]['delete']);
        }
        foreach ($change_list[$collection] as $update_categories) {
          foreach ($update_categories as $name) {
            if (is_string($name)) {
              $destination_storage->write($name, $source_storage->read($name));
            }
          }
        }
      }
    }
  }

}
