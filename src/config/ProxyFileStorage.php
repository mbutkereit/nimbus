<?php

namespace Drupal\nimbus\config;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;

/**
 * Class ProxyFileStorage.
 *
 * Not a real proxy ... drupals bad implementation please dont't ask .....
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
   * @var string[]
   */
  private $directories;

  /**
   * ProxyFileStorage constructor.
   *
   * @param string[] $directories
   *    Array with directories.
   * @param string $collection
   *   (optional) The collection to store configuration in. Defaults to the
   *   default collection.
   */
  public function __construct(array $directories, $collection = StorageInterface::DEFAULT_COLLECTION) {
    parent::__construct(config_get_config_directory(CONFIG_SYNC_DIRECTORY), $collection);
    $this->directories = $directories;
    foreach ($directories as $directory) {
      $this->fileStorages[] = new FileStorage($directory, $collection);
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
    $test = $this->fileStorages;
    $element = array_pop($test);
    $element->write($name, $data);
  }

  /**
   * No Implemented because we don't want deletion.
   *
   * {@inheritdoc}.
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
   * Todo need implementation for override folder.
   *
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
   * Todo need a better implementation.
   *
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
    $test = $this->directories;
    $element = array_pop($test);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function existsReturnPath($name) {
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
        $return_value[] = $this->directories[$i];
      }
      $i++;
    }
    return implode(',', $return_value);
  }

}
