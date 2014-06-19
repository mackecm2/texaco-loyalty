<?php
require "GeneralReportFunctions.php";
require "../include/DB.inc";
		
function CreatePointsReview( $MonthYYYY, $LastMonth  )
{
    //  Mantis 2505 MRM 11 OCT 10 - Exclude Stopped Points from all calculations
	//	Cards registered and used in 0 - 12 months	
	$sql = "select SUM(Balance) + sum( if( StoppedPoints < 0, 0, StoppedPoints ) ) AS Total from Analysis.Liability$MonthYYYY where DATEDIFF( NOW(),LastActiveDate ) < 366";
  	$tot1 = DBSingleStatQuery($sql) ; 
	$RunningTotal = $tot1;
  	//	Cards not registered but used in 0 - 12 months	 
	$sql = "SELECT sum( if( StoppedPoints < 0, 0, StoppedPoints ) ) AS Total FROM texaco.Cards WHERE MemberNo IS NULL AND LastSwipeDate IS NOT NULL AND DATEDIFF( NOW(),LastSwipeDate ) < 366"; 
  	$tot2 = DBSingleStatQuery($sql) ;
	$RunningTotal = $RunningTotal + $tot2;
  	//	Cards registered but not used in 13 - 18 months	 
	$sql = "select SUM(Balance) + sum( if( StoppedPoints < 0, 0, StoppedPoints ) ) AS Total from Analysis.Liability$MonthYYYY where ( DATEDIFF( NOW(),LastActiveDate ) > 365 AND DATEDIFF( NOW(),LastActiveDate ) < 549 )";
  	$tot3 = DBSingleStatQuery($sql) ;
	$RunningTotal = $RunningTotal + $tot3;
  	//	Cards not registered and not used in 13 - 18 months
	$sql = "SELECT sum( if( StoppedPoints < 0, 0, StoppedPoints ) ) AS Total FROM texaco.Cards WHERE MemberNo IS NULL AND LastSwipeDate IS NOT NULL AND ( DATEDIFF( NOW(),LastSwipeDate ) > 365 AND DATEDIFF( NOW(),LastSwipeDate ) < 549 )";
  	$tot4 = DBSingleStatQuery($sql) ;
	$RunningTotal = $RunningTotal + $tot4;
  	//	Cards registered but not used in 19 - 24 months	 
	$sql = "select SUM(Balance) + sum( if( StoppedPoints < 0, 0, StoppedPoints ) ) AS Total from Analysis.Liability$MonthYYYY where ( DATEDIFF( NOW(),LastActiveDate ) > 548 AND DATEDIFF( NOW(),LastActiveDate ) < 732 )";
   	$tot5 = DBSingleStatQuery($sql) ;
	$RunningTotal = $RunningTotal + $tot5;
   	//	Cards not registered and not used in 19 - 24 months
	$sql = "SELECT sum( if( StoppedPoints < 0, 0, StoppedPoints ) ) AS Total FROM texaco.Cards WHERE MemberNo IS NULL AND LastSwipeDate IS NOT NULL AND ( DATEDIFF( NOW(),LastSwipeDate ) > 548 AND DATEDIFF( NOW(),LastSwipeDate ) < 732 )";
  	$tot6 = DBSingleStatQuery($sql) ;
	$RunningTotal = $RunningTotal + $tot6;
  	//	Cards registered but not used in 24+ months	 
	$sql = "select SUM(Balance) + sum( if( StoppedPoints < 0, 0, StoppedPoints ) ) AS Total from Analysis.Liability$MonthYYYY where ( DATEDIFF( NOW(),LastActiveDate ) > 731 )";
  	$tot7 = DBSingleStatQuery($sql) ;
	$RunningTotal = $RunningTotal + $tot7;
  	//	Cards not registered and not used in 24+ months
	$sql = "SELECT sum( if( StoppedPoints < 0, 0, StoppedPoints ) ) AS Total FROM texaco.Cards WHERE MemberNo IS NULL AND (( DATEDIFF( NOW(),LastSwipeDate ) > 731 ) OR LastSwipeDate IS NULL )";
	$tot8 = DBSingleStatQuery($sql) ;
	$RunningTotal = $RunningTotal + $tot8;
  	//	Cards registered with no swipe date	
	$sql = "SELECT IF((Sum( Balance ) + sum( if( StoppedPoints < 0, 0, StoppedPoints ) )) IS NULL,0,(Sum( Balance ) + sum( if( StoppedPoints < 0, 0, StoppedPoints ) ))) AS Total FROM Analysis.Liability$MonthYYYY WHERE LastActiveDate IS NULL OR LastActiveDate = 0";
	$tot9 = DBSingleStatQuery($sql) ;
	$RunningTotal = $RunningTotal + $tot9;
	
	    //  Mantis 2505 MRM 25 OCT 10 - need points redeemable for registered members who have swiped in the last 12 months
	//	Cards registered and used in 0 - 12 months	
	$sql = "select SUM(FLOOR( Balance / 500 ) * 500) AS Total from Analysis.Liability$MonthYYYY where Balance >0 AND DATEDIFF( NOW( ) , LastSwipeDate ) < 367";
	
	$tot10 = DBSingleStatQuery($sql) ;

	$sql = "replace into MonthlyLiablityReport ( YearMonth, MemberType, RunningTotal, tot1, tot2, tot3, tot4, tot5, tot6, tot7, tot8, tot9, tot10 )
	values ( $LastMonth, 'Process', $RunningTotal, $tot1, $tot2, $tot3, $tot4, $tot5, $tot6, $tot7, $tot8, $tot9, $tot10 )";

	DBQueryExitOnFailure( $sql );

}

