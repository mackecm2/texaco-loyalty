	<?php
error_reporting( E_ALL );

	$db_user = "StaffProcess";
	$db_pass = "Staf7pr0ce55";

	include "../../include/DB.inc";

		function createHitRecord($bonushit, $month, $transno, $points)
	{
		$sql = "Insert into $bonushit ( Month, TransactionNo, SequenceNo, PromotionCode, Points ) values ( $month, $transno, 3, 'TriplePts', $points )";
		$results = DBQueryExitOnFailure( $sql );
	}

	function UpdateTransactionRecord( $transno, $points, $table )
	{
		$sql = "Update $table set PointsAwarded = ( $points * 3 ) / 2 where TransactionNo = $transno";
		$results = DBQueryExitOnFailure( $sql );
	}
	
	function AdjustStoppedPoints ( $cardno, $points )
	{
		$sql = "Update Cards set StoppedPoints = StoppedPoints + $points where CardNo = '".$cardno."'";
		$results = DBQueryExitOnFailure( $sql );
	}
	
	
	// Main function

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	connectToDB( MasterServer, TexacoDB );
	

	$sql = "SELECT TransactionNo, CardNo, Date_Format( TransTime, '%Y %m' ) AS Month, PointsAwarded FROM FirstSwipeNoBonusNonMembers";
	$results = DBQueryExitOnFailure( $sql );

	while( $row = mysql_fetch_array( $results ) )
	{
		$transno = $row['TransactionNo'];
		$cardno =  $row['CardNo'];
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
		AdjustStoppedPoints ( $cardno, $points );
		createHitRecord($bonushit, $month, $transno, $points);
	}

	echo date("Y-m-d H:i:s").' '.__FILE__." completed. \r\n";

?>
