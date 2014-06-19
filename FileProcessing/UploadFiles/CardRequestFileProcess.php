	<?php


	//******************************************************************
	//
	// CardRequestFileProcess.php
	//
	//  
	//
	//******************************************************************


	function RequestProcessLine( $fields )
	{
		global $cardNumberCol, $requestNoCol, $requestTypeCol;
		//$fields = explode( ",", $line );
		// Check if the transaction already exists

		$cardNumber = $fields[$cardNumberCol];
		$requestNo  = $fields[$requestNoCol];
		
		$requestType = $fields[$requestTypeCol];

		$memberNo = checkCardRequestNumber( $requestNo );
		if( $memberNo )
		{
			$accountNo = GetAccountNo( $memberNo );
			MergeCardToMember( $cardNumber, $memberNo, false );
			SatisfyRequest( $requestNo );
			if( $requestType == RequestReplacementCard )
			{
				CheckMemberHasPrimary( $cardNumber, $memberNo );
			}
			if( $requestType ==  RequestGroupTreasure or $requestType == RequestGroupSecretairy or $requestType == RequestGroupMember )
			{
				CreatePersonalBonus( $memberNo, "GrpBonus", "CURRENT_DATE()", "12 * 7 day" );
			}
			echo "<BR>Added $cardNumber to Member $memberNo";
			InsertTrackingRecord( TrackingNewCardAdded,  $memberNo, $accountNo, "", 0);
		}
		else
		{
			// error logged in checking function
	//		DBLogError( "Failed to find member for request no $requestNo, CardNo $cardNumber" );
		}
	}

	function RequestProcessHeaderLine( $fields )
	{
		global $cardNumberCol, $requestNoCol, $requestTypeCol;
//		$fields = explode( ",", $line );

		$fieldHeaders = array();
		foreach( $fields as $key => $header )
		{
			$fieldHeaders[$header] = $key;
		}

		if( !isset( $fieldHeaders["CardNo"] ) ) 
		{
			DBLogError( "Missing column name CardNo" );

			return false;
		}

		if( !isset( $fieldHeaders["RefNo"] ))
		{
			DBLogError( "Missing column name CardNo" );

			return false;
		}
	
		$cardNumberCol = $fieldHeaders["CardNo"];
		$requestNoCol  = $fieldHeaders["RefNo"];
		$requestTypeCol = $fieldHeaders["RequestType"];
		return true;
	}


	function RequestProcessFiles($fileToProcess)
	{

		global $lineNo, $ProcessName;
		
		$ProcessName = "CardRequest";
		$fileMove =  LocationFileProcessing."Processed/CardRequestFile/";
		
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
					if( RequestProcessHeaderLine( $line ) )
					{
						$success = false;			
						$lineNo = 1;

						while( $line = fgetcsv( $fr, 2048 ))
						{
							$lineNo++;
							RequestProcessLine( $line );
						}

					}
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
				echo "</BODY></HTML>";
			}
	}

	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

	?>