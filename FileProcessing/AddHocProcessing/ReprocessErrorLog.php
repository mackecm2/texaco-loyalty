<?php

	include "../../include/DB.inc";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   

	$update = true;

	connectToDB();

	$sql = "Select * from ErrorLog";

	$transactions = DBQueryExitOnFailure( $sql );

	while( $row = mysql_fetch_assoc( $transactions ) )
	{
		$lineNo = "null";
		$temp = explode( " while processing line", $row["ErrorString"]  );
		
		if( count( $temp ) == 1 )
		{
			//echo $row["ErrorString"];
			$errorStr = mysql_escape_string( $temp[0] );
		}
		else
		{
			$errorStr = mysql_escape_string( $temp[0] );
			$temp = explode( " in file ", $temp[1] );
 			$lineNo = trim($temp[0]);
			$filename = trim( $temp[1], "'" );

		}

		if( strstr( $row["CreatedBy"], "TXA" ) )
		{
			$filename = $row["CreatedBy"];
			$CreatedBy = "Batch Load";
		}
		else
		{
			$CreatedBy = $row["CreatedBy"];
		}

		$fields = "CreationDate, ErrorString";
		$values = "'$row[CreationDate]','$errorStr'";

		if( $filename != "" )
		{
			$fields .= ",File";
			$values .= ",'$filename'";
		}
		if( $lineNo != "" )
		{
			$fields .= ",LineNo";
			$values .= ",$lineNo";
		}
		if( $CreatedBy )
		{
			$fields .= ",CreatedBy";
			$values .= ",'$CreatedBy'";
		}

		$sql = "INSERT into NewErrorLog ( $fields ) values ( $values )";

		DBQueryExitOnFailure( $sql );

	}
?>