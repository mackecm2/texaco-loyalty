<?php 
//* MRM 17/06/2008 – changed all date("h:i:s") to ("H:i:s")
	require "../include/DB.inc";
	require "GeneralReportFunctions.php";													
	
	//* next line added for greater clarity in logs - MRM 17/06/2008
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

	$db_user = "DAdmin";
	$db_pass = "Global";
	
	$slave = connectToDB( ReportServer, ReportDB );

	$db_user = "root";
	$db_pass = "trave1";
	
	$master = connectToDB( MasterServer, TexacoDB );

	$month = GetThisMonth();
	$LastMonth = DecrementMonth( $month ); 


	$sql = "select CardNo, CONCAT( RPad( Recency, 2, ' '), RPad( Value, 2, ' '),  RPad( Frequency, 2, ' ')) as SegmentCode from RawKPIData$LastMonth";

	$slaveRes = mysql_query( $sql, $slave )  or die( mysql_error() );

	echo "There are ". mysql_num_rows($slaveRes). " to be updated";
	$c = 0;

	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$c++;
		$sql = "Update Cards set SegmentCode = '$row[SegmentCode]' where CardNo = '$row[CardNo]'";
		mysql_query( $sql, $master )  or die( mysql_error() );

		if( ($c % 10000) == 0 )
		{
			echo date("H:i:s");
			echo " $c\n";
		}
	}
  	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  	echo date("H:i:s");
	//*     echo "Finished\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>