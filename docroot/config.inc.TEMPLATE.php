<?php
// Define configuration
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "ojs2");


/**
 * Can be SQLITE or MYSQL
 */
$DB_TYPE = 'SQLITE';
/**
 * IF SQLITE is used this must be defined;
 */
define("SQLITE_LOCATION" ,'mysqlitedb.db');

$PDF_URL = "http://ejournals.library.ualberta.ca/custom/pdfs/EnergyLawEditions1999-2005Files/";

$TEMP_TABLE_NAME = 'ojs_import_helper';
