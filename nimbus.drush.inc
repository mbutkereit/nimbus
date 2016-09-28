<?php

/**
 * @file
 * Provides Configuration Management commands.
 */

use Symfony\Component\Console\Output\ConsoleOutput;
use Drupal\nimbus\config\ProxyFileStorage;
use Drupal\config\StorageReplaceDataWrapper;
use Drush\Log\LogLevel;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\FileStorage;
use Drush\Config\StorageWrapper;
use Symfony\Component\Console\Helper\Table;

/**
 * Implements hook_drush_command().
 */
function nimbus_drush_command() {

  $items['nimbus-cex'] = array(
    'description' => 'Demonstrate how Drush commands work.',
    'aliases' => array('ncex'),
    'callback' => 'nimbus_drush_export',
  );

  $items['nimbus-cim'] = array(
    'description' => 'Wrapper for drush cim.',
    'aliases' => array('ncim'),
    'callback' => 'nimbus_drush_import',
  );

  $items['nimbus-force-uuid'] = array(
    'description' => 'Update uuids.',
    'aliases' => array('fuuid'),
    'callback' => 'nimbus_override_ids',
  );

  return $items;
}

/**
 * Implements hook_drush_command_alter().
 */
function nimbus_drush_command_alter(&$command) {
  if (isset($command['aliases'][0]) && $command['aliases'][0] == 'cim') {
    $command['callback'] = 'nimbus_drush_import';
  }
  if (isset($command['aliases'][0]) && $command['aliases'][0] == 'cex') {
    $command['callback'] = 'nimbus_drush_export';
  }
}

/**
 * A override function for ids.
 */
function nimbus_override_ids() {
  $source_storage = \Drupal::service('config.storage.staging');
  $query = \Drupal::database()->select('config', 'cf');
  $query->condition('collection', '');
  $query->addField('cf', 'name');
  $query->addField('cf', 'data');

  $result = $query->execute()->fetchAll();
  foreach ($result as $element) {
    if ($source_storage->exists($element->name)) {
      $value = $source_storage->read($element->name);
      $current_database_value = unserialize($element->data);
      if (isset($value['uuid']) && isset($current_database_value['uuid']) && $current_database_value['uuid'] != $value['uuid']) {
        $current_database_value['uuid'] = $value['uuid'];
        $new_database_entry = serialize($current_database_value);
        \Drupal::database()
          ->update('config')
          ->condition('collection', '')
          ->condition('name', $element->name)
          ->fields(
            [
              'data' => $new_database_entry,
            ]
          )->execute();
      }
    }
  }
}

/**
 * Create a beautiful table.
 *
 * @param mixed $rows
 *    Rows array from the diff.
 */
function create_table_export($rows) {
  $file_storage = \Drupal::service('config.storage.staging');
  if ($file_storage instanceof ProxyFileStorage) {
    $output = new ConsoleOutput();
    $table = new Table($output);
    $elements = [];
    foreach ($rows as $collection => $row) {
      foreach ($row as $key => $config_names) {
        foreach ($config_names as $config_name) {
          $path = ($key == 'delete') ? $file_storage->getFilePath($config_name) : $file_storage->getWriteDirectories();
          $elements[] = [
            $collection,
            $config_name,
            $key,
            $path,
          ];
        }
      }
    }
    $table
      ->setHeaders(['Collection', 'Config', 'Operation', 'Directory'])
      ->setRows($elements);
    $table->render();
  }
  else {
    _drush_print_config_changes_table($rows);
  }

}

/**
 * Create a beautiful table.
 *
 * @param mixed $rows
 *    Rows array from the diff.
 */
function create_table($rows) {
  $file_storage = \Drupal::service('config.storage.staging');
  if ($file_storage instanceof ProxyFileStorage) {
    $output = new ConsoleOutput();
    $table = new Table($output);
    $elements = [];
    foreach ($rows as $collection => $row) {
      foreach ($row as $key => $config_names) {
        foreach ($config_names as $config_name) {
          $elements[] = [
            $collection,
            $config_name,
            $key,
            $file_storage->getFilePath($config_name),
          ];
        }
      }
    }
    $table
      ->setHeaders(['Collection', 'Config', 'Operation', 'Directory'])
      ->setRows($elements);
    $table->render();
  }
  else {
    _drush_print_config_changes_table($rows);
  }

}

