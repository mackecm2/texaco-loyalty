<?php

	//********************************************************************
	//**    /Reporting/CreateTransactionReport.php - extensive whitespace pruning MRM 09/12/08
	//**     Mantis 702..
	//*********************************************************************/
include "GeneralReportFunctions.php";
include "../include/DB.inc";
function CreateTransactionReportTable( $Month )
{
	$sql = "drop table if exists NonnormailsedTransactionLog$Month";
	DBQueryExitOnFailure($sql);
	$sql = "create table NonnormailsedTransactionLog$Month
(
     AccountNo     BIGINT, 
     MemberNo     BIGINT,
     CardNo          char(20), 
     ClassOfTrade     char(5),
     SiteCode     int, 
     AreaCode     char(5), 
     RegionCode     char(7), 
     GenderCode     char, 
     Age          int, 
     SegmentCode     char(16), 
     AccountType     char(1),
     TransactionValue DECIMAL(6,2), 
     TransactionDate Date,
     TransactionTime Time,
     PointsAwarded     int, 
     Registered       int,
     OKMail           int,
     OKEmail          int,
     OKContact        int
)";
	DBQueryExitOnFailure($sql);
}

function AddIndexes( $Month )
{
	$sql = "Alter Table NonnormailsedTransactionLog$Month 
	           add index( AccountNo ), 
			   add index( MemberNo ),
			   add INDEX( CardNo ),
			   add INDEX( ClassOfTrade ),
			   add INDEX( SiteCode, MemberNo ),
			   add INDEX( AreaCode ),
			   add INDEX( RegionCode ),
			   add INDEX( GenderCode, Age ),
			   add INDEX( SegmentCode ),
			   add INDEX( Age, GenderCode ),
			   add INDEX( AccountType )";
 	DBQueryExitOnFailure($sql);
}
function PopulateTransactionReport( $Month ) 
{
	echo date("Y-m-d H:i:s");
	echo " Populating Transactions $Month\n";
	$NextMonth = GetStartOfNextMonth($Month);
	$sql = "insert into NonnormailsedTransactionLog$Month 
select 
NonnormalisedCardData.AccountNo, 
NonnormalisedCardData.MemberNo, 
t1.CardNo, 
s.COT, 
t1.SiteCode,  
s.AreaCode, 
s.RegionCode, 
NonnormalisedCardData.GenderCode, 
( TO_DAYS($NextMonth) - TO_DAYS(MAKEDATE(NonnormalisedCardData.DOB,183)) ) / 365,
NonnormalisedCardData.SegmentCode, 
NonnormalisedCardData.AccountType, 
TransValue, 
TransTime, 
TransTime, 
PointsAwarded,
MemberNo is not null,
OKMail,
OKEmail,
OKContact
from texaco.Transactions$Month as t1 
left Join Analysis.NonnormalisedCardData using ( CardNo ) 
left join texaco.sitedata as s on ( t1.SiteCode = s.SiteCode)"; 
	DBQueryExitOnFailure($sql);
}

function CreateBonusReportTable( $Month )
{
	echo date("Y-m-d H:i:s");
	echo " Populating Bonus $Month\n";
	$sql = "drop table if exists NonnormailsedBonusLog$Month";
	DBQueryExitOnFailure($sql);
	$sql = "create table NonnormailsedBonusLog$Month
(
     AccountNo     BIGINT, 
     MemberNo     BIGINT,
     CardNo          char(20),
	 PromotionCode    char(10),
     ClassOfTrade     char(5),
     SiteCode     int, 
     AreaCode     char(5), 
     RegionCode     char(7), 
     GenderCode     char, 
     Age          int, 
     TransactionValue DECIMAL(6,2), 
     TransactionDate Date,
     TransactionTime Time,
     TotalPointsAwarded     int,
	 ThisBonusPoints  int,
     Registered       int,
     OKMail           int,
     OKEmail          int,
     OKContact        int
)";
	DBQueryExitOnFailure($sql);
}
function PopulateBonusReportTable( $Month )
{
	$NextMonth = GetStartOfNextMonth($Month);
	$sql = "insert into NonnormailsedBonusLog$Month 
select 
NonnormalisedCardData.AccountNo, 
NonnormalisedCardData.MemberNo, 
t1.CardNo,
bh.PromotionCode,
s.COT, 
t1.SiteCode,  
AreaCode, 
RegionCode, 
NonnormalisedCardData.GenderCode, 
( TO_DAYS($NextMonth) - TO_DAYS(MAKEDATE(NonnormalisedCardData.DOB,183)) ) / 365,
TransValue, 
TransTime, 
TransTime, 
t1.PointsAwarded,
bh.Points,
MemberNo is not null,
OKMail,
OKEmail,
OKContact
from texaco.BonusHit$Month as bh join texaco.Transactions$Month as t1 using (TransactionNo) 
left Join Analysis.NonnormalisedCardData using ( CardNo ) 
left join texaco.sitedata as s on ( t1.SiteCode = s.SiteCode)"; 
	DBQueryExitOnFailure($sql);
}
function AddBonusIndexes( $Month )
{
	$sql = "Alter Table NonnormailsedBonusLog$Month 
	           add index( AccountNo ), 
			   add index( MemberNo ),
			   add INDEX( CardNo ),
			   add INDEX( PromotionCode ),
			   add INDEX( ClassOfTrade ),
			   add INDEX( SiteCode, MemberNo ),
			   add INDEX( AreaCode ),
			   add INDEX( RegionCode ),
			   add INDEX( GenderCode, Age ),
			   add INDEX( Age, GenderCode )";
 	DBQueryExitOnFailure($sql);
}

