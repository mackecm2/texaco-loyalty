<?php 
/*
 * ---------------------------------------------------
 * Monthly Liability Accrual process
 * ---------------------------------------------------
 * Author : MRM
 * Date   : 27 MAR 09
 * 
 * Based on RegularProcessing/Yearly/March2009LiabilityReduction.php
 * 
 * 22/06/09 - 
 * 
 * 
 * 
 */

if ($argc != 3 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) 
{
?>

This is a command line PHP script with one option.

  Usage:
  <?php echo $argv[0]; ?> <month> <age>

  <month> is the month for which you are running the forecast for.... e.g. 2009-08-01 
  <age> is the number of months you want to use before the accrual is allowed... e.g. 18
  With the --help, -help, -h, or -? options, you can get this help.

<?php

} 
else 
{

	require "../../include/DB.inc";
	require "../../Reporting/GeneralReportFunctions.php";													
	require "../../mailsender/class.phpmailer.php";
	
	#------- M A I N   P R O C E S S -----------------------------
	
	echo date("Y-m-d H:i:s").' '.__FILE__." ".$yourmonth." ".$yourage." started \r\n";
	#$timedate = date("Y-m-d H:i:s");
	$endDate = date("Y-m-d",strtotime("-2 years"));
	$timestamp = date ("FY");
	$taskname = $timestamp."Liability";
	$filepath =	"/data/www/websites/texaco/reportfiles/";
	$db_user = "pma001";
	$db_pass = "amping";
	$slave = connectToDB( ReportServer, AnalysisDB );
	$yourmonth = $argv[1];
    $yourage = $argv[2];
    $timeago = strtotime($yourmonth.' -'.$yourage.' months');    
    $endDate = date('Y-m-d', $timeago);  
    $NoOfDays = ($yourage/12)*365;
    $monthdate = date("m");  
    $date1 = "2009-".$monthdate."-01";
    $sql = "SELECT DATEDIFF( '$yourmonth', '$date1' )";
    $subtracteddate = DBSingleStatQuery($sql) ;
   	$yourdays = $NoOfDays - $subtracteddate;

	
	//------------------------------------------------------------------------------------------------------------------------------------
	//------- Section 1 - Registered Cards -----------------------------------------------------------------------------------------------
	//------------------------------------------------------------------------------------------------------------------------------------

	$sql = "select AccountNo,Balance from Analysis.LiabilityTestRun where DATEDIFF( NOW(),LastSwipeDate ) > $yourdays and ( DATEDIFF( NOW(),LastOrderDate ) > $yourdays or LastOrderDate is NULL )";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	$messagestr  = "Month      Registered XD Accounts      $yourage        Unregistered Cards\n\r";

	$messagestr .= "           #accounts          points           #cards         #points\n\r";
	$messagestr .= "---------------------------------------------------------------------\n\r";
	$messagestr .= $yourmonth."     ";
	$messagestr .= mysql_num_rows($slaveRes);
	echo $messagestr;
	
	$message .= $messagestr;
	
	$Points = 0;
	$count = 0;
	
	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
			if ( $row['Balance'] > 0 )
		{
			$Points += $row['Balance'];
			$count ++;
		}
	}
	  
	$messagestr  = "          $Points       ";
	echo $messagestr;
	$message .= $messagestr;
		
	//------------------------------------------------------------------------------------------------------------------------------------
	//---------- Section 2 - Unregistered Cards ------------------------------------------------------------------------------------------
	//------------------------------------------------------------------------------------------------------------------------------------
	$Points = 0;
	$count = 0;

	$sql = "select CardNo, StoppedPoints from LiabilityCardsWeeklyTable where LastSwipeDate < '$endDate'";
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	$message .= "------------------------------------------------------------------------------------------------------------------------------------\n\r";
	$messagestr  = mysql_num_rows($slaveRes);

	echo $messagestr;
	$message .= $messagestr;
	
	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		#	First add the points to the removed points total
		if ( $row['StoppedPoints'] > 0 )
		{
			$Points += $row['StoppedPoints'];
			$count ++;
		}

	}
	
	$messagestr  = "         $Points       \n\r\n\r";
	echo $messagestr;
	$message .= $messagestr;
	
	$messagestr = "Process Completed - ".date("Y-m-d H:i:s")."\n\r\n\r";
	echo $messagestr;
	
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
}
?>