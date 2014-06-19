<?php
	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";
//	$db_pass = "";

	require "../General/misc.php";

function UpdateCardMonthly( $LastMonth )
{
	echo date("h:i:s");
	echo "Update CardMonthly $LastMonth\n";

	$sql = "replace into CardMonthly2003( CardNo, YearMonth, SpendVal, PointsEarned, Swipes, CreationDate ) 
		select CardNo, $LastMonth, sum( TransValue ), sum( PointsAwarded), count(*), now()  
		from Transactions$LastMonth group by CardNo"; 
		DBQueryExitOnFailure($sql);
	echo date("h:i:s");
}

function GetNewMonth()
{
	$sql = "select date_format( now(), '%Y%m' )";
	$result = DBQueryExitOnFailure($sql);

	$row = mysql_fetch_row( $result );
	return $row[0];
}

function GetLastMonth()
{
	$sql = "select date_format( Date_sub(now(), INTERVAL 1 MONTH), '%Y%m' )";
	$result = DBQueryExitOnFailure($sql);

	$row = mysql_fetch_row( $result );
	return $row[0];
}

function DecrementMonth( $month )
{
	if( ($month % 100) == 1 )
	{
		return ($month - 100 + 11);
	}
	else
	{
		return ($month - 1);
	}
}

function UpdateAccountMonthly( $LastMonth )
{
	echo date("h:i:s");
	echo "Update AccountMonthly $LastMonth\n";
	$sql = "replace into AccountMonthly (AccountNo, YearMonth, SpendVal, PointsEarned, Swipes )
	Select AccountNo, YearMonth, sum(SpendVal), sum(PointsEarned), sum(Swipes) 
	from CardMonthly2003 join CardAccount using (CardNo) 
	where YearMonth = $LastMonth group by AccountNo, YearMonth";
	DBQueryExitOnFailure($sql);
	echo date("h:i:s");
}


function CreateCardAccountLookup()
{
	$sql = "drop table CardAccount";
	DBQueryExitOnFailure($sql);

	echo date("h:i:s");
	echo "Create lookup table\n";
	$sql = "create table CardAccount select CardNo, AccountNo from Cards join Members using(MemberNo)";
	DBQueryExitOnFailure($sql);

	$sql = "alter table CardAccount add primary key( CardNo )";
	DBQueryExitOnFailure($sql);
	echo date("h:i:s");
}


connectToDB();

CreateCardAccountLookup();

$lastmonth =  GetLastMonth();

for( $c = 0; $c < 6; $c++ )
{
	echo "$lastmonth\n";
	UpdateCardMonthly( $lastmonth );
	UpdateAccountMonthly( $lastmonth );
	$lastmonth =  DecrementMonth( $lastmonth );
}

?>