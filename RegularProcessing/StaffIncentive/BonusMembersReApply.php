	<?php
error_reporting( E_ALL );

	$db_user = "StaffProcess";
	$db_pass = "Staf7pr0ce55";

	include "../../include/DB.inc";

	function createBonusRecord($bonushit, $month, $transno, $points)
	{
		$sql = "Insert into $bonushit ( Month, TransactionNo, SequenceNo, PromotionCode, Points ) values ( $month, $transno, 3, 'TriplePts', $points )";
		$results = DBQueryExitOnFailure( $sql );

		return mysql_insert_id();
	}

	function UpdateTransactionRecord( $transno, $points, $table )
	{
		$sql = "Update $table set PointsAwarded = ($points * 3) / 2 where TransactionNo = $transno";
		$results = DBQueryExitOnFailure( $sql );
	}
	
	function AdjustBalance ( $accountno, $points )
	{
		$sql = "Update Accounts set Balance = Balance + $points where AccountNo = $accountno";
		$results = DBQueryExitOnFailure( $sql );
	}
	
	
	// Main function

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	connectToDB( MasterServer, TexacoDB );
	

	$sql = "SELECT TransactionNo, MemberNo, CardNo, Date_Format( TransTime, '%Y %m' ) AS Month, PointsAwarded FROM FirstSwipeNoBonusMembers";
	$results = DBQueryExitOnFailure( $sql );

	while( $row = mysql_fetch_array( $results ) )
	{
		$transno = $row['TransactionNo'];
		$cardno =  $row['CardNo'];
		$memberno =  $row['MemberNo'];
		$sql = "SELECT AccountNo FROM Members WHERE MemberNo = $memberno";
		$accountno = DBSingleStatQueryNoError( $sql );
		$textmonth = $row['Month'];
		$points = $row['PointsAwarded'];
		switch ($textmonth)
		{
			case("2008 08"):
				$table = 'Transactions200808';
				$month = '200808';
				$bonushit = 'BonusHit200808';
				break;
			case("2008 07"):
				$table = 'Transactions200807';
				$month = '200807';
				$bonushit = 'BonusHit200807';
				break;
			default:
   				echo "textmonth is $textmonth";
		}
		UpdateTransactionRecord( $transno, $points, $table );
		if ($accountno)
		{
		AdjustBalance ( $accountno, $points );
		}
		else 
		{
			echo "no account found for $cardno";
		}
		createBonusRecord($bonushit, $month, $transno, $points);
	}

	echo date("Y-m-d H:i:s").' '.__FILE__." completed. \r\n";

?>
