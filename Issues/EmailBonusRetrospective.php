<?php
	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";



	$uname = "RetroEmail";

	require "../include/DB.inc";
	require "../DBInterface/TrackingInterface.php";

	connectToDB();

	$sql = "select Members.MemberNo, Members.AccountNo 
		  from Members 
		  left join Tracking on( Members.MemberNo = Tracking.MemberNo and TrackingCode = 940 ) 
		  where Members.CreatedBy = 'WEB' and Passwrd is not null 
			and Email is not null and char_length(Email) > 9 
			and Tracking.MemberNo is null limit 10";

	$results = DBQueryExitOnFailure( $sql );

	$count = 0;
	while( $row = mysql_fetch_assoc( $results ) )
	{
		//AdjustBalance( TrackingEmailBonus50,$row["MemberNo"], $row["AccountNo"], "Retrospective Email bonus", 50 ); 
		echo "AdjustBalance( TrackingEmailBonus50,$row[MemberNo], $row[AccountNo], Retrospective Email bonus, 50 )"; 
				$count++;
		if( $count % 1000 == 0 )
		{
			echo $count;
		}
	}
	echo $count;

?>