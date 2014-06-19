	********************************************************************
	**
	**  Processing File
	**
	********************************************************************
	
	<?php

	//******************************************************************
	//
	// FileProcess.php
	// Reads in a file from compower and inserts the data into the database
	//
	// Requires the code in WriteAlgo.php to have been run first which
	// creates the file Calculate.inc.  This contains the code to calculate
	// bonus points from rules in the database.
	//
	//  	MRM 07/05/2008 - Spelling mistakes corrected
	//
	//******************************************************************

	$IIN = "123456";
	$db_host = "localhost";
	$db_name = "Texaco";
	$db_user = "root";
	$db_pass = "FLOWER";
	$fileToProcess = "C:\\projects\\Texaco\\SampleData\\SmallTest.txt";
	$ProcessName   = "COMPOWER";
	$gPopulate = false;

	include "C:\\projects\\Texaco\\Calculate.inc";


	class TransactionClass
	{
			var $cardNo;
			var $siteCode;
			var $transDate;
			var $transTime;
			var $transValue;
			var $EFTTransNo;

			var $transactionNo = 0;
			var $productCount = 0;
			var $starValueCurrency = 0;
			var $bonusPoints = 0;
	}

	class UserDataClass
	{
		var $accountNo;
		var $memberNo;
		var $latestSwipe;
	}

	class ProductClass
	{
		var $code;
		var $volume;
		var $value = 0;
		var $bonuskey;
	}

	function connectToDB()
	{
		global $con, $db;
		global 	$db_host, $db_name, $db_user, $db_pass;
		$con= @mysql_connect("$db_host","$db_user","$db_pass")
			or die ("Cannot connect to MySQL.");

		$db = @mysql_select_db("$db_name",$con)
			or die ("Cannot select the $db_name database. Please check your details in the database connection file and try again");

	}

	function logError($error)
	{	
		global $con, $db, $fileToProcess, $lineNo;
		echo "$error while processing line $lineNo in file'$fileToProcess'";
	}

	function processHeaderLine( $line )
	{
		global $records, $totalValue, $fileDate;
		if( strlen( $line ) != 55 )
		{
			logError( "Header line format issue - line not 55 characters.  Recieved Length = " .  strlen( $line ) ) ;
			return false;
		}
		else
		{
			$records = substr( $line, 0, 7 );
			$totalValue = substr( $line, 7, 9 );
			$fileDate = substr( $line, 16, 8 );
			echo "Header Data\n";
			echo " Records = $records \n";
			echo " Value   = $totalValue \n";
			echo " Date    = $fileDate \n";
			// Don't bother checking the fillers are all zero.
			return true;
		}
	}

	function checkSiteNumber( $siteNo )
	{
		global $gPopulate;

		$sql = "Select * from sites where SiteCode = $siteNo";

		$results = mysql_query( $sql );
		$numrows = mysql_num_rows($results);

		if( $numrows == 1 )
		{
			return true;
		}
		else
		{
			logError( "Site number $siteNo not recognised" );

			if( $gPopulate )
			{
				$sql = "INSERT into Sites (SiteCode, SiteName, CreationDate, CreatedBy ) values ( $siteNo, 'Test Site', now(), 'FileProcess.php')";
				$results = mysql_query( $sql );
				return true;
			}

			return false;
		}
	}


	function checkForDuplicate( $match )
	{
		global $fileToProcess;

		$sql = "Select * from Transactions where $match"; 

		$results = mysql_query( $sql );
		$numrows = mysql_num_rows($results);

		if( $numrows == 0 )
		{
			return true;
		}
		else
		{
			
			echo "Duplicate record \n";
			// compare the existing transaction and where it came from.
			$row = mysql_fetch_assoc( $results );
			$fileNameOnly = basename($fileToProcess);
			if( $row['InputFile'] == $fileNameOnly )
			{
				echo "File already processed!";
			}
			else if( $row['InputFile'] != 'null' )
			{
				logError( "Transaction already presented in file." );
			}
			else
			{
				// We should compare the fields except there are hardly any that overlap that
				// haven't already been compared to make the link.

				$sql = "Update Transactions set InputFile=$fileNameOnly where TransactionNo = $row[TransactionNo]";
				$results = mysql_query( $sql );
			}

			return false;
		}
	}

	function processTransaction( $line, &$TransactionData, &$UserData )
	{
		global $transactionsProcessed, $valueProcessed, $IIN;
		global $fileToProcess, $ProcessName;
		global $gPopulate;

		if( strlen( $line ) != 56 )
		{
			logError( "Transaction Record format issue - line not 56 characters.  Recieved Length = " .  strlen( $line )) ;
			return false;
		}
		else
		{
			// Reset the transaction information

			$TransactionData->transactionNo = 0;
			$TransactionData->productCount = 0;
			$TransactionData->starValueCurrency = 0;
			$TransactionData->bonusPoints = 0;

			$TransactionData->cardNo     = $IIN .$custNumber = substr( $line , 0 , 13 );
			$TransactionData->siteCode   = substr( $line , 13, 6  );
			$TransactionData->transDate  = substr( $line , 19, 8 );
			$compInd					 = substr( $line , 27, 1 );
			$TransactionData->transTime	 = substr( $line , 28, 5 );
			$TransactionData->transValue = substr( $line , 33, 7 );
			$filler						 = substr( $line , 40, 3 );
			$TransactionData->EFTTransNo = substr( $line , 43, 6 );
			$CardCode	= substr( $line , 49, 3 );
			$Flag		= substr( $line , 52, 1 );
			$PANKey		= substr( $line , 53, 1 );

			echo "Transaction Data \n";
			echo " cardNumber = $TransactionData->cardNo\n";
			echo " mercNumber = $TransactionData->siteCode\n";
			echo " transDate  = $TransactionData->transDate  \n";
			echo " compInd    = $compInd	\n";
			echo " transTime  = $TransactionData->transTime	\n";
			echo " transValue = $TransactionData->transValue \n";
			echo " filler     = $filler		\n";
			echo " EFTTransNo = $TransactionData->EFTTransNo \n";
			echo " CardCode   = $CardCode	\n";
			echo " Flag       = $Flag		\n";
			echo " PANKey     = $PANKey		\n";


			// Check if the transaction already exists
			
			if( checkForDuplicate( "CardNo = $TransactionData->cardNo and SiteCode = $TransactionData->siteCode and TransTime = '$TransactionData->transDate $TransactionData->transTime'") )
			{
				// Check SiteNo

				if( checkSiteNumber($TransactionData->siteCode))
				{
	
					$sql = "Select Members.AccountNo, Cards.MemberNo, '$TransactionData->transDate $TransactionData->transTime' > Cards.LastSwipeDate or IsNull(LastSwipeDate) from Cards join Members Using( MemberNo) where Cards.CardNo = $TransactionData->cardNo"; 
					$results = mysql_query( $sql );
					$numrows = mysql_num_rows($results);
					if( $numrows == 1 )
					{
						if( $gPopulate )
						{
							return false;
						}
						$row = mysql_fetch_row( $results );

						$UserData->accountNo = $row[0];
						$UserData->memberNo  = $row[1];
						$UserData->latestSwipe = $row[2];
						$fileNameOnly = basename($fileToProcess);

						// need to calculate points but this needs the products details

						$sql = "Insert into Transactions ( CardNo, AccountNo, SiteCode, TransTime, TransValue, PanInd, PayMethod, InputFile, CreatedBy ) values ( $TransactionData->cardNo, $UserData->accountNo, $TransactionData->siteCode, '$TransactionData->transDate $TransactionData->transTime', $TransactionData->transValue, $PANKey, $CardCode, '$fileNameOnly', '$ProcessName' )";

						$results = mysql_query( $sql );

						// Store for latter use
						if( $results )
						{
							$TransactionData->transactionNo = mysql_insert_id();
							$valueProcessed = $valueProcessed + $TransactionData->transValue;
							$transactionsProcessed++;

							$TransactionData->starValueCurrency = $TransactionData->transValue; 
						}
						else
						{
							logError( "Failed to insert transaction \n$sql\n" );
						}
						return true;
					}
					else if( $numrows == 0 )
					{
						logError( "Card number '$TransactionData->cardNo' or associated account number  not recognised\n");
						if( $gPopulate )
						{
							$sql = "insert into Cards (CardNo, MemberNo, CreatedBy ) values ( $TransactionData->cardNo, 1, 'FileProcess.php' )";
							$results = mysql_query( $sql );
						}
					}
					else
					{
						logError( "Database integrity is suspect '$sql' produced $numrows results\n");
					}
				}
			}
		}
	}


	function DateRange( $startDate, $endDate )
	{
		global $gTransactionData;

		if( $gTransactionData->transDate >= $startDate and $gTransactionData->transDate <= $endDate )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function ProductRange( $lowProduct, $highProduct )
	{
		global $gProductData;
		
		if( $gProductData->code >= $lowProduct and $gProductData->code <= $highProduct )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function SiteRange( $lowSite, $highSite )
	{
		global $gTransactionData;
		if( $gTransactionData->SiteCode >= $lowSite and $gTransactionData->SiteCode <= $highSite )
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	function Bonuses( $points, $perQ, $entryKey, $exclude )
	{
		global $gProductData, $gTransactionData;

		if( $exclude )
		{
			$gTransactionData->starValueCurrency -= $gProductData->value;
		}

		$gProductData->entryKey = $entryKey;

		$bonuses = IntVal( $gProductData->volume / $perQ) * $points;

		if( $bonuses > 0 )
		{
			$gTransactionData->$bonusPoints+= $bonuses;

			$sql = "UPDATE BonusPoints set PointsCostTD = PointsCostTD + $bonuses where BonusEntry = $entryKey";

			$results = mysql_query( $sql );
		}
		return $bonuses;
	}

	function processProduct( $line, &$TransactionData, &$UserData )
	{
		global $productsProcessed, $gProductData;
		if( strlen( $line ) != 51 )
		{
			logError( "Product Record format issue - line not 49 characters.  Received Length = " .  strlen( $line )) ;
			return false;
		}
		else
		{
			$seqNum		 = substr( $line, 1, 2 );
			$productCode = substr( $line, 3, 2 );
			$productValue = substr( $line, 5, 7 );
			$Space		 = substr( $line, 12, 1 );
			$Volume		 = substr( $line, 13, 7 );
			$Filler		 = substr( $line, 20, 23 );
			$EFTransNo	 = substr( $line, 43, 6 );

			echo "Product Data\n";
			echo " seqNum         = $seqNum\n";
			echo " productCode    = $productCode\n";
			echo " productValue   = $productValue\n";
			echo " Space          = $Space\n";
			echo " Volume         = $Volume\n";
			echo " Filler         = $Filler\n";
			echo " EFTransNo      = $EFTransNo\n";
			$productsProcessed++;

			$TransactionData->productCount++;
			if( $TransactionData->productCount != $seqNum )
			{
				logError( "Product sequence number out of sequence" );
				return false;
			}

			$gProductData->code = $productCode;
			$gProductData->volume = $Volume;
			$gProductData->value = $productValue;
			$PointsAwarded = CalculateProductBonus( );

			$sql = "Insert into ProductsPurchased( TransactionNo, SequenceNo, DepartmentCode, ProductCode, PointsAwarded, Quantity, Value ) values ( $TransactionData->transactionNo, $TransactionData->productCount, 0, $productCode, 0, $Volume, $productValue)";

			$results = mysql_query( $sql );

			return true;
		}
	}

	function UpdatePoints( $TransactionData, $userData )
	{
		// Calculate the total points
		$totalPoints = IntVal( $TransactionData->starValueCurrency / 100 ) + $TransactionData->bonusPoints;
		echo "Here---------------------------------------$totalPoints------------------------\n";

		// Update the transaction in the transaction log
		$sql = "Update Transactions set PointsAwarded = $totalPoints where TransactionNo = $TransactionData->transactionNo";

		$results = mysql_query( $sql );
		
		// Update the Cards details for swipes
		if( $userData->latestSwipe == 1 )
		{
			$updateLoc = ", lastSwipeLoc = $TransactionData->siteCode, lastSwipeDate = '$TransactionData->transDate $TransactionData->transTime'"; 
		}
		else
		{
			$updateLoc = "";
		}
		$sql = "Update Cards set TotalSwipes = TotalSwipes + 1 $updateLoc where CardNo = $TransactionData->cardNo";

		$results = mysql_query( $sql );

		// Update the associated account with the data.
		$sql = "Update Accounts set Balance = Balance + $totalPoints where AccountNo = $userData->accountNo";

		$results = mysql_query( $sql );

	}


	// Main function
	$gTransactionData = new TransactionClass();
	$gUserDate = new UserDataClass();
	$gProductData = new ProductClass();

	echo "$fileToProcess\n";

	$fr = fopen( $fileToProcess, "r");


	if(!$fr) 
	{
		echo "Error! Couldn't open the file.";
	} 
	else 
	{
		connectToDB();

		// $fr now can be used to represent the opened file
		$line = fgets( $fr );
		if( processHeaderLine( $line ) )
		{
			$success = false;			
			$productsProcessed = 0;
			$transactionsProcessed = 0;
			$valueProcessed = 0;
			$lineNo = 1;

			while( $line = fgets( $fr ))
			{
				$lineNo++;
				echo $line;
				if( substr( $line, 0, 1 ) == "P" )
				{
					if( $success )
					{
						$success = processProduct( $line, $gTransactionData, $gUserDate );
					}
				}
				else
				{
					if( $success )
					{
						UpdatePoints( $gTransactionData, $gUserDate );
					}
					$success = processTransaction( $line, $gTransactionData, $gUserDate );
				}
			}
			if( $success )
			{
				UpdatePoints( $gTransactionData, $gUserDate );
			}
			echo "sumarry data\n";
			echo " productsProcessed = $productsProcessed\n";
			echo " transactionsProcessed = $transactionsProcessed\n";
			echo " valueProcessed = $valueProcessed \n";
			echo " Records = $records \n";
			echo " Value   = $totalValue \n";

		}
	}
	fclose($fr);


	?>