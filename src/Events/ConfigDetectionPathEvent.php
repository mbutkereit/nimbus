<?php

namespace Drupal\nimbus\Events;

/**
 * @file
 */
use Symfony\Component\EventDispatcher\Event;

/**
 *
 */
class ConfigDetectionPathEvent extends Event {

  protected $fileStorages = [];

  /**
   * @return ConfigPath[]
   */
  public function getFileStorages() {
    return $this->fileStorages;
  }

  /**
   * @param ConfigPath[] $fileStorages
   */
  public function setFileStorages($fileStorages) {
    $this->fileStorages = $fileStorages;
  }

  /**
   * @param ConfigPath[] $fileStorages
   */
  public function addFileStorage(array $fileStorages) {
    foreach ($fileStorages as $fileStorage) {
      $this->fileStorages[] = $fileStorage;
    }
  }

}
