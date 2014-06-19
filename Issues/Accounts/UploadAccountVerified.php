<?php

//******************************************************************
//
// UploadAccountVerified.php
//
//  MRM - 19.09.11 - Created to enable upload of csv file to get Account Verified Date         Mantis 3744
//
//******************************************************************

require "../../include/DB.inc";
require "../../include/Locations.php";

function verified($date)
{     
	list($year, $month, $day) = explode("-", $date); 
	if ( checkdate( $month, $day, $year ) ) 
	{
		return true;
	}
    return false;
}

$db_name = "texaco";
$db_user = "HomeExport";
$db_pass = "FLOWER";

$ProcessName   = "UploadAccountVerfied";

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
$master = connectToDB( MasterServer, TexacoDB );

if( $argc == 1 )
{
	echo "\r\n\r\nPlease specify input file name!!\r\n\r\n".__FILE__." terminated.\r\n";
	exit;
}

$row = 0;
$count = 0;
if (($handle = fopen("$argv[1]", "r")) !== FALSE) 
//                                                 input file is on the test system as /home/mikem/2010AccountInvs.csv
{
		
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
    {
        $num = count($data);
        $row++;
        
		if( ($row % 100) == 0 )
		{
			echo date("H:i:s");
			echo " Processed $row, Updated $count\r\n";
		}
  
        if( $row != 1) // skip the header
        {
        	$accountno = $data[0];
        	$verified_date = $data[26];
        	if ( verified($verified_date) || !$verified_date )
        	{
        		$sql = "UPDATE AuditCase SET LastAuditCaseStatus = AuditCaseStatus, 
	        	DateLastAuditCaseStatusChanged = DateAuditCaseStatusChanged, 
	        	AuditCaseStatus = 'Closed - Account Verified', 
	        	DateAuditCaseStatusChanged = '$verified_date' WHERE  AccountID = $accountno";
	        	
	        	$sql = "INSERT INTO AuditCase( AccountID, AuditCaseType, AuditCaseStatus,
	        	 LastAuditCaseStatus, DateAuditCaseStatusChanged, DateLastAuditCaseStatusChanged )
	        	 VALUES ( $accountno, 'Manual', 'Closed - Account Verified', 'Closed - Account Verified', '$verified_date', '$verified_date' )
				 ON DUPLICATE KEY UPDATE AuditCaseStatus = 'Closed - Account Verified', DateAuditCaseStatusChanged = '$verified_date'" ;
				$results = DBQueryLogOnFailure( $sql );
	    	    	$sql = "INSERT INTO Tracking (MemberNo,	AccountNo, TrackingCode, Notes, Stars, CreationDate, CreatedBy)
						VALUES (NULL, '$accountno', '1236', 'Account Verified $verified_date', NULL, NOW(), 'Mantis3744 Upload'	)";
				$results = DBQueryLogOnFailure( $sql );  
				$count = $count + 1;
        	}
        	else 
        	{
        		echo "invalid date found \r\n";
        	}


        }
    }
    fclose($handle);
    echo date("Y-m-d H:i:s").' '.__FILE__." completed.$row rows processed, $count rows updated.\r\n";
}
else echo "\r\n\r\n$argv[1] not found. Please try again.\r\n\r\n".__FILE__." terminated.\r\n";
?>