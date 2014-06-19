<?php 
//* MRM 17/06/2008 – changed all date("h:i:s") to ("H:i:s")
//***** Reporting/CreateSegments.php
//  MRM (Mantis 702) - Whitespace removed and echoes made more meaningful 09/12/2008
// 

//******************************************************************************
// Sets how much of the Account and Card History to use set either to '' or 2003
define( "HistoryPeriod", '');

/*****************************************************************************
//
// This page has to first create the segmentation and then produce the KPI reports
//
// Because the client ones changes to this report to be able to be worked out
// retrospectivly so that we can create the retention figurures we have to work it
// all out so that we can project backwards.
//
// Also as there is a tendancy for data to come in late we have to be able to 
// re-evaluate repeatedly.

Recency.

New customers are classified N
N1 = new customer in the last 45 days
N2 = new customer 45days - 3 months.

Active customers are classified A
A1 = active in last month
A2 active between 1 and 3 months.

Lapsed customers are classified L
Last active between 3 and 6 months.

Dormant customers are classified D
Last active between 6 and 12 months.
 
Extra Dormant customers are classified XD
Last active greater than 12 months.


Frequency.

L = 1 or 2 swipes per month
M = 3, 4, or 5 swipes per month
H = 6+ swipes per month


Value.

L = < £60 total spend per month
M = > £60 but less than £120 total spend per month
MH = > £120 but less than £150 total spend per month
H = > £150 total spend per month

// 08 11 10 MRM Mantis 2635 Change various batch processes to only select Open accounts 
******************************************************************************/

	require "GeneralReportFunctions.php";													

/******************************************************************************
//
//	 Create a table of last swipe date by account
//
******************************************************************************/

function AccountLastSwipe($month)
{
	echo date("H:i:s");
	Echo " Creating LastSwipe Table for $month\n";
	$sql = "Drop table   if exists  AccountLastSwipe";
	DBQueryExitOnFailure($sql);
	$sql = "create table AccountLastSwipe 
	select AccountNo, max( YearMonth ) as LastMonth from texaco.AccountMonthly".HistoryPeriod." 
	JOIN texaco.AccountStatus USING ( AccountNo )
	where YearMonth <= $month and Swipes > 0 AND AccountStatus.Status = 'Open' group by AccountNo";
	
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	
	$sql = "alter table AccountLastSwipe add primary key (AccountNo)";
	DBQueryExitOnFailure($sql);

}

/*****************************************************************************
//
//	Create a table that has the first swipe date by account.
//
// This table doesn't rollback perfectly at present in the sense that it uses
// the current linkage of cards to work out account positions so if accounts
// are merged at a later date you end up with different results.
//
*****************************************************************************/

function CreateAccountFirstSwipe()
{
	echo date("H:i:s");
	echo " Creating First Swipe Summary\n";
	$sql = "drop table if exists AccountFirstSwipe";
	DBQueryExitOnFailure($sql);

	$sql = "create table AccountFirstSwipe  
	SELECT AccountNo, min( FirstSwipeDate ) AS FirstSwipeDate, min( Members.CreationDate ) AS RegistrationDate,
	 sum( TotalSpend ) AS TotalSpend, sum( FuelSpend ) AS FuelSpend, sum( ShopSpend ) AS ShopSpend,
	  sum( TotalSwipes ) AS TotalSwipes, 0 AS PointsEarnt
				FROM texaco.Cards
				JOIN texaco.Members
				USING ( MemberNo ) 
				JOIN texaco.AccountStatus
				USING ( AccountNo ) 
				WHERE FirstSwipeDate IS NOT NULL 
				AND AccountStatus.Status = 'Open'                 
				GROUP BY AccountNo";          

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	$sql = "Update AccountFirstSwipe join texaco.Accounts using( AccountNo ) set PointsEarnt = Balance + TotalRedemp";  
	DBQueryExitOnFailure($sql);

	$sql = "Alter table AccountFirstSwipe add Primary key ( AccountNo )";
	DBQueryExitOnFailure($sql);

}

/*****************************************************************************
//
//	This function is missing the calculation to do with merges and tracking
//   you may want to add it.
//
*****************************************************************************/

