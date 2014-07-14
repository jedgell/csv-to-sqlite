csv-to-sqlite
=============

WARNING
-------
This package is still in an __UNUSABLE__ state. Come back afer while. K? Thx.


This package aims to give users a simple language for describing a CSV file and letting the classes parse the CSV file into a SQLite database.

Use case:

Your boss emails you two CSV files from your antiquated XYZ system and says to build a third that merges data from files 1 and 2 keyed on column 4 of file 1 and column 1 of file 2.

Yes, I know you could just bring up your MySQL client, build a couple database tables that reflect the data for each and import the data and run a quick query. Or, heaven forfend, open the CSVs in Excel and write a couple macros and save the output in a third file.

Or you could have fun.
