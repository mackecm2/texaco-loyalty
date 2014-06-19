	<?php

	//******************************************************************
	//
	// GroupLoyaltyFileProcess.php
	//
	//  MRM - 28.07.09 - Based on BatchFileProcess.php, for the Group Loyalty Accounts
	//
	//******************************************************************


	function CreateGroupRecords( $fields, $AccountNo )
	{
		global $fieldArray, $ProcessName;
 		global $Changes;
		global $fieldsToFill;
		global $values;
		global $update;


		$Existing = $fields["ExistingCard"];
		$OldCard = $fields["OldCard"];
		if ($OldCard != "")
		{
			$Existing = $OldCard;
		}
		$CardNo = $fields["CardNo"];

		if( !luhnCheck( $CardNo ) )
		{
			LogError( "CardNo $CardNo fails Luhn Check....");
			return;
		}

		if( $Existing != "" and !luhnCheck( $Existing ) )
		{
			LogError( "CardNo $Existing fails Luhn Check....");
			return;
		}

		$cardsToLink = false;
		$linkExisting = false;
		$MemberNo = "";
//		$AccountNo = "";f
		$row = false;

		#$CreatedBy = "Batch$fields[BatchNo]";
		$CreatedBy = "Batchload";

		echo "<BR>Processing $CardNo....";

		// See if card to link to field is set and if so if it exists in DB

		if( $Existing != "" )
		{
			echo " Checking for $Existing ";
			$sql = "Select Cards.MemberNo, AccountNo from Cards left Join Members using( MemberNo ) where CardNo = '$Existing'";

			$results = DBQueryLogOnFailure( $sql );

			if( mysql_num_rows($results) == 0 )
			{
				LogError( "Existing Card $Existing not found...." );
				$cardsToLink = true;
				$linkExisting = true;
			}
			else
			{
				echo " found.... ";
				$row = mysql_fetch_assoc( $results );
				if( $row["MemberNo"] != "" )
				{
					$AccountNo = $row["AccountNo"];
				}
				else
				{
					echo " found but no member....";
					$cardsToLink = true;
					$linkExisting = true;
				}
			}
		}


		// Check for card no

		$sql = "Select Cards.MemberNo, AccountNo from Cards left Join Members using( MemberNo ) where CardNo = '$CardNo' ";
		$results = DBQueryLogOnFailure( $sql );

		if( mysql_num_rows( $results ) != 0 )
		{
			$row = mysql_fetch_assoc( $results );
			if( $row["MemberNo"] != "" )
			{
				LogError( "Card Already assigned....");
				$AccountNo = $row["AccountNo"];
				if( $cardsToLink )
				{
					// Swap the cards to link them still
					echo " but other card needs linking....";
					$t = $Existing;
					$Existing = $CardNo;
					$CardNo = $t;
					$linkExisting = false;
				}
			}
			else
			{
				$cardsToLink = true;
			}
		}
		else
		{
			echo "card not present....";
			$cardsToLink = true;
		}
		if( !$cardsToLink )
		{
			echo "No Cards need linking....";
			return;
		}

		$row = false;
        unset($row);
		if( $AccountNo != "" )
		{
		    $OrCheck = "";
			if( $fields["DOB"] != "" )
			{
				$OrCheck =  "OR DOB = $fields[DOB]";
			}

			$sql = "Select * from Members where AccountNo = $AccountNo and ((Surname = '$fields[Surname]' and Forename = '$fields[Forename]') OR (Surname is null and Forename is null )$OrCheck ) order by Surname Desc ";
			$results = DBQueryLogOnFailure( $sql );

			$numrows = mysql_num_rows($results);
			if( $numrows == 0 )
			{
				echo "Creating new member on account....";
				$MemberNo = 0;
				$row = false;
			}
			else
			{
				$row = mysql_fetch_assoc( $results );
				$MemberNo = $row["MemberNo"];
			}
		}

		$PrimaryMember = 'N';
		$trackingRecord = false;
		if( $AccountNo == "" )
		{
			$addFields = "";
			$addValues = "";
			if( $fields["SiteCode"] != "" )
			{
				$addFields .= ",HomeSite";
				$addValues .= ",$fields[SiteCode]";
			}
			$sql = "Insert into Accounts (  CreatedBy, CreationDate $addFields ) values ( '$CreatedBy', now() $addValues  )";

			$results = DBQueryLogOnFailure( $sql );
			$AccountNo = mysql_insert_id();
			$PrimaryMember = 'Y';
			$trackingRecord = true;
			echo " Added Account....";

			if( $Existing != "" )
			{
				// Need to link the existing card too as it was un registered.
				$linkExisting = true;
			}
		}

		$fieldsToFill = "";
		$values = "";
		$update = "";
		$Changes = "";
		foreach( $fieldArray as $fielddd )
		{

			if( $fielddd->memberCol != "" && $fields[$fielddd->name] != "" )
			{
				if ( $fielddd->memberCol == "StaffID" && $Existing != ""  )
				{
					//find out if the old card is registered
					$sql = "Select MemberNo from Cards where CardNo = '".$Existing."'";
					$results = DBQueryLogOnFailure( $sql );
					$cardfound = mysql_fetch_row( $results );

					if( $cardfound[0] )
					{
						echo " StaffID with registered member ($cardfound[0],$Existing)....";
					}
				//	else ..... originally we only added the StaffID if it was a kosher new registration.
				//             now we sift the bad uns out in RegularProcessing/StaffIncentive/BonusCalc.php
				//	{
						AddIfDifferent( $fields[$fielddd->name], $fielddd->memberCol, $row );
				//	}
				}
				else
				{
					AddIfDifferent( $fields[$fielddd->name], $fielddd->memberCol, $row );
				}

			}
		}
		unset($fielddd);

			if( strstr($fields["Prefs"], "S") and $fields["OptOutTex"] != "Y" )
			{
				AddIfDifferent( 'Y', 'OKSMS', $row);
			}
			else
			{
				AddIfDifferent( 'N', 'OKSMS', $row );
			}

			// Mail Field
			if( $fields["OptInPost"] == "Y" and $fields["OptOutTex"] != "Y" )
			{
				AddIfDifferent( 'Y', 'OKMail', $row );
			}
			else
			{
				AddIfDifferent( 'N', 'OKMail', $row );
			}

			// Email

			if( $fields["OptInElec"] == "Y" and $fields["OptOutTex"] != "Y")
			{
				AddIfDifferent( 'Y', 'OKEmail', $row );
			}
			else
			{
				if( $fields["Source"] == "TEXWEOU1" )
				{
					if( $fields["EmailOpt"] == 'Y' )
					{
						AddIfDifferent( 'N', 'OKEmail', $row );
					}
					else
					{
						AddIfDifferent( 'Y', 'OKEmail', $row );
					}
				}
				else
				{
					if( strstr($fields["Prefs"], "E") or $fields["Statements"] == "E" )
					{
						AddIfDifferent( 'Y', 'OKEmail', $row );
					}
					else
					{
						AddIfDifferent( 'N', 'OKEmail', $row );
					}
				}

			}


			// Third Party Mail

			if( $fields["Source"] == "TEX3" or $fields["Source"] == "TEX4" )
			{
				if( $fields["ThirdParty"] == 'Y' )
				{
					AddIfDifferent( 'N', 'TOKMail', $row );
				}
				else
				{
					AddIfDifferent( 'Y', 'TOKMail', $row );
				}
			}
			else
			{
				if( $fields["PartnerOptin"] == 'Y' )
				{
					AddIfDifferent( 'Y', 'TOKMail', $row );
				}
				else
				{
					AddIfDifferent( 'N', 'TOKMail', $row );
				}
			}


			// Statement Pref

			if( $fields["Statements"] != "" )
			{
				AddIfDifferent( $fields["Statements"], 'StatementPref', $row );
			}
			else if( $fields["Prefs"] != "" )
			{
				if( strstr( $fields["Prefs"], "E" ) )
				{
					AddIfDifferent( 'E', 'StatementPref', $row );
				}
				else if( strstr( $fields["Prefs"], "S" ) )
				{
					AddIfDifferent( 'S', 'StatementPref', $row );
				}
				else
				{
					AddIfDifferent( 'P', 'StatementPref', $row );
				}
			}
			else
			{
				if( $fields["OptOutTex"] != "Y" )
				{
					AddIfDifferent( 'P', 'StatementPref', $row );
				}
				else
				{
					AddIfDifferent( 'N', 'StatementPref', $row );
				}
			}


	 // Phone number processing

	//	WorkPhone, MobilePhone, HomePhone, WorkPhone2

			if( $fields["HomePhone"] != "" )
			{
				AddIfDifferent( $fields["HomePhone"], 'HomePhone', $row );
			}
			else if( $fields["MobilePhone"] != "" )
			{
				AddIfDifferent( $fields["MobilePhone"], 'HomePhone', $row );
			}

			if( $fields["WorkPhone"] != "" )
			{
				AddIfDifferent( $fields["WorkPhone"], 'WorkPhone', $row );
			}
			else if( $fields["WorkPhone2"] != "" )
			{
				AddIfDifferent( $fields["WorkPhone2"], 'WorkPhone', $row );
			}



		if( $MemberNo == "" )
		{

			$sql = "Insert into Members ( AccountNo, PrimaryMember, CanRedeem, PrimaryCard, CreatedBy, CreationDate $fieldsToFill ) values ( $AccountNo, '$PrimaryMember', '$PrimaryMember','$fields[CardNo]', '$CreatedBy', now() $values )";
			$results = DBQueryLogOnFailure( $sql );
			$MemberNo = mysql_insert_id();
			if( !$trackingRecord )
			{
				$trackingRecord = true;
				InsertTrackingRecord( TrackingAdditionalMember, $MemberNo, $AccountNo, "", 0 );
			}

			if( $fields["Source"] == "TEXWEOUZ" or $fields["Source"] == "TEXWEOUP" or $fields["Source"] == "TEXWEOUY" )
			{
				AdjustBalance( TrackingEmailBonus50, $MemberNo, $AccountNo, '', 50 );
				echo "50 Bonus Added....";
			}
		}
		else
		{
			if( $update != "" )
			{
				$sql = "Update Members set $update RevisedBy = '$ProcessName' where MemberNo = $MemberNo";
				$results = DBQueryLogOnFailure( $sql );
				echo " Updated Member....";
				InsertTrackingRecord( TrackingContactChange, $MemberNo, $AccountNo, $Changes, 0 );
			}
			else
			{
				echo "No update necessary....";
			}
		}

		echo "Merging Card $CardNo....";
		MergeCardToMember( $CardNo, $MemberNo, false );

		if( $linkExisting )
		{
			echo "Merging Card $Existing....";
			MergeCardToMember( $Existing, $MemberNo, false );
		}

		// ReleaseStoppedPoints( $AccountNo );	   now done in MergeCardToMember
		RecordQuestionAnswers( $fields, $MemberNo );
	}



