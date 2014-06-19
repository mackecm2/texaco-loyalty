	<?php
error_reporting( E_ALL );
/*
 * 
 * MRM 20 07 09 - Mantis 0001225: Double Points retro-fit
 * The process to retrospectively apply the bonus awards will be as follows:

	Read a transaction from the spreadsheet
	Find the matching transaction in Transactions200907
	Update the transaction record with double the original points
	Write a Bonus Hit record to the BonusHit200907 table (format: “200907”, Transaction Number, “1”, “DoublePtsA”, Number of Points) 
	If an account number is present in the spreadsheet transaction
	Add the number of original number of transaction points to the balance on the Account record
	Otherwise
	Check to see of the card has been registered since the spreadsheet was created
	If it has been registered 
	Add the original number of transaction points to the balance on the Account record
	Otherwise
	Add the original number of transaction points to the Stopped Points on the Card record
	Repeat the process for all transactions
 * ********************************************************************
 */
	$db_user = "StaffProcess";
	$db_pass = "Staf7pr0ce55";

	include "../../include/DB.inc";

	function createBonusRecord($bonushit, $month, $transno, $points)
	{
		$sql = "SELECT Max( SequenceNo ) AS Seq FROM $bonushit WHERE TransactionNo =$transno"; 
		$sequenceno = DBSingleStatQueryNoError( $sql ) + 1;
		$sql = "Insert into $bonushit ( Month, TransactionNo, SequenceNo, PromotionCode, Points ) values ( $month, $transno, $sequenceno, 'DoublePtsA', $points )";
		$results = DBQueryExitOnFailure( $sql );
	}

	function UpdateTransactionRecord( $transno, $points, $table )
	{
		$sql = "Update $table set PointsAwarded = PointsAwarded + $points where TransactionNo = $transno";
		$results = DBQueryExitOnFailure( $sql );
	}
	
	function AdjustBalance ( $accountno, $points )
	{
		$sql = "Update Accounts set Balance = Balance + $points where AccountNo = $accountno";
		$results = DBQueryExitOnFailure( $sql );
	}
	
		function AdjustStoppedPoints ( $cardno, $points )
	{
		$sql = "Update Cards set StoppedPoints = StoppedPoints + $points where CardNo = $cardno";
		$results = DBQueryExitOnFailure( $sql );
	}
	
	// Main function

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	connectToDB( MasterServer, TexacoDB );
	$transactionsprocessed = 0;
	$table = 'Transactions200907';
	
	$sql = "SELECT TransactionNo FROM Transactions200907rdp";
	$results = DBQueryExitOnFailure( $sql );

	while( $row = mysql_fetch_array( $results ) )
	{
				$transactionsprocessed++;
			if( ($transactionsprocessed % 10000) == 0 )
			{
				echo date("H:i:s");
				echo " Processed $transactionsprocessed transactions\r\n";
			}
		$sql1 = "SELECT CardNo,  AccountNo, FLOOR(TransValue) AS Points FROM $table WHERE TransactionNo=".$row['TransactionNo'];
		$results1 = DBQueryExitOnFailure( $sql1 );
		$row1 = mysql_fetch_array( $results1 );
		$transno = $row['TransactionNo'];
		$cardno =  $row1['CardNo'];
		$accountno =  $row1['AccountNo'];
		$points = $row1['Points'];
		UpdateTransactionRecord( $transno, $points, $table );
		createBonusRecord('BonusHit200907', 200907, $transno, $points);
		if ( $accountno == "" OR $accountno == NULL )
		{
			$sql2 = "SELECT MemberNo FROM Cards WHERE CardNo='".$cardno."'";
			$memberno = DBSingleStatQueryNoError( $sql2 );
			if ($memberno)
			{
				$sql3 = "SELECT AccountNo FROM Members WHERE MemberNo=".$memberno;
				$accountno = DBSingleStatQueryNoError( $sql3 );
				if ($accountno)
				{
					AdjustBalance ( $accountno, $points );
				}
				else 
				{
					echo "no account number found for member".$memberno."\r\n";
				}
			}
			else 
			{
				AdjustStoppedPoints ( $cardno, $points );
			}
		}
		else 
		{
			AdjustBalance ( $accountno, $points );
		}
	}

	echo date("Y-m-d H:i:s").' '.__FILE__." completed. \r\n";

?>