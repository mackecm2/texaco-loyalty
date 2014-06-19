<?php
error_reporting( E_ALL );
require "../../include/DB.inc";
require "../../include/Locations.php";

$db_name = "texaco";
$db_user = "HomeExport";
$db_pass = "FLOWER";

$ProcessName   = "AddAccountStatus";

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$master = connectToDB( MasterServer, TexacoDB );

$insertcount = 0;

//  this will add any missing accounts to the Account Status table:

// expected accounts be added to texaco 01/07/10 : 2423584, 2423585, 2423586,  2424376 - 2425299,  2426260 - 2426666

$sql = "SELECT AccountNo FROM Accounts LEFT JOIN AccountStatus USING ( AccountNo ) WHERE AccountStatus.AccountNo IS NULL";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
while( $row = mysql_fetch_assoc( $res ) )
{
	$AccountNo = $row['AccountNo'];
	$sql = "INSERT INTO AccountStatus (AccountNo, Status, StatusSetDate, FraudStatus, RevisedDate)
	        VALUES ('$AccountNo', 'Open', NOW(), '0', NOW( ))"; 
    DBQueryExitOnFailure( $sql ); 
	echo "Account $AccountNo inserted.\r\n";
	$insertcount++;
}	

echo date("Y-m-d H:i:s").' '.__FILE__." completed. $insertcount inserted.\r\n";

?>