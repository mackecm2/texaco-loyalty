<?php

	require "../../include/DB.inc";
	require "../../DBInterface/FileProcessRecord.php";
	require "../../Reporting/GeneralReportFunctions.php";													

	#$db_user = "ReadOnly";
	#$db_pass = "ORANGE";
	
	$db_user = "root";
	$db_pass = "Trave1";	
	
	echo "Start Write Back";

	#$slave = connectToDB( AnalysisServer, AnalysisDB );

	
	$master = connectToDB( MasterServer, TexacoDB );

	#$rec = CreateProcessStartRecord( "WriteBackStatement" );
	
	$sql = "select * from Analysis.Jun10StatementListcodes";

	$slaveRes = mysql_query( $sql, $master ) or die( mysql_error($master) );

	echo "There are ". mysql_num_rows($slaveRes). " to be updated\n";
	$c = 0;


	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$c++;
		$sql = "Insert into Statement( AccountNo, StateDate, Balance, Mail_seg ) 
			values ( $row[AccountNo],'2010-06-21', $row[Balance], '$row[ListCode]')";

//		echo "$sql\n";

		mysql_query( $sql, $master )  or die( mysql_error($master) );

		$sql = "Insert into CampaignHistory( MemberNo, AccountNo, CampaignType, CampaignCode,  ListCode, CreationDate, CreatedBy ) 
		values ( $row[MemberNo], $row[AccountNo],'STATEMENT', 'JUN10', '$row[ListCode]', '2010-06-21 15:00:00', 'SteveT')";

//		echo "$sql\n";
		
		mysql_query( $sql, $master )  or die( mysql_error($master) );

		if( ($c % 10000) == 0 )
		{
			echo date("h:i:s");
			echo " $c\n";
		}
	}

	#CompleteProcessRecord( $rec );


?>
