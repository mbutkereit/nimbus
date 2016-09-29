<?php

namespace Drupal\nimbus\config;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\nimbus\Storage\StorageFactory;

/**
 * Class ProxyFileStorage.
 *
 * @package Drupal\nimbus\config
 */
class ProxyFileStorage extends FileStorage {

  /**
   * All FileStorages.
   *
   * @var StorageInterface[]
   */
  private $fileStorages;

  /**
   * All directories.
   *
   * @var ConfigPath[]
   */
  private $directories;

  /**
   * @var
   */
  private $storageFactory;

  /**
   * ProxyFileStorage constructor.
   *
   * @param ConfigPath[] $directories
   *    Array with directories.
   * @param string $collection
   *   (optional) The collection to store configuration in. Defaults to the
   *   default collection.
   */
  public function __construct(array $directories, $collection = StorageInterface::DEFAULT_COLLECTION, StorageFactory $storage_factory = NULL) {
    parent::__construct(config_get_config_directory(CONFIG_SYNC_DIRECTORY), $collection);
    $this->directories = $directories;
    $this->storageFactory = $storage_factory;
    foreach ($directories as $directory) {
      if (is_dir(((string) $directory))) {
        $this->fileStorages[] = $this->storageFactory->create($directory, $collection);
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    foreach ($this->fileStorages as $fileStorage) {
      $response = $fileStorage->exists($name);
      if ($response == TRUE) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function read($name) {
    $response = FALSE;
    foreach ($this->fileStorages as $fileStorage) {
      $read = $fileStorage->read($name);
      if ($read != FALSE) {
        $response = $read;
      }
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names) {
    $list = [];
    foreach ($this->fileStorages as $fileStorage) {
      $list = $fileStorage->readMultiple($names) + $list;
    }
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
    $fileStorage = $this->fileStorages;
    $element = array_pop($fileStorage);
    $element->write($name, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    foreach ($this->fileStorages as $fileStorage) {
      if ($fileStorage->exists($name)) {
        $fileStorage->delete($name);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name) {
    throw new \Exception();
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data) {
    throw new \Exception();
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw) {
    throw new \Exception();
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    $list = [];
    foreach ($this->fileStorages as $fileStorage) {
      $list = array_merge($fileStorage->listAll($prefix), $list);
    }
    array_unique($list);
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
    throw new \Exception();
  }

  /**
   * {@inheritdoc}
   */
  public function createCollection($collection) {
    return new ProxyFileStorage($this->directories, $collection);
  }

  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames() {
    $list = [];
    foreach ($this->fileStorages as $fileStorage) {
      $list = array_merge($fileStorage->getAllCollectionNames(), $list);
    }
    array_unique($list);
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName() {
    $response = '';
    foreach ($this->fileStorages as $fileStorage) {
      $response = $fileStorage->getCollectionName();
    }
    return $response;
  }

  /**
   * The active write directory.
   *
   * @return string
   *    The current active write directories.
   */
  public function getWriteDirectories() {
    $directories = $this->directories;
    $element = array_pop($directories);
    return (string)$element;
  }

  /**
   * Returns the path to the configuration file.
   *
   * @return string
   *   The path to the configuration file.
   */
  public function getFilePath($name) {
    $i = 0;
    $return_value = [];
    foreach ($this->fileStorages as $fileStorage) {
      $response = $fileStorage->exists($name);
      if ($response == TRUE) {
        $return_value[] = (string)$this->directories[$i];
      }
      $i++;
    }
    return implode(',', $return_value);
  }

}
