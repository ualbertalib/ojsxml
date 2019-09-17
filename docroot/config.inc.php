<?php


/**
 * Define the filename of the SQLite database
 */
define("SQLITE_LOCATION" ,'mysqlitedb.db');

// Used to support MySQL however these database tended to be small so SQLite is all that is needed.
$DB_TYPE="SQLite";

$ISSUES_PER_FILE = 50;

// The place where the PDF files are located
$PDF_URL = "http://www.istl.org/";

$TEMP_TABLE_NAME = 'ojs_import_helper';
