	********************************************************************
	**
	**  Processing File
	**
	********************************************************************
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
	//	 GRANT insert on texaco.NewErrorLog to UKFuelsProcess@localhost
	//
	//  MRM 07/05/2008 - Spelling mistakes corrected
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

	#$filePath =  LocationUKFuelsDirectory;
	#$filePath =  LocationFileProcessing."Processed/UKFuels/";
	$filePath =  "/data/ukfuels/";
	
	// $filePattern = "TXAC????????.DAT";
	$filePattern = "TXAC20060616.DAT";
	$fileToProcess = "";
	$ProcessName   = "UKFUELS-RE";
	
	echo "File is $filePath$filePattern\n";

	function CheckUKSiteNumber( $siteNo )
	{
		global $gSiteData;

		$sql = "Select SiteCode, RegionCode, AreaCode from sitedata where UKFuelsMID = '$siteNo'";
		echo"$sql\n";
		$results = DBQueryExitOnFailure( $sql );
		$numrows = mysql_num_rows($results);
		if( $numrows == 1 )
		{
			$row = mysql_fetch_assoc( $results );
			$gSiteData->siteCode = $row["SiteCode"];
			$gSiteData->regionID = $row["RegionCode"];
			$gSiteData->areaID = $row["AreaCode"];
			return true;
		}
		else
		{
			$gSiteData->siteCode = $siteNo;
			$gSiteData->regionID = "-1";
			$gSiteData->areaID = "-1";
			LogWarning( "UK Site code $siteNo not recognised" );
			return false;
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
		global $CreationDate;
		$UKAccountNo = substr( $cardNumber, 6, 5 );
		
		$sql = "Select AccountCards.CardNo, MemberNo, AccountCards.CreationDate from AccountCards left join Cards using( CardNo) where GAccountNo = $UKAccountNo and Active = 'Y' limit 1";

		$results = DBQueryLogOnFailure( $sql );
		// Store for latter use
		if( $results )
		{
			if( mysql_num_rows( $results ) > 0 )
			{
				$row = mysql_fetch_assoc( $results );

				$CreationDate = $row["CreationDate"];
				if( $CreationDate < "2004-12-09" )
				{
					$CreationDate = "2004-10-22";
				}

				if( $row["MemberNo"] != "" )
				{
					//  now see if the UKFuels number exists
					$sql = "select MemberNo from Cards where CardNo = '$cardNumber' ";
					echo"$sql\n";
					
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
		if( strlen( $line ) != 32  && strlen( $line ) != 33 )
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
		global $CreationDate;
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
			echo "Transaction Data \n";
			echo " cardNumber = $cardNumber\n";
			echo " mercNumber = $siteCode\n";
			echo " transDate  = $gTransactionData->transDate  \n";
			echo " transTime  = $gTransactionData->transTime	\n";
			#echo " transValue = $gTransactionData->transValue \n";
			echo " EFTTransNo = $gTransactionData->EFTTransNo \n";

			$gTransactionData->Month = "20".substr($transDate,0,4);

			if( $gTransactionData->Month > 200410 )
			{
				echo "Here\n";
				if( checkAccountNumber( $cardNumber ) )
				{
					echo "Here1\n";
					if( $CreationDate < $gTransactionData->transDate )
					{
 						echo "Here2\n";
 						// Check SiteNo
						if( CheckUKSiteNumber($siteCode ))
						{
							echo "Here3\n";
							if( checkCardNumber( $cardNumber, false ) )
							{
								// Check if the transaction already exists
								// This has to be done after the card number check as this is 
								// where account card numers are converted.
								echo "Here4\n";
								if( checkForDuplicate( $cardNumber, $gSiteData->siteCode, "UKFUELS" ) )
								{
								
									echo "Here5\n";
									$fileNameOnly = basename($fileToProcess);

									// need to calculate points but this needs the products details
									$results = InsertTransaction( "UKFuels", $fileNameOnly );

									// Store for latter use
									if( $results )
									{
										$gStatsData->transactionsProcessed++;
										echo "Transaction Added $cardNumber, $gTransactionData->bonusPoints"; 
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
					else
					{
						echo "Transaction Before Account Registered $cardNumber\n" ;
					}
				}
			}
		}
		return false;
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
		echo "No Files found to process";
		exit();
	}
	foreach (glob( $filePath . $filePattern ) as $fileToProcess )
	{
		LogError( "Test Error Log"  );
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
			LogError( "Error! Couldn't open the file." );
		} 
		else 
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
					echo "sumarry data\n";
					echo " productsProcessed = $gStatsData->productsProcessed\n";
					echo " transactionsProcessed = $gStatsData->transactionsProcessed\n";
					echo " valueProcessed = $gStatsData->valueProcessed \n";
					echo " Records = $records \n";
					echo " Value   = $totalValue \n";

			}
		}
		fclose($fr);
		if( $fails != 0 )
		{
			fclose($failedrows);
		}
	}


?>