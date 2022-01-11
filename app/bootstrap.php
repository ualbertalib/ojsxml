<?php

use OJSXml\Config;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('classes/Database.php');
require('classes/MySQL.class.php');
require('classes/SQLiteDB.php');
require('classes/DatabaseFactory.php');

require('classes/Authors.php');
require('classes/TempTable.php');

require ('classes/XMLBuilder.php');
require('classes/IssuesXmlBuilder.php');
require('classes/UsersXmlBuilder.php');
require('classes/DBManager.php');

require('helpers/helpers.php');
require ('classes/Config.php');

require('classes/Logger.php');

Config::load("config.ini");
