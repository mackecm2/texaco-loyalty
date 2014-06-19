<?php
error_reporting( E_ALL );

	//******************************************************************
	//
	// DisplayNegativeStoppedPoints.php        MRM Last Updated 16/09/08 
	// Reads the Cards table and reports on any registered cards with negative balances
	// Update 16/09/08 - compares with last week's run to see if anything's changed.
	//  28/10/08 - JOIN changed to LEFT JOIN in line 28 - need to see unregistered cards as well. 
	//
	//******************************************************************

	$db_user = "UKFuelsProcess";
	$db_pass = "UKPassword";

	include "../../include/DB.inc";

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

	// Main function

	connectToDB( MasterServer, TexacoDB );


	echo "+---------------------+----------+-----------+---------------+---------------------+-------------+\r\n";
	echo "| CardNo              | MemberNo | AccountNo | StoppedPoints | LastSwipeDate       | SegmentCode |\r\n";
	echo "+---------------------+----------+-----------+---------------+---------------------+-------------+\r\n";
	$sql = "SELECT Cards.CardNo, Cards.MemberNo, Members.AccountNo, Cards.StoppedPoints, Cards.LastSwipeDate, Cards.SegmentCode FROM Cards LEFT JOIN Members USING (MemberNo) WHERE Cards.StoppedPoints < 0 ORDER BY Cards.StoppedPoints ASC";
	$results = DBQueryExitOnFailure( $sql );
	while( $row = mysql_fetch_array( $results ) )
	{
		echo ("| ".str_pad($row["CardNo"],16));
		echo (" | ".str_pad($row["MemberNo"],8));
		echo (" | ".str_pad($row["AccountNo"],9));
		echo (" | ".str_pad($row["StoppedPoints"],13));
		echo (" | ".str_pad($row["LastSwipeDate"],19));
		echo (" | ".str_pad($row["SegmentCode"],11)." |\r\n" );
		$oldsql = "SELECT * FROM NegativeStoppedPoints WHERE CardNo = '".$row['CardNo']."'";
		$oldresults = DBQueryExitOnFailure( $oldsql );
		$numrows = mysql_num_rows($oldresults);
		if( $numrows >0 )
		{
			while( $oldrow = mysql_fetch_array( $oldresults ) )
				{
				if ($oldrow["MemberNo"] != $row["MemberNo"])
				{
					echo (" | ****** ".$oldrow["MemberNo"]." has changed to ".$row["MemberNo"]." |\r\n" );
					$newsql = "UPDATE NegativeStoppedPoints SET MemberNo = ".$row["MemberNo"]." WHERE CardNo = '".$row['CardNo']."'";
					$newresults = DBQueryExitOnFailure( $newsql );
				}
				if ($oldrow["AccountNo"] != $row["AccountNo"])
				{
					echo (" | ****** ".$oldrow["AccountNo"]." has changed to ".$row["AccountNo"]." |\r\n" );
					$newsql = "UPDATE NegativeStoppedPoints SET AccountNo = ".$row["AccountNo"]." WHERE CardNo = '".$row['CardNo']."'";
					$newresults = DBQueryExitOnFailure( $newsql );
				}
				if ($oldrow["StoppedPoints"] != $row["StoppedPoints"])
				{
					echo (" | ****** ".$oldrow["StoppedPoints"]." has changed to ".$row["StoppedPoints"]." |\r\n" );
					$newsql = "UPDATE NegativeStoppedPoints SET StoppedPoints = ".$row["StoppedPoints"]." WHERE CardNo = '".$row['CardNo']."'";
					$newresults = DBQueryExitOnFailure( $newsql );
				}
				if ($oldrow["LastSwipeDate"] != $row["LastSwipeDate"])
				{
					echo (" | ****** ".$oldrow["LastSwipeDate"]." has changed to ".$row["LastSwipeDate"]." |\r\n" );
					$newsql = "UPDATE NegativeStoppedPoints SET LastSwipeDate = '".$row["LastSwipeDate"]."' WHERE CardNo = '".$row['CardNo']."'";
					$newresults = DBQueryExitOnFailure( $newsql );
				}
				if ($oldrow["SegmentCode"] != $row["SegmentCode"])
				{
					echo (" | ****** ".$oldrow["SegmentCode"]." has changed to ".$row["SegmentCode"]." |\r\n" );
					$newsql = "UPDATE NegativeStoppedPoints SET SegmentCode = '".$row["SegmentCode"]."' WHERE CardNo = '".$row['CardNo']."'";
					$newresults = DBQueryExitOnFailure( $newsql );
				}
			}	
		}
		else
		{
			echo (" | ****** New Card No with Negative Stopped Points : ".$row["CardNo"]." |\r\n" );
			$newsql = "INSERT INTO NegativeStoppedPoints (CardNo,StoppedPoints,LastSwipeDate,SegmentCode) VALUES (".$row['CardNo'].",".$row['StoppedPoints'].",'".$row['LastSwipeDate']."','".$row['SegmentCode']."')";
			$newresults = DBQueryExitOnFailure( $newsql );
			if ($row["AccountNo"] AND $row["AccountNo"] != '')
				{
				$newsql = "UPDATE NegativeStoppedPoints SET AccountNo = ".$row['AccountNo']." WHERE CardNo = '".$row['CardNo']."'";
				$newresults = DBQueryExitOnFailure( $newsql );
				}
			if ($row["MemberNo"] AND $row["MemberNo"] != '')
				{
				$newsql = "UPDATE NegativeStoppedPoints SET MemberNo = ".$row['MemberNo']." WHERE CardNo = '".$row['CardNo']."'";
				$newresults = DBQueryExitOnFailure( $newsql );
				}
		}
	}
	echo "+---------------------+----------+-----------+---------------+---------------------+-------------+\r\n";
	
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";


?>