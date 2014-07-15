<?php

namespace AttoUtils\CSVtoSQLite;

require dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class File {

  function __construct(array $configuration) {
    $this->configuration = $configuration;
    unset($configuration);
    $this->validateConfiguration();
    $this->processFile();
  }

  private function validateConfiguration() {

    foreach ($this->configuration as $file_name => &$file_options) {
      if (is_array($file_options)) {
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
              if (!in_array($value, array(TRUE, FALSE))) {
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
                  throw new Exception($option . ': ' . $callback, 'NULL');
                }
              }
              break;

            default:
              break;
          }
        }
      }
      else {
        throw new Exception(104, array('filename', 'not an array'));
      }
    }
  }

  function processFile() {
    foreach ($this->configuration as $file_name => &$file_options) {
      $this->processDelimeters($file_name);
      $this->processHasHeader($file_name);
      $this->processTableName($file_name);
      $file_contents = file_get_contents($file_name);
      $this->processUserFileCallbacks($file_name, $file_contents);
      $file_contents = explode(PHP_EOL, $file_contents);
      $this->processUserLineCallbacks($file_name, $file_contents);
      $this->processHeaderOverrides($file_name, $file_contents);
      $this->processHeaders($file_name, $file_contents);
      $file_contents = join(PHP_EOL, $file_contents);
      $this->saveProcessedFile($file_name, $file_contents);
      ksort($file_options);
      foreach ($file_options as $option => $value) {
        $this->$option = $value;
      }
      $this->original_file = $file_name;
      unset($this->configuration[$file_name]);
    }
    unset($this->configuration);
  }

  protected function processDelimeters($file_name) {
    if (!isset($this->configuration[$file_name]['delimeter'])) {
      $this->configuration[$file_name]['delimeter'] = ',';
    }
    if (!isset($this->configuration[$file_name]['enclosure'])) {
      $this->configuration[$file_name]['enclosure'] = '"';
    }
    if (!isset($this->configuration[$file_name]['escape'])) {
      $this->configuration[$file_name]['escape'] = '\\';
    }
  }

  protected function processHasHeader($file_name) {
    if (is_array($this->configuration[$file_name])) {
      if (!isset($this->configuration[$file_name]['has_header'])) {
        $this->configuration[$file_name]['has_header'] = TRUE;
      }
    }
    else {
      $this->configuration[$file_name] = array(
        'has_header' => TRUE,
      );
    }
  }

  protected function processTableName($file_name) {
    if (isset($this->configuration[$file_name]['table_name']) && !empty($this->configuration[$file_name]['table_name'])) {
      if (!preg_match('/^[a-z_\-0-9]+$/i', $this->configuration[$file_name]['table_name'])) {
        $this->configuration[$file_name]['table_name'] = strtolower(preg_replace("/[^\w\d]/ui", '_', $this->configuration[$file_name]['table_name']));
      }
    }
    else {
      $this->configuration[$file_name]['table_name'] = strtolower(preg_replace("/[^\w\d]/ui", '_', basename($file_name)));
    }
  }

  protected function processUserFileCallbacks($file_name, &$file_contents) {
    if (isset($this->configuration[$file_name]['file_callbacks']) && !empty($this->configuration[$file_name]['file_callbacks'])) {
      foreach ($this->configuration[$file_name]['file_callbacks'] as $callback => $arguments) {
        array_push($arguments, $file_contents);
        $file_contents = call_user_func_array($callback, $arguments);
        array_pop($arguments);
      }
    }
  }

  protected function processUserLineCallbacks($file_name, &$file_contents) {
    if (isset($this->configuration[$file_name]['line_callbacks']) && !empty($this->configuration[$file_name]['line_callbacks'])) {
      foreach ($file_contents as &$line) {
        $line = addslashes($line);
        $line = str_getcsv($line, $this->configuration[$file_name]['delimeter'], $this->configuration[$file_name]['escape'] . $this->configuration[$file_name]['enclosure']);
        foreach ($this->configuration[$file_name]['line_callbacks'] as $callback => $arguments) {
          array_push($arguments, $line);
          $line = call_user_func_array($callback, $arguments);
          if (!is_array($line)) {
            throw new Exception(202, array($callback));
          }
          array_pop($arguments);
        }
        $line = join($this->configuration[$file_name]['delimeter'], $line);
        $line = stripslashes($line);
      }
    }
  }

  protected function processHeaderOverrides($file_name, &$file_contents) {
    if (isset($this->configuration[$file_name]['override_headers']) && !empty($this->configuration[$file_name]['override_headers']) && isset($this->configuration[$file_name]['has_header']) && !empty($this->configuration[$file_name]['has_header'])) {
      if ($this->configuration[$file_name]['has_header'] === TRUE) {
        $file_headers = array_shift($file_contents);
        foreach ($this->configuration[$file_name]['override_headers'] as $old => $new) {
          $file_headers = str_replace($old, $new, $file_headers);
        }
        array_unshift($file_contents, $file_headers);
      }
      elseif ($this->configuration[$file_name]['has_header'] === FALSE) {
        array_unshift($new_file_contents, $this->configuration[$file_name]['override_headers']);
      }
    }
  }

  protected function processHeaders($file_name, &$file_contents) {
    $file_headers = str_getcsv(array_shift($file_contents), $this->configuration[$file_name]['delimeter'], $this->configuration[$file_name]['escape'] . $this->configuration[$file_name]['enclosure']);
    array_walk($file_headers, function (&$value, $key) {
      $value = strtolower(preg_replace('/[^\w\d\[\]]+/ui', '_', str_replace('"', '', trim($value))));
    });
    $this->configuration[$file_name]['headers'] = $file_headers;
    $file_headers = join($this->configuration[$file_name]['delimeter'], $file_headers);
    array_unshift($file_contents, $file_headers);
  }

  protected function saveProcessedFile($file_name, &$file_contents) {
    $processed_directory = dirname($file_name) . DIRECTORY_SEPARATOR . 'processed';
    if (!file_exists($processed_directory)) {
      mkdir($processed_directory, 0755);
    }
    $this->configuration[$file_name]['processed_file'] = $processed_directory . DIRECTORY_SEPARATOR . basename($file_name);
    file_put_contents($this->configuration[$file_name]['processed_file'], $file_contents);
  }

}
