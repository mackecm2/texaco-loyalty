<?php
require "GeneralReportFunctions.php";
require "../include/DB.inc";


/*
Create Table LappsedLiability
(
    CreationDate Datetime,
    Type         char(1),
    No12Month bigint,
    Pts12Month bigint,
    No12Mon500 bigint,
    Pts12Mon500 bigint,
    No12Mon5000 bigint,
    Pts12Mon5000 bigint,
    No12Mon50000 bigint,
    Pts12Mon50000 bigint,
    No18Month bigint,
    Pts18Month bigint,
    No18Mon500 bigint,
    Pts18Mon500 bigint,
    No18Mon5000 bigint,
    Pts18Mon5000 bigint,
    No18Mon50000 bigint,
    Pts18Mon50000 bigint,
    Total bigint,
    TotalPoints bigint
)
*/

	connectToDB( ReportServer, AnalysisDB );

	$rec = CreateReportProcessLog( );
	$timedate = date("Y-m-d")." ".date("H:i:s");
	//* next line exchanged for the one below it for greater clarity in logs - MRM 26/08/2008
	//echo "\r\nCreateLapsedLiabilityReport.php - started $timedate\r\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

	$Month12 = DBSingleStatQuery( "select Date_sub( now(), interval 12 month )" );
	$Month18 = DBSingleStatQuery( "select Date_sub( now(), interval 18 month )" );


	echo date( "Y-m-d H:i:s" );
	echo " Starting Lapsed Liability Report Process Unregistered\n"; 

	$sql = "insert into Reporting.LappsedLiability select now() as CreationDate, 'U',
		sum( LastSwipeDate < '$Month12' ),
		sum( if(  LastSwipeDate < '$Month12', StoppedPoints, 0 ) ),
		sum( LastSwipeDate < '$Month12' and StoppedPoints < 500 ),
		sum( if(  LastSwipeDate < '$Month12' and StoppedPoints < 500, StoppedPoints, 0 ) ),
		sum( LastSwipeDate < '$Month12' and StoppedPoints between 500 and 5000 ),
		sum( if(  LastSwipeDate < '$Month12' and StoppedPoints between 500 and 5000, StoppedPoints, 0 ) ),
		sum( LastSwipeDate < '$Month12' and StoppedPoints > 5000 ),
		sum( if(  LastSwipeDate < '$Month12' and StoppedPoints > 5000, StoppedPoints, 0 ) ),

		sum( LastSwipeDate < '$Month18' ), 
		sum( if(  LastSwipeDate < '$Month18', StoppedPoints, 0 ) ),
		sum( LastSwipeDate < '$Month18' and StoppedPoints < 500 ),
		sum( if(  LastSwipeDate < '$Month18' and StoppedPoints < 500, StoppedPoints, 0 ) ),
		sum( LastSwipeDate < '$Month18' and StoppedPoints between 500 and 5000 ),
		sum( if(  LastSwipeDate < '$Month18' and StoppedPoints between 500 and 5000, StoppedPoints, 0 ) ),
		sum( LastSwipeDate < '$Month18' and StoppedPoints > 5000 ),
		sum( if(  LastSwipeDate < '$Month18' and StoppedPoints > 5000, StoppedPoints, 0 ) ),

		
		count(*), 
		sum( StoppedPoints )
		from texaco.Cards where MemberNo is null";

	DBQueryExitOnFailure( $sql );

	$sql = "drop table if exists LappsedAccounts";
	DBQueryExitOnFailure( $sql );	

	/*
	
	$sql = "create table LappsedAccounts select Accounts.AccountNo, Balance, max( LastSwipeDate) 
			from texaco.Cards join texaco.Members using(MemberNo) 
			join texaco.Accounts using(AccountNo) group by AccountNo";
	
	DBQueryExitOnFailure( $sql );	
	*/		

	echo date( "Y-m-d H:i:s" );
	echo " Starting Lapsed Liability Report Process Registered\n"; 

	$sql = "drop table if exists t1";
	DBQueryExitOnFailure( $sql );


	$sql = "create Table t1 select AccountNo, max( LastSwipeDate) as LastSwipeDate 
		from texaco.Cards join texaco.Members using(MemberNo)  group by AccountNo";

	DBQueryExitOnFailure( $sql );

	$sql = "create Table LappsedAccounts select Balance, LastSwipeDate 
		from texaco.Accounts join t1 using( AccountNo )"; 

	DBQueryExitOnFailure( $sql );

	$sql = "insert into Reporting.LappsedLiability select now() as CreationDate, 'R',
		sum( LastSwipeDate < '$Month12' ),
		sum( if(  LastSwipeDate < '$Month12', Balance, 0 ) ),
		sum( LastSwipeDate < '$Month12' and Balance < 500 ),
		sum( if(  LastSwipeDate < '$Month12' and Balance < 500, Balance, 0 ) ),
		sum( LastSwipeDate < '$Month12' and Balance between 500 and 5000 ),
		sum( if(  LastSwipeDate < '$Month12' and Balance between 500 and 5000, Balance, 0 ) ),
		sum( LastSwipeDate < '$Month12' and Balance > 5000 ),
		sum( if(  LastSwipeDate < '$Month12' and Balance > 5000, Balance, 0 ) ),

		sum( LastSwipeDate < '$Month18' ), 
		sum( if(  LastSwipeDate < '$Month18', Balance, 0 ) ),
		sum( LastSwipeDate < '$Month18' and Balance < 500 ),
		sum( if(  LastSwipeDate < '$Month18' and Balance < 500, Balance, 0 ) ),
		sum( LastSwipeDate < '$Month18' and Balance between 500 and 5000 ),
		sum( if(  LastSwipeDate < '$Month18' and Balance between 500 and 5000, Balance, 0 ) ),
		sum( LastSwipeDate < '$Month18' and Balance > 5000 ),
		sum( if(  LastSwipeDate < '$Month18' and Balance > 5000, Balance, 0 ) ),

		count(*), 
		sum( Balance )
		from LappsedAccounts";

	DBQueryExitOnFailure( $sql );


	echo date( "Y-m-d H:i:s" );
	echo " Finished Lapsed Liability Report Process\n"; 

	CompleteProcessLog( $rec );
	$timedate = date("Y-m-d")." ".date("H:i:s");
	
		//* next line exchanged for the one below it for greater clarity in logs - MRM 26/08/2008
	//echo "CreateLapsedLiabilityReport.php - completed $timedate\r\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>