<?php

namespace Drupal\nimbus\Storage;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Interface StorageComparerFactoryInterface.
 *
 * @package Drupal\nimbus\Storage
 */
interface StorageComparerFactoryInterface {

  /**
   * Creates new StorageComparer object.
   *
   * @param \Drupal\Core\Config\StorageInterface $source_storage
   *   Storage object used to read configuration.
   * @param \Drupal\Core\Config\StorageInterface $target_storage
   *   Storage object used to write configuration.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   *
   * @return \Drupal\Core\Config\StorageComparerInterface
   *    The created storage comparer object.
   */
  public function create(
    StorageInterface $source_storage,
    StorageInterface $target_storage,
    ConfigManagerInterface $config_manager
  );

}
