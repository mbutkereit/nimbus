<?php

namespace Drupal\nimbus\Service;

use Drupal\Core\Config\StorageInterface;

/**
 * Interface NimbusConfigExporterInterface.
 *
 * @package Drupal\nimbus\Service
 */
interface NimbusConfigExporterInterface {

  /**
   * Exports the drupal configuration.
   *
   * @param \Drupal\Core\Config\StorageInterface $source_storage
   *    The source configuration storage.
   * @param \Drupal\Core\Config\StorageInterface $destination_storage
   *    The destination configuration storage.
   * @param array $change_list
   *    The array of configuration files that have changed.
   */
  public function exportConfiguration(
    StorageInterface $source_storage,
    StorageInterface $destination_storage,
    array $change_list
  );

  /**
   * Checks if it is meaningful to export configurations.
   *
   * This method returns TRUE only if changes have been made over the drupal
   * user interface that would cause a configuration file to be overwritten.
   *
   * @return bool
   *    Returns TRUE if meaningful otherwise FALSE.
   */
  public function isExportMeaningful();

  /**
   * Searches for changed configurations and returns them.
   *
   * @return array
   *    An array with changed configurations.
   */
  public function findChangedConfigurations();

}