function GroupProcessLine( $line, $accountno )	{
		global $fieldArray;
		$FieldLookup = array();
		foreach( $fieldArray as $field )
		{
			$value = mysql_escape_string( stripslashes( rtrim( substr( $line, $field->offset, $field->size ) ))); 
// @TODO - replace mysql_escape_string with mysql_real_escape_string
			$FieldLookup[ $field->name ] = $value;
		}
		
		$CardType = CardRangeCheck( $FieldLookup["CardNo"] );
	
		if ( $CardType == "Unknown" )
		{
			echo "******** INVALID CARD NUMBER $FieldLookup[CardNo] FOUND ********* Record skipped	....\n\r";		
			LogError("Invalid Card Number $FieldLookup[CardNo] found");	 		
		}
		else 
		{
			if( luhnCheck( 	$FieldLookup["CardNo"] ) )
			{
//				$FieldLookup[ "DOB" ]  = ConvertDate( $FieldLookup[ "DOB" ] );
				CreateGroupRecords( $FieldLookup, $accountno );
			}
			else
			{
				echo "CardNo $FieldLookup[CardNo] failed luhnCheck ....\n";	
			}
		}
	}
	


	// Main function

	function GroupProcessFile($fileToProcess,$accountno)
	{
		global 	$ProcessName;
		global  $uname;
		global $lineNo;

		$fileMove =  LocationFileProcessing. "Processed/BatchFile/";
		echo "<HTML><HEAD></HEAD><BODY>";
		echo "<BR>*****************************************************\n";
		echo "<BR> Processing Group Loyalty Batch Load Data.\n";
		echo "<BR>$fileToProcess\n";
		echo "<BR>Account Number :$accountno\n";
		echo "<BR>*****************************************************\n";
		$ProcessName = "GroupLoyaltyLoad";
		$uname = $ProcessName;


		BatchInitArray();


			//connectToDB( MasterServer, TexacoDB );

			echo "$fileToProcess\n";

			$fr = fopen( $fileToProcess, "r");

			if(!$fr)
			{
				echo "Error! Couldn't open the file.";
			}
			else
			{

				$fileRec = createFileProcessRecord($fileToProcess);
				if( $fileRec )
				{
					// $fr now can be used to represent the opened file//
//			$line = fgets( $fr );
//			$success = false;
					$lineNo = 0;

					while( $line = fgets( $fr ))
					{
						$lineNo++;
						GroupProcessLine( $line, $accountno );
					}
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) );
			}
		echo "Finished</BODY></HTML>";
	}
	?>