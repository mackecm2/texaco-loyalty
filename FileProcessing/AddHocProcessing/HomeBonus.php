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
	//  
	//
	//******************************************************************

	$IIN = "707655";
	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";
//	$db_pass = "";

	require "../../include/Locations.php";
	require "../General/misc.php";
	require "../General/classes.php";
	require "../General/BonusFuncs.php";
	require "../General/ProductAlocation.php";
	require "../General/DatabaseUpdate.php";
	require "../../DBInterface/FileProcessRecord.php";
	require "../../DBInterface/ExposureInterface.php";

	$filePath =  LocationFileProcessing;
	//$filePath =  "C:/Projects/SampleData/";

	$fileMove =  LocationFileProcessing."Processed/Compower/";
	$filePattern = "0411Bonus Homesite Cards.csv";
//	$filePattern = "SmallBonusBatch.csv";
	$fileToProcess = "";
	$ProcessName   = "COMPOWER";

	function processHeaderLine( $fields )
	{
		global $cardNumberCol, $StartDateCol, $EndDateCol;
/*		print_r( $fields );
		$fieldHeaders = array();
		foreach( $fields as $key => $header )
		{
			$fieldHeaders[$header] = $key;
		}

		if( !isset( $fieldHeaders["CARD_NO"] ) ) 
		{
			logError( "Missing column name CardNo" );

			return false;
		}

		if( !isset( $fieldHeaders["START"] ))
		{
			logError( "Missing column name Start" );

			return false;
		}

//		if( !isset( $fieldHeaders["End"] ))
//		{
//			logError( "Missing column name End" );
//
//			return false;
//		}


		$cardNumberCol = $fieldHeaders["CARD_NO"];
		$StartDateCol   = $fieldHeaders["START"]; 
	//	$EndDateCol  = $fieldHeaders["End"];
*/
		$cardNumberCol = 0;
		$StartDateCol   = 1; 
		$EndDateCol    = 2;
		return true;
	}

	function processCard( $fields )
	{
		global $gStatsData;
		global $fileToProcess, $ProcessName;
		global $cardNumberCol, $StartDateCol, $EndDateCol;


		$cardNumber = $fields[$cardNumberCol];
		$startDate  = $fields[$StartDateCol];
		$endDate  =  $fields[$EndDateCol];

			// Reset the transaction information

		echo "<BR>$cardNumber";

		$datearr = explode('/', $startDate);

		$startDate = date('Y-m-d', mktime(0,0,0, $datearr[1], $datearr[0], $datearr[2]));

		$datearr = explode('/', $endDate);

		$endDate = date('Y-m-d', mktime(0,0,0, $datearr[1], $datearr[0], $datearr[2]));

		echo $startDate;

		$sql = "Select Members.AccountNo, Cards.MemberNo, isnull(AwardStopDate) as CardStopped from Cards left join Members Using(MemberNo) left join Accounts Using(AccountNo) where Cards.CardNo = '$cardNumber'"; 

		$resultscard = mysql_query( $sql ) or die( mysql_error() . $sql );

		if( mysql_num_rows( $resultscard ) > 0 )
		{
			$cardData = mysql_fetch_assoc( $resultscard );
			if( !isset( $cardData["MemberNo"] ) or $cardData["MemberNo"] == ""  or $cardData["MemberNo"] == 0 )
			{
				logError( "Member not found $cardNumber" );
				return;
			}
			else
			{
				$MemberNo = $cardData["MemberNo"];
				echo " MemberNo $MemberNo "; 
				$sql = "Select * from PersonalCampaigns where MemberNo = $MemberNo and PromotionCode = 'SITECLS01'";

				$results = mysql_query( $sql ) or die( mysql_error() . $sql );
				if( mysql_num_rows( $results ) > 0)
				{
					$existingRow = mysql_fetch_assoc( $results );
					logError( "Duplicate Entry $cardNumber, $existingRow[StartDate] ($startDate)" );
					return;
				}
	
				$sql = "Insert into CampaignHistory ( MemberNo, AccountNo, CampaignType, CampaignCode, CreationDate, CreatedBy ) values ( $MemberNo, $cardData[AccountNo], 'SITECLOSE', 'SITECLS01', '$startDate', 'LateBonus' )";

				$SpendValue = 0;

				$results = mysql_query( $sql ) or die( mysql_error() . $sql );

				$sql = "Select * from Transactions where CardNo = '$cardNumber' and TransTime between '$startDate 00:00:00' and '$endDate 23:59:59'";

				$results = mysql_query( $sql ) or die( mysql_error() . $sql );
				// Store for latter use
				if( mysql_num_rows( $results ) > 0)
				{

					$addPoints = 0;

				
					while( $row = mysql_fetch_assoc( $results ) )
					{
						$HitsLeft--;

						$Month = substr( $row["TransTime"], 0, 4 ) . substr( $row["TransTime"], 5, 2 );
						$addPoints += $row["PointsAwarded"];

						$SpendValue = $SpendValue + $row["TransValue"];

						$sql = "Update Transactions$Month set PointsAwarded = PointsAwarded * 2 where TransactionNo = $row[TransactionNo]";
						
						mysql_query( $sql ) or die( mysql_error() . $sql );

						if( $row["CreatedBy"] == "COMPOWER" )
						{
							$sql = "INSERT into BonusHit$Month ( TransactionNo, SequenceNo , PromotionCode, Points ) values ( $row[TransactionNo], 1, 'WELCOME25', $row[PointsAwarded] )";

							if( !mysql_query( $sql ))
							{	
								echo mysql_error() . $sql;
							}
							echo "BH";
						}
					}

					if( $cardData["CardStopped"] == 0 or $cardData["AccountNo"] == "" )
					{
						echo "Points added to card $addPoints";
						$sql = "Update Cards set StoppedPoints = StoppedPoints + $addPoints where CardNo = '$cardNumber' ";
					}
					else
					{
						echo "Points added to account $addPoints";
						$sql = "Update Accounts set Balance = Balance + $addPoints where AccountNo = $cardData[AccountNo]";
					}

					echo $sql;
					if( !mysql_query( $sql ))
					{	
						echo mysql_error() . $sql;
					}
				}

				$sql = "Insert into PersonalCampaigns (MemberNo, PromotionCode, StartDate, EndDate, PeriodSpend, PromoHitsLeft, CreationDate, CreatedBy ) values ( $MemberNo, 'SITECLS01', '$startDate', '$endDate' , $SpendValue, -1, now(), 'HomeBonus' )";
				if( !mysql_query( $sql ))
				{	
					echo mysql_error() . $sql;
				}
			}
		}
		else					
		{
			logError( "Failed to find card $cardNumber" );
		}

	}





	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