function CreateMonthTables( $Month )
{
	CreateTransactionReportTable( $Month );
	PopulateTransactionReport( $Month ); 
	AddIndexes( $Month );
	AddToReportIndex( $Month, 'NonnormailsedTransactionLog' );
	CreateBonusReportTable( $Month );
	PopulateBonusReportTable( $Month );
	AddBonusIndexes( $Month );
	AddToReportIndex( $Month, 'NonnormailsedBonusLog' );
}
function ProcessBonusMonth( $Month )
{
	$sql = "delete from MonthlyBonusReport where Month = $Month";
	DBQueryExitOnFailure( $sql );
	$sql = "replace into MonthlyBonusReport (PromotionCode, Month, SiteCode, Hits, BonusCost, TotalCost, Revenue ) select PromotionCode, $Month, SiteCode, count(*) as Hits, sum(ThisBonusPoints), sum(TotalPointsAwarded), sum(TransactionValue) from NonnormailsedBonusLog$Month group by PromotionCode, SiteCode";
	DBQueryExitOnFailure( $sql );
}
function CreateCardLookupTable()
{
	echo date("Y-m-d H:i:s");
	echo " Create Card Look up table";
	$sql = "drop table if exists Analysis.NonnormalisedCardData";
 	DBQueryExitOnFailure($sql);
	$sql = "create table Analysis.NonnormalisedCardData  
		select m1.AccountNo, 
		m1.MemberNo, 
		CardNo, 
		m1.DOB, 
		A.SegmentCode,
		m1.OKEmail = 'Y' and m1.EMail is not null as OKEmail, 
		m1.OKMail = 'Y' as OKMail, 
		m1.OKEmail = 'Y' or m1.OKMail = 'Y' or m1.OKSMS = 'Y' or m1.OKHomePhone = 'Y' or m1.OKWorkPhone = 'Y' as OKContact, 
		' ' as AccountType, 
		0 as HomeSite, 
		m1.GenderCode, 
		FirstSwipeDate, 
		LastSwipeDate, 
		TotalSwipes, 
		TotalSpend 
		from texaco.Cards as C Left Join texaco.Members as m1 on (m1.MemberNo = C.MemberNo) join texaco.Accounts as A on(m1.AccountNo = A.AccountNo) ";
	DBQueryExitOnFailure($sql);
	echo ".";
	$sql = "update Analysis.NonnormalisedCardData as N join texaco.Accounts as A using(AccountNo)
	     set N.AccountType = A.AccountType, N.HomeSite = A.HomeSite where N.AccountNo is not null";
 	DBQueryExitOnFailure($sql);
	echo ".";
	$sql = "Alter Table Analysis.NonnormalisedCardData add primary key( CardNo )";
	DBQueryExitOnFailure($sql);
	echo ".\n";
}
// Main process
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	$slave = connectToDB( ReportServer, ReportDB );
	$rec = CreateReportProcessLog( );
	CreateCardLookupTable();
	$LastMonth = GetLastMonth();
	$Month = $LastMonth - 6;
	//* code added 02/02/2009 MRM - Mantis 784 .... 200901 - 200906 deliver bad result from LastMonth - 6	
		if( ($LastMonth % 100) > 0 && ($LastMonth % 100) < 7 )
	{
		$Month = $LastMonth - 94;
	}
	//* end of code added	
	
	while( $Month <= $LastMonth )
	{
		echo date("Y-m-d H:i:s");
		echo " Bonus report for $Month\n";
		CreateReportLog( "Month Tables for $Month" );
		CreateMonthTables( $Month );
		CreateReportLog( "Bonus report for $Month" );
		ProcessBonusMonth( $Month );
		$Month = IncrementMonth( $Month );
	}
	CompleteProcessLog( $rec );
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." ended \r\n";
?>
