<?php

namespace AttoUtils\CSVtoSQLite;

require dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class ParserImporter {

  /**
   * An array of processed files, keyed by order processed.
   *
   * @var mixed
   */
  var $files = array();

  /**
   * The current file being processed in processFiles() or FALSE if not.
   *
   * @var mixed
   */
  var $fileInProcess = FALSE;

  function __construct(array $configuration) {
    $this->configuration = $configuration;
    unset($configuration);
    $this->validateConfiguration();
    $this->createDatabase();
    $this->processFiles();
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
      $this->fileInProcess = $file_name;
      $file = new File(array($file_name => $file_configuration));
      $file_index = count($this->files);
      $this->files[$file_index] = $file;
      if ($this->files[$file_index] instanceof File) {
        unset($this->configuration['files'][$file_name]);
      }
      $this->fileInProcess = FALSE;
    }
    if (empty($this->configuration['files'])) {
      unset($this->configuration['files']);
    }
    if (empty($this->configuration)) {
      unset($this->configuration);
    }
  }

  function createDatabase() {
    $this->sqlite = new Database($this->configuration['database']);
    if ($this->sqlite->database instanceof \PDO) {
      unset($this->configuration['database']);
    }
  }

  function createTables() {
    // @todo: need to allow for column type overrides.
    $count_of_files = count($this->files);
    for ($i = 0; $i < $count_of_files; $i++) {
      $file_configuration = $this->files[$i];
      $statements = array();
      $statements[] = "DROP TABLE IF EXISTS " . $file_configuration->table_name;
      $statements[] = "CREATE TABLE " . $file_configuration->table_name . "(
        id INTEGER PRIMARY KEY,
        " . implode(" TEXT,\r\n", $file_configuration->headers) . " TEXT)";
      foreach ($statements as $statement) {
        $this->sqlite->database->exec($statement);
      }
    }
  }

  function importFiles() {
    $count_of_files = count($this->files);
    for ($i = 0; $i < $count_of_files; $i++) {
      $file_configuration = $this->files[$i];
      $placeholders = $file_configuration->headers;
      array_walk($placeholders, function (&$value, $key) {
        $value = ':' . $value;
      });
      $file_contents = file_get_contents($file_configuration->processed_file);
      $file_contents = explode(PHP_EOL, $file_contents);
      unset($file_contents[0]);
      $sql = "INSERT INTO " . $file_configuration->table_name . " (" . implode(', ', $file_configuration->headers) . ")
        VALUES (" . implode(', ', $placeholders) . ")";
      $statement = $this->sqlite->database->prepare($sql);
      foreach ($file_contents as $line) {
        $line = addslashes($line);
        $line = str_getcsv($line, $file_configuration->delimeter, $file_configuration->escape . $file_configuration->enclosure);
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
