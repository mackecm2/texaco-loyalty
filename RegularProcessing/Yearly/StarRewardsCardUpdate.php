
<?php

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													



$db_user = "pma001";
$db_pass = "amping";
$count = 0;


#$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.

print "Star Rewards Card Update Results\n\r";
echo "Process Started - $timedate\n\r";


$handle = fopen ("/tmp/RecardData.csv","r");

// prepare the output file
#unlink("/data/www/websites/texaco/RegularProcessing/Yearly/RecardMailingFile.csv");

#$fp = fopen("/data/www/websites/texaco/RegularProcessing/Yearly/RecardMailingFile.csv", 'w');
#fwrite( $fp, "MemberNo,AccountNo,NewCardNo,Salutation,Forename, Surname,Address1, Address2, Address3, Address4, Address5, Postcode\r\n" );


$lineno = 0;
$primaryerrors = "";
$inserterrors = "";

//	look up member
//	create new card record
//	create tracking note
//	create mailing file export

while ($data = fgetcsv ($handle, 2000, ","))
{


	#	Only do this if this isnt a header row

	if($data[0] <> 'Title')
	{
		$lineno ++;
		
		#	Assemble an array of the data we have received
		$PrimaryCardNo			= $data[14];
		$NewCardNo			= $data[22];

		$sql = "update Cards set CardType = 'StarRewards' where CardNo = '$NewCardNo' limit 1";
		
		#echo "$sql \r\n";
		$masterRes = mysql_query( $sql, $master ) or die( mysql_error($master) );

	}
	
	#if ($lineno == 20)
	#{
	#	break;
	#}


 	if( $lineno % 10000 == 0 )
	{
		$timedate = date("Y-m-d H:i:s");
		echo "$timedate Processed $count\n\r";

	}


}  // end while ($data = fgetcsv ($handle, 2000, ","))

#fclose( $fp );

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
print "$timedate Update Complete\n\r";
print " \n\r";


echo "Summary\n\r";
echo "----------------------------------------------\n\r";
echo "$lineno lines processed\n\r";


?>

