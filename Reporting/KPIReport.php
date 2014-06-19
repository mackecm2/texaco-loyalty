<?php
	$db_host = "localhost";
	$db_name = "Analysis";
	$db_user = "root";
	$db_pass = "trave1";

	require "../General/misc.php";


function CreateCellColumn( $Month, $Table, $Column, $Condition )
{
$sql = "insert into  Reporting.KPIReport
select
$Month as MonthYear,
'$Table' as TableName,
'$Column' as ColumnName,
count(*) as NoMembers,
sum(PointsEarned),
sum(Swipes) as TotalSwipes,
sum(TotalSpend) as TotalSpend,
sum(ShopSpend) as TotalShopSpend,
sum(FuelSpend) as TotalFuelSpend,
Avg(Relationship) as AvgRelationship,
Avg(Swipes) as AvgSwipesPerAccount,
Avg(Balance) as AvgBalancePerMember,
sum(MembersRedeemed) as MembersRedeemed
from RawKPIData$Month
$Condition";

echo $sql;
DBQueryExitOnFailure( $sql );

}

function CreateTable( $Month, $Table, $Condition )
{

	CreateCellColumn( $Month, $Table, 'Total', $Condition );
	if( $Condition == '' )
	{
		$Condition = 'where '; 
	}
	else
	{
		$Condition .= ' and ';
	}
	CreateCellColumn( $Month, $Table, 'OKMail', "$Condition OKMail = 'Y'" );
	CreateCellColumn( $Month, $Table, 'NotOkMail', "$Condition OKMail = 'N'" );
	CreateCellColumn( $Month, $Table, 'OKEMail', "$Condition OKEMail = 'Y'" );
	CreateCellColumn( $Month, $Table, 'NotOKEMail', "$Condition OKEMail = 'N'" );
	CreateCellColumn( $Month, $Table, 'OKContact', "$Condition OKContact = 'Y'" );
	CreateCellColumn( $Month, $Table, 'NotOKContact', "$Condition OKContact = 'N'" );
	CreateCellColumn( $Month, $Table, 'UnRegistered', "$Condition Registered = 'N'" );
}

