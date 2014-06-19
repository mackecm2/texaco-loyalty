	<?php
error_reporting( E_ALL );

	//******************************************************************
	//
	// FileProcess.php
	// Reads in a file from compower and inserts the data into the database
	//
	// Requires the code in WriteAlgo.php to have been run first which
	// creates the file Calculate.inc.  This contains the code to calculate
	// bonus points from rules in the database.
	//
	//  MRM 07/05/2008 - Spelling mistakes corrected
	//
	//******************************************************************

	function MTVTransactionInsert( $gUserData, $gSiteData, $fileNameOnly, $ProcessName )
	{
		global $gStatsData, $gTransactionData;
		
		global $ThisMonth;

		$results = InsertTransaction( "MTV", $fileNameOnly );

		// Store for latter use
		if( $results )
		{
			$gTransactionData->starValueCurrency = $gTransactionData->transValue; 
			$gStatsData->valueProcessed += $gTransactionData->transValue;
			$gStatsData->transactionsProcessed++;
			return true;
		}
		else
		{
			return false;
		}
	}

	function MTVprocessTransaction( $line, $fileNameOnly )
	{
		global $gStatsData, $expectedLen;
		global $fileToProcess, $ProcessName, $IIN;
		global $gTransactionData, $gUserData, $gSiteData;

		if( strlen( $line ) != 67 )
		{
			//LogError( "Transaction Record format issue - line not $expectedLen characters.  Received Length = " .  strlen( $line )) ;
			LogError( "Transaction Record format issue - line not 67 characters.  Received Length = " .  strlen( $line )) ;
			return false;
		}
		else
		{
			// Reset the transaction information

			$gTransactionData->transactionNo = 0;
			$gTransactionData->productCount = 0;
			$gTransactionData->starValueCurrency = 0;
			$gTransactionData->bonusPoints = 0;
			$gTransactionData->bonusSeq = 0;
			
			$cardNumber = substr( $line , 11 , 19 );
			$siteCode   = substr( $line , 30, 6  );
			$gTransactionData->Month = substr( $line , 36, 6 );
			$gTransactionData->transDate  = substr( $line , 36, 4 ) . '-' . substr( $line , 40, 2 ). '-'. substr( $line , 42, 2 );
			$gTransactionData->EFTTransNo  = substr( $line, 40, 4 );
			$gTransactionData->transValue = substr( $line , 50, 7 ) * 100;

//			echo "Transaction Data \n";
//			echo " cardNumber = $cardNumber\n";
//			echo " mercNumber = $siteCode\n";
//			echo " transDate  = $gTransactionData->transDate  \n";
//			echo " compInd    = $compInd	\n";
//			echo " transTime  = $gTransactionData->transTime	\n";
//			echo " transValue = $gTransactionData->transValue \n";
//			echo " filler     = $filler		\n";
//			echo " Month = $gTransactionData->Month \n";
//			echo " CardCode   = $gTransactionData->cardCode	\n";
//			echo " Flag       = $gTransactionData->flag			\n";
//			echo " PANKey     = $gTransactionData->PANKey		\n";
			
			
			// SDT Mantis 742
			// MTV can now accept bonus information where the Account is credited but it is not recorded as a transaction.
			
			$TxType = substr( $line , 60, 1 );
			$TrackingCode = substr( $line , 61, 4 );
			$totalPoints = substr( $line , 50, 7 );
			

			switch( $TxType )
			{
				case 'A':
		
					#echo "we have an Account Credit with Tracking Code $TrackingCode<br>";

					// See if card to link to field is set and if so if it exists in DB

					$sql = "Select Cards.MemberNo, AccountNo from Cards left Join Members using( MemberNo ) where CardNo = '$cardNumber'";
					# echo "<br>sql is $sql";
					$results = DBQueryLogOnFailure( $sql );

					if( mysql_num_rows($results) == 0 )
					{
						LogError( "Card $cardNumber not found" );
						break;
					}
					else
					{
						$row = mysql_fetch_assoc( $results );
						if( $row["MemberNo"] != "" )
						{
							$AccountNo = $row["AccountNo"];
							$MemberNo = $row["MemberNo"];
						}

					}

					// first credit the Account
					
					// Update the associated account with the data.
					$sql = "Update Accounts set Balance = Balance + $totalPoints where AccountNo = $AccountNo";

					$results = DBQueryLogOnFailure( $sql );
					
					
					// See if the tracking code exisits in the db

					$sql = "Select TrackingCode from TrackingCodes where TrackingCode = '$TrackingCode'";

					$results = DBQueryLogOnFailure( $sql );

					if( mysql_num_rows($results) == 0 )
					{
						LogError( "TrackingCode - $TrackingCode not found" );
						break;
					}
					else
					{

						$sql = "insert into Tracking set MemberNo = $MemberNo, AccountNo = $AccountNo, TrackingCode = $TrackingCode,Stars = $totalPoints, Notes = 'Batch Bonus import for cardno $cardNumber',CreationDate = now(),CreatedBy='BatchLoad'";
						# echo "<br>sql is $sql";
						$results = DBQueryLogOnFailure( $sql );

					}
					
			
				break;
				
				
				case 'M':
				default:
					// Check if the transaction already exists

					if( checkForDuplicate( $cardNumber, $siteCode, "MTV" ) )
					{
						// Check SiteNo
						// Used to throw away transactions that had unknown sites in but the
						// site data has proven to un reliable so now check site number will
						// log a warning

						CheckSiteNumber($siteCode, "SiteCode");

						if( checkCardNumber( $cardNumber, true ) )
						{

							// need to calculate points but this needs the products details

							MTVTransactionInsert( $gUserData, $gSiteData, $fileNameOnly, $ProcessName );

							return true;
						}
					}			

				break;
				
			
			
			} // end switch ( $TxType )
			
		

		}
	}


	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

//	echo "$filePath$filePattern\n";


	function MTVFile($lfileToProcess)
	{

		global $lineNo, $ProcessName, $Batch, $uname;
		global $gTransactionData, $gUserDate, $gProductData;
		global $gDeptmentData, $gSiteData, $gStatsData;
		global $fileToProcess;
		
		$ProcessName   = "MTV";
		$fileToProcess = $lfileToProcess;
		$uname = "MTV";

		$fileMove =  LocationFileProcessing."Processed/MTV/";
		
			//connectToDB( MasterServer, TexacoDB );
			echo "<HTML><HEAD></HEAD><BODY>";
			echo "<BR>*****************************************************\n";
			echo "<BR> MTV $fileToProcess\n";
			echo "<BR>*****************************************************\n";

			$gTransactionData = new TransactionClass();
			$gUserDate = new UserDataClass();
			$gProductData = new ProductClass();
			$gDeptmentData = new DeptClass();
			$gSiteData = new SiteClass();
			$gStatsData = new StatsClass();
			
			//necessary to set the range of acceptable months for Bonuses and Products and Transactions
			GetThisMonth();

			$fileNameOnly = basename($fileToProcess);
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
					while( $line = fgets( $fr ) )
					{
						$lineNo++;
						//echo $line;
						$success = MTVprocessTransaction( $line, $fileNameOnly );
						if( $success )
						{
							UpdatePoints( false  );
						}
						if( $lineNo % 1000 == 0 )
						{
							$t = $gStatsData->transactionsProcessed;
							echo "$lineNo Lines Processed $gStatsData->productsProcessed, $gStatsData->transactionsProcessed, $t\r\n";
						}
					}

					UpdateRecordsProcessed( $fileRec, $gStatsData );
					DisplayStats( $gStatsData );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
				echo "</BODY></HTML>";
			}
	}



	?>