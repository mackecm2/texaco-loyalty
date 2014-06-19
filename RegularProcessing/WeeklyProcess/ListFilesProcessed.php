	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**  Processing File
	**
	********************************************************************/
	


	//******************************************************************
	//
	// ListFilesProcessed.php
	// Reads the FilesProcessed table on the texaco database and reports on the last week's worth of files processed
	//
	//******************************************************************

	$db_user = "UKFuelsProcess";
	$db_pass = "UKPassword";

	include "../../include/DB.inc";

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	
	// Main function

	connectToDB( MasterServer, TexacoDB );
	
	

	echo "+------------------+---------------------+---------------------+------------+-----------+------------+------------+------+----------+\r\n";
	echo "| FileName         | StartTime           | EndTime             | ErrorCount | CreatedBy | NewRecords | Duplicates | Bad  | Warnings |\r\n";
	echo "+------------------+---------------------+---------------------+------------+-----------+------------+------------+------+----------+\r\n";
	$sql = "SELECT * FROM FilesProcessed WHERE StartTime > DATE_ADD(NOW(), INTERVAL -7 DAY) AND (CreatedBy = 'COMPOWER' OR CreatedBy = 'FIS' OR CreatedBy = 'UKFUELS')";
	$results = DBQueryExitOnFailure( $sql );
	while( $row = mysql_fetch_array( $results ) )
	{
		echo ("| ".str_pad($row["FileName"],16));
		echo (" | ".str_pad($row["StartTime"],19));
		echo (" | ".str_pad($row["EndTime"],19));
		echo (" | ".str_pad($row["ErrorCount"],10));
		echo (" | ".str_pad($row["CreatedBy"],9));
		echo (" | ".str_pad($row["NewRecords"],10));
		echo (" | ".str_pad($row["Duplicates"],10));
		echo (" | ".str_pad($row["Bad"],4));
		echo (" | ".str_pad($row["Warnings"],8)." |\r\n" );
	}
	echo "+------------------+---------------------+---------------------+------------+-----------+------------+------------+------+----------+\r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";


?>
