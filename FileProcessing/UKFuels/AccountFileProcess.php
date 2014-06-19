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
	include "../../DBInterface/CardInterface.php";
	include "../../DBInterface/TrackingInterface.php";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";

	$filePath =  LocationUKFuelsDirectory . "Test/";
	$fileMove =  LocationFileProcessing . "Processed/UKFuels/";
	$filePattern = "CentralAccounts*.csv";
	$fileToProcess = "";

//	$fileToProcess = "C:\\projects\\Texaco\\SampleData\\AllAccounts1.csv";
	$ProcessName   = "UKFUELS";
	$fullLoad = true;

	function GetAccountCardMemberNo( $GAccountNo )
	{
		$sql = "Select MemberNo from AccountCards join Cards using (CardNo) where GAccountNo = $GAccountNo";

		$results = DBQueryLogOnFailure( $sql );							
		
		if( $results )
		{
			$row = mysql_fetch_assoc( $results );
			if( $row )
			{
				return $row["MemberNo"];
			}
		}
		return false;
	}

	function MoveAccountCard( $GAccountNo, $cardNumber )
	{
		$sql = "Update AccountCards set CardNo = '$cardNumber' where GAccountNo = $GAccountNo";
		$results = DBQueryLogOnFailure( $sql );							

	}

	function AddAccountCard( $GAccountNo, $cardNo )
	{
		$sql = "Insert into AccountCards( GAccountNo, CardNo, CreationDate, CreatedBy ) values ( $GAccountNo, '$cardNo', now(), 'UKFuels' )";
		$results = DBQueryLogOnFailure( $sql );							
	}

	$FileList = glob( $filePath . $filePattern );

	if( !$FileList )
	{
		echo "No files to process";
	}
	else
	{
		foreach ($FileList as $fileToProcess )
		{

			$fr = fopen( $fileToProcess, "r");

			if(!$fr) 
			{
				echo "Error! Couldn't open the file.";
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
	#				$duplicates = 0;
	#				$reenable = 0;
					$existing = 0;
					$relinked = 0;
	#				$deletedRecords = 0;
					$badLinks = 0;

					echo $fr;
					$line = fgetcsv( $fr, 2048 ); // Skip first line.

					while( $line = fgetcsv( $fr, 2048))
					{
						$lineNo++;
						$GAccountNo = $line[0];
						$CardNo = $line[1];
						$memberU = GetAccountCardMemberNo( $GAccountNo );
						$memberG = GetCardMemberNo( $CardNo );

						if( $memberU && $memberU != "" )
						{
							// UK fules card exists
							if( $memberG && $memberG != "")
							{
								if( $memberU != $memberG )
								{
									// Moving the link to a new member
									MoveAccountCard( $GAccountNo, $CardNo );
									$relinked++;
								}
								else
								{
									// Everything the same
									$existing++;
								}
							}
							else
							{
								// GLobal card does not exist so ignore
								// Bad Link
								$badLinks++;
								logError( "Failed to link Non exitent Global card $GAccountNo, $CardNo" );
							}
						}
						else
						{
							// UK fules account card record  not exists
							if( $memberG && $memberG != "")
							{
								// Global card exists so create a account card record
								AddAccountCard( $GAccountNo, $CardNo);
								$NewRecord++;
							}
							else
							{
								$badLinks++;
								logError( "Failed to link Non exitent Global card $GAccountNo, $CardNo" );
							}
						}
					}

					UpdateFileProcessRecord( $fileRec );

					echo "NewRecord =      $NewRecord\n";
	#				echo "duplicates =     $duplicates\n";
	#				echo "reenable =       $reenable\n";
					echo "existing =       $existing\n";
					echo "relinked =       $relinked\n";
	#				echo "deletedRecords = $deletedRecords\n";
					echo "badLinks =       $badLinks\n";
				
				}
			}
			fclose($fr);
			rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
		}
	}


?>