function CreateMonthCreditDebits( $LastMonth )
{
	$sql = "select
	sum(if(Stars>0, Stars, 0 ) ) as Credits,
	sum( if( Stars<0, 0 - Stars, 0 ) ) as Debits
	from texaco.Tracking join texaco.TrackingCodes using ( TrackingCode ) 
	where Stars != 0 and Date_format( CreationDate, '%Y%m' ) = $LastMonth AND CreditDebit = 'Y'";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_assoc( $results );
	
	$sql = "SELECT sum( if( Stars <0, 0 - Stars, 0 ) ) AS PointsRelease
		FROM texaco.Tracking
		JOIN texaco.TrackingCodes
		USING ( TrackingCode ) 
		WHERE Stars !=0
		AND Date_format( CreationDate, '%Y%m' ) = $LastMonth AND TrackingCode = 1166";
	$results = DBQueryExitOnFailure( $sql );
	$row2 = mysql_fetch_assoc( $results );
	
	
	
	$sql = "update MonthlyLiablityReport Set Credits = $row[Credits], Debits = $row[Debits]-$row2[PointsRelease], PointsRelease=$row2[PointsRelease] where YearMonth = $LastMonth and MemberType = 'Process'";
	DBQueryExitOnFailure( $sql );

	$sql = "replace into MonthlyLiablityReport ( YearMonth, MemberType, Credits, Debits )
	values ( $LastMonth, 'Swipe Date', $row[Credits], $row[Debits] )";
	DBQueryExitOnFailure( $sql );

}

function CreateMonthRedeems( $LastMonth )
{
	$sql = "select	sum( Cost  ) as Redeemed
	from texaco.Orders Join texaco.OrderProducts using( OrderNo )
	where Date_format( CreationDate, '%Y%m' ) = $LastMonth and Status NOT IN ( 'R', 'C' )";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_assoc( $results );
	if( $row[Redeemed] )  
	{		
		$sql = "update MonthlyLiablityReport Set MonthlyLiablityReport.Redeemed = $row[Redeemed] where YearMonth = $LastMonth and MemberType = 'Process'";
		DBQueryExitOnFailure( $sql );
	
		$sql = "update MonthlyLiablityReport Set MonthlyLiablityReport.Redeemed = $row[Redeemed] where YearMonth = $LastMonth and MemberType = 'Swipe Date'";
		DBQueryExitOnFailure( $sql );
	}
}

function CreateMonthSpend( $LastMonth )
{
	$sql = "select
	sum( TransValue) as Spend,
	count(*) as Swipes,
	sum(PointsAwarded) as Points
	from texaco.Transactions
	where Date_format( CreationDate, '%Y%m' )= $LastMonth";

 	$results = DBQueryExitOnFailure( $sql );

	$row = mysql_fetch_assoc( $results );

	$sql = "update MonthlyLiablityReport
	Set MonthlyLiablityReport.Spend = $row[Spend],
	MonthlyLiablityReport.Swipes = $row[Swipes],
	MonthlyLiablityReport.Points = $row[Points]
	where YearMonth = $LastMonth and MemberType = 'Process'";
	DBQueryExitOnFailure( $sql );
}

function CreateMonthSpend2( $LastMonth )
{
	$sql = "select
	sum( TransValue) as Spend,
	count(*) as Swipes,
	sum(PointsAwarded) as Points
	from texaco.Transactions$LastMonth";

 	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_assoc( $results );

	$sql = "update MonthlyLiablityReport
	Set MonthlyLiablityReport.Spend = $row[Spend],
	MonthlyLiablityReport.Swipes = $row[Swipes],
	MonthlyLiablityReport.Points = $row[Points]
	where YearMonth = $LastMonth and MemberType = 'Swipe Date'";
	DBQueryExitOnFailure( $sql );
}

