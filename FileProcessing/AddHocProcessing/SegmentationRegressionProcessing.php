<?php 

	$db_host = "localhost";
	$db_name = "Analysis";
	$db_user = "root";
	$db_pass = "trave1";
//	$db_pass = "";
																	
	require "../../include/DB.inc";

function AccountLastSwipe($month)
{
	echo date("h:i:s");
	Echo "Create LastSwipe Table for $month\n";
	$sql = "Drop table   if exists  AccountLastSwipe";
	DBQueryExitOnFailure($sql);
	$sql = "create table AccountLastSwipe select AccountNo, max( YearMonth ) as LastMonth from AccountMonthly2003 where YearMonth <= $month group by AccountNo";  
	DBQueryExitOnFailure($sql);

	echo "Number rows = ". mysql_affected_rows() . "\n";

	
	$sql = "alter table AccountLastSwipe add primary key (AccountNo)";
	DBQueryExitOnFailure($sql);

}

function CreateAccountFirstSwipe()
{
	echo date("h:i:s");
	echo "Creating First Swipe Summary\n";
	$sql = "drop table if exists AccountFirstSwipe";
//	DBQueryExitOnFailure($sql);

	$sql = "create table AccountFirstSwipe 
		select 
		AccountNo, 
		min(FirstSwipeDate) as FirstSwipeDate,
		min(Members.CreationDate) as RegistrationDate
	from texaco.Cards 
	Join texaco.Members using(MemberNo)
	where FirstSwipeDate is not null
	group by AccountNo";

//	DBQueryExitOnFailure($sql);

//	echo "Number rows = ". mysql_affected_rows() . "\n";

	$sql = "Alter table AccountFirstSwipe add Primary key ( AccountNo )";
	DBQueryExitOnFailure($sql);

}


function AccountActiveMonths( $month )
{
	echo date("h:i:s");
	Echo "Create AccountActiveMonths Table for $month\n";
	$sql = "Drop Table if exists AccountActiveMonths";
	DBQueryExitOnFailure($sql);

	$sql = "create table AccountActiveMonths
	Select AccountLastSwipe.AccountNo, count(*) as ActiveMonths, sum( Swipes) as ActiveSwipes, sum(SpendVal) as ActiveSpend  
from AccountMonthly2003 join AccountLastSwipe using (AccountNo) 
where YearMonth between Period_Add( LastMonth, - 6 ) and LastMonth
group by AccountNo";
	DBQueryExitOnFailure($sql);

	echo "Number rows = ". mysql_affected_rows() . "\n";

	$sql = "alter table AccountActiveMonths add primary key (AccountNo)";
	DBQueryExitOnFailure($sql);
}


function  CreateAccountSegments( $month )
{
	echo date("h:i:s");
	echo "Creating Account Segments\n";
	$sql = "Drop Table if exists AccountSegments$month";
	DBQueryExitOnFailure($sql);

	$sql = "create table AccountSegments$month
	(
		AccountNo bigint primary key,
		AccountPrimaryCard char(20),
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
		Age         int,
		FirstSwipeDate datetime,
		LastSwipeDate  datetime
	)";
	
	DBQueryExitOnFailure($sql);

}


function PopulateAccountSegments( $month )
{
	$CalcPoint = Intval($month / 100) . "-". $month % 100 . "28";
	echo date("h:i:s");
	echo "Populate Account Segments\n";
	$sql = "insert 	into AccountSegments$month 
		(AccountNo, Recency, ActiveMonths, ActiveSwipes,AvgSwipesPerMonth, ActiveSpend, AvgSpendPerMonth, FirstSwipeDate, LastSwipeDate )
		SELECT AccountLastSwipe.AccountNo,
		CASE
		WHEN FirstSwipeDate > date_sub( '$CalcPoint', INTERVAL 45 DAY ) THEN 'N1'
		WHEN FirstSwipeDate > date_sub( '$CalcPoint', INTERVAL 3 Month ) THEN 'N2' 
		WHEN Period_Diff( LastMonth, $month ) <= 1   THEN 'A1'
		WHEN Period_Diff( LastMonth, $month ) <= 3   THEN 'A2'
		WHEN Period_Diff( LastMonth, $month ) <= 6   THEN 'L '
		WHEN Period_Diff( LastMonth, $month ) <= 12  THEN 'D '
		ELSE 'XD' END as Recency,
		A.ActiveMonths,
		A.ActiveSwipes,
		A.ActiveSwipes / A.ActiveMonths as AvgSwipesPerMonth,
		A.ActiveSpend,
		A.ActiveSpend / A.ActiveMonths as AvgSpendPerMonth,
		FirstSwipeDate,
		concat_ws( '-', substring( LastMonth, 1, 4 ), substring( LastMonth, 5, 2 ), '15' )

		FROM AccountFirstSwipe join AccountActiveMonths as A using( AccountNo ) join AccountLastSwipe using( AccountNo)

		where FirstSwipeDate < '$CalcPoint' and RegistrationDate < '$CalcPoint'";
	DBQueryExitOnFailure($sql);

	echo "Number rows = ". mysql_affected_rows() . "\n";

	$sql = "update AccountSegments$month set Frequency = 	       
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

	$sql = "update AccountSegments$month as A Join texaco.Members using (AccountNo)
		set 
			A.AccountPrimaryCard = PrimaryCard,
			A.OKMail = Members.OKMail ,  
			A.OKEmail = Members.OKEmail, 
			OKContact = if( Members.OKMail = 'Y' or Members.OKEmail = 'Y' or Members.OKHomePhone = 'Y' or Members.OKSMS = 'Y' or Members.OKWorkPhone = 'Y', 'Y', 'N' ),
			Age = YEAR( CURDATE( ) - ( DOB )) where Members.PrimaryMember = 'Y'";

	DBQueryExitOnFailure($sql);

}


