	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**   Promotions Automation - Email Approvers 
	**   Written by MRM 19/08/2008
	********************************************************************/



	//******************************************************************
	//
	// RegularProcessing/PromotionsCheck.php
	// 
	// Looks at the BonusPoints table
	// Checks for any Pending changes with a start date of within 24 hours of now
	// If any found, sends a Final Warning email to all MPromo user accounts
	// Checks for any Pending changes with a start date of within 48 hours of now
	// If any found (apart from those above), sends an email to all MPromo user accounts
	// Checks for any Pending changes with a start date less than now 
	// If any found, changes them to status of Expired and sends an e-mail to all MPromo User Accounts 
	//
	// All MAdmin User accounts will be copied in on any e-mails
	//
	//******************************************************************

	$db_user = 'pma001';
	$db_pass = 'amping';

	include "../../include/DB.inc";
	require("../../mailsender/class.phpmailer.php");
	include "../../include/BonusFunctions.inc";

	

	
	// Main function

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	connectToDB( MasterServer, TexacoDB );
	
	$today = date("Y-m-d");
	$sql = "SELECT * FROM  BonusPoints WHERE Status = 'P' AND Active = 'Y' AND DATEDIFF(StartDate, '".$today.".') < 4";
	$results = DBQueryExitOnFailure( $sql );
	$count = 0;
	while( $row = mysql_fetch_array( $results ) )
	{
		$process  = $row["PromotionCode"];
		$startdate = $row["StartDate"];
		$strtoday = strtotime($today);
		$strstartdate = strtotime($startdate);
		
		if ($strstartdate <= $strtoday)
	    // missed your chance - the promotion will expire
	        {
				$urgent = 0;
				$sql = "UPDATE BonusPoints SET Status = 'E' WHERE PromotionCode = '".$process.".'";
				$results = DBQueryExitOnFailure( $sql );
	        }
        else 
        {
	        if ($strstartdate < $strtoday + 259200)
	        // 24 hours to go!!! (not counting weekends)
	        {
				$urgent = 24;
	        }
	        else 
	        {
	        	if ($strstartdate < $strtoday + 345600)
	        	// 48 hours to go (not counting weekends)
	            {
					$urgent = 48;
	        	}
	        }
		}
	    sendemail($process, $startdate, $urgent);
		$count = $count + 1;
		
	}

	echo date("Y-m-d H:i:s").' '.__FILE__." completed - promotions count is $count \r\n";


?>
