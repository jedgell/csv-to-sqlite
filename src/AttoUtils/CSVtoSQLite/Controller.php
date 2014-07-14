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
      foreach ($this->configuration['files'] as $file_name => &$file_options) {
        if(is_array($file_options)) {
          if (!is_writable(dirname($file_name))) {
            throw new Exception(502, array(dirname($file_name), get_current_user()));
          }
          foreach ($file_options as $option => $value) {
            if (empty($value)) {
              throw new Exception(104, array($option, 'NULL'));
            }
            $file_contents = file_get_contents($file_name);
            switch ($option) {
              case 'delimeter':
              case 'enclosure':
              case 'escape':
                if (empty($value)) {
                  throw new Exception(104, array($option, $value));
                }
                break;

              case 'table_name':
                if (!preg_match('/^[a-z_\-0-9]+$/i', $value)) {
                  $new_name = strtolower(preg_replace("/[^\w\d]/ui", '_', $value));
                  trigger_error(sprintf('The value "%s" provided as an override value in the "table_name" configuration for file "%s" contains illegal characters. The generated table name will be "%s" after file processing.', $value, $file_name, $new_name));
                }
                break;

              case 'has_header':
                if (!in_array($value, array(TRUE,FALSE))) {
                  throw new Exception(104, array($option, $value));
                }
                break;

              case 'override_headers':
                $file_contents = explode(PHP_EOL, $file_contents);
                if (isset($file_options['has_header']) && $file_options['has_header'] === FALSE && count($value) !== count($file_contents[0])) {
                  throw new Exception(105, array('has_header', 'FALSE', 'override_headers'));
                }
                foreach ($value as $old => $new) {
                  if (!strpos($file_contents[0], $old)) {
                    throw new Exception(104, array('override_headers: old header', $old));
                  }
                  if (!preg_match('/^[a-z_\-0-9]+$/i', $new)) {
                    $new_name = strtolower(preg_replace("/[^\w\d]/ui", '_', $new));
                    trigger_error(sprintf('The value "%s" provided as an override value in the "override_headers" configuration for file "%s" contains illegal characters. The generated column name will be "%s" after file processing.', $new, $file_name, $new_name));
                  }
                }
                break;

              case 'line_callbacks':
              case 'file_callbacks':
                foreach ($value as $callback => $arguments) {
                  if (!is_callable($callback)) {
                    throw new Exception(104, array($option, $callback));
                  }
                  if (!is_array($arguments)) {
                    throw new Exception($option .': ' . $callback, 'NULL');
                  }
                }
                break;

              default:
                break;
            }
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


  // @todo: This process needs to be broken out into its own class.
  function processFiles() {
    foreach ($this->configuration['files'] as $file_name => &$file_options) {
      if (is_array($file_options)) {
        if (!isset($file_options['delimeter'])) {
          $file_options['delimeter'] = ',';
        }
        if (!isset($file_options['enclosure'])) {
          $file_options['enclosure'] = '"';
        }
        if (!isset($file_options['escape'])) {
          $file_options['escape'] = '\\';
        }
        if (!isset($file_options['has_header'])) {
          $file_options['has_header'] = TRUE;
        }
        if (isset($file_options['table_name']) && !empty($file_options['table_name'])){
          if (!preg_match('/^[a-z_\-0-9]+$/i', $file_options['table_name'])) {
            $file_options['table_name'] = strtolower(preg_replace("/[^\w\d]/ui", '_', $file_options['table_name']));
          }
        }
        else {
          $file_options['table_name'] = strtolower(preg_replace("/[^\w\d]/ui", '_', basename($file_name)));
        }
        $file_contents = file_get_contents($file_name);
        if (isset($file_options['file_callbacks']) && !empty($file_options['file_callbacks'])) {
          foreach ($file_options['file_callbacks'] as $callback => $arguments) {
            array_push($arguments, $file_contents);
            $file_contents = call_user_func_array($callback, $arguments);
            array_pop($arguments);
          }
        }
        $file_contents = explode(PHP_EOL, $file_contents);
        if (isset($file_options['line_callbacks']) && !empty($file_options['line_callbacks'])) {
          foreach ($file_options['line_callbacks'] as $callback => $arguments) {
            foreach ($file_contents as &$line) {
              $line = addslashes($line);
              $line = str_getcsv($line, $file_options['delimeter'], $file_options['escape'] . $file_options['enclosure']);
              array_push($arguments, $line);
              $line = call_user_func_array($callback, $arguments);
              if (!is_array($line)) {
                throw new Exception(202, array($callback));
              }
              $line = join($file_options['delimeter'], $line);
              array_pop($arguments);
            }
          }
        }
        if (isset($file_options['override_headers']) && !empty($file_options['override_headers']) && isset($file_options['has_header']) && !empty($file_options['has_header'])) {
          if ($file_options['has_header'] === TRUE) {
            $file_headers = array_shift($file_contents);
            foreach ($file_options['override_headers'] as $old => $new) {
              $file_headers = str_replace($old, $new, $file_headers);
            }
            array_unshift($file_contents, $file_headers);
          }
          elseif ($file_options['has_header'] === FALSE){
            array_unshift($new_file_contents, $file_options['override_headers']);
          }
        }
      }
      $file_headers = str_getcsv(array_shift($file_contents), $file_options['delimeter'], $file_options['escape'] . $file_options['enclosure']);
      foreach ($file_headers as &$header) {
        $header = strtolower(preg_replace("/^(\[|\"?)[^\w\d]+(\]|\"?)$/ui", '_', trim($header)));
      }
      array_unshift($file_contents, join($file_options['delimeter'], $file_headers));
      $file_contents = join(PHP_EOL, $file_contents);
      $processed_directory = dirname($file_name) . DIRECTORY_SEPARATOR . 'processed';
      if (!file_exists($processed_directory)) {
        mkdir($processed_directory, 0755);
      }
      $file_options['processed_file'] = $processed_directory . DIRECTORY_SEPARATOR . basename($file_name);
      file_put_contents($file_options['processed_file'], $file_contents);
    }
  }
}
