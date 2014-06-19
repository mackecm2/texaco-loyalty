<?php

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
$linecount = 0;

$db_user = "pma001";
$db_pass = "amping";


echo "Deceased Write Back\n\r";
echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$sql = "SELECT StatementIdentifier, DATE_FORMAT(StopTime, '%Y-%m-%d') AS Date FROM Analysis.StatementingData ORDER BY StopTime DESC LIMIT 1";
$results = DBQueryExitOnFailure( $sql );

while($row = mysql_fetch_assoc($results))
{
	$StatementMonth = $row[StatementIdentifier];
	$StatementDate = $row[Date];
	$Process =  $StatementMonth."WriteBackDeceased";
}

$filename = "/tmp/Deceased.txt";

$handle = fopen ($filename,"r");	
$lines = count(file($filename)); 
$c = 0;
while( $line = fgets( $handle ) )
{
	$accountno = substr( $line , 1, 7 );
	if ($c == 0)
	{
		echo date("Y-m-d H:i:s")." opening $lines lines of $filename \r\n";
	}
	else 
	{
		$findme   = '"';
		$pos = strpos($accountno, $findme);
		if ($pos>0)
		{
			$accno = substr($accountno, 0, $pos ); 			
		}
		else 
		{
			$accno = $accountno; 	
		}
		$sql = "select MemberNo from Members where AccountNo = $accno and PrimaryMember = 'Y' LIMIT 1";
		$MemberNo = DBSingleStatQuery($sql);

		$sql = "update Members set Deceased = 'Y', RevisedDate = now(),RevisedBy = '$Process' where MemberNo = $MemberNo limit 1";
		mysql_query( $sql, $master )  or die( mysql_error($master) );
		$sql = "Insert into Tracking( MemberNo, AccountNo, TrackingCode, Notes, CreationDate, CreatedBy )
		values ( $MemberNo, $accno,'6', 'Set Deceased by VCCP Suppressions update for $StatementMonth Statement', now(), '$Process')";
		mysql_query( $sql, $master )  or die( mysql_error($master) );

	}
	$c++;
	if( ($c % 100) == 0 )
	{
		echo date("Y-m-d H:i:s")." $c lines processed\r\n";	
	}
}
echo date("Y-m-d H:i:s")." $c lines processed\r\n";
echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>