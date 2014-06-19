	<?php
error_reporting( E_ALL );
/*
 * 
 * MRM 12 10 10 - Mantis 0002639: Promotion entered incorrectly - Double Points at Chris Cave Sites 
 * The process to retrospectively apply the bonus awards will be as follows:

	Extract all eligible transactions (those with a CreationDate between 2010-09-04 and 2010-10-06 17:49 and from Site Codes 886900, 886876, and 886901
	Update the transaction record with double the original points
	Write a Bonus Hit record to the relevant table (format: “201010”, Transaction Number, “1”, “DblpointsC”, Number of Points) 
	If an account number is present in the spreadsheet transaction
	Check to see if the card has been registered
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
		$sql = "Insert into $bonushit ( Month, TransactionNo, SequenceNo, PromotionCode, Points ) values ( $month, $transno, $sequenceno, 'DblpointsC', $points )";
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
		$sql = "Update Cards set StoppedPoints = StoppedPoints + $points where CardNo = '$cardno'";
		$results = DBQueryExitOnFailure( $sql );
	}
	
	// Main function

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	connectToDB( MasterServer, TexacoDB );
	$transactionsprocessed = 0;
	$balancesadjusted = 0;
	$stoppedpointsadjusted = 0;
	$nullpoints = 0;
	$transactionsskipped = 0;
	
	$sql = "SELECT * FROM Transactions WHERE TransTime > '2010-09-04' 
	AND CreationDate < '2010-10-06 17:49' AND SiteCode IN (886900, 886876, 886901) ";
	$results = DBQueryExitOnFailure( $sql );

	while( $row = mysql_fetch_array( $results ) )
	{
		$transactionsprocessed++;
		if( ($transactionsprocessed % 1000) == 0 )
			{
				echo date("H:i:s");
				echo " Processed $transactionsprocessed transactions\r\n";
			}

		$month = $row['Month']; 

		$table = 'Transactions'.$month;
		$bonushit = 'BonusHit'.$month;

		$sql1 = "SELECT CardNo, AccountNo, FLOOR(TransValue) AS Points, Status, FraudStatus 
		FROM $table LEFT JOIN AccountStatus USING ( AccountNo ) WHERE TransactionNo=".$row['TransactionNo'];
		$results1 = DBQueryExitOnFailure( $sql1 );
		$row1 = mysql_fetch_array( $results1 );
		$transno = $row['TransactionNo'];
		$cardno	= $row1['CardNo'];
		$accountno = $row1['AccountNo'];
		$points = $row1['Points'];
		$status = $row1['Status'];
		$fraudstatus = $row1['FraudStatus'];

		if ( $points > 0 )
		{
			UpdateTransactionRecord( $transno, $points, $table );
			createBonusRecord($bonushit, $month, $transno, $points);


			if ( $accountno == "" OR $accountno == NULL )
			{
				$sql2 = "SELECT MemberNo FROM Cards WHERE CardNo='".$cardno."'";
				$memberno = DBSingleStatQueryNoError( $sql2 );
				if ($memberno)
				{
					$sql3 = "SELECT AccountNo, Status, FraudStatus FROM Members JOIN AccountStatus USING ( AccountNo ) WHERE MemberNo = ".$memberno;
					$results3 = DBQueryExitOnFailure( $sql3 );
					$row3 = mysql_fetch_array( $results3 );
					$accountno = $row3['AccountNo'];
					$status = $row3['Status'];
					$fraudstatus = $row3['FraudStatus'];
					if ($accountno)
					{
						if ( $status == 'Closed' )
						{
							echo date("Y-m-d H:i:s")." Transaction number $transno for account $accountno - not processed; Closed Account.\r\n";
							$transactionsskipped++;
						}
						else 
						{
							if ( $fraudstatus == '4' )
							{
								echo date("Y-m-d H:i:s")." Transaction number $transno for account $accountno - not processed; Fraud Account.\r\n";
								$transactionsskipped++;
							}
							else 
							{
								AdjustBalance ( $accountno, $points );
								$balancesadjusted++;
							}
	
						}
					}
					else 
					{
						echo "no account number found for member".$memberno."\r\n";
						$transactionsskipped++;
					}
				}
				else 
				{
					AdjustStoppedPoints ( $cardno, $points );
					$stoppedpointsadjusted++;
				}
			}
			else 
			{
				if ( $status == 'Closed' )
				{
					echo date("Y-m-d H:i:s")." Transaction number $transno for account $accountno - not processed; Closed Account.\r\n";
					$transactionsskipped++;
				}
				else 
				{
					if ( $fraudstatus == '4' )
					{
						echo date("Y-m-d H:i:s")." Transaction number $transno for account $accountno - not processed; Fraud Account.\r\n";
						$transactionsskipped++;
					}
					else 
					{
						AdjustBalance ( $accountno, $points );
						$balancesadjusted++;
					}
				}
			}
		}
		else 
		{
			$nullpoints++;
		}
	}
	echo date("Y-m-d H:i:s")." Processed $transactionsprocessed transactions\r\n";
	echo date("Y-m-d H:i:s")." Processed $balancesadjusted balance adjustments\r\n";
	echo date("Y-m-d H:i:s")." Processed $stoppedpointsadjusted stopped points adjustments\r\n";
	echo date("Y-m-d H:i:s")." Processed $nullpoints transactions with zero points\r\n";
	echo date("Y-m-d H:i:s")." Skipped $transactionsskipped transactions\r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." completed. \r\n";

?>