function CreateUnregisteredList( $month ) 
{
	echo date("h:i:s");
	echo "CreateUnregisteredList\n";
	$sql = "drop table if exists UnregisteredCardsLastMonth";
	DBQueryExitOnFailure($sql);

	$sql = "create table UnregisteredCardsLastMonth 
			select CardNo, FirstSwipeDate from texaco.Cards left join texaco.Members using(MemberNo)
			where (Cards.MemberNo is null) or (Date_Format( Members.CreationDate, '%Y%m' ) > $month)"; 

	DBQueryExitOnFailure($sql);

	echo "Number rows = ". mysql_affected_rows() . "\n";

	$sql = "Alter table UnregisteredCardsLastMonth add primary key( CardNo )";
	DBQueryExitOnFailure($sql);

	$sql = "drop table if exists CardLastSwipe";
	DBQueryExitOnFailure($sql);

	$sql = "create table CardLastSwipe
			select U.CardNo, Max( YearMonth ) as LastSwipeMonth, FirstSwipeDate
			from  UnregisteredCardsLastMonth as U join texaco.CardMonthly2003 using(CardNo)
			where YearMonth	< $month
			Group by CardNo";

	DBQueryExitOnFailure($sql);

	echo "Number rows = ". mysql_affected_rows() . "\n";

	$sql = "Alter table CardLastSwipe add primary key( CardNo )";
	DBQueryExitOnFailure($sql);

}

function CreateUnregistersActiveMonths( $month )
{
	echo date("h:i:s");
	echo "Creating Card Active month\n";

	$sql = "Drop table if exists CardActiveMonths";
	DBQueryExitOnFailure($sql);

	$sql = "Create table CardActiveMonths 
			select 
			CardLastSwipe.CardNo, 
			count(*) as ActiveMonths,
			sum(Swipes) as ActiveSwipes, 
			sum(SpendVal) as ActiveSpend
			from CardLastSwipe join texaco.CardMonthly2003 using( CardNo ) 
			where YearMonth between  Period_Add( LastSwipeMonth, - 6 )  and LastSwipeMonth
			group by CardNo";

	DBQueryExitOnFailure($sql);

	echo "Number rows = ". mysql_affected_rows() . "\n";

	$sql = "Alter table CardActiveMonths add primary key( CardNo )";
	DBQueryExitOnFailure($sql);

}

function CreateUnregisteredSegments( $month)
{
	$sql = "Drop Table if Exists UnregisteredSegments$month";
	DBQueryExitOnFailure($sql);

	$sql = "create table UnregisteredSegments$month
	(
		CardNo varchar(20) primary key,
		Recency char(2),
		ActiveMonths  int,
		ActiveSwipes int,
		TotalSwipes  int,
		AvgSwipesPerMonth  float,
		ActiveSpend   decimal(10,2),
		TotalSpend   decimal(10,2),
		ShopSpend    decimal(10,2),
		AvgSpendPerMonth   decimal(10,2),
		Frequency char(2),
		Value char(2),
		FirstSwipeDate Datetime,  
		LastSwipeDate datetime
	)";

	DBQueryExitOnFailure($sql);
}



function PopulateUnregisteredSegments( $month )
{
	$CalcPoint = Intval($month / 100) . "-". $month % 100 . "28";
	echo date("h:i:s");

 	echo "PopulateUnregisteredSegments $month\n";

	$sql = "insert into UnregisteredSegments$month
	(CardNo, Recency, ActiveMonths, ActiveSwipes, AvgSwipesPerMonth, ActiveSpend, AvgSpendPerMonth, FirstSwipeDate, LastSwipeDate )
	SELECT CardLastSwipe.CardNo, 
		CASE
		WHEN FirstSwipeDate > date_sub( '$CalcPoint', INTERVAL 45 DAY ) THEN 'N1'
		WHEN FirstSwipeDate > date_sub( '$CalcPoint', INTERVAL 3 Month ) THEN 'N2' 
		WHEN Period_Diff( LastSwipeMonth, $month ) <= 1   THEN 'A1'
		WHEN Period_Diff( LastSwipeMonth, $month ) <= 3   THEN 'A2'
		WHEN Period_Diff( LastSwipeMonth, $month ) <= 6   THEN 'L '
		WHEN Period_Diff( LastSwipeMonth, $month ) <= 12  THEN 'D '
		ELSE 'XD' END as Recency,
		A.ActiveMonths,
		A.ActiveSwipes,
		A.ActiveSwipes / A.ActiveMonths as AvgSwipesPerMonth,
		A.ActiveSpend,
		A.ActiveSpend / A.ActiveMonths as AvgSpendPerMonth,
		FirstSwipeDate,
		concat_ws( '-', substring( LastSwipeMonth, 1, 4 ), substring( LastSwipeMonth, 5, 2 ), '15' )
		FROM CardLastSwipe join CardActiveMonths as A using (CardNo)";

	DBQueryExitOnFailure($sql);

	echo "Number rows = ". mysql_affected_rows() . "\n";

	$sql = "update UnregisteredSegments$month 
	set Frequency =        
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

}



 connectToDB();

//CreateAccountFirstSwipe();
 $month = 200412;

for( $month = 200412; $month > 200400; $month-- )
{
	AccountLastSwipe( $month );
	AccountActiveMonths( $month );
	CreateAccountSegments( $month );
	PopulateAccountSegments( $month );

	CreateUnregisteredList( $month );
	CreateUnregistersActiveMonths( $month );
	CreateUnregisteredSegments( $month );
	PopulateUnregisteredSegments( $month );
}

	echo date("h:i:s");
	echo "Finished\n";
?>