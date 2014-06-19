	<?php


	//******************************************************************
	//
	// CardRequestFileProcess.php
	//
	//  
	//
	//******************************************************************

	function checkLhunCardNumber( $CardNumber )
	{
		$no_digit = strlen($CardNumber);
		$oddoeven = $no_digit & 1;
		$sum = 0;

		for ($count = 0; $count < $no_digit; $count++) 
		{
			$digit = substr( $CardNumber, $count, 1);

			if( $digit < '0' or $digit > '9' )
			{
				echo "non digit in card number";
				return false;
			}

			if (!(($count & 1) ^ $oddoeven)) 
			{
				$digit *= 2;
				if ($digit > 9)
				{
					// very cleverly adds in one as well
					$digit -= 9;
				}
			}
			$sum += $digit;
		}
		return (($sum % 10) == 0);

	}

	function AddIfSet( $field, $value )
	{
		if( isset( $value ) and $value != "" )
		{
			return ", $field = '". mysql_escape_string($value) . "'";
		}
		else
		{
			return "";
		}
	}

	function AddIfDiff( $row, $field, $value )
	{
		if( isset( $value ) and $value != "" and $row[$field] != $value )
		{		
			echo "$field, ";
			 return ", $field = '". mysql_escape_string($value) . "'";
		}
		else
		{
			 return "";
		}
	}

	function SetOrNull( $field, $value )
	{
		if( isset( $value ) and $value != "" )
		{
			return ", $field = '". mysql_escape_string($value) . "'";
		}
		else
		{
			return ",$field = null";
		}
	}

	function ScratchcardUpdateMemberContacts( $row, $fields, $memberNo, $accountNo ) 
	{
		global $cardNumberCol, $cardNumberCol, $titleCol, $initialCol, $surnameCol;
		global $add1Col, $add2Col, $add3Col, $add4Col, $add5Col, $postcodeCol;
		global $NewMembers;
		global $UpdatedMembers;
		global $Errors;
		global $Unaffected;
		global $InitialsOnly;
		$diff = "";

		$diff .= AddIfDiff( $row, "Surname", $fields[$surnameCol] );
		if( $row["Forename"] == "" )
		{
			$diff .= AddIfDiff( $row, "Forename", $fields[$initialCol] );
		}
		$diff .= AddIfDiff( $row, "Title", $fields[$titleCol] );

		if(  ($fields[$add1Col] <> "" and $row["Address1"] != $fields[$add1Col] )
		  or ($fields[$add2Col] <> "" and $row["Address2"] != $fields[$add2Col] )
		  or ($fields[$add3Col] <> "" and $row["Address3"] != $fields[$add3Col] )
		  or ($fields[$add4Col] <> "" and $row["Address4"] != $fields[$add4Col] )
		  or ($fields[$add5Col] <> "" and $row["Address5"] != $fields[$add5Col] )
		  or ($fields[$postcodeCol] <> "" and $row["Postcode"] != $fields[$postcodeCol] )  )
		{
			echo " Update address\n";
			$diff .= SetOrNull( "Address1", $fields[$add1Col] );
			$diff .= SetOrNull( "Address2", $fields[$add2Col] );
			$diff .= SetOrNull( "Address3", $fields[$add3Col] );
			$diff .= SetOrNull( "Address4", $fields[$add4Col] );
			$diff .= SetOrNull( "Address5", $fields[$add5Col] );
			$diff .= SetOrNull( "Postcode", $fields[$postcodeCol] );
		}

		if( $diff == "" and AddIfDiff( $row, "Initials", $fields[$initialCol] ) != "" )
		{
			$InitialsOnly++;
		}
		$diff .= AddIfDiff( $row, "Initials", $fields[$initialCol] );


		if( $diff != "" )
		{
//			$diff = trim( $diff, " ," );
//			echo "<br><br>";
//			print_r( $row );
			$sql = "Update Members set RevisedBy = 'ScratchCard' $diff where MemberNo = $memberNo";
//			echo "<br>$sql";
			$results = DBQueryExitOnFailure( $sql );
			InsertTrackingRecord( TrackingContactChange, $memberNo, $accountNo, "Contact Details changed by October scratchcard", 0 );	
			$UpdatedMembers++;
		}
		else
		{
			$Unaffected++;
			echo "No differeces.\n";
		}
	}


	function ScratchcardProcessLine( $fields )
	{
		global $cardNumberCol, $cardNumberCol, $titleCol, $initialCol, $surnameCol;
		global $add1Col, $add2Col, $add3Col, $add4Col, $add5Col, $postcodeCol;
 		global $NewMembers;
		global $UpdatedMembers;
		global $Errors;
		global $Unaffected;
  		global $LinkedCards;

		$cardNumber = $fields[$cardNumberCol];
		$EscSurname = mysql_escape_string($fields[$surnameCol]); 

		if( !checkLhunCardNumber( $cardNumber ) )
		{
			$Errors++;
			echo "<br>*********************************************************************\n";
			echo "<br>Ill formed $cardNumber ";
			echo "<br>*********************************************************************\n";
		}
		else
		{

			$memberNo = GetCardMemberNo( $cardNumber );

			$sql = "SELECT MemberNo from Cards where CardNo = '$cardNumber'";

			$results = DBQueryExitOnFailure( $sql );

			if( mysql_num_rows( $results ) == 0 )
			{
				$Errors++;
				echo "<br>*********************************************************************\n";
				echo "<br>Card $cardNumber not found in db but should be";
				echo "<br>*********************************************************************\n";
			}
			else
			{
				$row = mysql_fetch_assoc( $results );
				$memberNo = $row["MemberNo"]; 
				if( $memberNo == 0 or $memberNo == "" )
				{
				//	echo "<br>Card unassigned checking if  member exists\n";

// Check Surname equal.
// That any postcode entry in the record matches as much of the postcode as possible.
// That the record has at least 3 chars.
// That the forename matches the given initial
// That if the forename is blank that the intial matches the initial.
					$Postcode = $fields[$postcodeCol];
					$Initial  = $fields[$initialCol];
					$sql = "Select AccountNo, MemberNo, Title, Forename, Initials, Surname, Address1, Address2, Address3, Address4, Address5, Postcode from Members where Surname = '$EscSurname' and ( substring( '$Postcode', 1, CHAR_LENGTH( Postcode ))  = Postcode ) and CHAR_LENGTH( Postcode ) > 2 and if( Forename is not null, Forename like '$Initial%', Initials is null or Initials like '$Initial%')";

					$results = DBQueryExitOnFailure( $sql );
					$num_rows = mysql_num_rows( $results );
					if( $num_rows == 0 )
					{
						$NewMembers++;
						echo "<br>Adding Member\n";
	//					echo "<br>Matching member not found in db need to create new member/account and link card";
  

						CreateMember( $cardNumber, $accountNo, $memberNo );

						ReleaseStoppedPoints( $accountNo );

						$sql = "Update Members set Surname = '$EscSurname'";
						$sql .= AddIfSet( "Forename", $fields[$initialCol] );
						$sql .= AddIfSet( "Initials", $fields[$initialCol] );
						$sql .= AddIfSet( "Title", $fields[$titleCol] );
						$sql .= AddIfSet( "Address1", $fields[$add1Col] );
						$sql .= AddIfSet( "Address2", $fields[$add2Col] );
						$sql .= AddIfSet( "Address3", $fields[$add3Col] );
						$sql .= AddIfSet( "Address4", $fields[$add4Col] );
						$sql .= AddIfSet( "Address5", $fields[$add5Col] );
						$sql .= AddIfSet( "Postcode", $fields[$postcodeCol] );
						$sql .= " where MemberNo = $memberNo";
						$results = DBQueryExitOnFailure( $sql );

						InsertTrackingRecord( TrackingNewAccount, $memberNo, $accountNo, "Created from October Scratchcard Information", 0 );
					}
					else if( $num_rows > 1 )
					{
						$Errors++;
			//			echo "<br>*********************************************************************\n";
			//			echo "<br>Multiple matching members\n";
			//			echo "<br>$sql\n";
			//			echo "<br>*********************************************************************\n";
						DBLogError( "Multiple Member Match $fields[$surnameCol], $Postcode" ); 
					}
					else
					{
						$LinkedCards++;
						$row = mysql_fetch_assoc( $results );
						$memberNo = $row["MemberNo"];
						$accountNo = $row["AccountNo"];
						echo "<br>Linking to Member\n";
 				//		echo "<br>*********************************************************************\n";
				//		echo "<br> Matching member need to link card\n";
				//		echo "<br>*********************************************************************\n";

						MergeCardToMember( $cardNumber, $memberNo, False );

 						InsertTrackingRecord( TrackingNewCardAdded, $memberNo, $accountNo, "Carded $cardNumber added by October scratchcard", 0 );

						ScratchcardUpdateMemberContacts( $row, $fields, $memberNo, $accountNo );
	
					}

				}
				else
				{
			//		echo "<br>Card already assigned checking details\n";
					$sql = "SELECT AccountNo, Title, Initials, Forename, Surname, Address1, Address2, Address3, Address4, Address5, Postcode from Members where MemberNo = $memberNo";
					echo "<br>Existing member";
					$results = DBQueryExitOnFailure( $sql );

					$row = mysql_fetch_assoc( $results );
					$accountNo = $row["AccountNo"];

					if( $row["Surname"] != "" and $fields[$surnameCol] != "" and strcasecmp($row["Surname"] , $fields[$surnameCol] ) != 0 )
					{
						$Errors++;
				//		echo "<br>$cardNumber Surname mismatch DB => $row[Surname] != $fields[$surnameCol] <= File\n";
						DBLogError( "$cardNumber Surname mismatch DB => $row[Surname] != $fields[$surnameCol] <= File" );
					}
					else
					{
 						ScratchcardUpdateMemberContacts( $row, $fields, $memberNo, $accountNo );
					}
				}
			}
		}
	}

	function ScratchcardProcessHeaderLine( $fields )
	{
		global $cardNumberCol, $cardNumberCol, $titleCol, $initialCol, $surnameCol;
		global $add1Col, $add2Col, $add3Col, $add4Col, $add5Col, $postcodeCol;

//		$fields = explode( ",", $line );

		$fieldHeaders = array();
		foreach( $fields as $key => $header )
		{
			$fieldHeaders[$header] = $key;
		}


		$cardNumberCol = $fieldHeaders["URN"];
		$titleCol  = $fieldHeaders["TITLE"];
		$initialCol = $fieldHeaders["INITIAL"];
		$surnameCol = $fieldHeaders["SURNAME"];
		$add1Col = $fieldHeaders["ADD_1"];
		$add2Col = $fieldHeaders["ADD_2"];
		$add3Col = $fieldHeaders["ADD_3"];
		$add4Col = $fieldHeaders["ADD_4"];
		$add5Col = $fieldHeaders["ADD_5"];
		$postcodeCol = $fieldHeaders["POST_CODE"];

		return true;
	}


	function ScratchcardProcessFiles($fileToProcess)
	{

		global $lineNo, $ProcessName;
		global $uname;
		global $NewMembers;
		global $UpdatedMembers;
		global $Errors;
		global $Unaffected;
		global $LinkedCards;
		global $InitialsOnly;

		$NewMembers = 0;
		$UpdatedMembers = 0;;
		$Errors = 0;
		$Unaffected = 0;
		$LinkedCards = 0;
		$InitialsOnly = 0;

		$ProcessName = "ScratchCard";
		$uname = $ProcessName;
		$fileMove =  LocationFileProcessing."Processed/AddHocFiles/";
		
			//connectToDB( MasterServer, TexacoDB );
			echo "<HTML><HEAD></HEAD><BODY>";
			echo "<BR>*****************************************************\n";
			echo "<BR>Processing Scratch Card Data.\n";
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
					if( ScratchcardProcessHeaderLine( $line ) )
					{
						$success = false;			
						$lineNo = 1;

						while( $line = fgetcsv( $fr, 2048 ))
						{
							$lineNo++;
							ScratchcardProcessLine( $line );
						}

					}
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
			    $UpdatedMembers -= $NewMembers;
				echo "<br>New Members Created $NewMembers\n";
				echo "<br>Members Updated $UpdatedMembers\n";
				echo "<br>Updated Initials Only $InitialsOnly\n";
				echo "<br>Members Not changed $Unaffected\n";
				echo "<br>Cards linked to exsiting Members $LinkedCards \n";
				echo "<br>Errors $Errors\n";
				echo "</BODY></HTML>";
			}
	}

	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

	?>