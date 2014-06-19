<?php 
//	include "ReadOnlyAccount.php";

	$db_user = "root";
	$db_pass = "trave1";

	include "../include/DB.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	connectToDB( MasterServer, TexacoDB );

	$sql = "Show full Processlist";
 
	$Results = DBQueryExitOnFailure( $sql );

	DisplayTable( $Results, 0 );
?>