/**
 * The overrie method for the default drush export.
 *
 * @param string $destination
 *    Optional destination folder.
 *
 * @return array|bool|mixed
 *    The drush respone.
 */
function nimbus_drush_export($destination = NULL) {
  drush_print('Override Export');
  global $config_directories;

  $file_storage = \Drupal::service('config.storage.staging');
  if (!($file_storage instanceof ProxyFileStorage)) {
    drush_print('Something is wrong with your setup.');
    return TRUE;
  }

  // Determine which target directory to use.
  if ($target = drush_get_option('destination')) {
    if ($target === TRUE) {
      // User did not pass a specific value for --destination. Make one.
      /** @var drush_version_control_backup $backup */
      $backup = drush_include_engine('version_control', 'backup');
      $config_directories[CONFIG_SYNC_DIRECTORY] = $backup->prepare_backup_dir('config-export');
    }
    else {
      $config_directories[CONFIG_SYNC_DIRECTORY] = $target;
      // It is important to be able to specify a destination directory that
      // does not exist yet, for exporting on remote systems.
      drush_mkdir($config_directories[CONFIG_SYNC_DIRECTORY]);
    }
  }
  else {
    $choices = drush_map_assoc(array_keys($config_directories));
    unset($choices[CONFIG_ACTIVE_DIRECTORY]);
    if (!isset($destination) && count($choices) >= 2) {
      $destination = drush_choice($choices, 'Choose a destination.');
      if (empty($destination)) {
        return drush_user_abort();
      }
    }
    elseif (!isset($destination)) {
      $destination = CONFIG_SYNC_DIRECTORY;
    }
    $config_directories[CONFIG_SYNC_DIRECTORY] = config_get_config_directory($destination);
  }

  // Prepare a new branch, if applicable.
  $remote = drush_get_option('push', FALSE);
  $original_branch = FALSE;
  $branch = FALSE;
  if ($remote) {
    // Get the branch that we're on at the moment.
    $result = drush_shell_cd_and_exec($config_directories[CONFIG_SYNC_DIRECTORY], 'git rev-parse --abbrev-ref HEAD');
    if (!$result) {
      return drush_set_error('DRUSH_CONFIG_EXPORT_NO_GIT', dt("The drush config-export command requires that the selected configuration directory !dir be under git revision control when using --commit or --push options.", array('!dir' => $destination_dir)));
    }
    $output = drush_shell_exec_output();
    $original_branch = $output[0];
    $branch = drush_get_option('branch', FALSE);
    if (!$branch) {
      $branch = $original_branch;
    }
    if ($branch != $original_branch) {
      // Switch to the working branch;
      // create it if it does not exist.
      // We do NOT want to use -B here,
      // as we do NOT want to reset the
      // branch if it already exists.
      $result = drush_shell_cd_and_exec($config_directories[CONFIG_SYNC_DIRECTORY], 'git checkout %s', $branch);
      if (!$result) {
        $result = drush_shell_cd_and_exec($config_directories[CONFIG_SYNC_DIRECTORY], 'git checkout -b %s', $branch);
      }
    }
  }

  // Do the actual config export operation.
  $result = _drush_config_export_extended($destination, $config_directories[CONFIG_SYNC_DIRECTORY], $branch);

  // Regardless of the result of the export, reset to our original branch.
  if ($branch != $original_branch) {
    drush_shell_cd_and_exec($config_directories[CONFIG_SYNC_DIRECTORY], 'git checkout %s', $original_branch);
  }

  return $result;
}

/**
 * The overrie method for the default drush import.
 *
 * @param string $source
 *    Optional destination folder.
 *
 * @return array|bool|mixed
 *    The drush respone.
 */
