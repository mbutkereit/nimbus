<?php

namespace Drupal\nimbus\config\SpecialStorages;

use Drupal\Core\Config\FileStorage;

/**
 *
 */
class DevelopFileStorage extends FileStorage {

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
    $filtered = $data;
    parent::write($name, $data);
  }

}
