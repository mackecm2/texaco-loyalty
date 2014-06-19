<?php

//******************************************************************
//
// UploadAccountStatus.php
//
//  MRM - 06.07.10 - Created to enable upload of csv file to get AccountStatus table up to date        Mantis 2366
//
//******************************************************************

require "../../include/DB.inc";
require "../../include/Locations.php";

$db_name = "texaco";
$db_user = "HomeExport";
$db_pass = "FLOWER";

$ProcessName   = "UploadAccountStatus";

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
$master = connectToDB( MasterServer, TexacoDB );

function date_uk_to_american($date, $replace_separator = FALSE)
{
	  $days  = '0?[1-9]|[12][0-9]|3[01]';
	  $months= '0?[1-9]|1[0-2]';
	  $year  = '\d{2}|\d{4}';
	  $non_alpha = '[^0-9a-zA-Z]+';
	  return preg_replace( "/^[[:space:]]*($days)($non_alpha)($months)($non_alpha)($year)/", $replace_separator === FALSE ? '$3$2$1$4$5' : '$3'.$replace_separator.'$1'.$replace_separator.'$5', $date);
}

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
	$dbList = array("RevisedDate","CardNo","ConfirmSpend1SentDate","ConfirmSpend2SentDate","ConfirmSpend1ReturnedDate","ConfirmSpend2ReturnedDate","ProofOfReceiptsSentDate","ProofOfReceiptsReturnedDate","AccountClosedDate","AccountClearedDate","ConfirmSpend1Comments");
		
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
        	$sql = "UPDATE AccountStatus JOIN Members USING ( AccountNo ) JOIN Cards USING ( MemberNo ) SET ";
        	for ($c=0; $c < $num; $c++) 
	        {
	        	if($data[$c] !='' && $data[$c] != NULL )
	        	{
	        	    if( $c == 10  )
		        	{
		        		$comment = mysql_real_escape_string($data[$c]);
			      		$sql .= "$dbList[$c] = '".$comment."', ";

		        	}
		        	else  if( $c > 1 AND $c < 11)
		        	{
		        		$mysqldate = strtotime( date_uk_to_american($data[$c], '/') );
						$thisdate = date( 'Y-m-d', $mysqldate );
						
						// only want to update the field if the new date is more recent that what's already on the db
						
						$sql .= "$dbList[$c] = IF(ISNULL($dbList[$c]), '$thisdate', IF ($dbList[$c] > '$thisdate', $dbList[$c], '$thisdate')), ";
		        	}
	        		else  if( $c == 0)
		        	{
		        		$mysqldate = strtotime( date_uk_to_american($data[$c], '/') );
						$reviseddate = date( 'Y-m-d', $mysqldate );
		        	}
	        	}
	
	        }
	        
			$sql .= " RevisedDate = IF(ISNULL(RevisedDate), '$reviseddate', IF (RevisedDate > '$reviseddate', RevisedDate, '$reviseddate')) WHERE CardNo = '".$data[1]."';";
			$results = DBQueryLogOnFailure( $sql );
			$numrows = mysql_affected_rows();
			$count = $count + $numrows;

        }
    }
    fclose($handle);
    echo date("Y-m-d H:i:s").' '.__FILE__." completed.$row rows processed, $count rows updated.\r\n";
}
else echo "\r\n\r\n$argv[1] not found. Please try again.\r\n\r\n".__FILE__." terminated.\r\n";
?>