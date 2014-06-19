<?php

$Reporting = true;
$db_user = "ReportGenerator";
$db_pass = "tldttoths";

function AddToReportIndex( $LastMonth, $BaseName )
{

	$sql = "Replace into ReportMonths ( Month, TableRoot, ReportTable ) values ( $LastMonth, '$BaseName', '$BaseName$LastMonth' )";
	DBQueryExitOnFailure($sql);

}

function GetThisMonth()
{
	$sql = "select date_format( now(), '%Y%m' )";
	return DBSingleStatQuery($sql);
}

function GetLastMonth()
{
	$sql = "select date_format( Date_sub(now(), INTERVAL 1 MONTH), '%Y%m' )";
	return DBSingleStatQuery($sql);
}

function ConvertMonthToWord( $yyyymm )
{
	$sql = "SELECT DATE_FORMAT(CONCAT(left('$yyyymm', 4),'-',right('$yyyymm', 2),'-01'), '%M%Y')";
	return DBSingleStatQuery($sql);
}

function GetStartOfNextMonth( $month )
{
	$sql = "select concat( Period_add( $month, 1 ), '01')";
	return DBSingleStatQuery($sql);
}

function GetPreviousMonth( $month )
{
	$sql = "select Period_add( $month, -11 )";
	return DBSingleStatQuery($sql);

}

function DecrementMonth( $month )
{
	if( ($month % 100) == 1 )
	{
		return ($month - 100 + 11);
	}
	else
	{
		return ($month - 1);
	}
}


function IncrementMonth( $month )
{
	if( ($month % 100) == 12 )
	{
		return ($month + 100 - 11);
	}
	else
	{
		return ($month + 1);
	}
}

function CreateReportLog( $String )
{
	$sql = "Insert into Reporting.ReportLog( CreationDate, CompletionDate, Comment, File ) values (now(), now(), '$String', '$_SERVER[SCRIPT_FILENAME]' )";
	DBQueryExitOnFailure($sql);
}

/* alter table ReportLog add column ID int auto_increment primary key first; */
/* alter table ReportLog add column CompletionDate datetime */

function CreateReportProcessLog( )
{
	$sql = "Insert into Reporting.ReportLog( CreationDate, Comment, File )  values (now(), 'ProcessLog', '$_SERVER[SCRIPT_FILENAME]' )";
	DBQueryExitOnFailure($sql);
	return mysql_insert_id();
}

function CompleteProcessLog( $recNum )
{
	global $errorCount;
	$sql = "Update Reporting.ReportLog set CompletionDate = now() where ID = $recNum";
	$results = DBQueryExitOnFailure( $sql );
}

?>