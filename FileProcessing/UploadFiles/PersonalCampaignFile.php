	<?php


	//******************************************************************
	//
	// CardRequestFileProcess.php
	//
	//  
	//
	//******************************************************************


	function CampaignProcessLine( $fields )
	{
		global $CardNoCol, $StartDateCol, $EndDateCol, $PromoCodeCol, $PointsCol, $uname;
		//$fields = explode( ",", $line );
		// Check if the transaction already exists

		$CardNo = $fields[$CardNoCol];
		$PromoCode  = $fields[$PromoCodeCol];

		$MemberNo = GetCardMemberNo( $CardNo );

		if( $MemberNo )
		{
			if( $StartDateCol != -1 and $EndDateCol != -1 )
			{
				$StartDate  = $fields[$StartDateCol];
				$EndDate  = $fields[$EndDateCol];
				if( $StartDate != "" and $EndDate != "" )
				{
					$sql = "INSERT into PersonalCampaigns ( MemberNo, PromotionCode, StartDate, EndDate, CreationDate, CreatedBy ) values ( $MemberNo, '$PromoCode', '$StartDate', '$EndDate', now(), '$uname') ";
					DBQueryLogOnFailure( $sql );
				}
			}

			if( $PointsCol != -1 )
			{
				$Points = $fields[$PointsCol];
				
				if( $Points != "" and $Points != 0 )
				{
					$AccountNo = GetAccountNo( $MemberNo );
					AdjustBalance( TrackingBonusFile, $MemberNo, $AccountNo, $PromoCode, $Points );
				}
			}

		}
		else
		{
			DBLogError( "$CardNo Failed to find member for PersonalCampaign " );
		}
	}

	function CampaignProcessHeaderLine( $fields )
	{
		global $CardNoCol, $StartDateCol, $EndDateCol, $PromoCodeCol, $PointsCol;
//		$fields = explode( ",", $line );

		$fieldHeaders = array();
		foreach( $fields as $key => $header )
		{
			$fieldHeaders[$header] = $key;
			echo $header;
		}

		if( !isset( $fieldHeaders["CardNo"] ) ) 
		{
			DBLogError( "Missing column name CardNo" );
			return false;
		}

		if( isset( $fieldHeaders["StartDate"] ))
		{
			$StartDateCol  = $fieldHeaders["StartDate"];
		}
		else
		{
			Echo "<BR>Information: No StartDate column found";
			$StartDateCol = -1;
		}

		if( isset( $fieldHeaders["EndDate"] ))
		{
			$EndDateCol  = $fieldHeaders["EndDate"];
		}
		else
		{
			Echo "<BR>Information: No EndDate column found";
			$EndDateCol = -1;
		}

		if( !isset( $fieldHeaders["PromotionCode"] ))
		{
			DBLogError( "Missing column name PromotionCode" );
			return false;
		}

		if( isset( $fieldHeaders["Points"] ))
		{
			$PointsCol = $fieldHeaders["Points"];
		}
		else
		{	
			Echo "<BR>Information: No Points column found";
			$PointsCol = -1;
		}

		

		$CardNoCol = $fieldHeaders["CardNo"];
		$PromoCodeCol   = $fieldHeaders["PromotionCode"];
		return true;
	}


	function PersonalCampaignFile($fileToProcess)
	{

		global $lineNo, $ProcessName, $Batch, $uname;
		
		$ProcessName = "PersonalCamp";

		$uname = substr( basename($fileToProcess), 0, 20 );

		$fileMove =  LocationFileProcessing."Processed/PersonalCampaign/";
		
			//connectToDB( MasterServer, TexacoDB );
			echo "<HTML><HEAD></HEAD><BODY>";
			echo "<BR>*****************************************************\n";
			echo "<BR> Personal Campaign $fileToProcess\n";
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
					if( CampaignProcessHeaderLine( $line ) )
					{
						$success = false;			
						$lineNo = 1;

						while( $line = fgetcsv( $fr, 2048 ))
						{
							$lineNo++;
							CampaignProcessLine( $line );
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