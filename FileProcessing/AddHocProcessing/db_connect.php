<?php


// Make a connection to the database - this is always needed as we always check if a user is logged in
// for every request they make. IMPORTANT NOTE: The $db_user, $db_pass, $db_host and $db_name variables
// are referenced for all db i/o including the protex scripts (see includes/init-dbconnect.php among others).
$db_user = 'pma001';
$db_pass = 'amping';
$db_host = 'localhost';
$db_name = 'texaco';

$con= @mysql_connect("$db_host","$db_user","$db_pass")
or mysqlError("Cannot connect to MySQL.");

$db = @mysql_select_db("$db_name",$con)
or mysqlError("Cannot select the $db_name database. Please check your details in the database connection file and try again");

// Includes available to every script that includes this one
include_once("inc-mysql.php");
include_once("debug.php");

// FQSELF is our the fully qualified address of PHP_SELF meaning that it includes the http host address.
// Its a single location, a global variable, and it allows us to easily change the http protocol from
// development to production (ie change http:// to https:// from insecure to secure SSL).  I have written
// it here since db_connect is called by every other script and therefore makes the variable globally
// available.
$_SERVER['FQSELF']="https://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";
$_SERVER['LOGLEVEL'] = 3;
?>