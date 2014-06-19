	<?php


	//******************************************************************
	//
	// CardRequestFileProcess.php
	//
	//  
	//
	//******************************************************************


	function GoneAwayProcessLine( $fields )
	{
		global $cardNumberCol;
		global $AlreadyGone, $Updated, $ErrorCount;
		$cardNumber = $fields[$cardNumberCol];

		// Find member No

		$memberNo = GetCardMemberNo( $cardNumber );

		if( $memberNo and $memberNo != 0 )
		{
			// Mark member as gone away.
			
			$sql = "Update Members set GoneAway = 'Y' where MemberNo = $memberNo";
			//echo "<br>$sql\n";
			DBQueryExitOnFailure( $sql );

			if( mysql_affected_rows() == 0 )
			{
				echo "<br>$cardNumber already marked as gone away\n";
				$AlreadyGone++;
			}
			else
			{
				$Updated++;
				$accountNo = GetAccountNo( $memberNo );
			// Add comment to tracking history.

				InsertTrackingRecord( TrackingGoneAway, $memberNo, $accountNo, "Jan 2005 Statement mail Returned in post", 0 );
			}
		}
		else
		{
			DBLogError( "Failed to find member for card no $cardNumber" );
			$ErrorCount++;
		}
	}

	function GoneAwayProcessHeaderLine( $fields )
	{
		global $cardNumberCol;
		$cardNumberCol = 2;
		$batchCol   = 0;
		return true;
	}


	function GoneAwayProcessFiles($fileToProcess)
	{
		global $lineNo, $ProcessName;											
		global $AlreadyGone, $Updated, $ErrorCount;
		$AlreadyGone = 0;
		$Updated = 0;
		$ErrorCount = 0;
				
		$ProcessName = "CardRequest";
		$fileMove =  LocationFileProcessing."Processed/GoneAways/";
			//connectToDB( MasterServer, TexacoDB );
			echo "<HTML><HEAD></HEAD><BODY>";
			echo "<BR>*****************************************************\n";
			echo "<BR> Processing Gone Aways file.\n";
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
					if( GoneAwayProcessHeaderLine( $line ) )
					{
						$success = false;			
						$lineNo = 1;

						while( $line = fgetcsv( $fr, 2048 ))
						{
							$lineNo++;
							GoneAwayProcessLine( $line );
						}

					}
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
				echo "<br>Error count = $ErrorCount\n";
				echo "<br>Aready Gone = $AlreadyGone\n";
				echo "<br>Updated     = $Updated\n";

				echo "</BODY></HTML>";
			}
	}

	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

	?>