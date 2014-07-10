<?php

namespace CsvToSqlite\Framework;

use Exception;

class Model {

  function __construct(array $configuration) {
    switch ($configuration['type']) {
      case 'memory':
        $this->database = new PDO('sqlite::memory:');
        break;

      case 'file':
        if (!isset($configuration['file_name']) || empty($configuration['file_name'])) {
          throw new CsvToSqlite\Exception('A file name must be provided for SQLite databases of type "file".', 100);
        }
        if (!isset($configuration['file_path']) || empty($configuration['file_path'])) {
          throw new CvsToSqlite\Exception('A file path must be provided for SQLite databases of type "file".', 101);

        }
        $this->database = new PDO('sqlite:messaging.sqlite3');

      default:
        # code...
        break;
    }

    $this->database->setAttribute(PDO::ATTR_PERSISTENT, TRUE);
    $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

}
