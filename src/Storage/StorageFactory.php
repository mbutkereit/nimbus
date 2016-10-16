<?php

namespace Drupal\nimbus\Storage;

use Drupal\Core\Config\FileStorage;

/**
 * Class StorageFactory.
 *
 * @package Drupal\nimbus\Storage
 */
class StorageFactory {

  /**
   * Constructs a new Storage.
   *
   * @param string $directory
   *   A directory path to use for reading and writing of configuration files.
   * @param string $collection
   *   The collection to store configuration in. Defaults to the
   *   default collection.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *    The created storageFactory.
   */
  public function create($directory, $collection) {
    return new FileStorage((string) $directory, $collection);
  }

}
