<?php 

	require "../../include/DB.inc";
	require "../../Reporting/GeneralReportFunctions.php";													

$timedate = date("Y-m-d H:i:s");


$Points = 0;
$TotalStars = 0;
$count = 0;

$db_user = "pma001";
$db_pass = "amping";

echo "Mantis 986 Retro Site Closure Bonus Allocation\n\r";
echo "Process Started - $timedate\n\r";


/*

Already ran:

CREATE TABLE RetroDoubleBonusAccounts SELECT CardNo, AccountNo, SiteCode FROM Transactions 
WHERE (SiteCode = 886802 AND TransTime > DATE_SUB('2008-09-18' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 886438 AND TransTime > DATE_SUB('2008-09-29' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 886136 AND TransTime > DATE_SUB('2008-10-08' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 886138 AND TransTime > DATE_SUB('2008-10-14' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 886822 AND TransTime > DATE_SUB('2008-10-24' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 491089 AND TransTime > DATE_SUB('2008-11-17' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 254110 AND TransTime > DATE_SUB('2008-12-05' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 251800 AND TransTime > DATE_SUB('2009-01-13' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 886327 AND TransTime > DATE_SUB('2009-01-22' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 491102 AND TransTime > DATE_SUB('2009-01-06' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 886843 AND TransTime > DATE_SUB('2009-02-07' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL)
OR (SiteCode = 610698 AND TransTime > DATE_SUB('2009-02-10' ,INTERVAL 6 MONTH) AND AccountNo IS NOT NULL) GROUP BY CardNo;

ALTER TABLE `RetroDoubleBonusAccounts` ADD `ClosureDate` DATE NOT NULL;

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2009-01-06' WHERE SiteCode = 491102;  

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2008-09-18' WHERE SiteCode = 886802;  

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2008-09-29' WHERE SiteCode = 886438;   

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2008-10-08' WHERE SiteCode = 886136;     

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2008-10-14' WHERE SiteCode = 886138;    

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2008-10-24' WHERE SiteCode = 886822;   

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2008-11-17' WHERE SiteCode = 491089;    

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2008-12-05' WHERE SiteCode = 254110;    

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2009-01-13' WHERE SiteCode = 251800;     

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2009-01-22' WHERE SiteCode = 886327;     

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2009-02-07' WHERE SiteCode = 886843;     

UPDATE RetroDoubleBonusAccounts SET ClosureDate = '2009-02-10' WHERE SiteCode = 610698;    




*/





$master = connectToDB( MasterServer, TexacoDB );



$sql = "SELECT * FROM Transactions AS T JOIN RetroDoubleBonusAccounts AS R USING ( CardNo ) 
WHERE T.TransTime > R.ClosureDate AND T.TransTime < DATE_ADD(R.ClosureDate, INTERVAL 4 WEEK) AND TransValue >= 25";

$Res = mysql_query( $sql, $master ) or die( mysql_error($master) );

echo "Number of Transactions - ". mysql_num_rows($Res). "\n\r";


while( $row = mysql_fetch_assoc( $Res ) )
{
	
	echo "CardNo $row[CardNo] / Month $row[Month] / Value $row[TransValue] / TxNo $row[TransactionNo]\r\n";

	// Calculate the points due
	$Stars = number_format($row['TransValue'], 0, '.', ''); 
	#echo "Points to be allocated: $Stars\r\n";
	
	#	Add the points to the Retro points total
	$TotalStars += $Stars;
	$count ++;

	
	//	Check this has not already been applied.
	
	$sql = "select * from BonusHit$row[Month] where PromotionCode = 'SITECLS01' and TransactionNo = $row[TransactionNo] limit 1";
	$res = mysql_query( $sql, $master );
	
	if(mysql_fetch_assoc( $res ))
	{
	
		// It already exists.
		echo "This bonus has already been applied\r\n";
	
	}
	else
	{
	
		//	Now create a BonusHit record.

		$sql = "INSERT INTO BonusHit$row[Month] (`Month` ,`TransactionNo` ,`SequenceNo` ,`PromotionCode` ,`Points` ) VALUES ($row[Month],$row[TransactionNo] , '5', 'SITECLS01', $Stars)";
		echo "Insert sql:\r\r $sql \r\n";

		mysql_query( $sql, $master )  or die( mysql_error($master) );

		//	Now update the Transaction record

		$sql = "update Transactions$row[Month] set PointsAwarded = (PointsAwarded + $Stars) where TransactionNo = '$row[TransactionNo]'  limit 1";

		echo "Tx update sql: \r\n $sql\n\r";

		mysql_query( $sql, $master )  or die( mysql_error($master) );


		//	Now we need to create a Tracking record


		$sql = "INSERT INTO `Tracking` 
					( `AccountNo` ,`TrackingCode` ,`Notes` , `Stars` , `CreatedBy` , `CreationDate` ) 
					VALUES 
					('$row[AccountNo]','1200','$Stars added for historical SiteClosure not originally bonused', '0', 'SteveT',now())";



		#echo "Tracking sql : \r\n $sql\n\r";

		mysql_query( $sql, $master )  or die( mysql_error($master) );

		// 	Now update the Account.

		$sql = "update Accounts set Balance = (Balance + $Stars) where AccountNo = $row[AccountNo] limit 1";

		echo "Account update sql: \r\n $sql\n\r";

		mysql_query( $sql, $master )  or die( mysql_error($master) );
	

	}

	
	unset($row);

}

$timedate = date("Y-m-d H:i:s");

echo "$timedate Process Completed\n\r";
echo "Total Points Retrospectively allocated:  $TotalStars\n\r";
echo "Number of Transactions: $count\n\r";


?>
