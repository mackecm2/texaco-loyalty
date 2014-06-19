<?php
error_reporting(E_ERROR);


function CreateDailyBillingReport($filepath,$thisyear,$thismonth) 
{
	echo date("H:i:s");
 	echo " Creating Daily Billing Report\r\n";
	$filename = "TxDailyDetails".$thisyear.$thismonth.".csv";
	$outputfile = fopen("$filepath$filename", "w");

	$filetitlerow = "Day,Site Code,Created By,Total\n";
	fwrite($outputfile, $filetitlerow);

	if ($thismonth == 1 )
	{
		mysqlSelect($ourDetail,"Date_Format( CreationDate, '%Y %m %d' ) AS Day, SiteCode, CreatedBy, count(*) AS Total",
		"Transactions","YEAR(CreationDate) =  YEAR(NOW()) -1 AND MONTH(CreationDate) = 12 GROUP BY Date_Format(CreationDate, '%Y %m %d'), SiteCode, CreatedBy",0);
	}
	else 
	{
		mysqlSelect($ourDetail,"Date_Format( CreationDate, '%Y %m %d' ) AS Day, SiteCode, CreatedBy, count(*) AS Total",
		"Transactions","YEAR(CreationDate) = YEAR(NOW()) AND MONTH(CreationDate) = MONTH(NOW()) -1 GROUP BY Date_Format(CreationDate, '%Y %m %d'), SiteCode, CreatedBy",0);
	}
	$numline = 0;
	foreach ($ourDetail as $row)
	{
		$numline++;
		$filerow = "$row[Day],$row[SiteCode],$row[CreatedBy],$row[Total]\n";
		fwrite($outputfile, $filerow);
		if( $numline % 1000 == 0 )
		{
			echo date("H:i:s")." ".$numline." lines written\r\n";
		}
	}
	echo date("H:i:s")." ".$numline." lines written\r\n";
	fclose($outputfile);
}


function CreateMonthlyBillingReport($filepath,$thisyear,$thismonth) 
{
   	echo date("H:i:s");
 	echo " Creating Monthly Billing Report\r\n";
	$filename = "TxCounts".$thisyear.$thismonth.".csv";
	$outputfile = fopen("$filepath$filename", "w");

	$filetitlerow = "Month,Site Code,Site Name,Created By,Total\n";
	fwrite($outputfile, $filetitlerow);
	
if ($thismonth == 1 )
	{
		mysqlSelect($ourDetail,"Date_Format( T.CreationDate, '%Y %m' ) AS 	Month , T.SiteCode, T.CreatedBy, count( * ) AS Total ",
		"Transactions AS T","YEAR(T.CreationDate) =  YEAR(NOW()) -1 AND MONTH(T.CreationDate) = 12
		GROUP BY Date_Format( T.CreationDate, '%Y %m' ) , T.SiteCode, T.CreatedBy",0);
	}
	else 
	{
		mysqlSelect($ourDetail,"Date_Format( T.CreationDate, '%Y %m' ) AS 	Month , T.SiteCode, T.CreatedBy, count( * ) AS Total ",
		"Transactions AS T ","YEAR(T.CreationDate) = YEAR(NOW()) AND MONTH(T.CreationDate) = MONTH(NOW()) -1
		GROUP BY Date_Format( T.CreationDate, '%Y %m' ) , T.SiteCode, T.CreatedBy",0);
	}
	
	$numline = 0;
	foreach ($ourDetail as $row)
	{
		$numline++;
		// MRM 19 AUG 09 we used to Join Transactions and sitedata to get the Site Name
		// but there's duplicate site codes
		// so now we look up the site name in each iteration
		$sql = "SELECT SiteName FROM sitedata WHERE SiteCode = $row[SiteCode] LIMIT 1";
		$SiteName = DBSingleStatQuery( $sql );
		$filerow = "$row[Month],$row[SiteCode],$SiteName,$row[CreatedBy],$row[Total]\n";
		fwrite($outputfile, $filerow);
			if( $numline % 1000 == 0 )
		{
			echo date("H:i:s")." ".$numline." lines written\r\n";
		}
	}
	echo date("H:i:s")." ".$numline." lines written\r\n";
	fclose($outputfile);
}

include "GeneralReportFunctions.php";
include "../include/DB.inc";

$slave = connectToDB( ReportServer, TexacoDB );

$filepath =	"/data/www/websites/texaco/reportfiles/";
$thismonth = date("m");
$thisyear = date("Y");

echo date("Y-m-d H:i:s").' '.__FILE__." started. \r\n";
CreateDailyBillingReport($filepath,$thisyear,$thismonth);
CreateMonthlyBillingReport($filepath,$thisyear,$thismonth);
echo date("Y-m-d H:i:s").' '.__FILE__." completed. \r\n";

?>
