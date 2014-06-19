	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**  Processing File
	**
	********************************************************************/



	//******************************************************************
	//
	// /FileProcessing/UKFuels/FileProcess.php
	// Reads in a file from UK Fuels and inserts the data into the database
	//
	// Requires the code in WriteAlgo.php to have been run first which
	// creates the file Calculate.inc.  This contains the code to calculate
	// bonus points from rules in the database.
	//
	//	 GRANT insert on texaco.NewErrorLog to UKFuelsProcess@localhost
	//
    //  MRM 29/04/08 - Made the error messages a bit more meaningful
    //  MRM 07/05/2008 - Spelling mistakes corrected
    //  MRM 27/05/08 - "Test Error Log" echo commented
    //  MRM 27/05/08 - "LogWarning( "UK Site code $siteNo not recognised" )" changed to "LogWarning( "UK Fuels MID $siteNo not recognised" )"
    //  MRM 30/05/08  - changes from around line 333 for Mantis 457
	//
	//******************************************************************

	$db_user = "UKFuelsProcess";
	$db_pass = "UKPassword";
	include "../General/Calculate.php";
	include "../General/BonusFuncs.php";
	include "../General/ProductAlocation.php";
	include "../General/DatabaseUpdate.php";
	include	 "../General/classes.php";
	include "../../include/Locations.php";
	include "../../include/DB.inc";
	include "../../DBInterface/FileProcessRecord.php";
	include "../../DBInterface/ExposureInterface.php";
 	require "../../DBInterface/CardInterface.php";

	$filePath =  LocationUKFuelsDirectory;
	$fileMove =  LocationFileProcessing."Processed/UKFuels/";
	$filePattern = "TXAC????????.DAT";
	$fileToProcess = "";
	$ProcessName   = "UKFUELS";

		//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 06/05/2008
	//*  echo date("Y-m-d H:i:s").' '.$_SERVER['PHP_SELF']." started \r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

	function CheckUKSiteNumber( $siteNo )
	{
		global $gSiteData;
		$sql = "Select SiteCode, RegionCode, AreaCode from sitedata where UKFuelsMID = '$siteNo'";

		$results = DBQueryExitOnFailure( $sql );
		$numrows = mysql_num_rows($results);
		if( $numrows >0 )
		{
			$row = mysql_fetch_assoc( $results );
			$gSiteData->siteCode = $row["SiteCode"];
			$gSiteData->regionID = $row["RegionCode"];
			$gSiteData->areaID = $row["AreaCode"];
			return true;
		}
		else
		{
			//* this section added for unknown codes from Sites that are undergoing change of ownership MRM 3/06/09 
		//
			$sql = "SELECT SiteNo AS SiteCode, RegionCode, AreaCode  FROM UKFMIDsites JOIN sites USING ( SiteNo ) WHERE UKFMIDsites.UKFuelsMID = '$siteNo'";
			$results = DBQueryExitOnFailure( $sql );
			$numrows = mysql_num_rows($results);
			if( $numrows >0 )
			{
				$row = mysql_fetch_assoc( $results );
				$gSiteData->siteCode = $row["SiteCode"];
				$gSiteData->regionID = $row["RegionCode"];
				$gSiteData->areaID = $row["AreaCode"];
				LogError( "** UKFMID $siteNo found in UKFMIDsites but not in sitedata.") ;
				return true;
			}
			else       // end of additional bit
			{
			$gSiteData->siteCode = $siteNo;
			$gSiteData->regionID = "-1";
			$gSiteData->areaID = "-1";
			LogWarning( "UK Fuels MID $siteNo not recognised" );
			return false;
			}
		}
	}

	function processHeaderLine( $line )
	{
		global $records, $totalValue, $fileDate, $expectedRecordLength;

		$expectedRecordLength = 78;
		$recordType = substr( $line, 0, 1 );
		$fileDate = substr( $line, 1, 8 );
		$pollerID = substr( $line, 9, 2 );
		if( $recordType == "H" && $pollerID == "CM" )
		{
			echo "Header Data\n";
			echo " Records = $records \n";
			echo " Value   = $totalValue \n";
			echo " Date    = $fileDate \n";
			return true;
		}
		return false;
	}

	function checkAccountNumber( $cardNumber )
	{
		$UKAccountNo = substr( $cardNumber, 6, 5 );

		$sql = "Select AccountCards.CardNo, MemberNo from AccountCards left join Cards using( CardNo) where GAccountNo = $UKAccountNo limit 1";

		$results = DBQueryLogOnFailure( $sql );
		// Store for latter use
		if( $results )
		{
			if( mysql_num_rows( $results ) > 0 )
			{
				$row = mysql_fetch_assoc( $results );
				if( $row["MemberNo"] != "" )
				{
					//  now see if the UKFuels number exists
					$sql = "select MemberNo from Cards where CardNo = '$cardNumber' ";
					$results = DBQueryLogOnFailure( $sql );
					if( $results )
					{
						if( mysql_num_rows( $results ) > 0 )
						{
							$row2 = mysql_fetch_assoc( $results );
							if( $row["MemberNo"] != $row2["MemberNo"] )
							{
								LogError( "Card $cardNumber moved from member $row[MemberNo] to $row2[MemberNo] " );
								$sql = "Update Cards set MemberNo = $row[MemberNo] where CardNo = '$cardNumber'";
								$results = DBQueryLogOnFailure( $sql );
							}
						}
						else
						{
							$sql = "Insert into Cards ( CardNo, MemberNo, CreatedBy, CreationDate ) values ( '$cardNumber', $row[MemberNo], 'UKFuels', now() )";
							$results = DBQueryLogOnFailure( $sql );
						}
						return true;
					}
				}
				else
				{
					LogWarning( "Account card $UKAccountNo linked to unlinked card $row[CardNo]" );
				}
			}
		}
		return false;
	}


	function processTrailer( $line )
	{
		global $records, $totalValue, $fileDate;
		if( strlen( $line ) != 32  && strlen( $line ) != 33  && strlen( $line ) != 78)
		{
			LogError( "Trailer line format issue - line not 32 characters.  Received Length = " .  strlen( $line ) ) ;
			return false;
		}
		else
		{
			$recordType = substr( $line, 0, 1 );
			$TotalVolume = substr( $line, 1, 9 );
			$filler1  = substr( $line, 10, 2 );
			$TotalValue = substr( $line, 12, 6 );
			$filler2  = substr( $line, 18, 2 );
			$fileDate  = substr( $line, 21, 8 );
			$pollerID  = substr( $line, 29, 2 );
		}
	}

	function ConvertToTable( $transDate )
	{
		return "20". substr( $transDate, 0 , 2 ) . substr( $transDate, 3, 2 );
	}

	function processTransaction( $line )
	{
		global $gStatsData, $expectedRecordLength;
		global $fileToProcess, $ProcessName, $IIN;
		global $gTransactionData, $gUserData, $gSiteData;

		if( strlen( $line ) != $expectedRecordLength )
		{
			LogError( "Transaction Record format issue - line not $expectedRecordLength characters.  Received Length = " .  strlen( $line )) ;
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
			$gTransactionData->transValue = 0;

			$gTransactionData->EFTTransNo = substr( $line , 0, 6 );

			$cardNumber = substr( $line , 6 , 19 );
			$siteCode   = substr( $line , 25, 6  );
			$transDate  = substr( $line , 31, 6 );
			$transTime	 = substr( $line , 37, 4 );

			$gTransactionData->transDate  = "20".substr( $transDate , 0, 2 )."-".substr( $transDate, 2, 2 )."-".substr( $transDate , 4, 2 );
			$gTransactionData->transTime	 = substr( $transTime, 0, 2 ). ":". substr( $transTime, 2, 2 );
	//		echo "Transaction Data \n";
	//		echo " cardNumber = $cardNumber\n";
	//		echo " mercNumber = $siteCode\n";
	//		echo " transDate  = $gTransactionData->transDate  \n";
	//		echo " transTime  = $gTransactionData->transTime	\n";
	//		echo " transValue = $gTransactionData->transValue \n";
	//		echo " EFTTransNo = $gTransactionData->EFTTransNo \n";

			$gTransactionData->Month = "20".substr($transDate,0,4);

			if( checkAccountNumber( $cardNumber ) )
			{
				// Check SiteNo
				if( CheckUKSiteNumber($siteCode ))
				{
					if( checkCardNumber( $cardNumber, false ) )
					{
						// Check if the transaction already exists
						// This has to be done after the card number check as this is
						// where account card numers are converted.

						if( checkForDuplicate( $cardNumber, $gSiteData->siteCode, "UKFUELS" ) )
						{
							$fileNameOnly = basename($fileToProcess);

							// need to calculate points but this needs the products details
							$results = InsertTransaction( "UKFuels", $fileNameOnly );

							// Store for latter use
							if( $results )
							{
								$gStatsData->transactionsProcessed++;
							}
							else
							{
								LogError( "Failed to insert transaction \n$sql\n" );
							}
							return true;
						}
					}
				}
			}
		}
	}

	function processProduct( $line )
	{
		global $gStatsData, $gProductData, $expectedRecordLength;
		global $gTransactionData, $gUserData;

		if( strlen( $line ) != $expectedRecordLength )
		{
			LogError( "Product Record format issue - line not $expectedRecordLength characters.  Received Length = " .  strlen( $line )) ;
			return false;
		}
		else
		{
			$Volume		 = substr( $line, 41, 5 );
			$productValue = substr( $line, 46, 5 );
			$productCode = substr( $line, 51, 2 );

	//		echo "Product Data\n";
	//		echo " productCode    = $productCode\n";
	//		echo " productValue   = $productValue\n";
	//		echo " Volume         = $Volume\n";

			$gTransactionData->productCount++;
			$gTransactionData->transValue += $productValue;
			$gTransactionData->starValueCurrency += $productValue;

			$gStatsData->productsProcessed++;
			$gStatsData->valueProcessed += $productValue;

			$gProductData->code = $productCode;
			$gProductData->volume = $Volume;
			$gProductData->value = $productValue;
			$PointsAwarded = CalculateProductVolumeBonus( );
			$PointsAwarded += CalculateProductValueBonus( );
			ProductAllocate(  );

			return ProductInsertPurchase( $gTransactionData, $gProductData );
		}
	}




	// Main function
	//  We use globals for all the data because of the split in the code to
	// Auto generated code

	connectToDB( MasterServer, TexacoDB );

	GetThisMonth();

	$FilesToProcess = glob( $filePath . $filePattern );

	if( !$FilesToProcess )
	{
		echo "\r\n";
		echo "************************************************************\r\n";
		echo "***POSSIBLE ERROR !! ***  No UK Fuels Files found to process\r\n";
		echo "************************************************************\r\n";
		echo "\r\n";
		exit();
	}
	foreach (glob( $filePath . $filePattern ) as $fileToProcess )
	{
		//LogError( "Test Error Log"  );
		$gTransactionData = new TransactionClass();
		$gUserDate = new UserDataClass();
		$gProductData = new ProductClass();
		$gDeptmentData = new DeptClass();
		$gSiteData = new SiteClass();
		$gStatsData = new StatsClass();


		$fr = fopen( $fileToProcess, "r");
		$fails = 0;
		if(!$fr)
		{
		echo "\r\n";
		echo "************************************************************\r\n";
		echo "***ERROR !! ***  Could not open UK Fuels File ".$fileToProcess."\r\n";
		echo "************************************************************\r\n";
		echo "\r\n";
		LogError( "*** Error! Couldn't open the file." );
		}
		else
		{
			//Next bit added MRM 30/5/08 for Mantis 457
			$goodUKSiteNumbers = true;
			while(! feof($fr))
			{
				$line = fgets($fr);
				if( substr( $line, 0, 1 ) == "0" )
				{
					$siteCode   = substr( $line , 25, 6  );	
					if(CheckUKSiteNumber($siteCode) == false)
					{
						$goodUKSiteNumbers = false;
					}
				}
  			}
			if ($goodUKSiteNumbers)
			{
				fseek($fr, 0);
				echo "All UK Fuels MIDs good.\r\n";
			// end of MRM's bit
				$fileRec = createFileProcessRecord($fileToProcess);
				if( $fileRec )
				{
					// $fr now can be used to represent the opened file
					$line = fgets( $fr );
					if( processHeaderLine( $line ) )
					{
						$success = false;
						$lineNo = 1;
						$prev = "";
						while( $line = fgets( $fr ))
						{
							$lineNo++;
							if( substr( $line, 0, 1 ) == "T" )
							{
								if( $success )
								{
									UpdatePoints( true );
								}
								processTrailer( $line );
							}
							else
							{
								if( substr( $line, 0, 42 ) != substr( $prev, 0, 42 ) )
								{
									if( $success )
									{
										UpdatePoints( true );
									}
									$success = processTransaction( $line );
								}
								if( $success )
								{
									$success = processProduct( $line );
								}
								if( !$success )
								{
									if( $fails == 0 )
									{
										$failedrows = fopen( $filePath."Failed".basename($fileToProcess), "w+" );
									}
									fputs( $failedrows, $line );
									$fails++;
								}
							}
							$prev = $line;
						}
						echo "UK Fuels FileProcess.php Summary data\n";
						echo " productsProcessed = $gStatsData->productsProcessed\n";
						echo " transactionsProcessed = $gStatsData->transactionsProcessed\n";
						echo " valueProcessed = $gStatsData->valueProcessed \n";
						echo " Records = $records \n";
						echo " Value   = $totalValue \n";

					}
					UpdateRecordsProcessed( $fileRec, $gStatsData );
				}
			}
		}
		fclose($fr);
		if ($goodUKSiteNumbers)
		{
			echo "renaming the file\r\n";
			rename( $fileToProcess, $fileMove . basename($fileToProcess) );
		}
		else 
		{
			echo " Bad UK MIDs found; File ". $fileToProcess . " not processed.\n";
		}
		if( $fails != 0 )
		{
			fclose($failedrows);
		}
	}

	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>