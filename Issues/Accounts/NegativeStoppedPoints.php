<?php
error_reporting( E_ALL );
require "../../include/DB.inc";
require "../../include/Locations.php";

$db_name = "texaco";
$db_user = "HomeExport";
$db_pass = "FLOWER";

$ProcessName   = "NegativeStoppedPoints";

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$master = connectToDB( MasterServer, TexacoDB );

$regupdatecount   = 0;
$unregupdatecount = 0;

$sql = "SELECT Cards.CardNo, Members.AccountNo, Cards.StoppedPoints FROM Cards LEFT JOIN Members USING (MemberNo) WHERE Cards.StoppedPoints < 0 ";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
while( $row = mysql_fetch_assoc( $res ) )
{
	$CardNo        = $row['CardNo'];
	$AccountNo     = $row['AccountNo'];
	$StoppedPoints = $row['StoppedPoints'];

	$sql = "UPDATE Cards SET StoppedPoints = 0 WHERE CardNo = '$CardNo'"; 
    DBQueryExitOnFailure( $sql ); 
    
    if ($row['AccountNo'])  
    {
    	$sql = "INSERT INTO Tracking ( AccountNo, TrackingCode, Notes, Stars, CreatedBy, CreationDate ) 
			VALUES ('$AccountNo ', 1154, 'Negative Stopped Points $StoppedPoints released', 0, 'Mantis 2987',now())";
		DBQueryExitOnFailure( $sql ); 
		$regupdatecount++;	
		echo date("Y-m-d H:i:s")." Account $AccountNo, Card $CardNo $StoppedPoints released\r\n";
    }
    else 
    {
    	echo date("Y-m-d H:i:s")." Card $CardNo $StoppedPoints released\r\n";
    	$unregupdatecount++;	
    }

}	
echo date("Y-m-d H:i:s").' '.__FILE__." completed. $regupdatecount registered cards updated, $unregupdatecount unregistered cards updated.\r\n";
?>