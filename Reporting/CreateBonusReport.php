<?php 
	
	$Reporting = true;

	require "../include/DB.inc";
	require "GeneralReportFunctions.php";

/*
	Create Table MonthlyBonusReport
	(
	    PromotionCode char(10),
	    Month         int,
	    SiteCode      int,
	    Hits          int,
	    BonusCost     int,
	    TotalCost     int,
	    Revenue       Decimal(10, 2 ),
	    index( PromotionCode, Month, SiteCode )
	);
*/

	function ProcessBonusMonth( $Month )
	{
		$sql = "delete from MonthlyBonusReport where Month = $Month";
		DBQueryExitOnFailure( $sql );

		$sql = "replace into MonthlyBonusReport (PromotionCode, Month, SiteCode, Hits, BonusCost, TotalCost, Revenue ) select PromotionCode, $Month, SiteCode, count(*) as Hits, sum(ThisBonusPoints), sum(TotalPointsAwarded), sum(TransactionValue) from NonnormailsedBonusLog$Month group by PromotionCode, SiteCode";
		DBQueryExitOnFailure( $sql );
	}

	connectToDB();

	$Month = 200411;
	$LastMonth = GetLastMonth();

	while( $Month <= $LastMonth )
	{
		echo "Bonus report for $Month\n";
		ProcessBonusMonth( $Month );
		$Month = IncrementMonth( $Month );
	}
?>