function nimbus_drush_import($source = NULL) {
  global $config_directories;
  drush_print('Overriden Import');

  $file_storage = \Drupal::service('config.storage.staging');
  if (!($file_storage instanceof ProxyFileStorage)) {
    drush_print('Something is wrong with your setup.');
    return TRUE;
  }

  // Determine source directory.
  if ($target = drush_get_option('source')) {
    $config_directories[CONFIG_SYNC_DIRECTORY] = $target;
  }
  else {
    $choices = drush_map_assoc(array_keys($config_directories));
    unset($choices[CONFIG_ACTIVE_DIRECTORY]);
    if (!isset($source) && count($choices) >= 2) {
      $source = drush_choice($choices, 'Choose a source.');
      if (empty($source)) {
        return drush_user_abort();
      }
    }
    elseif (!isset($source)) {
      $source = CONFIG_SYNC_DIRECTORY;
    }
    $config_directories[CONFIG_SYNC_DIRECTORY] = config_get_config_directory($source);
  }

  // Determine $source_storage in partial and non-partial cases.
  /** @var \Drupal\Core\Config\StorageInterface $active_storage */
  $active_storage = \Drupal::service('config.storage');
  if (drush_get_option('partial')) {
    $source_storage = new StorageReplaceDataWrapper($active_storage);
    $file_storage = \Drupal::service('config.storage.staging');
    foreach ($file_storage->listAll() as $name) {
      $data = $file_storage->read($name);
      $source_storage->replaceData($name, $data);
    }
  }
  else {
    $source_storage = \Drupal::service('config.storage.staging');
  }

  // If our configuration storage is being filtered, then attach all filters
  // to the source storage object.  We will use the filtered values uniformly
  // for comparison, full imports, and partial imports.
  $storage_filters = drush_config_get_storage_filters();
  if (!empty($storage_filters)) {
    $source_storage = new StorageWrapper($source_storage, $storage_filters);
  }

  /** @var \Drupal\Core\Config\ConfigManagerInterface $config_manager */
  $config_manager = \Drupal::service('config.manager');
  $storage_comparer = new StorageComparer($source_storage, $active_storage, $config_manager);

  if (!$storage_comparer->createChangelist()->hasChanges()) {
    return drush_log(dt('There are no changes to import.'), LogLevel::OK);
  }

  if (drush_get_option('preview', 'list') == 'list') {
    $change_list = array();
    foreach ($storage_comparer->getAllCollectionNames() as $collection) {
      $change_list[$collection] = $storage_comparer->getChangelist(NULL, $collection);
    }
    create_table($change_list);
  }
  else {
    // Copy active storage to the temporary directory.
    $temp_dir = drush_tempdir();
    $temp_storage = new FileStorage($temp_dir);
    $source_dir_storage = \Drupal::service('config.storage.staging');
    foreach ($source_dir_storage->listAll() as $name) {
      if ($data = $active_storage->read($name)) {
        $temp_storage->write($name, $data);
      }
    }
    drush_shell_exec('diff -x %s -u %s %s', '*.git', $temp_dir, $config_directories[CONFIG_SYNC_DIRECTORY]);
    $output = drush_shell_exec_output();
    drush_print(implode("\n", $output));
  }

  if (drush_confirm(dt('Import the listed configuration changes?'))) {
    return drush_op('_drush_config_import', $storage_comparer);
  }
}

/**
 * Extended drush updatet funktionality.
 *
 * @param string $destination
 *    The destination.
 * @param string $destination_dir
 *    The destination dir.
 * @param string $branch
 *    The branch.
 *
 * @return array|bool
 *    Return successfull or not
 */
