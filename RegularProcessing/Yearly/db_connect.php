<?php

// Make a connection to the database - this is always needed as we always check if a user is logged in
// for every request they make. IMPORTANT NOTE: The $db_user, $db_pass, $db_host and $db_name variables
// are referenced for all db i/o including the protex scripts (see includes/init-dbconnect.php among others).

//$db_host = "weoudb";
//$db_name = 'texaco';

	$db_host = "localhost";
	$db_name = "texaco";

//	$db_user = "HomeSiteProcess";
//	$db_pass = "non-secure";


$db_user = "ReportGenerator";
$db_pass = "tldttoths";

$con= @mysql_connect("$db_host","$db_user","$db_pass")
or die("Cannot connect to MySQL.". $db_host.",".$db_user.",".$db_pass);

$db = @mysql_select_db("$db_name",$con)
or die("Cannot select the $db_name database. Please check your details in the database connection file and try again");

// Includes available to every script that includes this one
include_once("inc-mysql.php");




?>

