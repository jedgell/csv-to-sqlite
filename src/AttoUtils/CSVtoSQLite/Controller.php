<?php

namespace AttoUtils\CSVtoSQLite;

require dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class Controller {

  function __construct(array $configuration) {
    $this->configuration = $configuration;
    unset($configuration);
    $this->validateConfiguration();
    $this->processFiles();
    $this->createDatabase();
    $this->createTables();
    $this->importFiles();
  }

  private function validateConfiguration() {
    // Config items not validated here are passed down to their appropriate
    // classes for validation.
    try {
      if (!isset($this->configuration['files']) || empty($this->configuration['files']) || !is_array($this->configuration['files'])) {
        throw new Exception(503, array());
      }
      if (!isset($this->configuration['database']) || !is_array($this->configuration['database'])) {
        throw new Exception(103, array());
      }
    } catch (AttoUtils\CSVtoSQLite\Exception $e) {
      echo $e->__toString();
      exit;
    }
  }

  function processFiles() {
    foreach ($this->configuration['files'] as $file_name => $file_configuration) {
      $file = new File(array($file_name => $file_configuration));
      $this->configuration['files'][$file_name] = $file->configuration[$file_name];
    }
  }

  function createDatabase() {
    $this->model = new Model($this->configuration['database']);
  }

  function createTables() {
    // @todo: need to allow for column type overrides.
    foreach ($this->configuration['files'] as $file_configuration) {
      $statements = array();
      $statements[] = "DROP TABLE IF EXISTS " . $file_configuration['table_name'];
      $statements[] = "CREATE TABLE " . $file_configuration['table_name'] . "(
        id INTEGER PRIMARY KEY,
        " . implode(" TEXT,\r\n", $file_configuration['headers']) . " TEXT)";
      foreach ($statements as $statement) {
        $this->model->database->exec($statement);
      }
    }
  }

  function importFiles() {
    foreach ($this->configuration['files'] as $file_configuration) {
      $placeholders = $file_configuration['headers'];
      array_walk($placeholders, function (&$value, $key) {
        $value = ':' . $value;
      });
      $file_contents = file_get_contents($file_configuration['processed_file']);
      $file_contents = explode(PHP_EOL, $file_contents);
      unset($file_contents[0]);
      $sql = "INSERT INTO " . $file_configuration['table_name'] . " (" . implode(', ', $file_configuration['headers']) . ")
        VALUES (" . implode(', ', $placeholders) . ")";
      $statement = $this->model->database->prepare($sql);
      foreach ($file_contents as $line) {
        $line = addslashes($line);
        $line = str_getcsv($line, $file_configuration['delimeter'], $file_configuration['escape'] . $file_configuration['enclosure']);
        $i = 0;
        foreach ($line as $line_item) {
          $statement->bindValue($placeholders[$i], $line_item);
          $i++;
        }
        $statement->execute();
      }
    }
  }

}
