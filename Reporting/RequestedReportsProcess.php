<?php
	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "ReadOnly";
	$db_pass = "ORANGE";


	require "../FileProcessing/General/misc.php";
	require "../include/Locations.php";
	connectToDB();

	$sql = "Select * from ReportsToRun where Status	= 'P'";

	$results = DBQueryExitOnFailure( $sql );

	if( mysql_num_rows( $results ) > 0 )
	{
		
		while( $row = mysql_fetch_assoc( $results ) )
		{
			$FileRoot = $row["Description"].$row["ID"].".txt";
			$ID = $row["ID"];

			$sql = "Update ReportsToRun set Started = now(), Status = 'R'  where ID = $ID";
			$success = DBQueryExitOnFailure( $sql );
			
			$sql = str_replace( '%f', LocationReportsDirectory.$FileRoot, $row["SQLSTR"] ); 

			$success = DBQueryExitOnFailure( $sql );

			if( $success )
			{
				$sql = "Update ReportsToRun set Finished = now(), Status = 'S', ResultsFile = '$FileRoot' where ID = $ID";
				$success = DBQueryExitOnFailure( $sql );
			}
			else
			{
				echo mysql_error();
				LogError( "Failed to run $sql".mysql_error() );
				$sql = "Update ReportsToRun set Finished = now(), Status = 'F' where ID = $ID";
				$success = DBQueryExitOnFailure( $sql );
			}
		}
	}


?>