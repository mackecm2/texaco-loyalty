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
	// Requires the code in WriteAlgo.php to hav	e been run first which
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

	require "../General/misc.php";
	require "../../DBInterface/FileProcessRecord.php";
	require "../../DBInterface/ExposureInterface.php";

	$filePath =  LocationFileProcessing;

	$ProcessName   = "COMPOWER";

	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

//	echo "$filePath$filePattern\n";
	echo "Version 1.7\n";
	connectToDB();

	
	echo 'Creating Exposure point';
	$InitialBalance = CreateExposurePoint( "Prior to Bonus file load October30Bonus" );

	$fileRec = createFileProcessRecord("October30Bonus");
	if( $fileRec )
	{
		echo "\nStart Selection\n";
		$addedPoints = 0;
		$sql = "SELECT * from Transactions200410 where TransValue >= 30.00";

		$transactions = mysql_query( $sql ) or die( mysql_error() . $sql );

		echo "Process transactions ". mysql_num_rows( $transactions ). " to process\n";
		while( $transactionrow = mysql_fetch_assoc( $transactions ) )
		{
			$sql = "Select Accounts.AccountNo, AwardStopDate is null as CardOK from Cards join Members using(MemberNo) Join Accounts Using(AccountNo) where CardNo = '$transactionrow[CardNo]'";

			$Accounts = mysql_query( $sql )	or die( mysql_error() . $sql );

			$useAccount = false;

			if( mysql_num_rows( $Accounts ) > 0 )
			{
				$account = mysql_fetch_assoc( $Accounts );
				if( $account["AccountNo"] != 0 and $account["AccountNo"] != "" and $account["CardOK"] == 1 )
				{
					$useAccount = true;
				}
			}

			if( $useAccount )
			{
				$sql = "Update Accounts Set Balance = Balance + 20 where AccountNo = $account[AccountNo]";	
				$Accounts = mysql_query( $sql )	or die( mysql_error() . $sql );
			}
			else
			{
				$sql = "Update Cards Set StoppedPoints = StoppedPoints + 20 where CardNo = '$transactionrow[CardNo]'";
				$Accounts = mysql_query( $sql )	or die( mysql_error() . $sql );
			}

			$sql = "INSERT into BonusHit200410( TransactionNo, SequenceNo,  PromotionCode, Points ) values ( $transactionrow[TransactionNo], 3, 'October30', 20 )";

			mysql_query( $sql )	or die( mysql_error() . $sql );
			
			$sql = "Update Transactions200410 set PointsAwarded = PointsAwarded + 20 where TransactionNo = $transactionrow[TransactionNo]"; 

			mysql_query( $sql )	or die( mysql_error() . $sql );
			$addedPoints ++;

			if( $addedPoints % 1000 == 0 )
			{
				echo "$addedPoints Transactions Processed\n";
			}
		}
		$FinalBalance = CreateExposurePoint( 'After compower file load October30Bonus' );

		$Movement = $FinalBalance - $InitialBalance;

		echo "The effect on the balance was $Movement\n";
		echo "Added points = ". $addedPoints * 20;
		UpdateFileProcessRecord( $fileRec );
	}
	
	?>