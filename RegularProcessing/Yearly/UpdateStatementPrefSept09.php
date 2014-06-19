<?php

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													


$db_user = "root";
$db_pass = "Trave1";
$count = 0;


#$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$timedate = date("Y-m-d H:i:s");

//	Start of Script.

print "Star Rewards Statement Preference Update Sept 09\n\r";
echo "Process Started - $timedate\n\r";



echo date("Y-m-d H:i:s")."  Setting Statement Pref to E for all valid emails\n\r";

$sql = "update Members set StatementPref = 'E' where email like '%@%'";
$masterRes = mysql_query( $sql, $master ) or die( mysql_error($master) );



echo date("Y-m-d H:i:s")."  Updating Email responders\n\r";
$handle = fopen ("/tmp/emailresponders.csv","r");

$responderscount	= 0;

while ($data = fgetcsv ($handle, 1000, ","))
{
	
	$CardNo 	= mysql_escape_string($data['0']);
	
	$responderscount++;
	
	$sql = "update Members join Cards using (MemberNo) set StatementPref = 'P'  where Cards.CardNo = '$CardNo' ";

	mysql_query( $sql, $master )  or die( mysql_error($master) );

}

fclose( $handle );

echo date("Y-m-d H:i:s")."  Email responders updated: $responderscount\n\r";



echo date("Y-m-d H:i:s")."  Updating Email bounces\n\r";
$handle = fopen ("/tmp/emailbounces.csv","r");

$count 	= 0;

while ($data = fgetcsv ($handle, 1000, ","))
{
	
	$email 	= mysql_escape_string($data['0']);
	
	$count++;
	
	$sql = "update Members set StatementPref = 'P', EmailVerified = NULL where email = '$email'";

	mysql_query( $sql, $master )  or die( mysql_error($master) );

}
fclose( $handle );

echo date("Y-m-d H:i:s")."  Email bounces updated: $count\n\r";


echo date("Y-m-d H:i:s")." Process Complete\n\r";
echo " \n\r";



?>

