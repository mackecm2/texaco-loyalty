<?php
//* MRM 17/06/2008 – changed all date("h:i:s") to ("H:i:s")
	$db_user = "ReportGenerator";
	$db_pass = "tldttoths";	
/*
	Grant Usage on texaco.* to MonthlyTotals@localhost identified by 'non-secure';

	set PASSWORD for 'HomeSiteProcess'@'localhost' = OLD_PASSWORD( 'non-secure' );

	Grant SELECT, INSERT, UPDATE, DELETE on texaco.CardMonthly2003 to MonthlyTotals@localhost;
	Grant SELECT, INSERT, UPDATE, DELETE on texaco.AccountMonthly2003 to MonthlyTotals@localhost;
	Grant SELECT on texaco.Transactions to MonthlyTotals@localhost;
	Grant SELECT on texaco.Orders to MonthlyTotals@localhost;
	Grant SELECT on texaco.Tracking to MonthlyTotals@localhost;
	Grant SELECT on texaco.OrderProducts to MonthlyTotals@localhost;

*/
	
	require "../../include/DB.inc";
	require "../../DBInterface/FileProcessRecord.php";



function UpdateCardMonthly( $LastMonth )
{
	echo date("H:i:s");
	echo " Update CardMonthly2003 with $LastMonth data\n";

	$sql = "replace into CardMonthly2003( CardNo, YearMonth, SpendVal, PointsEarned, Swipes, CreationDate ) 
		select CardNo, $LastMonth, sum( TransValue ), sum( PointsAwarded), count(*), now()  
		from Transactions$LastMonth group by CardNo"; 
		DBQueryExitOnFailure($sql);
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
	echo date("H:i:s");
	echo " Update AccountMonthly2003 with $LastMonth data\n";
	
	$sql = "replace into AccountMonthly2003( AccountNo, YearMonth, SpendVal, PointsEarned, Swipes )
		select AccountNo, $LastMonth, sum( TransValue ), sum( PointsAwarded), count(*)
		from Transactions$LastMonth 
		where AccountNo != 0 and AccountNo is not null
		group by AccountNo"; 
	
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Rows replaced ". mysql_affected_rows() ."\n";
}



function UpdateAccountMonthlyBits( $LastMonth )
{
	echo date("H:i:s");
	echo " Update Account Monthly Adjustments\n";

	$T = "TEMPORARY";

	$sql = "drop $T table IF Exists MonthAdjust";
	DBQueryExitOnFailure($sql);

	$sql = "create $T table MonthAdjust select AccountNo, sum( if( Stars > 0, Stars, 0 ) ) as APlus, sum( if(Stars < 0, Stars, 0 )) as AMinus from Tracking	where Date_format( CreationDate, '%Y%m' ) = $LastMonth  and Stars is not null and Stars != 0 group by AccountNo";
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Rows inserted ". mysql_affected_rows() ."\n";
	echo date("H:i:s");
	echo " Insert Account Monthly Adjustments into AccountMonthly2003\n";

	$sql = "insert into AccountMonthly2003 select AccountNo, $LastMonth, 0, 0, 0, 0, APlus, AMinus, now() from MonthAdjust  ON DUPLICATE  KEY UPDATE AccountMonthly2003.AdjustPlus = Values( AdjustPlus), AccountMonthly2003.AdjustMinus = values(AdjustMinus)";
	DBQueryExitOnFailure($sql);

	echo date("H:i:s");
	echo " Rows inserted ". mysql_affected_rows() ."\n";

	$sql = "drop $T table IF Exists MonthAdjust";
//	DBQueryExitOnFailure($sql);


 	echo date("H:i:s");
	echo " Update Account Monthly Redemptions\n";

	$sql = "drop $T table IF Exists MonthRedeem";
	DBQueryExitOnFailure($sql);

	$sql = "create $T table MonthRedeem select AccountNo, sum( Cost ) as Redeemed from Orders join OrderProducts using( OrderNo ) where Date_format( Orders.CreationDate, '%Y%m' ) = $LastMonth group by AccountNo";

	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Rows created ". mysql_affected_rows() ."\n";

	$sql = "insert into AccountMonthly2003 select AccountNo, $LastMonth, 0, 0, 0, Redeemed, 0, 0, now() from  MonthRedeem ON DUPLICATE KEY UPDATE AccountMonthly2003.PointsRedeemed = Values(PointsRedeemed)";
	echo date("H:i:s");
	echo " Insert Account Monthly Redemptions into AccountMonthly2003\n";
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
	echo " Rows created ". mysql_affected_rows() ."\n";

	$sql = "drop $T table IF Exists MonthRedeem";
//	DBQueryExitOnFailure($sql);

}

/*
function CreateCardAccountLookup()							   
{
	$sql = "drop table if exists CardAccount";
	DBQueryExitOnFailure($sql);

	echo date("H:i:s");
	echo "Create lookup table\n";
	$sql = "create table CardAccount select CardNo, AccountNo from Cards join Members using(MemberNo)";
	DBQueryExitOnFailure($sql);

	$sql = "alter table CardAccount add primary key( CardNo )";
	DBQueryExitOnFailure($sql);
	echo date("H:i:s");
}
*/

	connectToDB( MasterServer, TexacoDB );

//CreateCardAccountLookup();
	$spr = CreateProcessStartRecord( "MonthlyTotals" );

	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  echo Date("Y-m-d h:i:s");
	//*	 Echo " Start CreateMonthlySums\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$lastmonth =  GetLastMonth();

for( $c = 0; $c < 4; $c++ )
{
	echo "$lastmonth\n";
	UpdateCardMonthly( $lastmonth );
	UpdateAccountMonthly( $lastmonth );
	UpdateAccountMonthlyBits( $lastmonth );
	$lastmonth =  DecrementMonth( $lastmonth );
}
	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  	echo Date("Y-m-d h:i:s");
	//*     Echo " Finish CreateMonthlySums\n";

	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

	CompleteProcessRecord( $spr );

?>																	    