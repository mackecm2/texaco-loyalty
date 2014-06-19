<?php

	$errorCount = 0;

	function DBGetSQLDate()
	{
		$sql = "select date_format(now(), '%Y%m%d')";
		$results = DBQueryExitOnFailure( $sql );
		
		$row = mysql_fetch_row( $results );

		return $row[0];
	}

	function GetBatchFilename( $timestamp )
	{
		$sql = "Select date_format(\"$timestamp\", \"%Y_%m_%d %H_%i\")";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		return $row[0];
	}


?>