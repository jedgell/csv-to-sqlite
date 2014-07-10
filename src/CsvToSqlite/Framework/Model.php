<?php

namespace CsvToSqlite\Framework;

use Exception;

class Model {

  function __construct(array $configuration) {

    if (!extension_loaded('PDO_SQLITE')) {
      throw new Exception(null, 500, null, array('PDO_SQLITE'));
    }

    switch ($configuration['type']) {
      case 'memory':
        $this->database = new PDO('sqlite::memory:');
        break;

      case 'file':
        if (!isset($configuration['file_name']) || empty($configuration['file_name'])) {
          throw new Exception(null, 101, null, array('file_name', 'file'));
        }
        if (!isset($configuration['file_path']) || empty($configuration['file_path'])) {
          throw new Exception(null, 101, null, array('file_path', 'file'));
        }
        if (!file_exists($configuration['file_path'] . DIRECTORY_SEPARATOR . $configuration['file_name']) && is_writable($configuration['file_path'])) {
          $this->database = new PDO('sqlite:' . $configuration['file_path'] . DIRECTORY_SEPARATOR . $configuration['file_name']);
        }
        break;

      default:
        throw new Exception(null, 100, null, null);
        break;
    }

    $this->database->setAttribute(PDO::ATTR_PERSISTENT, TRUE);
    $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

}
