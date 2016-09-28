<?php

namespace Drupal\nimbus;

use Drupal\Core\Config\StorageInterface;

/**
 * Interface UuidUpdaterInterface.
 *
 * @package Drupal\nimbus
 */
interface UuidUpdaterInterface {

  /**
   * Update filtered entries.
   */
  public function update();

  /**
   * Filter a array of config entries.
   *
   * @param \Drupal\nimbus\ConfigChange[] $entries
   *
   * @return \Drupal\nimbus\ConfigChange[]
   */
  public function filterEntries(array $entries);

  /**
   * Return a array of ConfigChange objects.
   *
   * @return \Drupal\nimbus\ConfigChange[]
   */
  public function getEntries();

  /**
   * Update config entries.
   *
   * @param string $config_name
   *    The config name.
   * @param array $new_data
   *    The array with the updated data.
   * @param string $collection
   *    The collection string.
   */
  public function updateEntry($config_name, array $new_data, $collection = StorageInterface::DEFAULT_COLLECTION);

}
