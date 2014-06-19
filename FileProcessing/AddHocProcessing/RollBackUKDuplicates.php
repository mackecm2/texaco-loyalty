<?php
	include "../../include/DB.inc";
	$db_user = "root";
	$db_pass = "trave1";																		   

	$slave = connectToDB( ReplicationServer, AnalysisDB );

	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "Select * from UKDuplicates4";

	$results = mysql_query( $sql, $slave );

	while( $row = mysql_fetch_assoc( $results ) )
	{
		$PointsAwarded = $row["PointsAwarded" ];
		$AccountNo = $row["AccountNo"];

		$sql = "Update Accounts set Balance = Balance - $PointsAwarded where AccountNo = $AccountNo";

	 	if( !mysql_query( $sql, $master ))
		{
			echo mysql_error();
		}
//		echo $sql;
	}

?>