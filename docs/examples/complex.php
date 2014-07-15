<?php

// That include_once, tho! Insane! Yes, probably could've just typed
// include_once '../../../src/AttoUtils/CSVtoSQLite/ParserImporter.php';
// but then, where's the fun in that? That doesn't bake your cpu!
include_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'AttoUtils' . DIRECTORY_SEPARATOR . 'CSVtoSQLite' . DIRECTORY_SEPARATOR . 'ParserImporter.php';

function lower_case_states($line) {
  array_walk($line, function(&$item, $key) {
    trim($item);
  });
  ucwords(strtolower($line[0]));
  return $line;
}

function nix_quotes_around_text($line) {
  array_walk($line, function(&$item, $key) {
    if (!$new_val = intval($item)) {
      $item = str_replace('"', '', $item);
    }
  });
  return $line;
}

function nix_quotes_around_numbers($line) {
  $i = 0;
  array_walk($line, function(&$item, $key) use(&$i) {
    if (in_array($key, array(4, 5)) && $new_val = intval(str_replace(array(',', '"'), '', $item))) {
      $item = $new_val;
    }
    $i++;
  });
  return $line;
}

function remove_utc($file) {
  return str_replace('UTC', '', $file);
}

$config = array(
  'database' => array(
    'type' => 'file',
    'file_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'processed',
    'file_name' => 'example.sqlite3',
  ),
  'files' => array(
    // dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'States.csv' => array(),
    dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'Countries.csv' => array(
      'delimeter' => '|',
      'enclosure' => '"',
      'escape' => '\\',
      'line_callbacks' => array(
        'nix_quotes_around_text' => array(),
      ),
    ),
    dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'States.csv' => array(
      'table_name' => 'us_states',
      'has_header' => TRUE,
      'override_headers' => array(
        'most populous city' => 'biggest_city',
        'square miles' => 'sq_miles',
      ),
      'file_callbacks' => array(
        'remove_utc' => array(),
      ),
      'line_callbacks' => array(
        'lower_case_states' => array(),
        'nix_quotes_around_numbers' => array(),
      ),
    ),
  ),
);

$a = new AttoUtils\CSVtoSQLite\ParserImporter($config);
var_dump($a);
