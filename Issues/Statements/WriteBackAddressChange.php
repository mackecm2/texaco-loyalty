<?php 

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
$linecount = 0;

$db_user = "pma001";
$db_pass = "amping";
	
echo "ChangeOfAddress Write Back\n\r";
echo "Process Started - $timedate\n\r";

$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$handle = fopen ("/tmp/Changeofaddress.txt","r");



while ($data = fgetcsv ($handle, 1000, ","))
{
	
	$primarycard = $data[0];
	#echo "Primarycard is $primarycard\n";

	$sql = "select Cards.MemberNo,Members.AccountNo as AccountNo from Cards join Members using (MemberNo) where CardNo = '$primarycard' limit 1;";
	#echo "$sql\n";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		#echo "Card = $primarycard, MemberNo = $row[MemberNo],AccountNo = $row[AccountNo]\n\r";
	
		$data['2'] = mysql_escape_string($data['2']);
		$data['3'] = mysql_escape_string($data['3']);
		$data['4'] = mysql_escape_string($data['4']);
		$data['5'] = mysql_escape_string($data['5']);
		$data['6'] = mysql_escape_string($data['6']);
		
		$sql = "update Members set Address1 = '$data[2]',Address2 = '$data[3]',
		  Address3 = '$data[4]',Address4 = '$data[5]',Address5 = '$data[6]',PostCode = '$data[7]',
		  RevisedDate = now(),RevisedBy = 'SteveT' where MemberNo = '$row[MemberNo]' limit 1;";

		#echo "$sql\n";

		mysql_query( $sql, $master )  or die( mysql_error($master) );

		$sql = "Insert into Tracking( MemberNo, AccountNo, TrackingCode, Notes, CreationDate, CreatedBy ) 
		values ( $row[MemberNo], $row[AccountNo],'1', 'Address Update following VCCP Data Cleanse for Mar 10 Statement', now(), 'SteveT')";

		#echo "$sql\n";
		
		mysql_query( $sql, $master )  or die( mysql_error($master) );
		
		#$linecount++;
		#if($linecount >= '10')
		#{
		#	exit();
		#}

	}
}



$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

echo "$timedate Process Completed\n\r";
?>
