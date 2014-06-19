<?php
error_reporting(E_ERROR);

include "GeneralReportFunctions.php";

	$db_user = "pma001";
	$db_pass = "amping";
	
	// This password needs to be changed whenever you run Issues/Passwords/EncryptPasswords.php MRM 09/12/2008
	// Used to be DAdmin but was changed to pma001 by MRM MRM 09/04/2009 - Mantis 927
	// Mantis 2464 - MRM 10 08 10 changed mysqlselects for DBQueryExitOnFailure($sql);
	
include "../include/DB.inc";
echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
$slave = connectToDB( ReportServer, ReportDB );

$timedate = date("Y-m-d")." ".date("H:i:s");
$filedate = date("Y-m-d");
$filepath =	"/data/www/websites/texaco/reportfiles/"	    ;

$month = GetLastMonth();

echo "Processing month - $month\r\n";

CreateTopValueTransactionReport($month);
CreateHighValueTransactionsReport($month);
CreateHighFrequencyReport($month);
CreateStoppedAccountsReport($month);

$timedate = date("Y-m-d")." ".date("H:i:s");
echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

function CreateTopValueTransactionReport($month) 
{
	global $filepath;
	
	$filename = "TopValueTransactions$month.csv";
	
	$outputfile = fopen("$filepath$filename", "w");

	$filetitlerow = "SiteCode,AccountNo,MemberNo,TxValue\n";
	fwrite($outputfile, $filetitlerow);

	$sql = "SELECT SiteCode,AccountNo,MemberNo,max(TransactionValue) as TxValue 
	FROM NonnormailsedTransactionLog$month
	WHERE AccountType = '' and AccountNo is not NULL group by SiteCode";
	
	$results = DBQueryExitOnFailure($sql);
		
	while( $row = mysql_fetch_assoc( $results )  )
	{
		$filerow = "$row[SiteCode],$row[AccountNo],$row[MemberNo],$row[TxValue]\n";
		fwrite($outputfile, $filerow);

	}
	fclose($outputfile);
}

function CreateHighValueTransactionsReport($month) 
{
	global $filepath;

	$filename = "HighValueTransactions$month.csv";
	
	$outputfile = fopen("$filepath$filename", "w");

	$filetitlerow = "AccountNo,MemberNo,TxValue,SiteCode\n";
	fwrite($outputfile, $filetitlerow);

	$sql = "SELECT SiteCode,AccountNo,MemberNo,max(TransactionValue) as TxValue
	FROM NonnormailsedTransactionLog$month
	WHERE AccountNo is not NULL and TransactionValue >= '150' group by AccountNo";
	
	$results = DBQueryExitOnFailure($sql);
	
	while( $row = mysql_fetch_assoc( $results )  )
	{
		$filerow = "$row[AccountNo],$row[MemberNo],$row[TxValue],$row[SiteCode]\n";
		fwrite($outputfile, $filerow);
	}
	fclose($outputfile);
}

function CreateHighFrequencyReport($month) 
{
	global $filepath;

	$filename = "HighFrequencyTransactions.csv";
	
	$outputfile = fopen("$filepath$filename", "w");

	$filetitlerow = "AreaCode,SiteCode,AccountNo,MemberNo,TxValue\n";
	fwrite($outputfile, $filetitlerow);

	$sql = "drop table if exists temp";
	DBQueryExitOnFailure($sql);

	$sql = "create table temp select AccountNo,Count(*) as Frequency from NonnormailsedTransactionLog$month where AccountNo is not NULL group by MemberNo";
	DBQueryExitOnFailure($sql);

		$sql = "SELECT N.AreaCode,N.SiteCode,N.AccountNo,N.MemberNo,TransactionValue,TransactionDate,TransactionTime
		 	from temp join NonnormailsedTransactionLog$month as N using (AccountNo) 
			where Frequency > 10 order by N.AccountNo";
		
		$results = DBQueryExitOnFailure($sql);
		
		while( $row = mysql_fetch_assoc( $results )  )
	{
		$filerow = "$row[AreaCode],$row[SiteCode],$row[AccountNo],$row[MemberNo],$row[TransactionValue],$row[TransactionDate],$row[TransactionTime]\n";
		fwrite($outputfile, $filerow);
	}
	fclose($outputfile);
}

function CreateStoppedAccountsReport($month) 
{
	global $filepath;
	$filename = "StoppedAccounts.csv";
	
	$outputfile = fopen("$filepath$filename", "w");

	$filetitlerow = "AccountNo,MemberNo,Forename,Surname,PostCode,RedemptionStopDate\n";
	fwrite($outputfile, $filetitlerow);

	$sql = "SELECT A.AccountNo,M.MemberNo,M.Forename,M.Surname,M.Address1,M.PostCode,A.RedemptionStopDate 
	FROM texaco.Accounts as A join texaco.Members as M using (AccountNo) WHERE A.AccountNo is not NULL 
	and A.RedemptionStopDate is not NULL order by A.RedemptionStopDate DESC";

	$results = DBQueryExitOnFailure($sql);

	while( $row = mysql_fetch_assoc( $results )  )
	{
		$filerow = "$row[AccountNo],$row[MemberNo],$row[Forename],$row[Surname],$row[Address1],$row[PostCode],$row[RedemptionStopDate]\n";
		fwrite($outputfile, $filerow);
	}
	fclose($outputfile);
}
?>