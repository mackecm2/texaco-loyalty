<?php
	#	This script can take a while to process
	set_time_limit(0);


//	include "ReadOnlyAccount.php";
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";


	connectToDB( ReplicationServer, TexacoDB );

#	$sql = "select Date_Format(CreationDate, '%Y %m') as Month, CreatedBy, count(*) as Total from Transactions where CreationDate > '2004-10-22' group by Date_Format(CreationDate, '%Y %m'), CreatedBy";
	$sql = "select Date_Format(CreationDate, '%Y %m') as Month, CreatedBy, count(*) as Total from Transactions where CreationDate > '2006-08-01' group by Date_Format(CreationDate, '%Y %m'), CreatedBy";

	$results = DBQueryExitOnFailure( $sql );

	DisplayTable( $results, 0 );

	
?>