//	echo "$filePath$filePattern\n";
	echo "Version 1.7\n";
	connectToDB();

	$fileList = glob( $filePath . $filePattern );
	if( !$fileList )
	{
		echo "No files to process\r\n";
	}
	else
	{
		foreach ( $fileList as $fileToProcess )
		{

			$gTransactionData = new TransactionClass();
			$gUserDate = new UserDataClass();
			$gProductData = new ProductClass();
			$gDeptmentData = new DeptClass();
			$gSiteData = new SiteClass();
			$gStatsData = new StatsClass();

			echo "$fileToProcess\r\n";

			$fr = fopen( $fileToProcess, "r");

			if(!$fr) 
			{
				echo "Error! Couldn't open the file.\r\n";
			} 
			else 
			{
				echo 'Creating Exposure point';
				$InitialBalance = CreateExposurePoint( "Prior to Bonus file load" .basename($fileToProcess) );

				$fileRec = createFileProcessRecord($fileToProcess);
				if( $fileRec )
				{
					// $fr now can be used to represent the opened file
					$line = fgetcsv( $fr, 2048);
					if( processHeaderLine( $line ) )
					{
						$success = false;			
						$lineNo = 1;

						while( $line = fgetcsv( $fr, 2048) )
						{
							$lineNo++;
							//echo $line;
							$success = processCard( $line );
						}
					}
					echo 'Creating Exposure point';
					$FinalBalance = CreateExposurePoint( 'After compower file load' .basename($fileToProcess) );

					$Movement = $FinalBalance - $InitialBalance;

					echo "The effect on the balance was $Movement\n";
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
			}
		}
	}
	
	?>