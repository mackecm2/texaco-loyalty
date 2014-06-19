	<?php
error_reporting( E_ALL );

	$db_user = "StaffProcess";
	$db_pass = "Staf7pr0ce55";

	include "../../include/DB.inc";

	function createHitRecord($bonushit, $month, $transno, $points)
	{
		$sql = "Insert into $bonushit ( Month, TransactionNo, SequenceNo, PromotionCode, Points ) values ( $month, $transno, 1, 'TriplePts', $points )";
		$results = DBQueryExitOnFailure( $sql );

		return mysql_insert_id();
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
		
		if ($textmonth = '2008 08')
		{
			$table = 'Transactions200808';
			$month = '200808';
			$bonushit = 'BonusHit200808';
		}
		if ($textmonth = '2008 07')
		{
			$table = 'Transactions200807';
			$month = '200807';
			$bonushit = 'BonusHit200807';
		}
		createHitRecord($bonushit, $month, $transno, $points);
				
	}

	echo date("Y-m-d H:i:s").' '.__FILE__." completed. \r\n";

?>
