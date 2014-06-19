<?php 

	require "../../include/DB.inc";
	require "../../Reporting/GeneralReportFunctions.php";													

$timedate = date("Y-m-d H:i:s");

$Points = 0;
$count = 0;

$db_user = "pma001";
$db_pass = "amping";

echo "December 2006 Liability Reduction\n\r";
echo "Registered XD Accounts with LastSwipeDate (of any of the Accounts Cards) older than 2004-12-01\n\r
	and LastOrderDate older than 2005-12-01 \n\rand LastTrackingDate older than 2005-12-01 \n\r";
echo "Process Started - $timedate\n\r";

$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$sql = "select AccountNo,Balance from Analysis.LiabilityDataDec06 
	where (LastSwipeDate < '2004-12-01' or LastSwipeDate is NULL)
	and (LastOrderDate < '2005-12-01' or LastOrderDate is NULL) 
	and (LastTrackingDate < '2005-12-01' or LastTrackingDate is NULL)";

$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

echo "Number of Members - ". mysql_num_rows($slaveRes). "\n\r";


while( $row = mysql_fetch_assoc( $slaveRes ) )
{
	#	First add the points to the removed points total
	$Points += $row['Balance'];
	$count ++;

	#	Now wipe the Balance from the Account

	$sql = "Update Accounts set Balance = '0' where AccountNo = $row[AccountNo] limit 1;";
	
	#echo "SQL - $sql\n\r";
	
	mysql_query( $sql, $master )  or die( mysql_error($master) );
	
	
	#	Now we need to create a Tracking record

	$sql = "INSERT INTO `Tracking` 
				( `AccountNo` ,`Notes` , `Stars` , `CreatedBy` , `CreationDate` ) 
				VALUES 
				('$row[AccountNo]','Liability Management Dec 06. XD Member No Swipes 24Months+', '-$row[Balance]', 'Dec06Liability',now())";

	#echo "SQL - $sql\n\r";

	mysql_query( $sql, $master )  or die( mysql_error($master) );
	
	if( ($count % 50000) == 0 )
	{
		echo date("h:i:s");
		echo "Processed $count\n\r";
	}	
}
  
echo "Total Points Recovered = $Points\n\r";

$timedate = date("Y-m-d H:i:s");



echo "$timedate Process Completed\n\r";
?>