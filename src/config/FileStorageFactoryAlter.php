<?php

namespace Drupal\nimbus\config;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;

/**
 * Provides a factory for creating config file storage objects.
 */
class FileStorageFactoryAlter {

  /**
   * Returns a FileStorage object working with the active config directory.
   *
   * @return \Drupal\Core\Config\FileStorage FileStorage
   *    Return a config file storage.
   *
   * @deprecated in Drupal 8.0.x and will be removed before 9.0.0. Drupal core
   * no longer creates an active directory.
   */
  static public function getActive() {
    return new ProxyFileStorage([new FileStorage(config_get_config_directory(CONFIG_ACTIVE_DIRECTORY))]);
  }

  /**
   * Returns a FileStorage object working with the sync config directory.
   *
   * @return \Drupal\Core\Config\FileStorage FileStorage
   *    Return a config file storage.
   */
  static public function getSync() {
    global $_nimbus_config_override_directories;
    $file_storages = [];

    // Module.
    $modules = \Drupal::moduleHandler()->getModuleList();

    foreach ($modules as $module) {
      if ($module->getType() != 'profile') {
        $extension_path = drupal_get_path($module->getType(), $name = $module->getName()) . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY;
        $file_storages[] = $extension_path;
      }
    }

    // Profile.
    $extension_path = drupal_get_path('profile', drupal_get_profile()) . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY;
    $file_storages[] = $extension_path;

    $file_storages[] = config_get_config_directory(CONFIG_SYNC_DIRECTORY);
    if (isset($_nimbus_config_override_directories)) {
      if (is_array($_nimbus_config_override_directories)) {
        foreach ($_nimbus_config_override_directories as $directory) {
          $file_storages[] = $directory;
        }
      }
    }

    return new ProxyFileStorage($file_storages);
  }

}