function SubTotalSpendsCalc( $month )
{
	$sql = "Drop Table if exists AccountDeltas";
	DBQueryExitOnFailure($sql);

	$sql = "Drop Table if exists CardDeltas";
	DBQueryExitOnFailure($sql);


	$sql = "Show Tables from texaco like 'ProductsPurchased$month'";
	$res = DBQueryExitOnFailure($sql);

	if( mysql_num_rows($res) > 0 ) 
	{
		echo date("H:i:s");
		echo " Creating AccountDeltas Table\n";
		$sql = "create Table AccountDeltas select AccountNo, count(*) as Swipes, sum(TransValue) as TotalSpend, sum( if( ProductType = 1, Value, 0 )) as FuelSpend, sum( if( ProductType = 2, Value, 0 )) as ShopSpend, sum( T.PointsAwarded) as DPointsAwarded from texaco.Transactions$month as T left join texaco.ProductsPurchased$month using( TransactionNo ) left join texaco.ProductTypes using( ProductCode ) where AccountNo is not null group by AccountNo";		

		DBQueryExitOnFailure($sql);
		echo date("H:i:s");
		echo " Number of rows inserted = ". mysql_affected_rows() . "\n";
		echo date("H:i:s");
		echo " Creating CardDeltas Table\n";
		$sql = "create Table CardDeltas select CardNo, count(*) as Swipes, sum(TransValue) as TotalSpend, sum( if( ProductType = 1, Value, 0 )) as FuelSpend, sum( if( ProductType = 2, Value, 0 )) as ShopSpend, sum( T.PointsAwarded) as DPointsAwarded from texaco.Transactions$month as T left join texaco.ProductsPurchased$month using( TransactionNo ) left join texaco.ProductTypes using( ProductCode ) group by CardNo";		

		DBQueryExitOnFailure($sql);
		echo date("H:i:s");
		echo " Number of rows inserted = ". mysql_affected_rows() . "\n";
	}
	else
	{
		echo date("H:i:s");
		echo " Creating AccountDeltas Table\n";
		$sql = "create Table AccountDeltas select AccountNo, count(*) as Swipes, sum(TransValue) as TotalSpend, 0 as FuelSpend, 0 as ShopSpend, sum( T.PointsAwarded) as DPointsAwarded from texaco.Transactions$month as T where AccountNo is not null group by AccountNo";		

		DBQueryExitOnFailure($sql);
		echo date("H:i:s");
		echo "Number of rows inserted = ". mysql_affected_rows() . "\n";
		echo date("H:i:s");
		echo " Creating CardDeltas Table\n";
		$sql = "create Table CardDeltas select CardNo, count(*) as Swipes, sum(TransValue) as TotalSpend,  0 as FuelSpend, 0 as ShopSpend, sum( T.PointsAwarded) as DPointsAwarded from texaco.Transactions$month as T group by CardNo";		

		DBQueryExitOnFailure($sql);
		echo date("H:i:s");
		echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	}
	echo date("H:i:s");
	echo " Updating AccountFirstSwipe Table\n";
	$sql = "update AccountFirstSwipe as A join AccountDeltas as D using( AccountNo ) set A.TotalSpend = A.TotalSpend - D.TotalSpend, A.FuelSpend = A.FuelSpend - D.FuelSpend, A.ShopSpend = A.ShopSpend - D.ShopSpend, A.PointsEarnt = A.PointsEarnt - DPointsAwarded";

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows updated = ". mysql_affected_rows() . "\n";
	echo date("H:i:s");
	echo " Updating CardsEOMTotal Table\n";
	$sql = "update CardsEOMTotal as A join CardDeltas as D using( CardNo ) set A.TotalSwipes = A.TotalSwipes - D.Swipes, A.TotalSpend = A.TotalSpend - D.TotalSpend, A.FuelSpend = A.FuelSpend - D.FuelSpend, A.ShopSpend = A.ShopSpend - D.ShopSpend, A.Balance = A.Balance - DPointsAwarded";

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows updated = ". mysql_affected_rows() . "\n";

}

/*****************************************************************************
//
//	Count the number of Active Months prior to the last swipe date in the 
//  previous 6 months
//
*****************************************************************************/

function AccountActiveMonths( $month )
{
	echo date("H:i:s");
	Echo " Creating AccountActiveMonths Table for $month\n";
	$sql = "Drop Table if exists AccountActiveMonths";
	DBQueryExitOnFailure($sql);

	$sql = "create table AccountActiveMonths
	Select 
		AccountLastSwipe.AccountNo, 
		count(*) as ActiveMonths, 
		sum( Swipes) as ActiveSwipes, 
		sum(SpendVal) as ActiveSpend, 
		sum( if( YearMonth = '$month', Swipes, 0 ) ) as CurrentMonthSwipes, 
		sum( if( YearMonth = '$month', SpendVal, 0 ) ) as CurrentMonthSpend  
	from texaco.AccountMonthly".HistoryPeriod." join AccountLastSwipe using (AccountNo) 
	JOIN texaco.AccountStatus USING ( AccountNo )
	where YearMonth between Period_Add( LastMonth, - 6 ) and LastMonth 
	AND AccountStatus.Status = 'Open'  
	group by AccountNo";
	DBQueryExitOnFailure($sql);

	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	$sql = "alter table AccountActiveMonths add primary key (AccountNo)";
	DBQueryExitOnFailure($sql);
}

/*****************************************************************************
//
//	Just creates a blank table
//
*****************************************************************************/

function  CreateAccountSegments( $month )
{
	echo date("H:i:s");
	echo " Creating Account Segments\n";
	$sql = "Drop Table if exists AccountSegments";
	DBQueryExitOnFailure($sql);

	$sql = "create table AccountSegments
	(
		AccountNo bigint primary key,
		AccountPrimaryCard char(20),
		Recency char(2),
		ActiveMonths  int,
		ActiveSwipes int,
		TotalSwipes  int,
		CurrentMonthSwipes int,
		AvgSwipesPerMonth  float,
		ActiveSpend   decimal(10,2) NOT NULL DEFAULT '0',
		TotalSpend   decimal(10,2),
		ShopSpend    decimal(10,2),
		FuelSpend    decimal(10,2),	
		CurrentMonthSpend decimal(10,2),
		PointsEarnt int,
		AvgSpendPerMonth   decimal(10,2),
		Frequency char(2),
		Value char(2),
		Redeemed    enum( 'Y', 'N' ),
		OKMail      enum( 'Y', 'N' ),
		OKEMail     enum( 'Y', 'N' ),
		OKContact   enum( 'Y', 'N' ),
		StatementPref enum('N','P','E','S'),
		Age         int,
		FirstSwipeDate datetime,
		LastSwipeDate  datetime
	)";
	
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";
}

/*****************************************************************************
//
//	Translate all the tables into a Segment Code
//
//  MRM Period_Diff limits changed for early  month run 30/04/09

*****************************************************************************/



