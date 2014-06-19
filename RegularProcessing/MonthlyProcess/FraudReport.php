
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

print "Bunkering Card Transactions check\n\r";
echo "Process Started - $timedate\n\r";


$file = "RSMCSVDATA.DAT";


$handle = fopen ("/data/www/websites/texaco/FileProcessing/Compower/$file","r");
$outputfile = "/data/www/websites/texaco/FileProcessing/Processed/FraudReports/BunkerCardMatches".date("Y_m_d").".csv";
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

//	Write the output file first line
fwrite($file, "SiteNo,EFTTransNo,Date,Time,Value,CardNo,WEOUCardNo\r\n");


while ($data = fgetcsv ($handle, 1000, ","))
{


	#	Only do this if this isnt a header row
	$numtransactions ++;

	#	MRM 18/02/08 FastFuels (708251.... cards) removed from the Bunkering Report

	if(isset($data[5]) AND substr($data[5], 0, 6)!= '708251')
	{
	
			$UKFCardNo		= $data[5];
			$SiteCode		= $data[0];
			$EFTTransNo		= '0' + $data[4];
			$transdate		= $data[2];
			$transtime		= $data[3];	
			$Price  		= number_format(($data[1] / 100), 2, ".", ",")	;

			#echo "imported date is $transdate\r\n";

			$valid_date = alterdate($transdate);

			$SearchEFTTransNo = $EFTTransNo + 1;
			#$SearchEFTTransNo = $EFTTransNo;

			#echo " SQL is select SiteCode,CardNo,TransTime,TransValue,EFTTransNo from $table SiteCode = '$SiteCode' and TransValue = '$Price' and EFTTransNo = '$SearchEFTTransNo' and TransTime between '$valid_date 00:00:01' and '$valid_date 23:59:59'\r\n";

			if(mysqlSelect($txData,"SiteCode,CardNo,TransTime,TransValue,EFTTransNo"," $table ","SiteCode = '$SiteCode' and TransValue = '$Price' and EFTTransNo = '$SearchEFTTransNo' and TransTime between '$valid_date 00:00:01' and '$valid_date 23:59:59'","1") >0)
			{

				$matchedtxcount ++;

				#echo "$transdate,$txData[TransTime],$SiteCode,$txData[CardNo],$txData[TransValue],$UKFCardNo\r\n";
				echo "Transaction match\r\n";
				echo "Site: $SiteCode, EFTTransNo: $EFTTransNo, Date: $valid_date,  Time: $transtime, Value - $Price - transaction match - Bunker CardNo : $UKFCardNo\r\n";
				echo "Site: $txData[SiteCode],EFTTransNo $txData[EFTTransNo], DateTime: $txData[TransTime],WEOU Value - $txData[TransValue],WEOUCard - $txData[CardNo]\r\n";
				echo "------------\r\n";


				$totalcost += $Price;


				// Now write the original data to a file for UKFuels
				fwrite($file, "$SiteCode,$EFTTransNo,$valid_date,$transtime,$Price,$UKFCardNo,$txData[CardNo] \r\n");

			}
			else
			{
				$unmatchedtxcount ++;		

			}


		unset($txData);
	}
	else
	{
		if(!isset($data[5]))
		{
			++$baddates ;
			echo "Bad line - $numtransactions\n\r";
		}
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
