<?php
require_once('../config.php');
require_once('function.php');
 
// connecting to mysql
$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
// selecting database
if(!mysql_select_db(DB_NAME))
  print "Not connected with database.";
?>