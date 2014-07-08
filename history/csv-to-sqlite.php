
<?php

// Example usage

$files = array(
  'table_1' => '/path/to/file_1.csv',
  'table_2' => '/path/to/file_2.csv',
);

foreach ($files as $table => $file) {
  $line_items = csv_file_to_arrayed_data($file);
  import_csv_data_to_sql_memory_table($line_items, $table, TRUE);
}

// ************************************************************************** //

function csv_file_to_arrayed_data($csv_path) {
  $file_contents = file_get_contents($csv_path);
  return explode(PHP_EOL, $file_contents);
}

function import_csv_data_to_sql_memory_table($csv_line_items, $table_name, $verify = FALSE) {
  $data = array();
  $headers = array();
  clean_and_parse_data($csv_line_items, $data, $headers);
  $db = get_database_connection();
  try {
    do_table_create($db, $headers, $table_name);
    do_table_insert($db, $headers, create_placeholders($headers), $data, $table_name);
    if ($verify) {
      do_data_count_validation($db, $table_name, $csv_line_items);
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function clean_and_parse_data(&$csv_line_items, &$return_array, &$return_headers) {
  foreach ($csv_line_items as $line_item) {
    $return_array[] = str_getcsv($line_item);
  }
  // Remove file description
  unset($return_array[0]);
  // Clean and format header
  array_walk($return_array[1], 'header_alter');
  $return_headers = $return_array[1];
  // Remove header
  unset($return_array[1]);
  // Reset array indices & clean up memory
  $return_array = array_values($return_array);
  $csv_line_items = array();
  unset($csv_line_items);
}

function header_alter(&$val, $key) {
  $replace = array(
    ' ',
    '/',
    '-',
  );
  $remove = array(
    ' @',
    ' #',
    '.',
  );
  $val = strtolower(str_replace($replace, '_', str_replace($remove, '', $val)));
}

function create_placeholders($headers) {
  foreach ($headers as $header) {
    $placeholders[] = ":" . $header;
  }
  return $placeholders;
}

function do_data_count_validation($database, $table_name, $csv_line_items) {
  echo $table_name . " csv rows (expected): " . count($csv_line_items) . "\r\n";
  $result = $database->query("SELECT COUNT(*) AS count FROM " . $table_name)->fetchColumn();
  echo $table_name . " database rows (actual): " . $result . "\r\n";
}

function get_database_connection() {
  $database = new PDO('sqlite::memory:');
  $database->setAttribute(PDO::ATTR_PERSISTENT, TRUE);
  $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $database;
}

function do_table_create($database, $headers, $type) {
  $statements = array();
  $statements[] = "DROP TABLE IF EXISTS " . $type;
  $statements[] = "CREATE TABLE " . $type . "(
    id INTEGER PRIMARY KEY,
    " . implode(" TEXT,\r\n", $headers) . " TEXT)";
  foreach ($statements as $statement) {
    $database->exec($statement);
  }
}

function do_table_insert($database, $headers, $placeholders, $data, $type) {
  $sql = "INSERT INTO " . $type . " (" . implode(', ', $headers) . ") VALUES (" . implode(', ', $placeholders) . ")";
  $statement = $database->prepare($sql);

  // Bind values to placeholders
  foreach ($data as $values) {
    $i = 0;
    foreach($values as $value) {
      $statement->bindParam($placeholders[$i], $value);
      $i++;
    }
    // Execute the statement
    $statement->execute();
  }
}