function CreateMonthTables( $Month )
{
	CreateTable( $Month, 'AllMembers', '' );
	CreateTable( $Month, 'Active', "where Recency in ('A1','A2')"  );
	CreateTable( $Month, 'New', "where Recency in ('N1','N2')"  );
	CreateTable( $Month, 'Lapsed', "where Recency = 'L'"  );
	CreateTable( $Month, 'Dormant', "where Recency = 'D'"  );
	CreateTable( $Month, 'XDormant', "where Recency = 'XD'"  );
	CreateTable( $Month, 'Internet Joiners', "where Source = 'Internet'"  );
	CreateTable( $Month, 'Card In', "where Source = 'Card'"  );
	CreateTable( $Month, 'No Card', "where Source = 'No Card'"  );
	CreateTable( $Month, 'Phone', "where Source = 'Phone'"  );
	CreateTable( $Month, 'Unknown', "where Source = 'Unknown'"  );

	CreateTable( $Month, 'High Value Active', "where Recency in ('A1','A2')  and Value = 'H'" );
	CreateTable( $Month, 'Medium High Value Active', "where Recency in ('A1','A2')  and Value = 'MH'" );
	CreateTable( $Month, 'Medium Value Active', "where Recency in ('A1','A2')  and Value = 'M'" );
	CreateTable( $Month, 'Low Value Active', "where Recency in ('A1','A2')  and Value = 'L'" );

	CreateTable( $Month, 'High Value Lapsed', "where Recency = 'L' and Value = 'H'" );
	CreateTable( $Month, 'Medium High Value Lapsed', "where Recency = 'L' and Value = 'MH'" );
	CreateTable( $Month, 'Medium Value Lapsed', "where Recency = 'L' and Value = 'M'" );
	CreateTable( $Month, 'Low Value Lapsed', "where Recency = 'L' and Value = 'L'" );

	CreateTable( $Month, 'High Value Dormant', "where Recency = 'D' and Value = 'H'" );
	CreateTable( $Month, 'Medium High Value Dormant', "where Recency = 'D' and Value = 'MH'" );
	CreateTable( $Month, 'Medium Value Dormant', "where Recency = 'D' and Value = 'M'" );
	CreateTable( $Month, 'Low Value Dormant', "where Recency = 'D' and Value = 'L'" );

	CreateTable( $Month, 'High Value XDormant', "where Recency = 'XD' and Value = 'H'" );
	CreateTable( $Month, 'Medium High Value XDormant', "where Recency = 'XD' and Value = 'MH'" );
	CreateTable( $Month, 'Medium Value XDormant', "where Recency = 'XD' and Value = 'M'" );
	CreateTable( $Month, 'Low Value XDormant', "where Recency = 'XD' and Value = 'L'" );

	# Lapsed Members
#	CreateTable( $Month, 'High/Avg Value High Frequency Lapsed', "where Recency = 'L' and Value in ( 'H', 'M', 'MH'  ) and Frequency in ( 'H', 'M', 'MH')" );
#	CreateTable( $Month, 'High/Avg Value Low Frequency Lapsed', "where Recency = 'L' and Value in ( 'H', 'M', 'MH'  ) and Frequency = 'L'" );
#	CreateTable( $Month, 'Low Value Lapsed', "where Recency = 'L' and Value = 'L'" );

	# Active Member
	# Top row
#	CreateTable( $Month, 'High Value Active Long', "where Recency in ('A1','A2') and Value = 'H' and Relationship > 13 " );
#	CreateTable( $Month, 'High Value High/Avg Frequency Active Short', "where Recency in ('A1','A2') and Value = 'H' and Frequency in ('H', 'M', 'MH') and Relationship between 2 and 12 " );
#	CreateTable( $Month, 'High Value Low Frequency Active short', "where Recency in ('A1','A2') and Value = 'H' and Frequency = 'L' and Relationship between 2 and 12 " );

	# Middle row
#	CreateTable( $Month, 'Medium Value Active long', "where Recency in ('A1','A2') and Value = 'M' and Relationship > 13 ");
#	CreateTable( $Month, 'Medium Value High/Avg Freqency Active Short', "where Recency in ('A1','A2') and Value = 'M' and Frequency in('H', 'M', 'MH') and Relationship between 2 and 12 " );
#	CreateTable( $Month, 'Medium Value Low Frequency Active Short', "where Recency in ('A1','A2') and Value = 'M' and Frequency = 'L' and Relationship between 2 and 12 " );

	# Bottom Row
#	CreateTable( $Month, 'Low Value Active Long', "where Recency in ('A1','A2') and Value = 'L' and Relationship > 13 ");
#	CreateTable( $Month, 'Low Value High/Avg Frequency Active short', "where Recency in ('A1','A2') and Value = 'L' and Frequency in('H', 'M', 'MH') and Relationship between 2 and 12 " );
#	CreateTable( $Month, 'Low Value Low Frequency Active short', "where Recency in ('A1','A2') and Value = 'L' and Frequency = 'L' and Relationship between 2 and 12 " );


}

