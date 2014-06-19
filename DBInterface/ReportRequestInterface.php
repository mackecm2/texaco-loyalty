<?php

	function InsertReportRequest( $requestsql, $description, $Headers )
	{
		global $uname;
		$requestsql = mysql_escape_string( $requestsql );
		$sql = "INSERT into ReportsToRun( SQLSTR, Description, ColumnHeads, CreatedBy, Created ) values ( '$requestsql', '$description', '$Headers', '$uname', now() )";
		return DBQueryExitOnFailure( $sql );
	}

	function GetReportStatus()
	{
		$sql = "SELECT ID, Description, Status, CreatedBy, Created, ErrorStr, ResultsFile  from ReportsToRun where Status <> 'D'";
		return DBQueryExitOnFailure( $sql );
	}

	function GetReportRecord( $ID )
	{
		$sql = "Select * from ReportsToRun where ID = $ID";

		$results =  DBQueryExitOnFailure( $sql );

		if( $results )
		{
			return mysql_fetch_assoc( $results );
		}
		return false;
	}

	function DeleteReport( $ID )
	{

		$sql = "Select ResultsFile from ReportsToRun where ID = $ID";

		$results =  DBQueryExitOnFailure( $sql );

		if( $results )
		{
			$row = mysql_fetch_assoc( $results );
			$filename = $row["ResultsFile"];

			$sql = "Update ReportsToRun set Status = 'D' where ID = $ID";
			DBQueryExitOnFailure( $sql );
			return $filename;
		}
		return false;
	}
?>