function PopulateAccountSegments( $month )
{
	$dayofmonth = date("d");
	if ( $dayofmonth > 16 )
	{
		$month_diff = 1;
	}
	else 
	{
		$month_diff = 2;
	}
	$CalcPoint = Intval($month / 100) . "-". $month % 100 . "-28";
	echo date("H:i:s");
	echo " Populating Account Segments\n";
	$sql = "insert into AccountSegments 
		(AccountNo, Recency, ActiveMonths, ActiveSwipes, CurrentMonthSwipes, AvgSwipesPerMonth, ActiveSpend, AvgSpendPerMonth, CurrentMonthSpend, FirstSwipeDate, LastSwipeDate, TotalSwipes, TotalSpend, ShopSpend, FuelSpend, PointsEarnt )
		SELECT AccountLastSwipe.AccountNo,
		CASE
		WHEN FirstSwipeDate > date_sub( '$CalcPoint', INTERVAL 45 DAY ) THEN 'N1'
		WHEN FirstSwipeDate > date_sub( '$CalcPoint', INTERVAL 3 Month ) THEN 'N2' 
		WHEN Period_Diff( $month, LastMonth ) < $month_diff   THEN 'A1'
		WHEN Period_Diff( $month, LastMonth ) < $month_diff + 2   THEN 'A2'
		WHEN Period_Diff( $month, LastMonth ) < $month_diff + 5   THEN 'L '
		WHEN Period_Diff( $month, LastMonth ) < $month_diff + 11  THEN 'D '
		ELSE 'XD' END as Recency,
		A.ActiveMonths,
		A.ActiveSwipes,
		A.CurrentMonthSwipes,
		A.ActiveSwipes / A.ActiveMonths as AvgSwipesPerMonth,
		A.ActiveSpend,
		A.ActiveSpend / A.ActiveMonths as AvgSpendPerMonth,
		A.CurrentMonthSpend,
		FirstSwipeDate,
		concat_ws( '-', substring( LastMonth, 1, 4 ), substring( LastMonth, 5, 2 ), '15' ),
		F.TotalSwipes,
		F.TotalSpend,
		F.ShopSpend,
		F.FuelSpend,
		F.PointsEarnt
		FROM AccountFirstSwipe as F join AccountActiveMonths as A using( AccountNo ) join AccountLastSwipe using( AccountNo)
		JOIN texaco.AccountStatus USING ( AccountNo )
		where FirstSwipeDate < '$CalcPoint' AND AccountStatus.Status = 'Open' and RegistrationDate < '$CalcPoint'";
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	$sql = "update AccountSegments set Frequency = 	       
		CASE
		WHEN AvgSwipesPerMonth < 3 THEN 'L'
		WHEN AvgSwipesPerMonth Between 3 and 5 THEN 'M'
		ELSE 'H'
		END, 
		Value =	
		CASE
		WHEN AvgSpendPerMonth < 60 THEN 'L '
		WHEN AvgSpendPerMonth < 120 THEN 'M '
		WHEN AvgSpendPerMonth < 150 THEN 'MH'
		ELSE 'H '
		END";
	DBQueryExitOnFailure($sql);
	/*
	 * MRM 26/02/09 Age calculations changed due to DOB becoming yyyy only (Mantis 705)
	 * (TO_DAYS('$CalcPoint' ) - TO_DAYS(MAKEDATE( DOB, 183 ))) / 365 - uses existing logic but assumes customer born on 01/07 of the year
	 * ..... 183 is six months in days ..... so we'll be right half the time. 
	 */
	$sql = "update AccountSegments  as A Join texaco.Members using (AccountNo)
		set 
			A.AccountPrimaryCard = PrimaryCard,
			A.OKMail = Members.OKMail ,  
			A.OKEmail = Members.OKEmail, 
			OKContact = if( Members.OKMail = 'Y' or Members.OKEmail = 'Y' or Members.OKHomePhone = 'Y' or Members.OKSMS = 'Y' or Members.OKWorkPhone = 'Y', 'Y', 'N' ),
			A.StatementPref = Members.StatementPref,
			Age = (TO_DAYS('$CalcPoint' ) - TO_DAYS(MAKEDATE( DOB, 183 ))) / 365
		where Members.PrimaryMember = 'Y'";

	DBQueryExitOnFailure($sql);

}


function CreateUnregisteredList( $month ) 
{
	echo date("H:i:s");
	echo " Creating UnregisteredCardsLastMonth Table\n";
	$sql = "drop table if exists UnregisteredCardsLastMonth";
	DBQueryExitOnFailure($sql);

	$sql = "create table UnregisteredCardsLastMonth 
			select CardNo, FirstSwipeDate, TotalSwipes, TotalSpend, FuelSpend, ShopSpend from texaco.Cards left join texaco.Members using(MemberNo)
			where ((Cards.MemberNo is null) or (Date_Format( Members.CreationDate, '%Y%m' ) > $month))
			and Date_Format(Cards.CreationDate, '%Y%m') <= $month "; 

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	$sql = "Alter table UnregisteredCardsLastMonth add primary key( CardNo )";
	DBQueryExitOnFailure($sql);
	
	echo date("H:i:s");
	echo " Creating CardLastSwipe Table\n";
	$sql = "drop table if exists CardLastSwipe";
	DBQueryExitOnFailure($sql);

	$sql = "create table CardLastSwipe
			select U.CardNo, Max( YearMonth ) as LastSwipeMonth, FirstSwipeDate
			from  UnregisteredCardsLastMonth as U join texaco.CardMonthly".HistoryPeriod." using(CardNo)
			where YearMonth	<= $month
			Group by CardNo";

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	$sql = "Alter table CardLastSwipe add primary key( CardNo )";
	DBQueryExitOnFailure($sql);

}

