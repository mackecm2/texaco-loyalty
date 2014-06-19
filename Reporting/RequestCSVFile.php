<?php
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/CSVFile.php";

	$sql = $_GET["SQL"];

	$sql = stripslashes($sql );

	$results = DBQueryExitOnFailure( $sql );

	OutputCSV( "Report.csv", $results );
?>