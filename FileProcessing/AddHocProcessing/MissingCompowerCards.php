 /*
 create table missingCards 
 select t.CardNo, 
 sum(PointsAwarded) as StoppedPoins, 
 min(TransTime) as FirstSwipeDate, 
 max(TransTime) as LastSwipeDate, 
 min(SiteCode) as FirstSwipeLoc, 
 max(SiteCode) as LastSwipeLoc, 
 sum(TransValue) as TotalSpend,
 count(*) as Swipes
 from texaco.Transactions as t left join texaco.Cards as c using(CardNo) 
 where c.CardNo is null
 and t.CreationDate > '2005-06-01'
 group by t.CardNo; 
*/
<?php
	include "../../include/DB.inc";
	$db_user = "root";
	$db_pass = "trave1";																		   

	$slave = connectToDB( ReplicationServer, AnalysisDB );

	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "Select * from missingCards11";

	$results = mysql_query( $sql, $slave );

	while( $row = mysql_fetch_assoc( $results ) )
	{
		$sql = "insert into Cards 
			(CardNo, StoppedPoints, FirstSwipeDate, LastSwipeDate, FirstSwipeLoc, LastSwipeLoc, TotalSpend, TotalSwipes, CreationDate, CreatedBy )
			values
			( '$row[CardNo]', $row[StoppedPoints], '$row[FirstSwipeDate]','$row[LastSwipeDate]' , $row[FirstSwipeLoc], $row[LastSwipeLoc], $row[TotalSpend], $row[Swipes], '2005-08-12 12:00:00', 'COMPOWER' )";
		echo ".";
	 	if( !mysql_query( $sql, $master ))
		{
			echo mysql_error();
		}

	}

?>