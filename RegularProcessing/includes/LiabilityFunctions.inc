<?php 
/*
 * ---------------------------------------------------
 * Liability Accrual - create working tables
 * ---------------------------------------------------
 * Author : MRM
 * Date   : 21 OCT 09
 * 
 * This is basically the section 0 of RegularProcessing/Monthly/LiabilityAccrual.php
 * 
 * 
 * 
 * 
 */
function CreateWorkingTables($slave,$timestamp)
{
	//------------------------------------------------------------------------------------------------------------------------------------
	//------- Section 0 - Create Working Tables ------------------------------------------------------------------------------------------
	//------------------------------------------------------------------------------------------------------------------------------------
	$sql = "drop table if exists AccountsLastSwipe";
	
	mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	echo date("Y-m-d H:i:s")." creating AccountsLastSwipe \r\n";
	$sql = "create table AccountsLastSwipe SELECT Accounts.AccountNo, max( Cards.LastSwipeDate ) AS LastSwipeDate
		FROM texaco.Accounts
		LEFT JOIN texaco.Members
		USING ( AccountNo ) 
		LEFT JOIN texaco.Cards
		USING ( MemberNo ) 
		GROUP BY AccountNo";
	
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	echo date("Y-m-d H:i:s")." add Primary Key to AccountsLastSwipe \r\n";
	$sql = "ALTER TABLE AccountsLastSwipe ADD PRIMARY KEY ( AccountNo )" ;
	
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	//Create MaxOrders table, to calculate the Last Redemption Date for the each Account:
	
	$sql = "drop table if exists MaxOrders";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	echo date("Y-m-d H:i:s")." creating MaxOrders \r\n";
	$sql = "create table MaxOrders select 
	AccountNo,max(CreationDate) as LastOrderDate 
	from texaco.Orders 
	group by AccountNo";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	$sql = "ALTER TABLE MaxOrders ADD PRIMARY KEY ( AccountNo )" ;
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	//Create Last Tracking Date table:
	
	$sql = "drop table if exists MaxTracking";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	echo date("Y-m-d H:i:s")." creating MaxTracking \r\n";
	$sql = "create table MaxTracking SELECT Accounts.AccountNo, max( Tracking.CreationDate ) AS LastTrackingDate
		FROM texaco.Accounts
		JOIN texaco.Tracking
		USING ( AccountNo ) 
		JOIN texaco.TrackingCodes
		USING ( TrackingCode ) 
		WHERE TrackingCodes.CreditDebit = 'Y'
		AND TrackingCodes.Active = 'Y'
		GROUP BY Accounts.AccountNo";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	$sql = "ALTER TABLE MaxTracking ADD PRIMARY KEY ( AccountNo )";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	//These will then be combined as follows:
	echo date("Y-m-d H:i:s")." creating Liability".$timestamp." \r\n";
	$sql = "drop table if exists Liability".$timestamp;
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	$sql = "create table Liability".$timestamp." 
	select 
	Accounts.AccountNo,
	SegmentCode,
	Balance, 
	AccountType,
	t2.LastOrderDate,
	t3.LastTrackingDate,
	t4.LastSwipeDate,
	GREATEST( IFNULL( t2.LastOrderDate, 0 ) , IFNULL( t3.LastTrackingDate, 0 ) , IFNULL( t4.LastSwipeDate, 0 ) ) AS LastActiveDate
	from texaco.Accounts
	left join MaxOrders  as t2  on (Accounts.AccountNo = t2.AccountNo)
	left join MaxTracking  as t3  on (Accounts.AccountNo = t3.AccountNo)
	left join AccountsLastSwipe  as t4  on (Accounts.AccountNo = t4.AccountNo)
	group by Accounts.AccountNo";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	$sql = "ALTER TABLE Liability".$timestamp." ADD PRIMARY KEY ( AccountNo )" ;
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	$sql = "ALTER TABLE Liability".$timestamp." ADD StoppedPoints INT( 11 ) NOT NULL DEFAULT '0' AFTER Balance";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	$sql = "SELECT A.AccountNo, SUM( C.StoppedPoints ) AS StoppedPts FROM texaco.Cards AS C 
	JOIN texaco.Members AS M USING ( MemberNo ) JOIN texaco.Accounts AS A USING ( AccountNo ) WHERE StoppedPoints > 0 GROUP BY AccountNo"; 
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		if ( $row[StoppedPts] != 0 )
		{
			$sql1 = "UPDATE Liability".$timestamp." SET StoppedPoints = $row[StoppedPts] WHERE AccountNo = $row[AccountNo] ";
			$slaveRes1 = mysql_query( $sql1, $slave ) or die( mysql_error($slave) );
		}
	}
	
	
	
	
	
	
	if ($timestamp == "WeeklyTable")
	{
		echo date("Y-m-d H:i:s")." creating LiabilityCardsWeeklyTable\r\n";
	
		$sql = "drop table if exists LiabilityCardsWeeklyTable";
		$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		
		$sql = "create table LiabilityCardsWeeklyTable 
		SELECT CardNo, StoppedPoints, LastSwipeDate
		FROM texaco.Cards
		WHERE StoppedPoints <>0
		AND MemberNo IS NULL";
		$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		
	}
}