function CreateUnregistersActiveMonths( $month )
{
	echo date("H:i:s");
	echo " Creating CardActiveMonths Table\n";

	$sql = "Drop table if exists CardActiveMonths";
	DBQueryExitOnFailure($sql);

	$sql = "Create table CardActiveMonths 
			select 
			CardLastSwipe.CardNo, 
			count(*) as ActiveMonths,
			sum(Swipes) as ActiveSwipes, 
			sum(SpendVal) as ActiveSpend,
			sum( if( YearMonth = '$month', Swipes, 0 ) ) as CurrentMonthSwipes, 
			sum( if( YearMonth = '$month', SpendVal, 0 ) ) as CurrentMonthSpend  
			from CardLastSwipe join texaco.CardMonthly".HistoryPeriod." using( CardNo ) 
			where YearMonth between  Period_Add( LastSwipeMonth, - 6 )  and LastSwipeMonth
			group by CardNo";

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	$sql = "Alter table CardActiveMonths add primary key( CardNo )";
	DBQueryExitOnFailure($sql);

}

function CreateUnregisteredSegments( $month)
{
	echo date("H:i:s");
	echo " Creating UnregisteredSegments Table\n";
	$sql = "Drop Table if Exists UnregisteredSegments";
	DBQueryExitOnFailure($sql);

	$sql = "create table UnregisteredSegments
	(
		CardNo varchar(20) primary key,
		Recency char(2),
		ActiveMonths  int,
		ActiveSwipes int,
		TotalSwipes  int,
		CurrentMonthSwipes int,
		AvgSwipesPerMonth  float,
		ActiveSpend   decimal(10,2),
		TotalSpend   decimal(10,2),
		ShopSpend    decimal(10,2),
		FuelSpend	 decimal(10,2),
		CurrentMonthSpend decimal(10,2),
		AvgSpendPerMonth   decimal(10,2),
		Frequency char(2),
		Value char(2),
		FirstSwipeDate Datetime,  
		LastSwipeDate datetime
	)";

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";
}


/*****************************************************************************
//
//  Creats a table that can then be decremented from as we move back in time.
//
*****************************************************************************/

function CreateCardUnswipeData( $Month )
{
	echo date("H:i:s");
	echo " Creating CardsEOMTotal Table\n";
	$sql = "Drop table if exists  CardsEOMTotal";
	DBQueryExitOnFailure($sql);

	$sql = "create table CardsEOMTotal select CardNo, TotalSwipes, TotalSpend, FuelSpend, ShopSpend, StoppedPoints as Balance from texaco.Cards where Period_Diff( $Month, Date_Format(LastSwipeDate, '%Y%m') ) < 36";  
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	$sql = "alter table CardsEOMTotal add primary key( CardNo)";
	DBQueryExitOnFailure($sql);

}

function CreateAccountRedemptions( $month )
{
	echo date("H:i:s");
	echo " Creating AccountRedemptions Table\n";

	$sql = "drop table if exists AccountRedemptions";
	DBQueryExitOnFailure($sql);

	$sql = "create table AccountRedemptions select AccountNo, 
	sum( if( Date_Format( CreationDate, '%Y%m') > $month , Cost, 0 )) as PostDates, 
	sum( if( Date_Format( CreationDate, '%Y%m') > $month, 0, Cost )) as PreDates, 
	max( if( Date_Format( CreationDate, '%Y%m') <= $month and ProductId = '4444', 1, 0 )) as Fuel, 
	max( if( Date_Format( CreationDate, '%Y%m') <= $month and ProductId != '4444', 1, 0 )) as NonFuel, 
	min( CreationDate) as FirstRedemption  
	from texaco.Orders Join texaco.OrderProducts using( OrderNo) 
	JOIN texaco.AccountStatus USING ( AccountNo )
	WHERE AccountStatus.Status = 'Open' 
	group by AccountNo";
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";
	
	$sql = "alter table AccountRedemptions add primary key ( AccountNo )";
	DBQueryExitOnFailure($sql);

}

function PopulateUnregisteredSegments( $month )
//  MRM Period_Diff limits changed for early  month run 30/04/09
{
	$dayofmonth = date("d");
	if ( $dayofmonth > 16 )
	{
		$month_diff = 1;
	}
	else 
	{
		$month_diff = 2;
	}
	$CalcPoint = Intval($month / 100) . "-". $month % 100 . "-28";
	echo date("H:i:s");
 	echo " Inserting into UnregisteredSegments Table for $month\n";

	$sql = "insert into UnregisteredSegments
	(CardNo, Recency, ActiveMonths, TotalSwipes, ActiveSwipes, CurrentMonthSwipes, AvgSwipesPerMonth, TotalSpend, FuelSpend, ShopSpend, ActiveSpend, CurrentMonthSpend, AvgSpendPerMonth, FirstSwipeDate, LastSwipeDate )
	SELECT CardLastSwipe.CardNo, 
		CASE
		WHEN FirstSwipeDate > date_sub( '$CalcPoint', INTERVAL 45 DAY ) THEN 'N1'
		WHEN FirstSwipeDate > date_sub( '$CalcPoint', INTERVAL 3 Month ) THEN 'N2' 
		WHEN Period_Diff( $month, LastSwipeMonth ) < $month_diff   THEN 'A1'
		WHEN Period_Diff( $month, LastSwipeMonth ) < $month_diff + 2   THEN 'A2'
		WHEN Period_Diff( $month, LastSwipeMonth ) < $month_diff + 5   THEN 'L '
		WHEN Period_Diff( $month, LastSwipeMonth ) < $month_diff + 11  THEN 'D '
		ELSE 'XD' END as Recency,
		A.ActiveMonths,
		T.TotalSwipes,
		A.ActiveSwipes,
		A.CurrentMonthSwipes,
		A.ActiveSwipes / A.ActiveMonths as AvgSwipesPerMonth,
		T.TotalSpend,
		T.FuelSpend,
		T.ShopSpend,
		A.ActiveSpend,
		A.CurrentMonthSpend,
		A.ActiveSpend / A.ActiveMonths as AvgSpendPerMonth,
		FirstSwipeDate,
		concat_ws( '-', substring( LastSwipeMonth, 1, 4 ), substring( LastSwipeMonth, 5, 2 ), '15' )
		FROM CardLastSwipe join CardActiveMonths as A using(CardNo) join CardsEOMTotal as T using( CardNo )";

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";
	
	$sql = "update UnregisteredSegments 
	set Frequency =        
		CASE 
		WHEN AvgSwipesPerMonth < 3 THEN 'L'
		WHEN AvgSwipesPerMonth Between 3 and 5 THEN 'M'
		WHEN AvgSwipesPerMonth >= 5 THEN 'H'
		ELSE 'N'
		END, 
		Value =
		CASE
		WHEN AvgSpendPerMonth < 60 THEN 'L '
		WHEN AvgSpendPerMonth < 120 THEN 'M '
		WHEN AvgSpendPerMonth < 150 THEN 'MH'
		WHEN AvgSpendPerMonth >= 150 THEN 'H '
		ELSE 'N '
		END";

	DBQueryExitOnFailure($sql);

}

