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

	$slave = connectToDB( ReplicationServer, TexacoDB );

	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "Select * from missingTransactions07";

	$results = mysql_query( $sql, $slave );

	while( $row = mysql_fetch_assoc( $results ) )
	{
		$sql = "select AccountNo from Cards join Members using( MemberNo ) where CardNo = '$row[CardNo]'";

		$results2 = mysql_query( $sql, $master );
		if( !$results2 )
		{
			echo mysql_error();
		}

		if( mysql_num_rows( $results2 ) == 0 )
		{
			$sql = "Update Cards set StoppedPoints = StoppedPoints + $row[StoppedPoints],
					TotalSwipes = TotalSwipes  + $row[Swipes],
					TotalSpend  = TotalSpend + $row[TotalSpend],
					FirstSwipeLoc = $row[FirstSwipeLoc],
					LastSwipeLoc = $row[LastSwipeLoc],
					FirstSwipeDate = '$row[FirstSwipeDate]',
					LastSwipeDate = '$row[LastSwipeDate]'
					where CardNo = '$row[CardNo]'";
			echo $sql;
			if( !mysql_query( $sql, $master ))
			{
				echo mysql_error();
			}
		}
		else
		{
			$row2 = mysql_fetch_row( $results2 );
			$AccountNo = $row2[0];

			$sql = "Update Cards set 
					TotalSwipes = TotalSwipes  + $row[Swipes],
					TotalSpend  = TotalSpend + $row[TotalSpend],
					FirstSwipeLoc = $row[FirstSwipeLoc],
					LastSwipeLoc = $row[LastSwipeLoc],
					FirstSwipeDate = '$row[FirstSwipeDate]',
					LastSwipeDate = '$row[LastSwipeDate]'
					where CardNo = '$row[CardNo]'";
			echo $sql;
 	 		if( !mysql_query( $sql, $master ))
			{
				echo mysql_error();
			}

			$sql = "Update Accounts set Balance = Balance + $row[StoppedPoints] where AccountNo = $AccountNo";
			echo $sql;
			if( !mysql_query( $sql, $master ))
			{
				echo mysql_error();
			}
		}
	}

?>