function RegisteredLiability($timestamp, $days)
{
	$sql = "select AccountNo,Balance from Analysis.Liability".$timestamp."
 		where ( DATEDIFF( NOW(),LastSwipeDate ) > $days or LastSwipeDate is NULL )
  		and ( DATEDIFF( NOW(),LastOrderDate ) > $days or LastOrderDate is NULL )
  		and ( DATEDIFF( NOW(),LastTrackingDate ) > $days or LastTrackingDate is NULL ) and Balance <> 0
  		and ( AccountType <> 'G' or AccountType IS NULL ) ";
	return ($sql);
}

function RegisteredLiabilityStoppedPoints($timestamp, $days)

// 02/02/10 MRM 			"WHERE A.Balance = 0 AND A.SegmentCode LIKE 'XD%'" changed to "WHERE A.Balance = 0"
// 08/03/10 MRM 			DATEDIFF for redemption and swipe dates added
{
	$sql = "SELECT AccountNo, CardNo, StoppedPoints FROM Cards 
			JOIN Members AS M
			USING ( MemberNo ) 
			JOIN Accounts AS A
			USING ( AccountNo ) JOIN Analysis.Liability".$timestamp." AS L USING ( AccountNo ) 
			WHERE A.Balance = 0 
			 AND Cards.StoppedPoints <> 0
			 AND (A.AccountType <> 'G' OR A.AccountType IS NULL)			 
 		AND ( DATEDIFF( NOW(),L.LastSwipeDate ) > $days OR L.LastSwipeDate is NULL )
  		AND ( DATEDIFF( NOW(),L.LastOrderDate ) > $days OR L.LastOrderDate is NULL )
  		AND ( DATEDIFF( NOW(),L.LastTrackingDate ) > $days OR L.LastTrackingDate is NULL )";
	return ($sql);
}

function RegisteredFraudAccLiability()

{
	$sql = "SELECT AccountNo,Balance, CardNo, SUM(StoppedPoints) AS StoppedPoints FROM texaco.Accounts 
	JOIN texaco.AccountStatus USING ( AccountNo ) 
	JOIN texaco.Members USING ( AccountNo ) 
	JOIN texaco.Cards USING ( MemberNo ) 
	WHERE RedemptionStopDate IS NOT NULL  AND AwardStopDate IS NOT NULL AND FraudStatus = '4' 
	AND ( Balance <> 0 OR StoppedPoints <> 0 ) GROUP BY AccountNo";
	return ($sql);
}

function UnregisteredLiability($days)
{
	$sql = "select CardNo, StoppedPoints from Cards where (StoppedPoints <> 0 and MemberNo is NULL and LastSwipeDate < '$days')
	 or (SegmentCode  like 'XD%' and StoppedPoints <> 0 and MemberNo is NULL
	  and (LastSwipeDate < '$days' OR (LastSwipeDate IS NULL AND CardType = 'WEOU')))";
	return ($sql);
}
?>