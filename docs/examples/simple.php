<?php

// That include_once, tho! Insane! Yes, probably could've just typed
// include_once '../../../src/AttoUtils/CSVtoSQLite/ParserImporter.php';
// but then, where's the fun in that? That doesn't bake your cpu!
include_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'AttoUtils' . DIRECTORY_SEPARATOR . 'CSVtoSQLite' . DIRECTORY_SEPARATOR . 'ParserImporter.php';


$config = array(
  'database' => array(
    'type' => 'file',
    'file_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'processed',
    'file_name' => 'example.sqlite3',
  ),
  'files' => array(
    dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'Cars.csv' => array(),
  ),
);

$a = new AttoUtils\CSVtoSQLite\ParserImporter($config);
var_dump($a);
