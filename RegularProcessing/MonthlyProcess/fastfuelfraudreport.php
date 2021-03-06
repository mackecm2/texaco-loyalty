
<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
#   require 'slavedb_connect.php';
// MRM 09/12/2008 Changes for Mantis 684

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";	
require "functions.php";

$db_user = "pma001";
$db_pass = "amping";
echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
$slave = connectToDB( ReportServer, AnalysisDB );
#$master = connectToDB( MasterServer, AnalysisDB );


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.

print "Fastfuel Transactions check\n\r";
echo "Process Started - $timedate\n\r";

$handle = fopen ("/data/ukfuels/FFDATA.DAT","r");
$outputfile = "/data/www/websites/texaco/FileProcessing/Processed/UKFuels/FastFuel/FastFuelMatches".date("Y_m_d").".csv";
#$handle = fopen ("/data/www/websites/texaco/FileProcessing/Processed/UKFuels/FastFuel/FFDATA.DAT","r");
$file = fopen("$outputfile", "a");


$numtransactions 	= 0;
$matchedtxcount 	= 0;
$unmatchedtxcount 	= 0;
$totalcost 		= 0;
$baddates		= 0;

$month = GetLastMonth();
$table = "Transactions$month";
//$table = "Transactions200701";

$sql = "create table $table select CardNo,SiteCode,EFTTransNo,TransTime,TransValue from texaco.$table";
mysql_query($sql);

$sql = "ALTER TABLE $table ADD INDEX ( `EFTTransNo` ) ";
mysql_query($sql);

#echo "------------\r\n";
echo "Tx Date,Tx Time,SiteCode,WEOU Card Number,Tx Value, UKFCardNo\r\n";

while ($data = fgetcsv ($handle, 1000, ","))
{


	#	Only do this if this isnt a header row
	$numtransactions ++;

	if(isset($data[9]))
	{
	
		$UKFCardNo		= $data[0];
		$SiteCode		= $data[9];
		$EFTTransNo		= $data[8];
		$transdate		= $data[4];
		$transtime		= $data[5];	
		$Price			= $data[6];
		$Qty			= $data[7];
		
		#echo "imported date is $transdate\r\n";

		$valid_date = formatdate($transdate);
		#echo "date is $valid_date\r\n";
		
		$SearchEFTTransNo = $EFTTransNo + 1;
		#$SearchEFTTransNo = $EFTTransNo;

		#echo " SQL is select CardNo,TransTime from $table where SiteCode = '$SiteCode' and EFTTransNo = '$EFTTransNo' and TransTime between '$valid_date 00:00:01' and '$valid_date 23:59:59'\r\n";
		
		if(mysqlSelect($txData,"SiteCode,CardNo,TransTime,TransValue,EFTTransNo"," $table ","SiteCode = '$SiteCode' and TransValue = '$Price' and EFTTransNo = '$SearchEFTTransNo' and TransTime between '$valid_date 00:00:01' and '$valid_date 23:59:59'","1") >0)
		{

			$matchedtxcount ++;
			
			echo "$transdate,$txData[TransTime],$SiteCode,$txData[CardNo],$txData[TransValue],$UKFCardNo\r\n";
			#echo "Site: $SiteCode, Date: $transdate, EFTNo: $EFTTransNo matches transaction using CardNo : $txData[CardNo]\r\n";
			#echo "Site - $SiteCode,EFTTransNo $EFTTransNo, Date $transdate,Time $transtime, Value - $Price, Qty -  $Qty matches with:\r\n";
			#echo "Site - $txData[SiteCode],EFTTransNo $txData[EFTTransNo], Date $txData[TransTime],WEOU Value - $txData[TransValue],Card - $txData[CardNo]\r\n";
			#echo "------------\r\n";
			
			
			$totalcost += $Price;
			
			
			// Now write the original data to a file for UKFuels
			fwrite($file, "$data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8],$data[9],$data[10],$data[11],$data[12]\r\n");

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


fclose($file);
			
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
echo date("Y-m-d H:i:s").' '.__FILE__." completed"; 

?>
