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



$sql = "select MemberNo, AccountNo from Members where OKEmail is null";

$results = DBQueryExitOnFailure( $sql );

while( $row = mysql_fetch_assoc( $results ) )
{
	$MemberNo = $row["MemberNo"];
	$AccountNo = $row["AccountNo"];

	$sql = "Update Members set OKEmail = 'N' where MemberNo = $MemberNo";

	$results2 = DBQueryExitOnFailure( $sql );

	InsertTrackingRecord( 1158,  $MemberNo, $AccountNo, "", 0 );
}

?>