<?php

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
$linecount = 0;

$db_user = "pma001";
$db_pass = "amping";
$Process = "Tracking Wr/Back";
 
if(defined('STDIN') ) 
{
	echo $Process."\n\r";
  	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
}

else 
{
	echo("Not Running from CLI!!!! Terminating as I need CL input."); 
	exit;
}
  

$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

echo "Please enter the full pathname/filename of your input file: ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
$filename = (trim($line));
fclose("php://stdin");

echo "Please enter the text for the tracking record (DO NOT USE QUOTATION MARKS!!): ";
$handle1 = fopen ("php://stdin","r");
$line = fgets($handle1);
$notes = (trim($line));
fclose("php://stdin");

echo "Please enter the tracking code you want to use: ";
$handle2 = fopen ("php://stdin","r");
$line = fgets($handle2);
$tcode = (trim($line));
fclose("php://stdin");

$handle3 = fopen ($filename,"r");	
$lines = count(file($filename)); 
$c = 0;

while( $line = fgets( $handle3 ) )
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
		if ($MemberNo)
		{
			$sql = "Insert into Tracking( MemberNo, AccountNo, TrackingCode, Notes, CreationDate, CreatedBy )
					values ( $MemberNo, $accno,'$tcode', '$notes', now(), '$Process')";
			mysql_query( $sql, $master )  or die( mysql_error($master) );
		}
	}
	$c++;
	if( ($c % 1000) == 0 )
	{
		echo date("Y-m-d H:i:s")." $c lines processed\r\n";	
	}
}
echo date("Y-m-d H:i:s")." $c lines processed\r\n";
echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>