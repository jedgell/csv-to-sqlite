<?php

// That include_once, tho! Insane! Yes, probably could've just typed
// include_once '../../../src/AttoUtils/CSVtoSQLite/ParserImporter.php';
// but then, where's the fun in that? That doesn't bake your cpu!
// Actually, this helps ensure that the script works on Linux/Unix/OSX/Windows,
// but if you know that you'll only ever be running in one environment, feel
// free to hardcode it.
include_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'AttoUtils' . DIRECTORY_SEPARATOR . 'CSVtoSQLite' . DIRECTORY_SEPARATOR . 'ParserImporter.php';


$configuration = array(
  'database' => array(
    'type' => 'file',
    // For file_path, we could simply use './data/processed'. See above.
    'file_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'processed',
    'file_name' => 'example.sqlite3',
  ),
  'files' => array(
    dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'Cars.csv' => array(),
  ),
);

$parsed_csv = new AttoUtils\CSVtoSQLite\ParserImporter($configuration);
// Yep. That's pretty much it. If your CSV doesn't require much processing, all
// you need is a simple configuration array and one call and you're done!
// Uncomment the next line to get a better idea of what's returned.
// var_dump($parsed_csv);
// Now we have a PDO object, let's put it to use:
$db = $parsed_csv->sqlite->database;
// All data is stored as TEXT until https://github.com/jedgell/csv-to-sqlite/issues/4
// is resolved. Use CAST expressions to do proper evaluations. More info:
// http://www.sqlite.org/lang_expr.html#castexpr
// Anyway, let's find all the cars on the list that can do 0-60 in under 9s.
$result = $db->query('SELECT * FROM cars_csv WHERE CAST(zero_to_sixty AS INTEGER) < 9');
// Now, list 'em out.
foreach ($result as $row) {
  echo "ID: " . $row['id'] . "\n";
  echo "Make: " . $row['name'] . "\n";
  echo "Model Year: " . $row['year'] . "\n";
  echo "Zero to Sixty in: " . $row['zero_to_sixty'] . " seconds\n";
  echo "\n";
}
// Oh. BTW: You got an index on each row for free. How awesome am I?
// You should see something like this:
//  Id: 2
//  Make: AMC Ambassador DPL
//  Model Year: 70
//  Zero to Sixty in: 8.5 seconds
//
//  Id: 207
//  Make: Ford Mustang Boss 302
//  Model Year: 70
//  Zero to Sixty in: 8 seconds
//
//  Id: 290
//  Make: Plymouth Barracuda 340
//  Model Year: 70
//  Zero to Sixty in: 8 seconds
//
//  Id: 299
//  Make: Plymouth Fury III
//  Model Year: 70
//  Zero to Sixty in: 8.5 seconds
