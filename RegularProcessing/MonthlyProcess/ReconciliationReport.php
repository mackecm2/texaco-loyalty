<?php
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
//                               ReconciliationReport.php
//      Author : Mike MacKechnie
//        Date : 27 OCT 2009
//	    Mantis : 1419
//
//      Report files in csv format to allow Chevron to reconcile Star Rewards transaction invoice. 
//      Files to be collected by Chevron using sFTP access.
//      Request made by Jason Wolff, [JWHZ@chevron.com]
//
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

function CreateMonthlyTable($outputfile, $sql)
{
	$results = DBQueryExitOnFailure( $sql );
	$lines = mysql_num_rows($results);

	$sql .= " INTO OUTFILE '$outputfile' FIELDS TERMINATED BY ',' ESCAPED BY '\\\' LINES TERMINATED BY '\n'";
	$results = DBQueryExitOnFailure( $sql );
	echo date("Y-m-d H:i:s").' '.$outputfile." written - ";
	echo "$lines records\n\r"; 
}
// - - - - - - - - -  M A I N   P R O C E D U R E  - - - - - - - - - - - - - - - - - - 

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";
$db_user = "pma001";
$db_pass = "amping";
echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
$slave = connectToDB( ReportServer, TexacoDB );
$onemonthago = date("mY",strtotime("-1 month"));

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$outputfile = "/data/Chevron/downloads/ActiveLoyaltyCardNumbers_".$onemonthago.".txt";

$sql1 = "SELECT C.CardNo AS UKFuelsCardNumber, DATE_FORMAT(C.CreationDate, '%d/%m/%Y') AS LoyaltyActiveDate FROM Cards AS C
JOIN Members AS M USING ( MemberNo ) JOIN Accounts AS A USING ( AccountNo ) 
WHERE CardNo LIKE '70835%' AND MemberNo IS NOT NULL AND (A.SegmentCode LIKE 'A%' OR  A.SegmentCode LIKE 'N%')";

CreateMonthlyTable($outputfile, $sql1);
 
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$outputfile = "/data/Chevron/downloads/ATOSInvoiceLinkFile_".$onemonthago.".txt";

$sql1 = "SELECT DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 MONTH), '%m/%Y') AS InvoiceMonth,
 Filename, DATE_FORMAT(EndTime, '%d/%m/%Y') AS ProcessDate FROM FilesProcessed
 WHERE CreatedBy = 'COMPOWER'
 AND DATE_FORMAT(EndTime, '%m%Y') = DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 MONTH), '%m%Y')";

CreateMonthlyTable($outputfile, $sql1); 

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$outputfile = "/data/Chevron/downloads/UKFuelInvoiceLinkFile_".$onemonthago.".txt";
 
$sql1 = "SELECT DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 MONTH), '%m/%Y') AS InvoiceMonth,
 Filename, DATE_FORMAT(EndTime, '%d/%m/%Y') AS ProcessDate FROM FilesProcessed
 WHERE CreatedBy = 'UKFUELS'
 AND DATE_FORMAT(EndTime, '%m%Y') = DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 MONTH), '%m%Y')"; 

CreateMonthlyTable($outputfile, $sql1);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

echo date("Y-m-d H:i:s").' '.__FILE__." completed\n\r"; 
?>