	********************************************************************
	**
	**  Processing File
	**
	********************************************************************
	
	<?php

	//******************************************************************
	//
	// AccountFileProcess.php
	// Reads in an account file from ukfules and inserts the data into the database
	//
	//
	//******************************************************************

	include "../General/misc.php";
	include "../../include/Locations.php";
	include "../../DBInterface/FileProcessRecord.php";

	$db_host = "localhost";
	$db_name = "Texaco";
	$db_user = "UKFuelsProcess";
	$db_pass = "UKPassword";


	$filePath =  LocationFileProcessing . "/ToProcess/UKFuels/";
	$fileMove =  LocationFileProcessing . "/Processed/UKFuels/";
	$filePattern = "uk_sites.txt";
	$fileToProcess = "";

	$ProcessName   = "UKFUELS";

	$filestoprocess = glob( $filePath . $filePattern );

	if( !$filestoprocess )
	{

		echo "No files to process\n";
	}
	else
	{
		foreach ( $filestoprocess as $fileToProcess)
		{

			$fr = fopen( $fileToProcess, "r");

			if(!$fr) 
			{
				echo "Error! Couldn't open the file '$fileToProcess'.";
			} 
			else 
			{
				connectToDB();

				$fileRec = createFileProcessRecord($fileToProcess);
				if( $fileRec )
				{
					$success = false;			
					$lineNo = 1;
					$prev = "";

					$NewRecord = 0;
					$existing = 0;
					$relinked = 0;
					$badLinks = 0;

					while( $line = fgets( $fr ))
					{
						$lineNo++;
						$texnum = substr( $line, 0, 6 );
						$uknum  = substr( $line, 6, 6 );

						$sql = "Select UKFuelsCode from Sites where SiteCode = $texnum";
						$results = DBQueryExitOnFailure( $sql );

						if( $results )
						{
							if( mysql_num_rows( $results ) != 0 )
							{
								$row = mysql_fetch_row( $results );

								if( $row[0] == $uknum )
								{
									$existing++;
								}
								else 
								{
									if( $row[0] == "" )
									{
										$NewRecord++;
									}
									else
									{
										$relinked++;
									}
									$sql = "Update Sites set UKFuelsCode = $uknum where SiteCode = $texnum";
									$results = DBQueryExitOnFailure( $sql );
								}
							}
							else
							{
								$badLinks++;
								logError( "Texaco site code does not exist for $texnum ($uknum)");
							}
						}
					}
					echo "NewRecord =      $NewRecord\n";
					echo "existing =       $existing\n";
					echo "relinked =       $relinked\n";
					echo "badLinks =       $badLinks\n";
				
				}
			}
			fclose($fr);
			rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
		}
	}
?>