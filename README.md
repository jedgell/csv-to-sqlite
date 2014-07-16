csv-to-sqlite
=============

Make CSV files useful!

## WARNING

This package is still in an __UNDOCUMENTED__ and __UNTESTED__ state. The package
is usable (although as of 7/14/2014 not very well tested), but is lacking any 
real documentation. Please refer to the examples in the doc directory.

## About:

This package aims to give users a simple language for describing a CSV file and 
letting the PHP classes parse the CSV file into a SQLite database. The worker 
class, once instantiated with a proper configuration, parses CSV file(s) into a 
SQLite database as individual tables and provides access to a PDO object for 
querying and further manipulation.

Extended configuration options give you the ability to: 

* define per-file callbacks (which receive the full file contents *_may change_) 
and 
* per-file-line callbacks (which receive individual rows of data as arrays)
* rename column headers (which are used to name columns in the table)
* define per-file table name
* define the database file's name & location

to manipulate the data before it is dumped into the 
database. After file manipulation, copies of the files are stored in a 
subdirectory of the directory in which the original file resides showing all 
manipulation. Future versions of this script may have a configuration option 
to not store the post-processing file, but right now, for performance purposes, 
that's where it's going.

Other options allow you to  and to define the table's name. Column names,
regardless of whether they're overridden or not, are normalized to remove any 
non-alpha-numeric characters (which are replaced with underscores). A header of 
`2nd set of pix @ beach - New Hope` would become column 
`2nd_set_of_pix___beach_new_hope`. Perhaps you want to use that override headers
mapping, huh? With no table name defined, a file named 
`my-file-full-of-data.csv` becomes table `my_file_full_of_data_csv`.

Configuration and use options are (as thoughtfully as I can) explored in the
[INSTRUCTIONS.md][instructions] file.

## Use case:

Your boss emails you two CSV files from your antiquated XYZ system and says to 
build a third that merges data from files 1 and 2 keyed on column 4 of file 1 
and column 1 of file 2.

Yes, I know you could just bring up your MySQL client, build a couple database 
tables that reflect the data for each and import the data and run a quick query.
Or, heaven forfend, open the CSVs in Excel and write a couple macros and save 
the output in a third file.

Or you could have fun.

[instructions]:docs/examples/INSTRUCTIONS.md