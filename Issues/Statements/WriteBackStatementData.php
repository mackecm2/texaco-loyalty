<?php

	require "../../include/DB.inc";
	require "../../DBInterface/FileProcessRecord.php";
	require "../../Reporting/GeneralReportFunctions.php";													

	$db_user = "root";
	$db_pass = "Trave1";	
	
function WriteBack($accno, $month, $date)
{
	$sql = "select MemberNo, ListCode, Balance from Analysis.".$month."StatementListcodes where AccountNo = $accno";

	$results = DBQueryExitOnFailure( $sql );
	while($row = mysql_fetch_assoc($results))
	{
		$sql = "Insert into Statement(  AccountNo, StateDate, Balance, Mail_seg ) 
			values ( $accno, '$date', $row[Balance], '$row[ListCode]')";

		$results = DBQueryExitOnFailure( $sql );

		$sql = "Insert into CampaignHistory( MemberNo, AccountNo, CampaignType, CampaignCode,  ListCode, CreationDate, CreatedBy ) 
		values ($row[MemberNo], $accno,'STATEMENT', '$month', '$row[ListCode]', now(), '$month"."StatementWriteBack')";

		$results = DBQueryExitOnFailure( $sql );	
	}
}	
	
function ProcessFile($filename,$month,$date)
{
$handle = fopen ($filename,"r");	
	$lines = count(file($filename)); 
	$c = 0;
	while( $line = fgets( $handle ) )
	{
		$accountno = substr( $line , 1, 7 );
		if ($c == 0)
		{
			echo date("Y-m-d H:i:s")." opening $lines lines of $filename \r\n";
		}
		else 
		{
			$findme   = '"';
			$pos = strpos($accountno, $findme);
			if ($pos>0)
			{
				$accno = substr($accountno, 0, $pos ); 			
			}
			else 
			{
				$accno = $accountno; 	
			}
		WriteBack($accno, $month, $date);	
		}
		$c++;
		if( ($c % 10000) == 0 )
		{
			echo date("Y-m-d H:i:s")." $c lines processed\r\n";	
		}
	}
	echo date("Y-m-d H:i:s")." $c lines processed\r\n";	
}
/*
 * M A I N   P R O C E S S =================================================================
 */
	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	$master = connectToDB( MasterServer, TexacoDB );
	$sql = "SELECT StatementIdentifier, DATE_FORMAT(StopTime, '%Y-%m-%d') AS Date FROM Analysis.StatementingData ORDER BY StopTime DESC LIMIT 1";
	$results = DBQueryExitOnFailure( $sql );
	
	while($row = mysql_fetch_assoc($results))
	{
		$StatementMonth = $row[StatementIdentifier];
		$StatementDate = $row[Date];
	}
	
	echo date("Y-m-d H:i:s")." Starting write back of $StatementMonth data \r\n";
	$Efilename = "/tmp/".$StatementMonth."EmailStatement.txt";
	$Pfilename = "/tmp/".$StatementMonth."PaperStatement.txt";

	ProcessFile($Efilename,$StatementMonth,$StatementDate);
	ProcessFile($Pfilename,$StatementMonth,$StatementDate);

	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n"; 
?>