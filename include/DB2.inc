<?php

	include "ServerName.inc";
	#include "inc-mysql.php";
	define( "TexacoDB", "texaco" );
	define( "ReportDB", "Reporting" );
	define( "AnalysisDB", "Analysis" );

	define( "PermissionsBigAdjust", "A" );
	define( "PermissionsAccountCard", "a" );
	define( "PermissionsBonusManager", "B" );
	define( "PermissionsConfigPages", "C" );
	define( "PermissionsExtraSearch", "E" );
	define( "PermissionsEBSPages", "e" );
	define( "PermissionsDailyProcess", "D" );
	define( "PermissionsMemberHistory", "H" );
	define( "PermissionsRestrictedHistoryPrinting", "h" );
	define( "PermissionsMergeMembers", "M" );
	define( "PermissionsQuestionUser", "Q" );
	define( "PermissionsShopping", "P" );
	define( "PermissionsRequestCard", "R" );
	define( "PermissionsReporting", "r" );
	define( "PermissionsSmallAdjust", "S" );
	define( "PermissionsTracking",  "T" );
	define( "PermissionsUserManager", "U" );
	define( "PermissionsIssuesPages", "I" );


if( $SERVER_NAME_FOR_ALL ==  "PANCAKE" )
{
#  Home box

	define("ReportServer", "localhost" );
	define("MasterServer", "localhost" );
	define("ReplicationServer", "localhost" );
	define("AnalysisServer", "localhost" );
}	
else if( $SERVER_NAME_FOR_ALL == "MASTER" ) 
{
#Live Box

	define("ReportServer", "192.168.199.1:3307" );
	define("MasterServer", "192.168.199.1:3307" );
	define("ReplicationServer", "192.168.199.1:3307" );
	define("AnalysisServer", "192.168.199.1:3307" );
}
else if( $SERVER_NAME_FOR_ALL == "SLAVE" ) 
{

	define("ReportServer", "localhost" );
	define("MasterServer", "texaco.rsmsecure.com" );
	define("ReplicationServer", "localhost" );
	define("AnalysisServer", "localhost" );
}
else if( $SERVER_NAME_FOR_ALL == "TEST"  ) 
{

	define("ReportServer", "192.168.199.4:3308" );
	define("MasterServer", "192.168.199.4:3308" );
	define("ReplicationServer", "192.168.199.4:3308" );
	define("AnalysisServer", "192.168.199.4:3308" );
}
else if( $SERVER_NAME_FOR_ALL == "SINGLE"  ) 
{

	define("ReportServer", "192.168.199.4:3307" );
	define("MasterServer", "192.168.199.4:3307" );
	define("ReplicationServer", "192.168.199.4:3307" );
	define("AnalysisServer", "192.168.199.4:3307" );
	
}


	function connectToDB( $host = null, $db = null )
	{
		global 	$db_host, $db_name, $db_user, $db_pass;
		if( !isset($host) )
		{
			$host = $db_host;
		}

		if( !isset($db) )
		{
			$db =  $db_name;
		}

		$con= @mysql_connect($host,$db_user,$db_pass)
			or die ("Cannot connect to MySQL." . $host . $db_name .$db_user . $db_pass . mysql_error());

		$dbr = @mysql_select_db( $db, $con)
			or die ("Cannot select the $db using $db_user $db_pass database. Please check your details in the database connection file and try again".mysql_error() );
		return $con;
	}

	function DBSingleStatQuery( $sql )
	{
		$results = DBQueryExitOnFailure( $sql );

		$row = mysql_fetch_row( $results );
		if( !$row )
		{
			 echo "$sql returned no results!!\n";
		}
		return $row[0];
	}


	function DBSingleStatQueryNoError( $sql )
	{
		$results = DBQueryExitOnFailure( $sql );

		$row = mysql_fetch_row( $results );
		if( !$row )
		{
			 return false;
		}
		return $row[0];
	}


	function DBQueryExitOnFailure( $sql )
	{
		global $uname;
		$results = mysql_query( $sql );

		if( !$results )
		{
			echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
			echo "<HTML>\n";
			echo "<HEAD>\n";
			echo "<TITLE> No Permissions </TITLE>\n";
			echo "</HEAD>\n";
			echo "<BODY>\n";
			echo "<div style=\"vertical-align:middle; text-align:center\">\n";
			echo "Current user $uname.\n";
			echo "<Br>\n";
			echo "You do not have permission to perform the requested operation.\n";
			echo "<br>\n";
			echo $sql;
			echo "<Br>\n";
			echo mysql_error();
			echo "</div>\n";
			echo "</BODY>\n";
			echo "</HTML>\n";
			exit();
		}
		return $results;
	}
		
 	function DBQueryLogOnFailure( $sql )
	{
		$results = mysql_query( $sql );
		if( !$results ) 
		{
			logError( "Failed to run $sql ".mysql_error() );
		}
		return $results;
	}

	function DBLogErrorOnFailure( $sql )
	{
		global $uname;
		$results = mysql_query( $sql );
		if( !$results )
		{
			DBLogError( $sql. mysql_error());
		}
		return $results;
	}

	function DBLogError( $error )
	{
		global $fileToProcess, $lineNo, $errorCount, $ProcessName;
		$errorCount++;
		echo "<br>$error\n";
		$str = mysql_real_escape_string( $error );

		$fields = "ErrorString, Severity";
		$values = "'$str', 1";

		if( isset( $fileToProcess ) )
		{
			$fields .= ",File";
			$info = pathinfo($fileToProcess);
			$values .= ",'$info[basename]'";
		}
		if( isset( $lineNo ) )
		{
			$fields .= ",LineNo";
			$values .= ",$lineNo";
		}
		if( isset( $ProcessName ) )
		{
			$fields .= ",CreatedBy";
			$values .= ",'$ProcessName'";
		}

		$sql = "INSERT into NewErrorLog ( $fields ) values ( $values )";
		mysql_query( $sql ) or die( mysql_error() );
	}

	function LogWarning( $error )
	{
		global $fileToProcess, $lineNo, $errorCount, $ProcessName;
		$errorCount++;
		$str = mysql_real_escape_string( $error );
//		echo "<br>$str\n";

		$fields = "ErrorString, Severity";
		$values = "'$error', 9";

		if( isset( $fileToProcess ) )
		{
			$fields .= ",File";
			$values .= ",'$fileToProcess'";
		}
		if( isset( $lineNo ) )
		{
			$fields .= ",LineNo";
			$values .= ",$lineNo";
		}
		if( isset( $ProcessName ) )
		{
			$fields .= ",CreatedBy";
			$values .= ",'$ProcessName'";
		}

		$sql = "INSERT into NewErrorLog ( $fields ) values ( $values )";
		mysql_query( $sql ) or die( mysql_error() );
	}


	function LogError( $error )
	{
		DBLogError( $error );
	}

	function CheckPermisions( $Required )
	{
		global $userPerms;
		return( strstr( $userPerms, $Required ) );
	}

	function ConvertDate( $inDate )
	{
		$outDate = "";
		if( strlen( $inDate ) == 10 )
		{
			$outDate = substr( $inDate, 6, 4 ) . "-". substr( $inDate, 3, 2 ) . "-". substr( $inDate, 0, 2 );
		}
		return $outDate;
	}

	function smart_escape($value)
	{
	   // Stripslashes
		if (get_magic_quotes_gpc()) 
		{
			$value = stripslashes($value);
		}
		return mysql_real_escape_string($value);
	}



?>