//=============================================================================================


function CreateCellColumn( $Month, $Table, $Column, $Condition )
{
	global $Mode;

	if( $Mode != "RetentionOnly" )
	{
		$sql = "replace into Reporting.NewKPIReport
		( MonthYear, TableName, ColumnName, TotalMembers, CurrentMonthMembers, PointsEarned, TotalSwipes,
		  CurrentMonthSwipes, TotalSpend, TotalShopSpend, TotalFuelSpend, CurrentMonthSpend, AvgRelationship,
		  AvgSwipes, TotalBalance, MembersRedeemed )
		select
		$Month as MonthYear,
		'$Table' as TableName,
		'$Column' as ColumnName,
		count(*) as NoMembers,
		sum(CurrentMonthSpend > 0 ) as CurrentMonthMembers,
		sum(PointsEarned),
		sum(TotalSwipes) as TotalSwipes,
		sum(CurrentMonthSwipes) as CurrentMonthSwipes,
		sum(TotalSpend) as TotalSpend,
		sum(ShopSpend) as TotalShopSpend,
		sum(FuelSpend) as TotalFuelSpend,
		sum(CurrentMonthSpend) as CurrentMonthSpend,
		Avg(Relationship) as AvgRelationship,
		Avg(TotalSwipes) as AvgSwipesPerAccount,
		Avg(Balance) as AvgBalancePerMember,
		sum(MembersRedeemed) as MembersRedeemed
		from RawKPIData$Month as M1
		$Condition";
		DBQueryExitOnFailure( $sql );
	}

	if( $Mode != "NoRetention" )
	{
	   CreateRetentionCell( $Month, $Table, $Column, $Condition );
	}
}

function CreateRetentionCell( $Month, $Table, $Column, $Condition )
{

	$LastYear = $Month - 100;

	if( $LastYear > 200400 )
	{

		//	How many were there in last year's table with the same criteria ?
		$sql = "select count(*) as NoMembers from RawKPIData$LastYear as M1	$Condition";
		
		$LastYearCount = DBSingleStatQuery( $sql );

		$M2Condition = str_replace( "where", "and", $Condition);
		$M2Condition = str_replace( "M1", "M2", $M2Condition);
		
		$RetainedCount = 0;

		//	For unregistered we use the CardNo as there is no MemberNo.
		
		if($Column == 'UnRegistered')
		{
		
			$sql = "select count(*) as Retained from RawKPIData$LastYear as M1 join RawKPIData$Month as M2 using(CardNo) $Condition $M2Condition";
			$RetainedCount = DBSingleStatQuery( $sql );

		}
		else
		{
			
			// For Non Members it is more difficult.
			// Members may have registered since last year's data was taken
			// select all last year's unregistered Cards
			
			$sql = "select CardNo from RawKPIData$LastYear as M2 where MemberNo is NULL $M2Condition";
			$memberData = DBQueryExitOnFailure( $sql );
			while( $member = mysql_fetch_array( $memberData ) )
			{
				// pulse through each card and see if it appears in this month's data
				
				// Is this card now registered ?

				$sql = "select MemberNo from texaco.Cards where CardNo = '$member[CardNo]' and MemberNo is not NULL";
				$cardData = DBSingleStatQueryNoError( $sql );
				if ($cardData)
				{
				
				
					// We have a MemberNo so this card is now registered
					// but is it in the current year data ?
					
					
					$sql = "select MemberNo from RawKPIData$Month as M2 where MemberNo = $cardData $M2Condition";
					
					if(DBSingleStatQueryNoError( $sql ))
					{
						$RetainedCount++;
					}
					
					
				}
				else
				{
					// This card is still unregistered - is it in the Current months data ?
					// NB this should not be performed for the AllMembers table
					
					if($Table <> 'AllMembers')
					{
						$sql = "select CardNo from RawKPIData$Month as M2 where CardNo = '$member[CardNo]' $M2Condition";
						if(DBSingleStatQueryNoError( $sql ))
						{						
							$RetainedCount++;
						}
					
					}
				
				}
				
				unset($cardData);
				unset($thisyearData);
				unset($member);
			
			}
			
			unset($memberData);
			
			// Now look where the MemberNo is not NULL
			
			$sql = "select MemberNo from RawKPIData$LastYear as M2 where MemberNo is not NULL $M2Condition";
			$memberData = DBQueryExitOnFailure( $sql );
			while( $member = mysql_fetch_array( $memberData ) )
			{
				$sql = "select MemberNo from RawKPIData$Month as M2 where MemberNo = '$member[MemberNo]' $M2Condition";
				if(DBSingleStatQueryNoError( $sql ))
				{
					$RetainedCount++;
				}
			
				unset($member);
			
			}
			
			unset($thisyearData);
			unset($memberData);

		}
		
		$sql = "Update Reporting.NewKPIReport set LastYearCount = $LastYearCount, RetainedCount = $RetainedCount where MonthYear = $Month and TableName = '$Table' and ColumnName = '$Column'"; 
		DBQueryExitOnFailure( $sql );

	}
}

