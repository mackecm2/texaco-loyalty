<?php
error_reporting( E_ALL );
require "../../include/DB.inc";
require "../../include/Locations.php";

$db_name = "texaco";
$db_user = "HomeExport";
$db_pass = "FLOWER";

$ProcessName   = "AddProofOfReceiptsDate";

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$master = connectToDB( MasterServer, TexacoDB );

$updatecount = 0;

$sql = "SELECT AccountNo, CreationDate FROM Tracking WHERE TrackingCode = 1127 OR TrackingCode = 1214";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
while( $row = mysql_fetch_assoc( $res ) )
{
	$AccountNo = $row['AccountNo'];
	$LetterCreationDate = $row['CreationDate']; 
	$sql = "UPDATE AccountStatus SET ProofOfReceiptsSentDate = '$LetterCreationDate' WHERE AccountNo = $AccountNo AND ProofOfReceiptsSentDate IS NULL"; 
    DBQueryExitOnFailure( $sql ); 
    $rowcount = mysql_affected_rows();
    
    if ( mysql_affected_rows() > 0 )  
    {
    	echo "Account $AccountNo updated with ProofOfReceiptsSentDate = $LetterCreationDate\r\n";
		$updatecount++;	
    }

}	
echo date("Y-m-d H:i:s").' '.__FILE__." completed. $updatecount accounts updated.\r\n";
?>