<?php
// Define configuration

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "adminuser");
define("DB_NAME", "ojs2");

/**
 * Can be SQLITE or MYSQL
 */
$DB_TYPE = 'SQLITE';

/**
 * IF SQLITE is used this must be defined;
 */
define("SQLITE_LOCATION" ,'mysqlitedb.db');

$ISSUES_PER_FILE = 50;

$PDF_URL = "http://journals.library.ualberta.ca/custom/pdfs/";

$TEMP_TABLE_NAME = 'ojs_import_helper';