function CreateTable( $Month, $Table, $Condition )
{

	echo date("H:i:s");
	echo " Replacing Reporting.NewKPIReport $Month $Table \n";

	CreateCellColumn( $Month, $Table, 'Total', $Condition );

	if( $Condition == '' )
	{
		$Condition = 'where '; 
	}
	else
	{
		$Condition .= ' and ';
	}
	CreateCellColumn( $Month, $Table, 'OKMailOnly', "$Condition M1.OKMail = 'Y' and M1.OKEmail != 'Y'" );
	CreateCellColumn( $Month, $Table, 'NotOkMailOnly', "$Condition M1.OKMail = 'N' and M1.OKEmail = 'Y'" );
	CreateCellColumn( $Month, $Table, 'OKEMailOnly', "$Condition M1.OKEMail = 'Y' and M1.OKMail = 'N'" );
	CreateCellColumn( $Month, $Table, 'NotOKEMailOnly', "$Condition M1.OKEMail = 'N' and M1.OKMail = 'Y'");
	CreateCellColumn( $Month, $Table, 'OKMailAndEmail', "$Condition M1.OKContact = 'Y' and M1.OKMail = 'Y'" );
	CreateCellColumn( $Month, $Table, 'NotOKMailAndEmail', "$Condition M1.OKContact = 'N' and M1.OKMail = 'N'" );
	CreateCellColumn( $Month, $Table, 'OKContact', "$Condition M1.OKContact = 'Y'" );
	CreateCellColumn( $Month, $Table, 'NotOKContact', "$Condition M1.OKContact = 'N'" );

	CreateCellColumn( $Month, $Table, 'StatementPrefN', "$Condition M1.StatementPref = 'N'" );
	CreateCellColumn( $Month, $Table, 'StatementPrefP', "$Condition M1.StatementPref = 'P'" );
	CreateCellColumn( $Month, $Table, 'StatementPrefE', "$Condition M1.StatementPref = 'E'" );
	CreateCellColumn( $Month, $Table, 'StatementPrefS', "$Condition M1.StatementPref = 'S'" );

	CreateCellColumn( $Month, $Table, 'Registered', "$Condition M1.Registered = 'Y'" );
	CreateCellColumn( $Month, $Table, 'UnRegistered', "$Condition M1.Registered = 'N'" );

}

function CreateMonthTables( $Month )
{
	CreateTable( $Month, 'AllMembers', '' );
	CreateTable( $Month, 'Active', "where M1.Recency in ('A1','A2')"  );
	CreateTable( $Month, 'New', "where M1.Recency in ('N1','N2')"  );
	CreateTable( $Month, 'Lapsed', "where M1.Recency = 'L'"  );
	CreateTable( $Month, 'Dormant', "where M1.Recency = 'D'"  );
	CreateTable( $Month, 'XDormant', "where M1.Recency = 'XD'"  );
	CreateTable( $Month, 'Internet Joiners', "where M1.Source = 'Internet'"  );
	CreateTable( $Month, 'Card In', "where M1.Source = 'Card'"  );
	CreateTable( $Month, 'No Card', "where M1.Source = 'No Card'"  );
	CreateTable( $Month, 'Phone', "where M1.Source = 'Phone'"  );
	CreateTable( $Month, 'Unknown', "where M1.Source = 'Unknown'"  );

	CreateTable( $Month, 'High Value Active', "where M1.Recency in ('A1','A2')  and M1.Value = 'H'" );
	CreateTable( $Month, 'Medium High Value Active', "where M1.Recency in ('A1','A2')  and M1.Value = 'MH'" );
	CreateTable( $Month, 'Medium Value Active', "where M1.Recency in ('A1','A2')  and M1.Value = 'M'" );
	CreateTable( $Month, 'Low Value Active', "where M1.Recency in ('A1','A2')  and M1.Value = 'L'" );

	CreateTable( $Month, 'High Value Lapsed', "where M1.Recency = 'L' and M1.Value = 'H'" );
	CreateTable( $Month, 'Medium High Value Lapsed', "where M1.Recency = 'L' and M1.Value = 'MH'" );
	CreateTable( $Month, 'Medium Value Lapsed', "where M1.Recency = 'L' and M1.Value = 'M'" );
	CreateTable( $Month, 'Low Value Lapsed', "where M1.Recency = 'L' and M1.Value = 'L'" );

	CreateTable( $Month, 'High Value Dormant', "where M1.Recency = 'D' and M1.Value = 'H'" );
	CreateTable( $Month, 'Medium High Value Dormant', "where M1.Recency = 'D' and M1.Value = 'MH'" );
	CreateTable( $Month, 'Medium Value Dormant', "where M1.Recency = 'D' and M1.Value = 'M'" );
	CreateTable( $Month, 'Low Value Dormant', "where M1.Recency = 'D' and M1.Value = 'L'" );

	CreateTable( $Month, 'High Value XDormant', "where M1.Recency = 'XD' and M1.Value = 'H'" );
	CreateTable( $Month, 'Medium High Value XDormant', "where M1.Recency = 'XD' and M1.Value = 'MH'" );
	CreateTable( $Month, 'Medium Value XDormant', "where M1.Recency = 'XD' and M1.Value = 'M'" );
	CreateTable( $Month, 'Low Value XDormant', "where M1.Recency = 'XD' and M1.Value = 'L'" );

}

