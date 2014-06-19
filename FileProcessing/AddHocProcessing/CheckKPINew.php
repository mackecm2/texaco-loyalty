<?php 

	require "../../include/DB.inc";

	$db_user = "DAdmin";
	$db_pass = "Global";
	
	$slave = connectToDB( ReportServer, TexacoDB );

	$db_user = "root";
	$db_pass = "trave1";

	$Month = "2005-09-30";

	while( $Month > '2003' )
	{

		$sql = "select count(*) from Cards left join Members using( MemberNo ) where Cards.CreationDate Between Date_sub( '$Month', interval 3 Month) and '$Month' and (Cards.MemberNo is null or Members.CreationDate > $Month)";

		$Unregistered = DBSingleStatQuery( $sql );

		$sql = "select COUNT(*) FROM Members where Members.CreationDate Between Date_sub( '$Month', interval 3 Month) and '$Month' and PrimaryMember = 'Y'";

		$registered = DBSingleStatQuery( $sql );


		$sql = "select Last_Day(Date_sub( '$Month',  interval 1 month))";


		$Total =   $registered + $Unregistered; 

		echo "$Month, $registered, $Unregistered, $Total\n";

		$Month = DBSingleStatQuery( $sql ); 

	}
?>