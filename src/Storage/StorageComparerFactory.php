<?php

namespace Drupal\nimbus\Storage;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\StorageInterface;

/**
 * Class StorageComparerFactory.
 *
 * @package Drupal\nimbus\Storage
 */
class StorageComparerFactory implements StorageComparerFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function create(
    StorageInterface $source_storage,
    StorageInterface $target_storage,
    ConfigManagerInterface $config_manager
  ) {
    return new StorageComparer(
      $source_storage,
      $target_storage,
      $config_manager
    );
  }

}
