<?php
	include "../../include/DB.inc";
	$db_user = "root";
	$db_pass = "trave1";																		   

	$slave = connectToDB( ReplicationServer, AnalysisDB );

	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "Select * from AccountsLastSwipe";

	$results = mysql_query( $sql, $slave );
	$c = 0;
	while( $row = mysql_fetch_assoc( $results ) )
	{
		$LastSwipe = $row["LastSwipeDate" ];
		$AccountNo = $row["AccountNo"];

		$sql = "Update Accounts set RevisedDate = '$LastSwipe' where AccountNo = $AccountNo";
	 	if( !mysql_query( $sql, $master ))
		{
			echo mysql_error();
		}
		$c++;
		if( $c % 10000 == 0 )
		{
			echo "$c\n";
		}
	}

?>