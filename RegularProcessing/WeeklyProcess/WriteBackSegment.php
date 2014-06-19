<?php 
//* MRM 17/06/2008 – changed all date("h:i:s") to ("H:i:s")
	require "../../include/DB.inc";
	require "../../DBInterface/FileProcessRecord.php";
	require "../../Reporting/GeneralReportFunctions.php";													

	$db_user = "ReadOnly";
	$db_pass = "ORANGE";
	
		//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  echo Date("Y-m-d h:i:s");
	//*  Echo " Start WriteBackSegment\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";


	$slave = connectToDB( ReportServer, ReportDB );

	$db_user = "ReportGenerator";
	$db_pass = "tldttoths";	
	
	$master = connectToDB( MasterServer, TexacoDB );

	$month = GetThisMonth();
	$LastMonth = DecrementMonth( $month ); 

	$rec = CreateProcessStartRecord( "WriteBackSegment" );

	echo "$month"."\r\n";

	$sql = "select AccountNo, CONCAT( RPad( Recency, 2, ' '), RPad( Value, 2, ' '),  RPad( Frequency, 2, ' ')) as SegmentCode from RawKPIData$LastMonth where AccountNo is not null";

	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

	echo "There are ". mysql_num_rows($slaveRes). " to be updated\n";
	$c = 0;

	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$c++;
# 		$sql = "Update Members set SegmentCode = '$row[SegmentCode]' where AccountNo = $row[AccountNo] and PrimaryMember = 'Y'";
		$sql = "Update Accounts set SegmentCode = '$row[SegmentCode]' where AccountNo = $row[AccountNo]";

		mysql_query( $sql, $master )  or die( mysql_error($master) );

		if( ($c % 50000) == 0 )
		{
			echo date("H:i:s");
			echo " $c updated\n";
		}
	}
	CompleteProcessRecord( $rec );
  	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  	echo Date("Y-m-d h:i:s");
	//*     Echo " Finish WriteBackSegment\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>