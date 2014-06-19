<?php

include "../../include/DB.inc";
include "../../DBInterface/TrackingInterface.php";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   
	//$db_pass = "";

	$update = true;

	connectToDB();

 $sql = "select * from Transactions where InputFile = 'TXA27072005b.M.338.272'";

$results = DBQueryExitOnFailure( $sql );

while( $row = mysql_fetch_assoc( $results ) )
{
	$AccountNo = $row["AccountNo"];
	$PointsAwarded = 0 - $row["PointsAwarded"];

	$sql = "select * from Members where AccountNo = $AccountNo and PrimaryMember = 'Y'"; 
	$results2 = DBQueryExitOnFailure( $sql );
	$row2 = mysql_fetch_assoc( $results2 );
	$MemberNo = $row2["MemberNo"];

	AdjustBalance( 1159, $MemberNo, $AccountNo, "", $PointsAwarded );
}

?>