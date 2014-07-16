# csv-to-sqlite

## Introduction

This package was designed to do one thing: "make CSV files queryable." It began
with a simple enough question from a new PHP programmer "Is there a way to do,
what in SQL would be a LEFT JOIN on the array representing one CSV file to
another array representing a second file?" The easy answer was "No.". That was
on a Friday morning. Before closing, I'd come up with a script for automating
the import of CSV files into an in-memory SQLite DB and returning that database
as a PDO object for querying. This is an outgrowth of that original script.

## Basics

See a working example in [simple.php][example1]

Include the file `src/AttoUtils/CSVtoSQLite/ParserImporter.php` in your script,
if you're not using composer to manage it.

Create a configuration array like so:

    $configuration = array(
      'database' => array(
        'type' => 'file',
        'file_path' => '/full/path/to/database/directory',
        'file_name' => 'database_name.sqlite3',
      ),
      'files' => array(
        '/full/path/to/your/file.csv' => array(),
      ),
    );

Note that, even though it's empty, each file name must point to an array.

Pass the array to a new instance of ParserImporter:

    $parsed_and_imported = new ParserImporter($config);

Grab your PDO connection:

    $database = $parsed_and_imported->sqlite->database;

Query it 'til you drop!

## Quirks

1. At the moment, in order for non-text comparisons to work, you should 
use SQLite's CAST() function. All data is stored as TEXT until 
[this issue][issue4] is resolved. Use the [CAST expressions][cast] to do proper 
evaluations in your SQL statements:

    $sql = 'SELECT * FROM cars_csv WHERE CAST(zero_to_sixty AS INTEGER) < 9';
    $result = $db->query($sql);

2. Something else.

[example1]:./examples/simple.php
[issue4]:https://github.com/jedgell/csv-to-sqlite/issues/4
[cast]:http://www.sqlite.org/lang_expr.html#castexpr