function CreateMonthRawData( $Month )
{
	$sql = "create table RawKPIData$Month
	(
	AccountNo bigint,
	CardNo char(20),
	Recency char(2),
	Frequency char(2),
	Value  char(2),
	PointsEarned int,
	Swipes int,
	TotalSpend decimal( 10,2),
	ShopSpend  decimal( 10,2),
	FuelSpend  decimal( 10,2),
	Relationship int,
	Balance int,
	MembersRedeemed int,
	RedeemedFuel int,
	RedeemedVoucher int,
	Registered  enum( 'Y', 'N' ),
	OKMail enum( 'Y', 'N' ),
	OKEmail  enum( 'Y', 'N' ),
	OKContact enum( 'Y', 'N' ),
	Source char(10)
	)";
	echo $sql;
	DBQueryExitOnFailure( $sql );

//	su.ShopSpend,
//	su.FuelSpend,


	$sql = "insert into RawKPIData$Month
	select
	Accounts.AccountNo,
	null,
	Recency,
	Frequency,
	Value,
	Balance + TotalRedemp,
	su.TotalSwipes,
	su.TotalSpend,
	null,
	null,
	Period_Diff( $Month, Date_format( FirstSwipe, '%Y%m') )+ 1,
	Balance,
	if( TotalRedemp > 0, 1, 0 ) ,
	null,
	null,
	'Y',
	OKMail,
	OKEmail,
	OKContact,
	null
	from texaco.Accounts
	join AccountSummary$Month as su using(AccountNo)
	join AccountSegments$Month using(AccountNo)";

	echo $sql;
	DBQueryExitOnFailure( $sql );

	$sql =  "insert into RawKPIData$Month 
	select
	null,
	u.CardNo,
	Recency,
	Frequency,
	Value,
	StoppedPoints,
	Cards.TotalSwipes,
	Cards.TotalSpend,
	Cards.ShopSpend,
	Cards.FuelSpend,
	Period_Diff( $Month, Date_format( FirstSwipeDate, '%Y%m')) + 1,
	StoppedPoints,
	0,
	null,
	null,
	'N',
	'N',
	'N',
	'N',
	'Unregistered'
	from UnregisteredSegments$Month as u
	join texaco.Cards using(CardNo)";

	echo $sql;
	DBQueryExitOnFailure( $sql );

	$sql = "update RawKPIData$Month as r join texaco.Members as m using( AccountNo )
	set
	r.Source = if( CreatedBy = 'WWW' or m.Source in('TEXWEOUE', 'TEXWEOUX', 'TEXWEOUY','TEXWEOUE'), 'Internet', 
		if( m.Source in( 'TEXWEOU', 'TEXWEOU2', 'TEXWEOUB', 'TEXWEOUT' ), 'No Card',
		   if( m.Source in( 'TEXWEOU3', 'TEXWEOU4' ), 'Card',
		   if( m.Source in( 'TEX3', 'TEX4', 'TEX5' ), 'Old',
		   if( m.Source is null and CreatedBy not in ( 'BOSUpdate', 'CIReceiptData','Feb2004_Recarding', 'Jas-ReCarding',
		   'MSBUILD','MTV','NewApps','RE-RE-CARDING','texdba','WEB' ), 'Phone', 'Unknown' )
		   ))))
	where PrimaryMember = 'Y' and Date_Format( m.CreationDate, '%Y%m' ) < $Month ";

	echo $sql;
	DBQueryExitOnFailure( $sql );


}

function JustSource($Month)
{
	$sql = "update RawKPIData$Month as r join texaco.Members as m using( AccountNo )
	set
	r.Source = if( CreatedBy = 'WWW' or m.Source in('TEXWEOUE', 'TEXWEOUX', 'TEXWEOUY','TEXWEOUE'), 'Internet', 
		if( m.Source in( 'TEXWEOU', 'TEXWEOU2', 'TEXWEOUB', 'TEXWEOUT' ), 'No Card',
		   if( m.Source in( 'TEXWEOU3', 'TEXWEOU4' ), 'Card',
		   if( m.Source in( 'TEX3', 'TEX4', 'TEX5' ), 'Old',
		   if( m.Source is null and CreatedBy not in ( 'BOSUpdate', 'CIReceiptData','Feb2004_Recarding', 'Jas-ReCarding',
		   'MSBUILD','MTV','NewApps','RE-RE-CARDING','texdba','WEB' ), 'Phone', 'Unknown' )
		   ))))
	where PrimaryMember = 'Y' and Date_Format( m.CreationDate, '%Y%m' ) < $Month ";

	echo $sql;
	DBQueryExitOnFailure( $sql );
}

connectToDB();

CreateMonthTables( 200501 );
//CreateMonthRawData( 200412 );
//JustSource(200412);
CreateMonthTables( 200412 );
//CreateMonthRawData( 200411 );
CreateMonthTables( 200411 );
//CreateMonthRawData( 200410 );
CreateMonthTables( 200410 );

?>