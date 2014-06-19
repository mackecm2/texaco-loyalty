<?php
	function createFileProcessRecord($fileToProcess)
	{
		global $ProcessName;
		$fileNameOnly = basename($fileToProcess);

		$sql = "select * from FilesProcessed where FileName = '$fileNameOnly'";
		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) > 0 )
		{
			$allFailed = true;
			while( $row = mysql_fetch_assoc( $results ) )
			{
				if( $row["EndTime"] != "" )
				{
					$allFailed = false;
				}
			}
			if( !$allFailed )
			{
				logError( "File already processed to end" );
				return false;
			}
		}

		$sql = "insert into FilesProcessed ( FileName, StartTime, CreatedBy ) values ( '$fileNameOnly'  ,now(), '$ProcessName')";

		$results = DBQueryExitOnFailure( $sql );

		return mysql_insert_id();
	}

	function UpdateFileProcessRecord( $recNum )
	{
		global $errorCount;
		$sql = "Update FilesProcessed set EndTime = now(), ErrorCount = $errorCount where ID = $recNum";
		$results = DBQueryExitOnFailure( $sql );
	}

	function UpdateRecordsProcessed( $recNum, $StatsData )
	{
		global $errorCount;
		if (!isset($errorCount))  $errorCount = 0;
		$sql = "Update FilesProcessed set EndTime = now(), NewRecords = $StatsData->transactionsProcessed, Duplicates = $StatsData->duplicates, Warnings = $StatsData->warnings, ErrorCount = $errorCount where ID = $recNum";
		$results = DBQueryExitOnFailure( $sql );
	}

	function CreateProcessStartRecord( $ProcessName )
	{
		$sql = "insert into FilesProcessed( CreatedBy, StartTime ) values ( '$ProcessName', now() )";
		$results = DBQueryExitOnFailure( $sql );

		return mysql_insert_id();
	}

	function CompleteProcessRecord( $Record )
	{
		$sql = "Update FilesProcessed set EndTime = now()  where ID = $Record"; 
		$results = DBQueryExitOnFailure( $sql );
	}

	function CompleteProcessRecordStats( $Record, $Inserted, $Changed )
	{
		$sql = "Update FilesProcessed set EndTime = now(), NewRecords = $Inserted, Duplicates = $Changed where ID = $Record"; 
		$results = DBQueryExitOnFailure( $sql );
	}

	
	function GetLastProcessTimes()
	{
		/*
			Create table TempLastRunTime
			(
				CreatedBy char(20),
				ID int
			)
		GRANT SELECT, INSERT, DELETE, UPDATE on texaco.TempLastRunTime to DAdmin@localhost;
		GRANT SELECT, INSERT, DELETE, UPDATE on texaco.TempLastRunTime to RAdmin@localhost;
		GRANT SELECT, INSERT, DELETE, UPDATE on texaco.TempLastRunTime to RUser@localhost;
		GRANT SELECT, INSERT, DELETE, UPDATE on texaco.TempLastRunTime to SUser@localhost;
		*/
		$sql = "delete from TempLastRunTime";
		DBQueryExitOnFailure( $sql );

		$sql = "insert into TempLastRunTime Select CreatedBy, max(ID) as ID from FilesProcessed group by CreatedBy";
		DBQueryExitOnFailure( $sql );

		$sql = "Select FilesProcessed.CreatedBy, StartTime, EndTime  from TempLastRunTime join FilesProcessed using( ID )";
		return DBQueryExitOnFailure( $sql );
	}

	/*
GRANT SELECT, INSERT, DELETE, UPDATE on Reporting.TempReportTime to DAdmin@localhost;
GRANT SELECT, INSERT, DELETE, UPDATE on Reporting.TempReportTime to RAdmin@localhost;
GRANT SELECT, INSERT, DELETE, UPDATE on Reporting.TempReportTime to RUser@localhost;
GRANT SELECT, INSERT, DELETE, UPDATE on Reporting.TempReportTime to SAdmin@localhost;
Create table TempReportTime
(
File char(20),
ID int
)
GRANT SELECT, INSERT, DELETE, UPDATE on Reporting.TempReportTime to DAdmin@'texaco.rsmsecure.com';
GRANT SELECT, INSERT, DELETE, UPDATE on Reporting.TempReportTime to RAdmin@'texaco.rsmsecure.com';
GRANT SELECT, INSERT, DELETE, UPDATE on Reporting.TempReportTime to RUser@'texaco.rsmsecure.com';
GRANT SELECT, INSERT, DELETE, UPDATE on Reporting.TempReportTime to SAdmin@'texaco.rsmsecure.com';

	*/
	function GetLastReportTimes()
	{
 		$sql = "delete from TempReportTime";
		DBQueryExitOnFailure( $sql );

		$sql = "insert into TempReportTime Select File, max(ID) as ID from ReportLog where Comment = 'ProcessLog' group by File";
		DBQueryExitOnFailure( $sql );

		$sql = "Select ReportLog.File, CreationDate, CompletionDate  from TempReportTime join ReportLog using( ID )";
		return DBQueryExitOnFailure( $sql );

	}
?>