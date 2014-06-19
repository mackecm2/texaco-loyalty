<?php 

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

$Points = 0;
$count = 0;

$db_user = "pma001";
$db_pass = "amping";
	
echo "Liability Reduction Process\n\r";
echo "Cards with No MemberNo where LastSwipeDate is older than 2006-05-01\n\r";
echo "Process Started - $timedate\n\r";

$master = connectToDB( MasterServer, TexacoDB );

$sql = "select CardNo,StoppedPoints from Cards where SegmentCode  like 'XD%'
and StoppedPoints > 0 and MemberNo is NULL and LastSwipeDate < '2006-05-01 00:00:00'";

$masterRes = mysql_query( $sql, $master ) or die( mysql_error($master) );

echo "Number of Members - ". mysql_num_rows($masterRes). "\n\r";


while( $row = mysql_fetch_assoc( $masterRes ) )
{
	#	First add the points to the removed points total
	$Points += $row['StoppedPoints'];
	$count ++;

	#	Now wipe the points from the Card

	$sql = "Update Cards set StoppedPoints = '0' where CardNo = '$row[CardNo]' limit 1;";
	#echo "SQL - $sql\n\r";
	
	mysql_query( $sql, $master )  or die( mysql_error($master) );

	#	Now write the Card Details etc away in the LiabilityReduction table

	$sql = "INSERT INTO `LiabilityReduction` 
		( `CardNo` , `Points` , `CreatedBy` , `CreationDate` ) 
		VALUES ($row[CardNo], $row[StoppedPoints], 'Nov07Process', now())";

	#echo "SQL - $sql\n\r";
	
	
	mysql_query( $sql, $master )  or die( mysql_error($master) );

	if( ($count % 20000) == 0 )
	{
		echo date("h:i:s");
		echo "Processed $count\n\r";
	}	
}
  
echo "Total Points Recovered = $Points\n\r";

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";


#	Now we need to create a Tracking record

$sql = "INSERT INTO `Tracking` 
			( `Notes` , `Stars` , `CreatedBy` , `CreationDate` ) 
			VALUES 
			('Points removed from Unregistered Cards with LastSwipeDate < 2006-05-01', '-$Points', 'Nov07Liability',now())";


mysql_query( $sql, $master )  or die( mysql_error($master) );
#echo "SQL - $sql\n\r";

echo "$timedate Process Completed\n\r";
?>
