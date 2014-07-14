<?php

namespace AttoUtils\CSVtoSQLite;

require dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use \AttoUtils\CSVtoSQLite\Exception;

class Model {

  function __construct(array $configuration) {

    // foreach ($configuration as $key => $value) {
    //   $this->$key = $value;
    // }
    $this->configuration = $configuration;
    unset($configuration);
    $this->verifyConfiguration();
    $this->createDatabase();
  }

  private function verifyConfiguration() {

    try {
      if (!extension_loaded('PDO_SQLITE')) {
        throw new Exception(500, array('PDO_SQLITE'));
      }

      switch ($this->configuration['type']) {
        case 'memory':
          break;

        case 'file':
          if (!isset($this->configuration['file_name']) || empty($this->configuration['file_name'])) {
            throw new Exception(101, array('file_name', 'file'));
          }
          if (!isset($this->configuration['file_path']) || empty($this->configuration['file_path'])) {
            throw new Exception(101, array('file_path', 'file'));
          }
          if (!is_writable($this->configuration['file_path'])) {
            throw new Exception(502, array($this->configuration['file_path'], get_current_user()));
          }
          break;

        default:
          break;
      }
    }
    catch (Exception $e) {
      echo $e->__toString();
      exit;
    }
  }

  function createDatabase() {
    try {
      switch ($this->configuration['type']) {
        case 'memory':
          $this->database = new \PDO('sqlite::memory:');
          break;

        case 'file':
          $this->database = new \PDO('sqlite:' . $this->configuration['file_path'] . DIRECTORY_SEPARATOR . $this->configuration['file_name']);
          break;

        default:
          break;
      }

      $this->database->setAttribute(\PDO::ATTR_PERSISTENT, TRUE);
      $this->database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    catch (\Exception $e) {
      echo $e->__toString();
    }
  }

}
