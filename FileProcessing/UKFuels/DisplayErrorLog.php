	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**  Processing File
	**
	********************************************************************/
	


	//******************************************************************
	//
	// DisplayErrorLog.php
	// Reads the NewErrorLog table on the texaco database and reports on the last week's worth of messages
	//
	//******************************************************************

	$db_user = "UKFuelsProcess";
	$db_pass = "UKPassword";

	include "../../include/DB.inc";

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	
	// Main function

	connectToDB( MasterServer, TexacoDB );
	
	echo "+------------------+--------+---------+----------+---------------------+-------------+---------------------------------------------+\r\n";
	echo "| File             | LineNo | ErrorNo | Severity | CreationDate        | CreatedBy   | ErrorString                                 |\r\n";
	echo "+------------------+--------+---------+----------+---------------------+-------------+---------------------------------------------+\r\n";
	$sql = "SELECT * FROM `NewErrorLog` WHERE Creationdate > DATE_ADD(NOW(), INTERVAL -7 DAY)";
	$results = DBQueryExitOnFailure( $sql );
	while( $row = mysql_fetch_array( $results ) )
	{
		if ($row["ErrorString"]!="Card Already assigned")
		{
			echo ("| ".str_pad($row["File"],16));
			echo (" | ".str_pad($row["LineNo"],6));
			echo (" | ".str_pad($row["ErrorNo"],7));
			echo (" | ".str_pad($row["Severity"],8));
			echo (" | ".$row["CreationDate"]);
			echo (" | ".str_pad($row["CreatedBy"],11));
			echo (" | ".str_pad($row["ErrorString"],43)." |\r\n" );
		}
	}
	echo "+------------------+--------+---------+----------+---------------------+-------------+---------------------------------------------+\r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";


?>
