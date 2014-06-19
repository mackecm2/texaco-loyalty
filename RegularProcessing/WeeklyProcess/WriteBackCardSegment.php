<?php 
//* MRM 17/06/2008 – changed all date("h:i:s") to ("H:i:s")
	require "../../include/DB.inc";
	require "../../DBInterface/FileProcessRecord.php";
	require "../../Reporting/GeneralReportFunctions.php";													

	$db_user = "ReadOnly";
	$db_pass = "ORANGE";
	
	$slave = connectToDB( ReportServer, ReportDB );

	$db_user = "ReportGenerator";
	$db_pass = "tldttoths";	
	
	$master = connectToDB( MasterServer, TexacoDB );

	//*
	//* next lines exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  
	//*	echo Date("Y-m-d h:i:s");
	//* Echo " Start WriteBackCardSegment\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	

	$month = GetThisMonth();
	$LastMonth = DecrementMonth( $month ); 

	$rec = CreateProcessStartRecord( "WriteBackCardSegment" );

	$sql = "select CardNo, CONCAT( RPad( Recency, 2, ' '), RPad( Value, 2, ' '),  RPad( Frequency, 2, ' ')) as SegmentCode from RawKPIData$LastMonth";

	$slaveRes = mysql_query( $sql, $slave )  or die( mysql_error() );

	echo "There are ". mysql_num_rows($slaveRes). " to be updated\n\r";
	$c = 0;

	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$c++;
		$sql = "Update Cards set SegmentCode = '$row[SegmentCode]' where CardNo = '$row[CardNo]'";
		mysql_query( $sql, $master )  or die( mysql_error() );

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
	//*     Echo " Finish WriteBackCardSegment\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>