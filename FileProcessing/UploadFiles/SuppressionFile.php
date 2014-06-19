<?php

	include "../../include/DB.inc";
	require "../../DBInterface/FileProcessRecord.php";
	include "../../DBInterface/TrackingInterface.php";
	$db_user = "root";
	$db_pass = "trave1";																		   

	function SupProcessHeaderLine( $line )
	{
		return true;
	}

	function SupProcessLine( $line )
	{
		$cardNo = $line[1];

		$sql = "select Members.MemberNo, AccountNo, Deceased, GoneAway, Address1, Postcode from Cards join Members using(MemberNo) where CardNo = '$cardNo'";

		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_assoc( $results );

		$MemberNo = $row["MemberNo" ];
		$AccountNo = $row["AccountNo" ];

		if( $row["Postcode"] != $line[8] )
		{
			echo "$cardNo Miss match postcode". $row["Postcode"] ." != ".$line[8] ."\n";
		}

		if( $line[9] == "G" )
		{
			if( $row["GoneAway"] != 'N' )
			{
				echo "Already Marked as Gone Away\n";
			}
			else
			{
				$sql = "Update Members set GoneAway = 'Y' where MemberNo = $MemberNo";
				DBQueryExitOnFailure( $sql );
				InsertTrackingRecord( 1141, $MemberNo, $AccountNo, "", 0 );
			}
		}
		else if( $line[9] == "D" )
		{
			if( $row["GoneAway"] != 'N' )
			{
				echo "$cardNoAlready Marked as Dead\n";
			}
			else
			{
				$sql = "Update Members set Deceased = 'Y' where MemberNo = $MemberNo";
				DBQueryExitOnFailure( $sql );
				InsertTrackingRecord( 6, $MemberNo, $AccountNo, "", 0 );
			}
		}
		else
		{
			echo "$cardNo Bad Line\n";
		}
	}

	function BatchProcessFile($fileToProcess)
	{
		global $lineNo, $ProcessName;
		global $ErrorCount;
		$ErrorCount = 0;
		$ProcessName = "SuppFile";
		$fileMove =  LocationFileProcessing."Processed/";
		
		//connectToDB( MasterServer, TexacoDB );
		echo "<HTML><HEAD></HEAD><BODY>";
		echo "<BR>*****************************************************\n";
		echo "<BR> Cards Supplied file\n";
		echo "<BR>$fileToProcess\n";
		echo "<BR>*****************************************************\n";

		$fr = fopen( $fileToProcess, "r");

		if(!$fr) 
		{
			echo "<BR>Error! Couldn't open the file.";
		} 
		else 
		{

			$fileRec = createFileProcessRecord($fileToProcess);
			if( $fileRec )
			{
				// $fr now can be used to represent the opened file
				$line = fgetcsv( $fr, 2048);
				if( SupProcessHeaderLine( $line ) )
				{
					$success = false;			
					$lineNo = 1;

					while( $line = fgetcsv( $fr, 2048 ))
					{
						$lineNo++;
						SupProcessLine( $line );
					}

				}
				UpdateFileProcessRecord( $fileRec );
			}
			fclose($fr);
			rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
			echo "</BODY></HTML>";
		}
	}

	connectToDB( MasterServer, TexacoDB );

	BatchProcessFile( "/data/temp/Supps Texaco May Statement.txt"); 

  ?>