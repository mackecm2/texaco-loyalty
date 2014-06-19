
<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
#   require 'slavedb_connect.php';


require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";	
require "functions.php";

$db_user = "pma001";
$db_pass = "amping";

$slave = connectToDB( ReportServer, AnalysisDB );
#$master = connectToDB( MasterServer, AnalysisDB );


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.

print "Fastfuel Transactions check\n\r";
echo "Process Started - $timedate\n\r";

#$handle = fopen ("/data/ukfuels/FFDATA2.DAT","r");
$handle = fopen ("/data/www/websites/texaco/FileProcessing/Processed/UKFuels/FastFuel/FFDATA.DAT","r");


$numtransactions 	= 0;
$matchedtxcount 	= 0;
$unmatchedtxcount 	= 0;
$totalcost 		= 0;
$baddates		= 0;


$month = GetLastMonth();
#$table = "Transactions$month";
$table = "Transactions200610";



#die("$thismonth");


$sql = "create table $table select CardNo,SiteCode,EFTTransNo,TransTime,TransValue from texaco.$table";
mysql_query($sql);

$sql = "ALTER TABLE $table ADD INDEX ( `EFTTransNo` ) ";
mysql_query($sql);

echo "------------\r\n";

while ($data = fgetcsv ($handle, 1000, ","))
{


	#	Only do this if this isnt a header row
	$numtransactions ++;

	if(isset($data[9]))
	{
	
		$SiteCode		= $data[9];
		$EFTTransNo		= $data[8];
		$transdate		= $data[4];
		$transtime		= $data[5];	
		$Price			= $data[6];
		$Qty			= $data[7];
		
		#echo "imported date is $transdate\r\n";

		$valid_date = formatdate($transdate);
		#echo "date is $valid_date\r\n";
		
		#$SearchEFTTransNo = $EFTTransNo + 1;
		$SearchEFTTransNo = $EFTTransNo;

		#echo " SQL is select CardNo,TransTime from $table where SiteCode = '$SiteCode' and EFTTransNo = '$EFTTransNo' and TransTime between '$valid_date 00:00:01' and '$valid_date 23:59:59'\r\n";
		
		if(mysqlSelect($txData,"SiteCode,CardNo,TransTime,TransValue,EFTTransNo"," $table ","SiteCode = '$SiteCode' and EFTTransNo = '$EFTTransNo' and TransTime between '$valid_date 00:00:01' and '$valid_date 23:59:59'","1") >0)
		{

			$matchedtxcount ++;
			
			if($Price == $txData['TransValue'])
			{	echo "Value Match\r\n";		}

			#echo "Site: $SiteCode, Date: $transdate, EFTNo: $EFTTransNo matches transaction using CardNo : $txData[CardNo]\r\n";
			echo "Site - $SiteCode,EFTTransNo $EFTTransNo, Date $transdate,Time $transtime, Value - $Price, Qty -  $Qty matches with:\r\n";
			echo "Site - $txData[SiteCode],EFTTransNo $txData[EFTTransNo], Date $txData[TransTime],WEOU Value - $txData[TransValue],Card - $txData[CardNo]\r\n";
			echo "------------\r\n";
			
			
			$totalcost += $Price;


		}
		else
		{
			$unmatchedtxcount ++;		

		}


		unset($txData);
	}
	else
	{
		++$baddates ;
		echo "Bad line - $numtransactions\n\r";
	
	}
		
		
		
		
		
}  // end while ($data = fgetcsv ($handle, 1000, ","))


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
print "$timedate Complete\n\r";
print " \n\r";


echo "Summary\n\r";
echo "----------------------------------------------\n\r";
echo "$numtransactions checked\n\r";
echo "$baddates badly formatted records\n\r";
echo "$unmatchedtxcount did not match\n\r";
echo "$matchedtxcount match existing transactions\n\r";
echo "Total cost of matched transactions = $totalcost\n\r";


$sql = "drop table $table";
mysql_query($sql);


?>
