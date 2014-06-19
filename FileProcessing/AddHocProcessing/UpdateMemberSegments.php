<?php 

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";
																	
	require "../../include/DB.inc";
connectToDB();

	$sql = "select * from thismonthssegments";
	$c = 0;
	$Results = DBQueryExitOnFailure( $sql );
	while( $row = mysql_fetch_assoc( $Results ) )
	{
		$sql = "Update Members set SegmentCode = '$row[SegmentCode]' where AccountNo = $row[AccountNo] and PrimaryMember = 'Y'";
		DBQueryExitOnFailure( $sql );
		$c++;
		if( $c % 10000 == 0 )
		{
			echo "$c\n";
		}
	}


?>