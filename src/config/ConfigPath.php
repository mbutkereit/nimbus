<?php

namespace Drupal\nimbus\config;

/**
 *
 */
class ConfigPath {

  private $configPath;

  private $additionalInformation = [];

  /**
   *
   */
  public function __construct($config_path) {
    $this->configPath = $config_path;
  }

  /**
   *
   */
  public function addAdditionalInformation($key, $info) {
    $this->additionalInformation[$key] = $info;
  }

  /**
   *
   */
  public function getAdditionalInformation() {
    return $this->additionalInformation;
  }

  /**
   *
   */
  public function __toString() {
    return $this->configPath;
  }

}
