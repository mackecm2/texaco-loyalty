	<?php

	//******************************************************************
	//
	// CardRequestFileProcess.php
	//
	//  MRM - 19.03.09 - Reverted back to DOB as date(10)
	//
	//******************************************************************



	class Field
	{
		var $name;
		var $number;
		var $size;
		var $offset;
		var $memberCol;

		function Field( $lname, $ltype, $lsize, $lCol )
		{
			global $fieldOffset;

			$this->name = $lname;
			$this->size = $lsize;
			$this->offset = $fieldOffset;
			$this->memberCol = $lCol;
			$fieldOffset += $lsize;
		}
	}

	function BatchInitArray()
	{
		global $fieldArray,  $fieldOffset;
		$fieldCounter = 0;
		$fieldOffset  = 0;

		$fieldArray = array();

		$fieldArray[$fieldCounter++] = new Field( "BatchNo",	"text", 4, "" );
		$fieldArray[$fieldCounter++] = new Field( "Date",		"date", 10, "" );
		$fieldArray[$fieldCounter++] = new Field( "CardNo",		"text", 19, "" );
		$fieldArray[$fieldCounter++] = new Field( "Title",		"text", 10, "Title" );
		$fieldArray[$fieldCounter++] = new Field( "Forename",	"text", 40, "Forename" );
		$fieldArray[$fieldCounter++] = new Field( "Surname",	"text", 40, "Surname" );
		$fieldArray[$fieldCounter++] = new Field( "DOB",		"date", 4, "DOB" );
		$fieldArray[$fieldCounter++] = new Field( "Address1",	"text", 40, "Address1" );
		$fieldArray[$fieldCounter++] = new Field( "Address2",	"text", 40, "Address2" );
		$fieldArray[$fieldCounter++] = new Field( "Address3",	"text", 40, "Address3" );
		$fieldArray[$fieldCounter++] = new Field( "Address4",	"text", 40, "Address4" );
		$fieldArray[$fieldCounter++] = new Field( "Address5",	"text", 40, "Address5" );
		$fieldArray[$fieldCounter++] = new Field( "Postcode",	"text", 8,  "PostCode" );
		$fieldArray[$fieldCounter++] = new Field( "Email",		"text", 80, "Email" );
		$fieldArray[$fieldCounter++] = new Field( "EmailOpt",	"Y/N",  1,  "" );
		$fieldArray[$fieldCounter++] = new Field( "WorkPhone",	"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "MobilePhone",	"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "ExistingCard",	"text", 19, "" );
		$fieldArray[$fieldCounter++] = new Field( "Profession",		"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Children",		"text", 10, "" );
		$fieldArray[$fieldCounter++] = new Field( "Income",			"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillTex",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillShell",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillBP",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillEsso",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillTesco",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillAsda",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillSains",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillOther",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillFree",		"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillFreq",		"Y/N", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "SpendShop",		"Y/N", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "Private",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Company",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Make",			"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewTex",			"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewMotor",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewTravel",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewSports",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewDining",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewFamily",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewShop",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OptOutTex",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OptOutColt",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "HomePhone",		"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "WorkPhone2",		"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "CollectFor",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Mileage",		"number", 6, "" );
		$fieldArray[$fieldCounter++] = new Field( "Visits",			"number", 2 , "");
		$fieldArray[$fieldCounter++] = new Field( "Company_pay",	"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Cars",			"text", 1 , "");
		$fieldArray[$fieldCounter++] = new Field( "Fleet",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "ThirdParty",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "PAF",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Colt",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Source",			"text", 8, "Source" );
		$fieldArray[$fieldCounter++] = new Field( "Statements",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "SiteCode",		"number", 10, "SourceSite" );
		$fieldArray[$fieldCounter++] = new Field( "FillTotal",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OutOf10",		"text", 2, "" );
		$fieldArray[$fieldCounter++] = new Field( "REWFuel",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "REWCarWash",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Prefs",			"text", 3, "" );
		$fieldArray[$fieldCounter++] = new Field( "AutoVoucher",	"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "PartnerOptin",	"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OldCard",	    "text", 19, "" );
		$fieldArray[$fieldCounter++] = new Field( "OptInElec",	    "Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OptInPost",	    "Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "StaffID",	    "text", 9, "StaffID" );
	}


	function NewBatchInitArray()
	{
		global $fieldArray,  $fieldOffset;
		$fieldCounter = 0;
		$fieldOffset  = 0;

		$fieldArray = array();

		$fieldArray[$fieldCounter++] = new Field( "CardNo",	"text", 19, "" );
		$fieldArray[$fieldCounter++] = new Field( "Title",	"text", 10, "Title" );
		$fieldArray[$fieldCounter++] = new Field( "Forename",	"text", 40, "Forename" );
		$fieldArray[$fieldCounter++] = new Field( "Surname",	"text", 40, "Surname" );
		$fieldArray[$fieldCounter++] = new Field( "DOB",	"date", 4, "DOB" );
		$fieldArray[$fieldCounter++] = new Field( "Address1",	"text", 40, "Address1" );
		$fieldArray[$fieldCounter++] = new Field( "Address2",	"text", 40, "Address2" );
		$fieldArray[$fieldCounter++] = new Field( "Address3",	"text", 40, "Address3" );
		$fieldArray[$fieldCounter++] = new Field( "Address4",	"text", 40, "Address4" );
		$fieldArray[$fieldCounter++] = new Field( "Address5",	"text", 40, "Address5" );
		$fieldArray[$fieldCounter++] = new Field( "Postcode",	"text", 8,  "PostCode" );
		$fieldArray[$fieldCounter++] = new Field( "HomePhone",	"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "MobilePhone","text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "Email",	"text", 80, "Email" );
		




	}




  	function AddIfDifferent( $newVal, $field, $row )
	{
		global $Changes;
		global $fieldsToFill;
		global $values;
		global $update;

		if( $row )
		{
			if($newVal != $row[$field]) 
			{
				$Changes.= "$field $row[$field] => $newVal ";
				$update.= "$field = '$newVal',";
			}
		}
		else
		{
			$fieldsToFill .= ",$field";
			$values .= ",'$newVal'";
		}
	}


	function CreateRecords( $fields )
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
			LogError( "CardNo $CardNo fails Luhn Check ....\n");
			return;
		}

		if( $Existing != "" and !luhnCheck( $Existing ) )
		{
			LogError( "CardNo $Existing fails Luhn Check ....\n"); 
			return;
		}

		$cardsToLink = false;
		$linkExisting = false;
		$MemberNo = "";
		$AccountNo = "";
		$row = false;

		#$CreatedBy = "Batch$fields[BatchNo]";
		$CreatedBy = "Batchload";

		echo "<BR>Processing $CardNo ....";

		// See if card to link to field is set and if so if it exists in DB

		if( $Existing != "" )
		{
			echo " Checking for $Existing ....";
			$sql = "Select Cards.MemberNo, AccountNo from Cards left Join Members using( MemberNo ) where CardNo = '$Existing'"; 

			$results = DBQueryLogOnFailure( $sql );

			if( mysql_num_rows($results) == 0 )
			{
				LogError( "Existing Card $Existing not found ...." ); 
				$cardsToLink = true;
				$linkExisting = true;
			}
			else
			{
				echo " found ....";
				$row = mysql_fetch_assoc( $results );
				if( $row["MemberNo"] != "" )
				{
					$AccountNo = $row["AccountNo"];
				}
				else
				{
					echo " found but no member ....";
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
				LogError( "Card Already assigned"); 
				$AccountNo = $row["AccountNo"];
				if( $cardsToLink )
				{
					// Swap the cards to link them still
					echo " but other card needs linking  ...."; 
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
			echo "card not present ....";
			$cardsToLink = true;
		}
		if( !$cardsToLink )
		{
			echo "No Cards need linking ....";
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
				echo "Creating new member on account ...."; 
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
	
			$results = DBQueryExitOnFailure( $sql );
			$AccountNo = mysql_insert_id();
			
			// Get the ID number of the new record   MIKE 30 06 10 Project Leeson  Mantis 2326     
           $sql = "INSERT INTO AccountStatus (AccountNo, Status, StatusSetDate, FraudStatus, RevisedDate) 
            		VALUES ('$AccountNo', 'Open', NOW(), '0', NOW( ))";
            DBQueryExitOnFailure( $sql ); 
			
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
						echo " StaffID with registered member ($cardfound[0],$Existing) ....";	
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
				echo "50 Bonus Added ....";
			}
		}
		else
		{
			if( $update != "" )
			{
				$sql = "Update Members set $update RevisedBy = '$ProcessName' where MemberNo = $MemberNo";
				$results = DBQueryLogOnFailure( $sql );
				echo " Updated Member ....";
				InsertTrackingRecord( TrackingContactChange, $MemberNo, $AccountNo, $Changes, 0 );
			}
			else
			{
				echo "No update necessary ....";
			}
		}

		echo "Merging Card $CardNo ....";
		MergeCardToMember( $CardNo, $MemberNo, false );  

		if( $linkExisting )
		{
			echo "Merging Card $Existing ....";
			MergeCardToMember( $Existing, $MemberNo, false ); 
		}

		// ReleaseStoppedPoints( $AccountNo );	   now done in MergeCardToMember
		RecordQuestionAnswers( $fields, $MemberNo );
	}


	function RecordQuestionAnswers( $fields, $MemberNo )
	{
		if( $fields["Children"] != "" ) 
		{
			RecordAnswer( QuestionNoChildren,  $MemberNo, $fields["Children"], 0 );
		}

		if( $fields["Company_pay"] != "" ) 
		{
			RecordAnswer( QuestionCompanyPaidFuel,  $MemberNo, $fields["Company_pay"], 0 );
		}		

	}


	function BatchProcessLine( $line )
	{
		global $fieldArray;
		$FieldLookup = array();
	//	echo $line;
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
				CreateRecords( $FieldLookup );
			}
			else
			{
				echo "CardNo $FieldLookup[CardNo] failed luhnCheck ....\n";	
			}
		}
	}


	// Main function

	function BatchProcessFile($fileToProcess,$type)
	{
		global 	$ProcessName;
		global  $uname;
		global $lineNo;

		$fileMove =  LocationFileProcessing. "Processed/BatchFile/";
		echo "<HTML><HEAD></HEAD><BODY>";
		echo "<BR>*****************************************************\n";
		echo "<BR> Processing Generic Batch Load Data.\n";
		echo "<BR>$fileToProcess\n";
		echo "<BR>*****************************************************\n";
		echo "<BR>";
		$ProcessName = "GenericLoad";
		$uname = $ProcessName;

		if($type == "Bulk")
		{
			BatchInitArray();
		}
		else
		{
			NewBatchInitArray();
		}

			//connectToDB( MasterServer, TexacoDB );

			echo "$fileToProcess\n";

			$fr = fopen( $fileToProcess, "r");

			if(!$fr) 
			{
				echo "Error! Couldn't open the file.\n";
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
						BatchProcessLine( $line );
					}
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
			}
		echo "Finished</BODY></HTML>";
	}
	?>