<?php 
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	$sql = $_GET["SQL"];

	echo $sql;

	$Results = DBQueryExitOnFailure( $sql );

	DisplayTable( $Results, 0 );

?>