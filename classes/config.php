<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('DBUSER')) define('DBUSER', "root");
if (!defined('DBPASS')) define('DBPASS', "");
if (!defined('DBNAME')) define('DBNAME', "fk_db");
if (!defined('DBHOST')) define('DBHOST', "localhost");
