<?php

namespace Drupal\nimbus;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class NimbusServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('config.storage.staging')) {
      try {
        $definition = $container->getDefinition('config.storage.staging');
        $definition->setFactory('Drupal\nimbus\config\FileStorageFactoryAlter::getSync');
      }
      catch (\Exception $e) {

      }
    }
  }

}