function CreateMonthRawData( $Month )
{
	$sql = "drop table if exists Reporting.RawKPIData$Month";
	DBQueryExitOnFailure( $sql );

	echo date("H:i:s");
 	echo " Creating RawKPIData$Month Table\n";

	
	$sql = "create table Reporting.RawKPIData$Month
	(
	AccountNo bigint,
	CardNo char(20),
	MemberNo bigint(20),
	Recency char(2),
	Frequency char(2),
	Value  char(2),
	PointsEarned int,
	TotalSwipes int,
	ActiveSwipes int,
	CurrentMonthSwipes int,
	TotalSpend decimal( 10,2) NOT NULL DEFAULT '0',
	ShopSpend  decimal( 10,2) NOT NULL DEFAULT '0',
	FuelSpend  decimal( 10,2) NOT NULL DEFAULT '0',
	ActiveSpend decimal( 10, 2) NOT NULL DEFAULT '0',
	CurrentMonthSpend decimal( 10,2 ) NOT NULL DEFAULT '0',
	Relationship int,
	Balance int,
	MembersRedeemed int,
	RedeemedFuel int,
	RedeemedVoucher int,
	Registered  enum( 'Y', 'N' ),
	OKMail enum( 'Y', 'N' ),
	OKEmail  enum( 'Y', 'N' ),
	OKContact enum( 'Y', 'N' ),
	StatementPref enum('N','P','E','S'),  
	Source char(10)
	)";
	DBQueryExitOnFailure( $sql );

//	su.ShopSpend,
//	su.FuelSpend,

	echo date("H:i:s");
  	echo " Inserting registered accounts into Reporting.RawKPIData".$Month."\n";

	$sql = "insert into Reporting.RawKPIData$Month
	select
	S.AccountNo,
	S.AccountPrimaryCard,
	null,
	S.Recency,
	S.Frequency,
	S.Value,
	S.PointsEarnt,
	S.TotalSwipes, 
	S.ActiveSwipes,
	S.CurrentMonthSwipes,
	S.TotalSpend, 
	S.ShopSpend, 
	S.FuelSpend, 
	S.ActiveSpend,
	S.CurrentMonthSpend,
	Period_Diff( $Month, Date_format( S.FirstSwipeDate, '%Y%m') )+ 1,
	if( R.PreDates is not null, S.PointsEarnt - R.PreDates, S.PointsEarnt ),
	if( R.PreDates is not null and R.PreDates > 0, 1, 0 ),
	if( R.Fuel is not null,  R.Fuel, 0 ),
	if( R.NonFuel is not null, R.NonFuel, 0 ),
	'Y',
	OKMail,
	OKEmail,
	OKContact,
	StatementPref,
	null
	from AccountSegments as S
		left join AccountRedemptions as R using( AccountNo ) 
	where AccountPrimaryCard is not null";

# 	join AccountSummary$Month as su using(AccountNo)

	DBQueryExitOnFailure( $sql );
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	echo date("H:i:s");
  	echo " Insert unregistered cards into Reporting.RawKPIData".$Month."\n";

	$sql =  "insert into Reporting.RawKPIData$Month 
	select
	null,
	u.CardNo,
	null,
	Recency,
	Frequency,
	Value,
	Balance,
	C.TotalSwipes,
	ActiveSwipes,
	u.CurrentMonthSwipes,
	C.TotalSpend,
	C.ShopSpend, 
	C.FuelSpend, 
	ActiveSpend,
	u.CurrentMonthSpend,
	Period_Diff( $Month, Date_format( u.FirstSwipeDate, '%Y%m')) + 1,
	Balance,
	0,
	null,
	null,
	'N',
	'N',
	'N',
	'N',
	'N',
	'Unregistered'
	from UnregisteredSegments as u
	join CardsEOMTotal as C using(CardNo)";

	DBQueryExitOnFailure( $sql );
	echo date("H:i:s");
	echo " Number of rows inserted = ". mysql_affected_rows() . "\n";

	echo date("H:i:s");
  	echo " Updating Source data for Reporting.RawKPIData".$Month."\n";


	$sql = "update Reporting.RawKPIData$Month as r join texaco.Members as m using( AccountNo )
	set
	r.Source = if( CreatedBy = 'WWW' or m.Source in('TEXWEOUE', 'TEXWEOUX', 'TEXWEOUY','TEXWEOUE'), 'Internet', 
		if( m.Source in( 'TEXWEOU', 'TEXWEOU2', 'TEXWEOUB', 'TEXWEOUT' ), 'No Card',
		   if( m.Source in( 'TEXWEOU3', 'TEXWEOU4' ), 'Card',
		   if( m.Source in( 'TEX3', 'TEX4', 'TEX5' ), 'Old',
		   if( m.Source is null and CreatedBy not in ( 'BOSUpdate', 'CIReceiptData','Feb2004_Recarding', 'Jas-ReCarding',
		   'MSBUILD','MTV','NewApps','RE-RE-CARDING','texdba','WEB' ), 'Phone', 'Unknown' )
		   )))),
	MemberNo = m.MemberNo
	where PrimaryMember = 'Y' and Date_Format( m.CreationDate, '%Y%m' ) < $Month ";

	DBQueryExitOnFailure( $sql );
	
	echo date("H:i:s");
	echo " Creating Index for Reporting.RawKPIData".$Month."\n";
	$sql = "alter table Reporting.RawKPIData$Month add index( CardNo )";
	DBQueryExitOnFailure( $sql );
	
	$sql = "alter table Reporting.RawKPIData$Month add index( MemberNo )";
	DBQueryExitOnFailure( $sql );	
}

