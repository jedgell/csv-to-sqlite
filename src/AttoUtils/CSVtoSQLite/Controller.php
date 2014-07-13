<?php

namespace AttoUtils\CSVtoSQLite;

require dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class Controller {
  function __construct(array $configuration) {
    $this->configuration = $configuration;
    unset($configuration);
    $this->validateConfiguration();
    $this->processFiles();
  }

  private function validateConfiguration() {
    // Config items not validated here are passed down to their appropriate
    // classes for validation.
    // @todo: Maybe there just needs to be a configuration validation class?
    // @todo: Maybe just validate all configuration here?
    try {
      if (!isset($this->configuration['files']) || empty($this->configuration['files']) || !is_array($this->configuration['files'])) {
        throw new Exception(503, array());
      }
      foreach ($this->configuration['files'] as $file_name => $file_options) {
        if(is_array($file_options)) {
          if (!is_readable($file_name)) {
            throw new Exception(502, array($file_name, get_current_user()));
          }
        }
        else {
          if (!is_readable($this->configuration['files'][$file_name])) {
            throw new Exception(502, $this->configuration['files'][$file_name]);
          }
        }
      }
      if (!isset($this->configuration['database']) || !is_array($this->configuration['database'])) {
        throw new Exception(103, array());
      }
    }
    catch (AttoUtils\CSVtoSQLite\Exception $e) {
      echo $e->__toString();
      exit;
    }
  }


}

$a = new Controller(array('test'=>'test'));
var_dump($a);



$a = array(
        'path/to/filename_1.csv' => array(
                'override_headers' => array(
                        'Header 1' => 'header1',
                        'Header 4' => 'header_4',
                ),
        ),
        'path/to/filename_2.csv',
        'path/to/filename_3.csv',
);

foreach ($a as $filename => $options) {
        var_dump($filename) . PHP_EOL;
//      print_r($options, TRUE);
}
