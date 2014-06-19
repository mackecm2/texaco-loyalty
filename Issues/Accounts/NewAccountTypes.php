<?php
error_reporting( E_ALL );
require "../../include/DB.inc";
require "../../include/Locations.php";

$db_name = "texaco";
$db_user = "HomeExport";
$db_pass = "FLOWER";

$ProcessName   = "NewAccountTypes";

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$master = connectToDB( MasterServer, TexacoDB );

$count = 0;
$convcount = 0;

echo date("Y-m-d H:i:s")." changing NULL Account Type to U...";

$sql = "UPDATE AccountTypes SET AccountType = 'U' WHERE AccountType IS NULL";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );

echo "...done\r\n";


echo date("Y-m-d H:i:s")." deactivating all account types part from B, G, P and U...";

$sql = "UPDATE AccountTypes SET Active = 'N' WHERE AccountType NOT IN ('B','G','P','U')";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );

echo "...done\r\n";


$sql = "SELECT AccountNo, AccountType FROM Accounts";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
while( $row = mysql_fetch_assoc( $res ) )
{
	$oldtype =$row['AccountType'];
	$accountno = $row['AccountNo'];

		switch( $row['AccountType'] )
		{
		case "A":
		case "B":
		case "H":
		case "L":
		case "T":
			$newtype = "B";
			break;
		case "G":
			$newtype = "G";
			break;
		case "D":
		case "P":
			$newtype = "P";
			break;
		case "C":
		case "F":
		case "O":
		case "S":
		$newtype = "U";
		default:   // this will handle null and blank account types
			$newtype = "U";
		}

	if ( $oldtype != $newtype ) 
	{
		$sql = "UPDATE Accounts SET AccountType = '$newtype' WHERE AccountNo = $accountno LIMIT 1";
		DBQueryExitOnFailure( $sql ); 
   		$sql = "INSERT INTO Tracking ( AccountNo, TrackingCode, Notes, CreatedBy, CreationDate ) 
			VALUES ('$accountno', 135, 'Account Type changed from $oldtype to $newtype for Arvato Data Cleanse', 'Mantis 3731',now())";
		DBQueryExitOnFailure( $sql ); 
		$convcount++;
	}

	$count++;	
   	if( ($count % 100000) == 0 )
	{
		echo date("Y-m-d H:i:s")." $count account records processed, $convcount account types converted\r\n";
	}

}	
echo date("Y-m-d H:i:s")." $count account records processed, $convcount account types converted\r\n";
?>