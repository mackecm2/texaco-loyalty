<?php
error_reporting( E_ALL );
require "../../include/DB.inc";
require "../../include/Locations.php";

$db_name = "texaco";
$db_user = "HomeExport";
$db_pass = "FLOWER";

$ProcessName   = "CloseInactiveAccounts";

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$master = connectToDB( MasterServer, TexacoDB );

$closedcount   = 0;
$unclosedcount = 0;

$sql = "SELECT AccountNo FROM texaco.AccountStatus JOIN Analysis.LiabilitySeptember2011 USING ( AccountNo )  JOIN texaco.Accounts USING ( AccountNo ) 
 where ( DATEDIFF( NOW(),LastSwipeDate ) > 365 or LastSwipeDate is NULL )
  		and ( DATEDIFF( NOW(),LastOrderDate ) > 365 or LastOrderDate is NULL )
  		and ( DATEDIFF( NOW(),LastTrackingDate ) > 365 or LastTrackingDate is NULL ) and Accounts.Balance = 0
  		and ( AccountType <> 'G' or AccountType IS NULL ) AND Status = 'Open' 
  		AND (DATEDIFF( NOW( ) , Accounts.CreationDate ) > 365 OR Accounts.CreationDate IS NULL)";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
while( $row = mysql_fetch_assoc( $res ) )
{
	$AccountNo     = $row['AccountNo'];

	$sql = "UPDATE texaco.AccountStatus SET Status = 'Closed', StatusSetDate = NOW() WHERE AccountNo = $AccountNo"; 
    DBQueryExitOnFailure( $sql ); 
    
   	$sql = "INSERT INTO Tracking ( AccountNo, TrackingCode, Notes, CreatedBy, CreationDate ) 
			VALUES ('$AccountNo', 1222, 'Account Closed for Arvato Data Cleanse', 'Mantis 3767',now())";
	DBQueryExitOnFailure( $sql ); 
	$closedcount++;	
   	if( ($closedcount % 1000) == 0 )
	{
		echo date("H:i:s");
		echo " $closedcount accounts closed\r\n";
	}

}	
echo date("Y-m-d H:i:s").' '.__FILE__." completed. $closedcount accounts closed.\r\n";
?>