function CreatePointsRedeemable( $LastMonth, $LastMonthInWords )
{
	$sql = "SELECT SUM( FLOOR( Balance /500 ) *500 ) AS Redemptionliability 
	FROM Analysis.Liability$LastMonthInWords WHERE Balance >0 AND DATEDIFF( NOW( ) , LastSwipeDate ) < 367";

 	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_assoc( $results );

	$sql = "update MonthlyLiablityReport
	Set MonthlyLiablityReport.PointsRedeemable = $row[Redemptionliability]
	where YearMonth = $LastMonth and MemberType = 'Process'";
	DBQueryExitOnFailure( $sql );
}
/*
function CreateRunningTotal( $LastMonth )
{
	$sql = "SELECT SUM( Balance ) AS BalanceTotal FROM texaco.Accounts";
 	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_assoc( $results );
	$balancetotal = $row[BalanceTotal]; 
	
	$sql = "SELECT SUM( StoppedPoints ) AS PointsTotal FROM texaco.Cards WHERE StoppedPoints > 0";
 	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_assoc( $results );
	$pointstotal = $row[PointsTotal]; 
	
	$runningtotal = $pointstotal + $balancetotal;  

	$sql = "UPDATE MonthlyLiablityReport SET RunningTotal = $runningtotal where YearMonth = $LastMonth";
	DBQueryExitOnFailure( $sql );
}
*/
function CreateTotals()

// MANTIS 2505 MRM 28 OCT 2010 Create a totals record for the Process report, using '999999999' as the YearMonth  

{
	$sql = "SELECT '999999999' AS YearMonth, SUM( Spend ) AS Spend,
 	SUM( Swipes ) AS Swipes, SUM( Points ) AS Points, SUM( Redeemed ) AS Redeemed, 
 	SUM( Credits ) AS Credits, SUM( PointsRelease ) AS PointsRelease, SUM( Debits ) AS Debits, 
 	NULL AS 'Liability +/-', NULL AS PointsRedeemable, NULL AS RunningTotal
	FROM MonthlyLiablityReport WHERE MemberType = 'Process' AND YearMonth > 201000";
 	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_assoc( $results );
	
	$sql = "replace into MonthlyLiablityReport
			( YearMonth, MemberType, Spend, Swipes, Points, Redeemed, Credits, PointsRelease, Debits )
			values ( $row[YearMonth], 'Process', $row[Spend], $row[Swipes], $row[Points], $row[Redeemed], $row[Credits],
			 $row[PointsRelease], $row[Debits] )";
	DBQueryExitOnFailure( $sql );
}

function CreateLiabilityMonth( $LastMonth, $ThisMonthInWords )
{
	echo date("H:i:s");
	echo " Creating Liability Report for $LastMonth";
  	CreateMonthCreditDebits( $LastMonth );
	echo ".";
  	CreateMonthRedeems( $LastMonth );
	echo ".";
 	CreateMonthSpend( $LastMonth );
	echo ".";
  	CreateMonthSpend2( $LastMonth );
  	echo ".";
  	CreatePointsRedeemable( $LastMonth, $ThisMonthInWords );
  	echo "Done\n";
}


//- - - - - - -   M A I N   P R O C E S S   - - - - - - - -//

	connectToDB( ReportServer, ReportDB );

	$rec = CreateReportProcessLog( );
	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

	if( $argc == 1 )
	{
		$ThisMonth = GetThisMonth();
		$LastMonth = GetLastMonth();
	}
	else
	{
		$ThisMonth = $argv[1];
		$LastMonth = DecrementMonth($argv[1]);
	}
	$ThisMonthInWords = ConvertMonthToWord( $ThisMonth );
	
	echo date("H:i:s");
	echo " Creating Points Review for $LastMonth...";
	CreatePointsReview($ThisMonthInWords, $LastMonth);
	echo " Done\n";

	for( $i = 0; $i < 6; $i++ )
	{
		CreateReportLog( "Liability for $LastMonth" );
		CreateLiabilityMonth($LastMonth, $ThisMonthInWords);
		$ThisMonthInWords = ConvertMonthToWord( $LastMonth );
		$LastMonth = DecrementMonth( $LastMonth );
	}
	
	echo date("H:i:s");
	echo " Creating Totals Review...";	
	CreateTotals();
	CompleteProcessLog( $rec );
	echo "Done\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>