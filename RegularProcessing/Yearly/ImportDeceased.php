<?php

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													

$db_user = "pma001";
$db_pass = "amping";
$count = 0;

$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.

print "Experian Address Change file\n\r";
echo "Process Started - $timedate\n\r";


$handle = fopen ("/tmp/deceased2.csv","r");


$lineno 			= 0;
$AddressChange 			= 0;
$PAFAddressVerified 		= 0;
$PAFAddressCloseVerified 	= 0;
$PAFAddressFailed 		= 0;
$PAFForeignAddress 		= 0;
$ForeignAddress			= 0;
$NonResidentialAddress		= 0;
$Deceased			= 0;
$GoneAway			= 0;


while ($data = fgetcsv ($handle, 2000, ","))
{



	#	Only do this if this isnt a header row

	if($data[0] <> 'URN')
	{
		$lineno ++;
		
		#	Assemble an array of the data we have received
		$CardNo			= $data[0];


		$sql = "select MemberNo,AccountNo from Members where PrimaryCard = '$CardNo' limit 1";

		$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		if($row = mysql_fetch_assoc( $slaveRes ) )
		{
			echo "MemberNo is $row[MemberNo]\r\n";
		



			$updatesql = "update Members set Deceased = 'Y' where MemberNo = '$row[MemberNo]' limit 1";

			#echo "$updatesql\r\n";

			$masterRes = mysql_query( $updatesql, $master ) or die( mysql_error($master) );


			# Write a tracking note against this Member

			$sql = "Insert into Tracking( MemberNo, AccountNo, TrackingCode, Notes, CreationDate, CreatedBy ) 
			values ( $row[MemberNo], $row[AccountNo],'6', 'Marked Deceased by Experian - Feb07 Statement run', now(), 'Steve')";

			#echo "$sql\n";
			#echo "Inserted Tracking Note\r\n";
			$masterRes = mysql_query( $sql, $master ) or die( mysql_error($master) );

}

		unset($row);
		
	if( ($lineno % 10) == 0 )
	{
		echo date("h:i:s");
		echo " $lineno\n";
	}		
		
		
	/*
		if (	$lineno == '2')
		{
			break;		
		}
	*/
	}


}  // end while ($data = fgetcsv ($handle, 2000, ","))


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
print "$timedate Import Complete\n\r";
print " \n\r";


echo "Summary\n\r";
echo "----------------------------------------------\n\r";
echo "$lineno lines processed\n\r";
echo "$AddressChange NCOA Addresses changed\n\r";
echo "$PAFAddressVerified PAF Verified lines\n\r";
echo "$PAFAddressCloseVerified PAF Close Address lines\n\r";
echo "$PAFAddressFailed PAF Failed Address lines\n\r";
echo "$PAFForeignAddress Foreign Address lines\n\r";
echo "$NonResidentialAddress NonResidential Address lines\n\r";
echo "$Deceased Deceased lines\n\r";
echo "$GoneAway GoneAway lines\n\r";






?>

