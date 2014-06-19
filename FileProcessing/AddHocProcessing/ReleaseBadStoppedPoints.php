<?php

include "../../include/DB.inc";
include "../../DBInterface/CardInterface.php";
include "../../DBInterface/TrackingInterface.php";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   
	//$db_pass = "";

	$update = true;

	connectToDB();



$sql = "select MemberNo from Cards where MemberNo is not null and StoppedPoints > 0";

$results = DBQueryExitOnFailure( $sql );

while( $row = mysql_fetch_assoc( $results ) )
{
	$MemberNo = $row["MemberNo"];

	$sql = "select  Accounts.AccountNo from Members join Accounts using( AccountNo ) where MemberNo = $MemberNo and AwardStopDate is null";

	$results2 = DBQueryExitOnFailure( $sql );

	$row2 = mysql_fetch_assoc( $results2 );

	$AccountNo = $row2["AccountNo"];

	if( $AccountNo == "" )
	{
		echo "$MemberNo is stopped AccountNo";
	}
	else
	{
		$Points = ReleaseStoppedPoints( $AccountNo );

		InsertTrackingRecord( 1154,  $MemberNo, $AccountNo, "Re-release of stopped points $Points", 0 );
	}
}

?>