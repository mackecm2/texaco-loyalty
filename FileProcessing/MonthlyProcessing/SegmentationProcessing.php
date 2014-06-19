<?php 

	$db_host = "localhost";
	$db_name = "Analysis";
	$db_user = "root";
	$db_pass = "trave1";
//	$db_pass = "";
																	
	require "../General/misc.php";


function CreateAccountSummary()
{
	echo "Creating Account Summary\n";
	echo date("h:i:s");
	$sql = "drop table if exists AccountSummary";
	DBQueryExitOnFailure($sql);

	$sql = "create table AccountSummary 
		select 
		AccountNo, 
		min(FirstSwipeDate) as FirstSwipe, 
		max(LastSwipeDate) as LastSwipe, 
		sum(TotalSwipes) as TotalSwipes, 
		sum(TotalSpend) as TotalSpend, 
		sum(FuelSpend) as FuelSpend, 
		sum(ShopSpend) as ShopSpend,
		sum(StoppedPoints) as StoppedPoints
	from texaco.Cards 
	Join texaco.Members using(MemberNo) 
	group by AccountNo";

	DBQueryExitOnFailure($sql);

	$sql = "Alter table AccountSummary add Primary key ( AccountNo )";
	DBQueryExitOnFailure($sql);

}

function CreateActiveMonths()
{
	echo date("h:i:s");
	echo "Create Active Months\n";
	$sql = "drop table if exists ActiveMonths";

	DBQueryExitOnFailure($sql);

	$sql = "create table ActiveMonths
		Select AccountSummary.AccountNo, 
		count(*) as ActiveMonths, 
		sum(Swipes) as ActiveSwipes, 
		sum(SpendVal) as ActiveSpend  
		from texaco.AccountMonthly2003 
		join AccountSummary using( AccountNo ) 
		where YearMonth between  Period_Add( Date_Format( LastSwipe, '%Y%m' ), - 6 )  and Date_Format( LastSwipe, '%Y%m' )
		group by AccountNo";
	
	DBQueryExitOnFailure($sql);

	$sql = "Alter table ActiveMonths add Primary key ( AccountNo )";
	DBQueryExitOnFailure($sql);

}


function  CreateAccountSegments()
{
	echo date("h:i:s");
	echo "Creating Account Segments\n";
	$sql = "Drop Table if exists AccountSegments";
	DBQueryExitOnFailure($sql);

	$sql = "create table AccountSegments
	(
		AccountNo bigint primary key,
		Recency char(2),
		ActiveMonths  int,
		ActiveSwipes int,
		TotalSwipes  int,
		AvgSwipesPerMonth  float,
		ActiveSpend   decimal(10,2),
		TotalSpend   decimal(10,2),
		ShopSpend    decimal(10,2),
		FuelSpend    decimal(10,2),	
		AvgSpendPerMonth   decimal(10,2),
		Frequency char(2),
		Value char(2),
		Redeemed    enum( 'Y', 'N' ),
		OKMail      enum( 'Y', 'N' ),
		OKEMail     enum( 'Y', 'N' ),
		OKContact   enum( 'Y', 'N' ),
		Age         int
	)";
	
	DBQueryExitOnFailure($sql);

}

function PopulateAccountSegments()
{
	echo "Populate Account Segments\n";
	$sql = "insert 	into AccountSegments 
		(AccountNo, Recency, ActiveMonths, TotalSwipes, ActiveSwipes,AvgSwipesPerMonth, ActiveSpend, TotalSpend, AvgSpendPerMonth )
		SELECT AccountSummary.AccountNo, 
		CASE
		WHEN FirstSwipe > '2004-11-15' THEN 'N1'
		WHEN FirstSwipe > '2004-10-01' THEN 'N2' 
		WHEN LastSwipe > '2004-11-15'  THEN 'A1'
		WHEN LastSwipe > '2004-10-01'  THEN 'A2'
		WHEN LastSwipe > '2004-07-01' THEN 'L '
		WHEN LastSwipe > '2004-01-01' THEN 'D '
		ELSE 'XD' END as Recency,
		ActiveMonths.ActiveMonths,
		TotalSwipes,
		ActiveMonths.ActiveSwipes,
		ActiveMonths.ActiveSwipes / ActiveMonths.ActiveMonths as AvgSwipesPerMonth,
		TotalSpend,
		ActiveMonths.ActiveSpend,
		ActiveMonths.ActiveSpend / ActiveMonths.ActiveMonths as AvgSpendPerMonth
		FROM AccountSummary join ActiveMonths using( AccountNo )";
	DBQueryExitOnFailure($sql);

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

	$sql = "update AccountSegments Join texaco.Members using (AccountNo)
		set  AccountSegments.OKMail = Members.OKMail ,  
			AccountSegments.OKEmail = Members.OKEmail, 
			OKContact = if( Members.OKMail = 'Y' or Members.OKEmail = 'Y' or Members.OKHomePhone = 'Y' or Members.OKSMS = 'Y' or Members.OKWorkPhone = 'Y', 'Y', 'N' ),
			Age = YEAR( CURDATE( ) - ( DOB ))
		where Members.PrimaryMember = 'Y'";

	DBQueryExitOnFailure($sql);

}


connectToDB();

//CreateAccountSummary();
//CreateActiveMonths();											
//CreateAccountSegments();
PopulateAccountSegments();



	echo date("h:i:s");
	echo "Finished\n";

?>