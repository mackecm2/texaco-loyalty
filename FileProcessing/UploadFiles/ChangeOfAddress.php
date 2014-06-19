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

		$sql = "select Members.MemberNo, AccountNo, Address1, Postcode from Cards join Members using(MemberNo) where CardNo = '$cardNo'";

		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_assoc( $results );

		$MemberNo = $row["MemberNo" ];
		$AccountNo = $row["AccountNo" ];

		$Address = "";
		if( $line[3] == "" )
		{
			$Address .= "Address1 = null,";
		}
		else
		{
			$Address .= "Address1 = '". mysql_escape_string( $line[3] ). "', ";
		}

		if( $line[4] == "" )
		{
			$Address .= "Address2 = null,";
		}
		else
		{
			$Address .= "Address2 = '". mysql_escape_string( $line[4] ). "', ";
		}

		if( $line[5] == "" )
		{
			$Address .= "Address3 = null,";
		}
		else
		{
			$Address .= "Address3 = '". mysql_escape_string( $line[5] ). "', ";
		}

		if( $line[6] == "" )
		{
			$Address .= "Address4 = null,";
		}
		else
		{
			$Address .= "Address4 = '". mysql_escape_string( $line[6] ). "', ";
		}

		if( $line[7] == "" )
		{
			$Address .= "Address5 = null,";
		}
		else
		{
			$Address .= "Address5 = '". mysql_escape_string( $line[7] ). "', ";
		}

		if( $line[9] == "" )
		{
			$Address .= "Postcode = null,";
		}
		else
		{
			$Address .= "Postcode = '$line[9]', ";
		}


			$sql = "Update Members set $Address GoneAway = 'N' where MemberNo = $MemberNo";
			DBQueryExitOnFailure( $sql );
			if( mysql_affected_rows() > 0 )
			{
				echo "Data changed\n";
				InsertTrackingRecord( 1, $MemberNo, $AccountNo, "", 0 );
			}
			else
			{
				echo "No rows affected\n";
			}
	}

	function BatchProcessFile($fileToProcess)
	{
		global $lineNo, $ProcessName;
		
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

	BatchProcessFile( "/data/temp/NCOA Texaco May Statement.txt"); 

  ?>