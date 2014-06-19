<?php 

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
$linecount = 0;

$db_user = "pma001";
$db_pass = "amping";
	
echo "Aug 06 Statement Write Back DM\n\r";
echo "Process Started - $timedate\n\r";

$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$handle = fopen ("/tmp/SeptemberDM.txt","r");
while( $line = fgets( $handle ) )
{
	
	$primarycard = substr( $line , 0, 19 );
	#$listcode = substr( $line , 0, 1 );
	$listcode = '2';
	#echo "Listcode is $listcode,primarycard is $primarycard\n";

	$sql = "select Cards.MemberNo,Members.AccountNo as AccountNo from Cards join Members using (MemberNo) where CardNo = '$primarycard' limit 1;";
	#echo "$sql\n";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		#echo "Card = $primarycard, MemberNo = $row[MemberNo],AccountNo = $row[AccountNo]\n\r";
	

		$sql = "Insert into Statement( AccountNo, StateDate, Mail_seg ) values ( '$row[AccountNo]','2006-08-21', $listcode)";

		#echo "$sql\n";

		mysql_query( $sql, $master )  or die( mysql_error($master) );

		$sql = "Insert into CampaignHistory( MemberNo, AccountNo, CampaignType, CampaignCode,  ListCode, CreationDate, CreatedBy ) 
		values ( $row[MemberNo], $row[AccountNo],'STATEMENT', 'AUG06', $listcode, '2006-08-21 15:02:47', 'Steve')";

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