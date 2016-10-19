<?php

namespace Drupal\nimbus\config;

/**
 * Class ConfigPath.
 *
 * @package Drupal\nimbus\config
 */
class ConfigPathWithPermission extends ConfigPath implements ConfigPathPermissionInterface {

  /**
   * The permissions array.
   *
   * @var array
   */
  private $permission;

  /**
   * ConfigPathWithPermission constructor.
   *
   * @param string $config_path
   *    The config path.
   * @param bool $readPermission
   *    The read permission.
   * @param bool $writePermission
   *    The write permission.
   * @param bool $deletePermission
   *    The delete permission.
   */
  public function __construct($config_path, $readPermission = FALSE, $writePermission = FALSE, $deletePermission = FALSE) {
    parent::__construct($config_path);
    $this->permission['read'] = $readPermission;
    $this->permission['write'] = $writePermission;
    $this->permission['delete'] = $deletePermission;
  }

  /**
   * {@inheritdoc}
   */
  public function hasReadPermission($name) {
    $response = $this->permission['read'];
    if ($this->permission['read'] instanceof \Closure) {
      $response = $this->permission['read']($name);
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function hasWritePermission($name, array $data) {
    $response = $this->permission['write'];
    if ($this->permission['write'] instanceof \Closure) {
      $response = $this->permission['write']($name);
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function hasDeletePermission($name) {
    $response = $this->permission['delete'];
    if ($this->permission['delete'] instanceof \Closure) {
      $response = $this->permission['delete']($name);
    }
    return $response;
  }

}
