<?php

namespace Drupal\nimbus\EventSubscriber\FileDetection;

use Drupal\Core\Config\InstallStorage;
use Drupal\nimbus\config\ConfigPath;
use Drupal\nimbus\Events\ConfigDetectionPathEvent;
use Drupal\nimbus\NimbusEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * @file
 */

/**
 *
 */
class ModuleDirectoriesSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ModuleDirectoriesSubscriber constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   *
   */
  public function onPreCreateFileConfigManager(ConfigDetectionPathEvent $event) {
    $file_storages = [];

    $modules = $this->moduleHandler->getModuleList();

    foreach ($modules as $module) {
      if ($module->getType() != 'profile') {
        $extension_path = $this->drupalGetPath($module->getType(), $module->getName()) . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY;
        $file_storages[] = new ConfigPath($extension_path);
      }
    }

    $event->addFileStorage($file_storages);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[NimbusEvents::ADD_PATH][] = ['onPreCreateFileConfigManager', 20];
    return $events;
  }

  /**
   * Wrapper for drupal_get_path().
   *
   * @param $type
   *   The type of the item; one of 'core', 'profile', 'module', 'theme', or
   *   'theme_engine'.
   * @param $name
   *   The name of the item for which the path is requested. Ignored for
   *   $type 'core'.
   *
   * @return string
   *   The path to the requested item or an empty string if the item is not
   *   found.
   */
  protected function drupalGetPath($type, $name) {
    return drupal_get_path($type, $name);
  }

}
