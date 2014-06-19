<?php

	$db_host = "localhost";
	$db_name = "Analysis";
	$db_user = "root";
	$db_pass = "trave1";
//	$db_pass = "";
	require "../General/misc.php";

	connectToDB();

	$sql = "select count(*) from PrizeBucket";

	$results = mysql_query( $sql ) or die( mysql_error() );

	$row = mysql_fetch_row( $results );

	$tickets = $row[0];

	for( $prizeCount = 1; $prizeCount < 1000 ; $prizeCount++ )
	{
		$rowsAffected = 0;
		while( $rowsAffected == 0 )
		{
			$ticket = mt_rand( 1, $tickets ); 

			$sql = "Update PrizeBucket set PrizeNo = $prizeCount where TicketNo = $ticket and PrizeNo is null";
			$result = mysql_query( $sql ) or die( mysql_error() );

			$rowsAffected = mysql_affected_rows();
			echo ".$rowsAffected.";
		}
	}

?>