function _drush_config_export_extended($destination, $destination_dir, $branch) {
  $commit = drush_get_option('commit');
  $comment = drush_get_option('message', 'Exported configuration.');
  $storage_filters = drush_config_get_storage_filters();

  // Retrieve a list of differences between the
  // active and target configuration (if any).
  $target_storage = \Drupal::service('config.storage.staging');
  /** @var \Drupal\Core\Config\StorageInterface $active_storage */
  $active_storage = \Drupal::service('config.storage');
  $comparison_source = $active_storage;

  // If the output is being filtered, then write a temporary copy before doing
  // any comparison.
  if (!empty($storage_filters)) {
    $tmpdir = drush_tempdir();
    drush_copy_dir($destination_dir, $tmpdir, FILE_EXISTS_OVERWRITE);
    $comparison_source = new FileStorage($tmpdir);
    $comparison_source_filtered = new StorageWrapper($comparison_source, $storage_filters);
    foreach ($active_storage->listAll() as $name) {
      // Copy active storage to our temporary active store.
      if ($existing = $active_storage->read($name)) {
        $comparison_source_filtered->write($name, $existing);
      }
    }
  }

  $config_comparer = new StorageComparer($comparison_source, $target_storage, \Drupal::service('config.manager'));
  if (!$config_comparer->createChangelist()->hasChanges()) {
    return drush_log(dt('The active configuration is identical to the configuration in the export directories.'), LogLevel::OK);
  }

  drush_print("Differences of the active config to the export directory:\n");
  $change_list = array();
  foreach ($config_comparer->getAllCollectionNames() as $collection) {
    $change_list[$collection] = $config_comparer->getChangelist(NULL, $collection);
  }
  // Print a table with changes in color, then re-generate again without
  // color to place in the commit comment.
  create_table_export($change_list);
  $comment .= "\n\n";

  if (!$commit && !drush_confirm(dt('The .yml files in your export directory (!target) will be deleted and replaced with the active config.', array('!target' => $target_storage->getWriteDirectories())))) {
    return drush_user_abort();
  }

  // Write all .yml files.
  $source_storage = \Drupal::service('config.storage');
  $destination_storage = \Drupal::service('config.storage.staging');
  // If there are any filters, then attach them to the destination storage.
  if (!empty($storage_filters)) {
    $destination_storage = new StorageWrapper($destination_storage, $storage_filters);
  }
  if (isset($change_list[''])) {

    foreach ($change_list['']['delete'] as $name) {
      if (is_string($name)) {
        $destination_storage->delete($name);
      }
    }
    unset($change_list['']['delete']);

    foreach ($change_list[''] as $update_categories) {
      foreach ($update_categories as $name) {
        if (is_string($name)) {
          $destination_storage->write($name, $source_storage->read($name));
        }
      }
    }
  }

  // Export configuration collections.
  $live_collection = \Drupal::service('config.storage')->getAllCollectionNames();
  $collections_iteration = array_merge($live_collection, $destination_storage->getAllCollectionNames());
  array_unique($collections_iteration);
  foreach ($collections_iteration as $collection) {
    $source_storage = $source_storage->createCollection($collection);
    $destination_storage = $destination_storage->createCollection($collection);
    if (isset($change_list[$collection])) {
      if (isset($change_list[$collection]['delete'])) {
        foreach ($change_list[$collection]['delete'] as $name) {
          if (is_string($name)) {
            $destination_storage->delete($name);
          }
        }
        unset($change_list[$collection]['delete']);
      }
      foreach ($change_list[$collection] as $update_categories) {
        foreach ($update_categories as $name) {
          if (is_string($name)) {
            $destination_storage->write($name, $source_storage->read($name));
          }
        }
      }
    }
  }

  drush_log(dt('Configuration successfully exported to !target.', array('!target' =>  $target_storage->getWriteDirectories())), LogLevel::SUCCESS);
  drush_backend_set_result($destination_dir);

  // Commit and push, or add exported configuration if requested.
  $remote = drush_get_option('push', FALSE);
  if ($commit || $remote) {
    // There must be changed files at the destination dir; if there are not,then
    // we will skip the commit-and-push step.
    $result = drush_shell_cd_and_exec($destination_dir, 'git status --porcelain .');
    if (!$result) {
      return drush_set_error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git status` failed."));
    }
    $uncommitted_changes = drush_shell_exec_output();
    if (!empty($uncommitted_changes)) {
      $result = drush_shell_cd_and_exec($destination_dir, 'git add -A .');
      if (!$result) {
        return drush_set_error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git add -A` failed."));
      }
      $comment_file = drush_save_data_to_temp_file($comment);
      $result = drush_shell_cd_and_exec($destination_dir, 'git commit --file=%s', $comment_file);
      if (!$result) {
        return drush_set_error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git commit` failed.  Output:\n\n!output", array('!output' => implode("\n", drush_shell_exec_output()))));
      }
      if ($remote) {
        // Remote might be FALSE, if --push was not specified, or
        // it might be TRUE if --push was not given a value.
        if (!is_string($remote)) {
          $remote = 'origin';
        }
        $result = drush_shell_cd_and_exec($destination_dir, 'git push --set-upstream %s %s', $remote, $branch);
        if (!$result) {
          return drush_set_error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git push` failed."));
        }
      }
    }
  }
  elseif (drush_get_option('add')) {
    drush_shell_exec_interactive('git add -p %s', $destination_dir);
  }

  return TRUE;
}