function JustSource($Month)
{
	$sql = "update Reporting.RawKPIData$Month as r join texaco.Members as m using( AccountNo )
	set
	r.Source = if( CreatedBy = 'WWW' or m.Source in('TEXWEOUE', 'TEXWEOUX', 'TEXWEOUY','TEXWEOUE'), 'Internet', 
		if( m.Source in( 'TEXWEOU', 'TEXWEOU2', 'TEXWEOUB', 'TEXWEOUT' ), 'No Card',
		   if( m.Source in( 'TEXWEOU3', 'TEXWEOU4' ), 'Card',
		   if( m.Source in( 'TEX3', 'TEX4', 'TEX5' ), 'Old',
		   if( m.Source is null and CreatedBy not in ( 'BOSUpdate', 'CIReceiptData','Feb2004_Recarding', 'Jas-ReCarding',
		   'MSBUILD','MTV','NewApps','RE-RE-CARDING','texdba','WEB' ), 'Phone', 'Unknown' )
		   ))))
	where PrimaryMember = 'Y' and Date_Format( m.CreationDate, '%Y%m' ) < $Month ";
	
	echo date("H:i:s");
	echo " Updating Reporting.RawKPIData".$Month."\n";
	DBQueryExitOnFailure( $sql );
}
//=============================================================================================
//       M A I N   P R O C E S S 
//=============================================================================================

	require "../include/DB.inc";

//=============================================================================================

	global $Mode;

	$Mode = "Normal";
	#$Mode = "RetentionOnly";
	#$Mode = "ReCalculate";
	
	// NoRetention, RetentionOnly, Normal

	$slave = connectToDB( ReportServer, ReportDB );

	$month = GetThisMonth();

	if( $Mode == "Normal" )
	{
		$rec = CreateReportProcessLog( );
		$timedate = date("Y-m-d")." ".date("H:i:s");
		echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

		CreateReportLog( "Started Segmentation" );
		//	Create some global stats at the account level (FirstSwipe, Totals, Points Earnt
		//		Cards + Members + Accounts => AccountFirstSwipe.

		CreateAccountFirstSwipe();
		//	Create a table of card totals at the current point in time.
		//		Cards => CardsEOMTotal

		CreateCardUnswipeData($month); 

		for( $i = 0; $i < 6; $i++ )
		{
			// Calculate the additions to the account since the end of the last month
			//	Transactions$month (&Products) => AccountDeltas
			//	Transactions$month (&Products) => CardDeltas
			//	AccountDeltas => AccountFirstSwipe
			//	CardDeltas => CardsEOMTotal

			SubTotalSpendsCalc( $month );

			$month = DecrementMonth( $month );
			CreateReportLog( "Segmenting $month" ); 

			// Create a table of last swipe dates for accounts before a given point in time
			//	AccountMonthly2003 => AccountLastSwipe 	

			AccountLastSwipe( $month );

			// Create a table of Active Months by Account prior to last swipe date.
			//	AccountMonthly2003, AccountLastSwipe => AccountActiveMonths

			AccountActiveMonths( $month );

			// Create a table of redemption information
			//  Orders, OrderProducts => AccountRedemptions

			CreateAccountRedemptions( $month );


			// 	Create the empty account segment table

			CreateAccountSegments( $month );

			// Populate the table
			// 	AccountFirstSwipe, AccountActiveMonths, AccountLastSwipe => AccountSegments

			PopulateAccountSegments( $month );

			// Create a table of Cards that were unregistered at a point in time
			//	Cards, Members => UnregisteredCardsLastMonth
			//	UnregisteredCardsLastMonth, CardMonthly2003 => CardLastSwipe 
			
			CreateUnregisteredList( $month );
			
			// Create a table of number of Active Months prior to the last swipe date
			// 	CardLastSwipe, CardMonthly2003 => CardActiveMonths

			CreateUnregistersActiveMonths( $month );

			// Create the Empty Segment Codes table

			CreateUnregisteredSegments( $month );

			// Populate the table
			//	CardLastSwipe, CardActiveMonths, CardsEOMTotal => UnregisteredSegments 
			
			PopulateUnregisteredSegments( $month );
			
			// Create the raw table for the report
			// texaco.Accounts, AccountSegments => RawKPIData$Month
			// UnregisteredSegments CardsEOMTotal => RawKPIData$Month

			CreateMonthRawData( $month );
			
			// RawKPIData$Month	=> RetentionReport
			// RawKPIData$Month => KPIReport

			CreateMonthTables( $month );

			CreateReportLog( "Updated KPI for $month" );
		}
		CreateReportLog( "Finished Segmentation" ); 
		CompleteProcessLog( $rec );
	}
	elseif( $Mode == "RetentionOnly" )
	{
		for( $i = 0; $i < 6; $i++ )
		{
			$month = DecrementMonth( $month );
			CreateMonthTables( $month );
		}
	}
	elseif( $Mode == "ReCalculate" )
	{
		for( $i = 0; $i < 3; $i++ )
		{
			$month = DecrementMonth( $month );
			CreateMonthRawData( $month );
			CreateMonthTables( $month );
		}
	}	
	
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>