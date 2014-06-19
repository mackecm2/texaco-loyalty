
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


	function CreateTransactionMasterTable( $finalMonth )
	{
		$SubTables = "";
		$y = 2004;
		$m = 01;
		$M = "";
		$c = "";
		while( $M < $finalMonth )
		{
			$M = sprintf( "%04d%02d", $y, $m );
			$SubTables .= "$c Transactions$M";  
			$c = ",";
			$m++;
			if( $m > 12 )
			{
				$m = 1;
				$y++;
			}
		}

		$sql = "flush tables";
 		DBQueryExitOnFailure($sql);

		$sql = "Drop table Transactions";
 		DBQueryExitOnFailure($sql);
 		echo "$sql\n";

		$sql = "flush tables";
 		DBQueryExitOnFailure($sql);

		$sql = "create table Transactions(
  `TransactionNo` int(11) NOT NULL auto_increment,
  `Month` int(11) default '1',
  `CardNo` varchar(20) NOT NULL default '',
  `AccountNo` bigint(20) default NULL,
  `SiteCode` int(11) default NULL,
  `TransTime` datetime default NULL,
  `TransValue` decimal(6,2) default NULL,
  `PanInd` char(1) default NULL,
  `Flag` char(1) default NULL,
  `PayMethod` char(1) default NULL,
  `PointsAwarded` int(11) default NULL,
  `InputFile` varchar(25) default NULL,
  `ReceiptNo` varchar(10) default NULL,
  `EFTTransNo` int(11) default NULL,
  `CreationDate` datetime default NULL,
  `CreatedBy` varchar(20) default NULL,
UNIQUE KEY(Month,TransactionNo),
INDEX( CardNo ))  ENGINE=MERGE UNION=($SubTables) INSERT_METHOD=NO";
		DBQueryExitOnFailure($sql);
		echo "$sql\n";
	}


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


?>
