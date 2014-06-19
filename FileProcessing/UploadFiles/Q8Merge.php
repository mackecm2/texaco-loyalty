	<?php

	//******************************************************************
	//
	// CardRequestFileProcess.php
	//
	//  
	//
	//******************************************************************


	function Q8ProcessLine( $fields )
	{
		global $cardNumberCol, $cardNumberCol, $titleCol, $firstnameCol, $surnameCol;
		global $add1Col, $add2Col, $add3Col, $add4Col, $add5Col, $postcodeCol;
		global $balanceCol, $siteCodeCol, $datetime;
		global $NewMembers, $LinkedCards, $ErrorCount;
		$cardNumber = $fields[$cardNumberCol];
  		$EscSurname = mysql_escape_string($fields[$surnameCol]); 

		// Find member No

		if( !checkLhunCardNumber( $cardNumber ) )
		{
			$Errors++;
			echo "<br>*********************************************************************\n";
			echo "<br>Ill formed $cardNumber ";
			echo "<br>*********************************************************************\n";
		}
		else
		{
			$sql = "SELECT MemberNo from Cards where CardNo = '$cardNumber'";

			$results = DBQueryExitOnFailure( $sql );

			if( mysql_num_rows( $results ) <> 0 )
			{
				$ErrorCount++;
				echo "<br>*********************************************************************\n";
				echo "<br>Card $cardNumber found in db but shouldn't be";
				echo "<br>Naughty naughty James waiting so long to submit a file will only cause\n";
				echo "<br>Problems but I guess I have to try and sort it out.";
				echo "<br>*********************************************************************\n";


			}
//			else

			// always try to create the member as dawleys keep delaying submiting this file
			// till after the cards have been sent and used.
			{
				$Postcode = $fields[$postcodeCol];
				$Initial  = substr( $fields[$firstnameCol], 0, 1);
				$Firstname = $fields[$firstnameCol];
				$Address1 = $fields[$add1Col];
				if($Address1 != "" )
				{
					$AddressMatch = " and (Address1 is null or Address1 = '$Address1')";
				}
				else
				{
					$AddressMatch = "";
				}
				echo $Firstname;
				$sql = "Select AccountNo, MemberNo, Title, Forename, Initials, Surname, Address1, Address2, Address3, Address4, Address5, Postcode from Members where Surname = '$EscSurname' and ( substring( '$Postcode', 1, CHAR_LENGTH( Postcode ))  = Postcode ) and CHAR_LENGTH( Postcode ) > 2 and if( Forename is not null, Forename = '$Firstname' or Forename = '$Initial', Initials is null or Initials like '$Initial%') $AddressMatch limit 1";

				$results = DBQueryExitOnFailure( $sql );
				$num_rows = mysql_num_rows( $results );
				if( $num_rows == 0 )
				{
					$NewMembers++;
					echo "<br>Adding Member\n";

					CreateMember( $cardNumber, $accountNo, $memberNo );

					ReleaseStoppedPoints( $accountNo );
					
					$HomeSite = $fields[$siteCodeCol];
					$sql = "Update Accounts set Homesite = '$HomeSite' where AccountNo = '$accountNo'";
  					$results = DBQueryExitOnFailure( $sql );

					$sql = "Update Members set Surname = '$EscSurname'";
					$sql .= AddIfSet( "Forename", $Firstname );
					$sql .= AddIfSet( "Initials", $Initial );
					$sql .= AddIfSet( "Title", $fields[$titleCol] );
					$sql .= AddIfSet( "Address1", $fields[$add1Col] );
					$sql .= AddIfSet( "Address2", $fields[$add2Col] );
					$sql .= AddIfSet( "Address3", $fields[$add3Col] );
					$sql .= AddIfSet( "Address4", $fields[$add4Col] );
					$sql .= AddIfSet( "Address5", $fields[$add5Col] );
					$sql .= AddIfSet( "Postcode", $fields[$postcodeCol] );
					$sql .= ",OKMail = 'Y'";
					$sql .= " where MemberNo = $memberNo";
					$results = DBQueryExitOnFailure( $sql );
				}
				else
				{
					$LinkedCards++;
					$row = mysql_fetch_assoc( $results );
					$memberNo = $row["MemberNo"];
					$accountNo = $row["AccountNo"];
					echo "<br>$cardNumber Linking to Member $memberNo\n";
			//		echo "<br>*********************************************************************\n";
			//		echo "<br> Matching member need to link card\n";
			//		echo "<br>*********************************************************************\n";

					MergeCardToMember( $cardNumber, $memberNo, False );

				}

				// Check to see if this member has already been welcomed
				$sql = "select * from CampaignHistory where MemberNo = $memberNo and CampaignCode = 'Q8Welcome'";

				$results = DBQueryExitOnFailure( $sql );
				
				if( mysql_num_rows( $results ) == 0 )
				{
					AdjustBalance( TrackingQ8Merge, $memberNo, $accountNo, "", $fields[$balanceCol] );
	
				// Need to set up the personal bonuses and contact history
					$sql = "insert into CampaignHistory ( MemberNo, AccountNo, CampaignType, CampaignCode, CreationDate, CreatedBy ) Values ( $memberNo, $accountNo, 'WELCOME', 'TopsWelcome', '$datetime', 'TopsMerge' )"; 

					$results = DBQueryExitOnFailure( $sql );
				}
				else
				{
 					$ErrorCount++;
				    Echo "$cardNumber member has already been welcomed\n";
				}
			}
		}
	}

	function Q8ProcessHeaderLine( $fields )
	{
		global $cardNumberCol, $cardNumberCol, $titleCol, $firstnameCol, $surnameCol;
		global $add1Col, $add2Col, $add3Col, $add4Col, $add5Col, $postcodeCol;
		global $balanceCol, $siteCodeCol;

		$fieldHeaders = array();
		foreach( $fields as $key => $header )
		{
			$fieldHeaders[strtoupper($header)] = $key;
		}

		$cardNumberCol = $fieldHeaders["URN"];
		$titleCol  = $fieldHeaders["TITLE"];
		$firstnameCol = $fieldHeaders["FIRSTNAME"];
		$surnameCol = $fieldHeaders["SURNAME"];
		$add1Col = $fieldHeaders["ADD_1"];
		$add2Col = $fieldHeaders["ADD_2"];
		$add3Col = $fieldHeaders["ADD_3"];
		$add4Col = $fieldHeaders["ADD_4"];
		$add5Col = $fieldHeaders["ADD_5"];
		$postcodeCol = $fieldHeaders["POST_CODE"];
		$balanceCol = $fieldHeaders["BALANCE"];
		$siteCodeCol = $fieldHeaders["SITECODE"];
		return true;
	}


	function Q8ProcessFiles($fileToProcess)
	{
		global $lineNo, $ProcessName;											
		global $NewMembers, $LinkedCards, $ErrorCount;
		global $datetime;
		global $uname;

		$NewMembers = 0;
		$LinkedCards = 0;
		$ErrorCount = 0;
		$datetime = date("Y-m-d H:i:s") ;
		$StartDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1,  date("Y")));
		$EndDate =   date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+43,  date("Y")));
			
		$ProcessName = "Q8Merge";
		$uname = $ProcessName;
		$fileMove =  LocationFileProcessing."Processed/Misc/";
			//connectToDB( MasterServer, TexacoDB );
			echo "<HTML><HEAD></HEAD><BODY>";
			echo "<BR>*****************************************************\n";
			echo "<BR> Processing Q8 merge file.\n";
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
					if( Q8ProcessHeaderLine( $line ) )
					{
						$success = false;			
						$lineNo = 1;

						while( $line = fgetcsv( $fr, 2048 ) )
						{
							$lineNo++;
							Q8ProcessLine( $line );
						}
						CopyWelcomeBatchToPersonalCampaign( $datetime, $StartDate, $EndDate, "Q8Welcome" );
					}
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
				echo "<br>Error count = $ErrorCount\n";
				echo "<br>New Members = $NewMembers\n";
				echo "<br>Linked Cards= $LinkedCards\n";

				echo "</BODY></HTML>";
			}
	}

	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

	?>