<?php

// That include_once, tho! Insane! Yes, probably could've just typed
// include_once '../../../src/AttoUtils/CSVtoSQLite/Controller.php';
// but then, where's the fun in that? That doesn't bake your cpu!
include_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'AttoUtils' . DIRECTORY_SEPARATOR . 'CSVtoSQLite' . DIRECTORY_SEPARATOR . 'Controller.php';

function lower_case_states($line) {
  array_walk($line, function(&$item, $key) {trim($item);});
  ucwords(strtolower($line[0]));
  return $line;
}

function remove_utc($file) {
  return str_replace('UTC', '', $file);
}

$config =  array(
    'database' => array(
      'type' => 'file',
      'file_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'processed',
      'file_name' => 'example.sqlite3',
    ),
    'files' => array (
      // dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'States.csv' => array(),
      dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'Countries.csv' => array(
        'delimeter' => '|',
        'enclosure' => '"',
        'escape' =>'\\',
      ),
      dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'States.csv' => array(
        'table_name' => 'us_states',
        'has_header' => TRUE,
        'override_headers' => array(
          'most populous city' => 'biggest_city',
          'square miles' => 'sq_miles',
        ),
        'file_callbacks' => array(
          'remove_utc' => array(
          ),
        ),
        'line_callbacks' => array(
          'lower_case_states' => array(
          ),
        ),
      ),
    ),
  );

$a = new AttoUtils\CSVtoSQLite\Controller($config);
//var_dump($a);
