<?php

namespace Drupal\nimbus\EventSubscriber\FileDetection;

use Drupal\nimbus\config\ConfigPath;
use Drupal\nimbus\Events\ConfigDetectionPathEvent;
use Drupal\nimbus\NimbusEvents;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @file
 */

/**
 *
 */
class ConstantDirectoriesSubscriber implements EventSubscriberInterface {

  /**
   * @param \Drupal\nimbus\Events\ConfigDetectionPathEvent $event
   */
  public function onPreCreateFileConfigManager(ConfigDetectionPathEvent $event) {
    global $_nimbus_config_override_directories;
    $file_storages = [];

    if (isset($_nimbus_config_override_directories)) {
      if (is_array($_nimbus_config_override_directories)) {
        foreach ($_nimbus_config_override_directories as $directory) {
          $file_storages[] = new ConfigPath($directory);
        }
      }
    }

    $file_storages[] = new ConfigPath(config_get_config_directory(CONFIG_SYNC_DIRECTORY));

    $event->addFileStorage($file_storages);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[NimbusEvents::ADD_PATH][] = ['onPreCreateFileConfigManager', 90];
    return $events;
  }

}
