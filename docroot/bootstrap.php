<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('config.inc.php');
require('../app/classes/Database.php');
require('../app/classes/MySQL.class.php');
require('../app/classes/SQLiteDB.php');
require('../app/classes/DatabaseFactory.php');

require('../app/classes/Authors.php');
require('../app/classes/TempTable.php');

use OJSXml\DatabaseFactory;
require('../app/helpers/helpers.php');
$db_f = new DatabaseFactory();
$db = $db_f->makeDB($DB_TYPE, SQLITE_LOCATION);

