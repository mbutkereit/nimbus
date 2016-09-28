<?php

namespace Drupal\nimbus;

/**
 * Class ConfigChange.
 *
 * @package Drupal\nimbus
 */
class ConfigChange {
  private $active;
  private $staging;

  /**
   *
   */
  public function __construct(array $active, array $staging) {
    $this->active = $active;
    $this->staging = $staging;
  }

  /**
   *
   */
  public function getStagingConfig() {
    return $this->staging;
  }

  /**
   *
   */
  public function getActiveConfig() {
    return $this->active;
  }

  /**
   *
   */
  public function getActiveUuid() {
    if (isset($this->active['uuid'])) {
      return $this->active['uuid'];
    }
    return FALSE;
  }

  /**
   *
   */
  public function getStagingUuid() {
    if (isset($this->staging['uuid'])) {
      return $this->staging['uuid'];
    }
    return FALSE;
  }

  /**
   * Check if uuid not equal.
   *
   * @return bool
   *    Is uuid not equivalent true.
   */
  public function isUuidNotEquivalent() {
    $response = FALSE;
    if ($this->getActiveUuid() && $this->getStagingUuid()) {
      if ($this->getActiveUuid() != $this->getStagingUuid()) {
        $response = TRUE;
      }
    }
    return $response;
  }

}
