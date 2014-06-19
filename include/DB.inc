<?php

        include "ServerName.inc";
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
        define( "PermissionsGroupLoyalty", "G" );
        define( "PermissionsMemberHistory", "H" );
        define( "PermissionsRestrictedHistoryPrinting", "h" );
        define( "PermissionsMassiveAdjust", "J" );
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
 		define ("BackgroundColour", "#FF6666"); 
        define("ReportServer", "localhost" );
        define("MasterServer", "localhost" );
        define("ReplicationServer", "localhost" );
        define("AnalysisServer", "localhost" );
}
else if( $SERVER_NAME_FOR_ALL == "MASTER" ) 
{
#Live Box
        define ("BackgroundColour", "#D0CAE6"); 
        define("ReportServer", "192.168.199.2:3307" );
        define("MasterServer", "192.168.199.2:3307" );
        define("ReplicationServer", "192.168.199.1:3307" );
        define("AnalysisServer", "192.168.199.2:3307" );
}
else if( $SERVER_NAME_FOR_ALL == "SLAVE" )  /// This is now deprecated MRM 12/08/09 
{
		define ("BackgroundColour", "#999900"); 
        define("ReportServer", "192.168.199.1:3307" );
        define("MasterServer", "192.168.199.1:3307" );
        define("ReplicationServer", "192.168.199.1:3307" );
        define("AnalysisServer", "192.168.199.1:3307" );
}
else if( $SERVER_NAME_FOR_ALL == "TEST"  ) 
{
		define ("BackgroundColour", "#CBE1E7");
        define("ReportServer", "192.168.199.4:3308" );
        define("MasterServer", "192.168.199.4:3308" );
        define("ReplicationServer", "192.168.199.4:3308" );
        define("AnalysisServer", "192.168.199.4:3308" );
}
else if( $SERVER_NAME_FOR_ALL == "SINGLE"  ) 
{
		define ("BackgroundColour", "#99FFFF");
        define("ReportServer", "weoudb:3307" );
        define("MasterServer", "weoudb:3307" );
        define("ReplicationServer", "weoudb:3307" );
        define("AnalysisServer", "weoudb:3307" );
}
else if( $SERVER_NAME_FOR_ALL == "DEMO"  ) 
{
		define ("BackgroundColour", "#E1E7CB");
        define("ReportServer", "weoudbrept:3307" );
        define("MasterServer", "weoudbrept:3307" );
        define("ReplicationServer", "weoudbrept:3307" );
        define("AnalysisServer", "weoudbrept:3307" );
}

// Mantis 2197 MRM 07 01 11 define environment for emails

$env = ucfirst(strtolower($SERVER_NAME_FOR_ALL));
if ( $env == "Master" ) $env = "Live";
define("ServerEnvironment", $env);

function connectToDB( $host = null, $db = null )
{

        #echo "In Here host=$host db=$db<br>";

        global  $db_host, $db_name, $db_user, $db_pass;
        if( !isset($host) )
        {
                $host = $db_host;
        }

        if( !isset($db) )
        {
                $db =  $db_name;
        }
        #echo "From DB.inc - We are connecting to $host, $db_name, $db_user, $db_pass<br>";

        $con= mysql_connect($host,$db_user,$db_pass)
                or die ("Cannot connect to MySQL. " . $host . $db_name . $db_user . mysql_error());



        $dbr = mysql_select_db( $db, $con)
                or die ("Cannot select the $db using $db_user database. Please check your details in the database connection file and try again".mysql_error() );

        #echo "Connection complete $con<br>";
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


function DBQueryExitOnFailure( $sql, $con = null, $func = '' )
{
        global $uname;
        if( isset( $con ) )
        {
                $results = mysql_query( $sql, $con );
        }
        else
        {
                $results = mysql_query( $sql );
        }
        if( !$results )
        {
        	if ( !$func )
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
                echo Date("Y-m-d H:i:s");
                echo "<Br>\n";
                echo "<p>************* E R R O R ! ! ! ! ! ! *******************</p>\n";
                echo "<p>An unexpected Error has occurred.</p>\n";
                echo "<p>Possible Causes\n";
                echo "<ul>\n";
                echo "<li>You do not have permission to perform the requested operation.</li>\n";
                echo "<li>Database is down</li>\n";
                echo "<li>Database table is corrupt </li>\n";
                echo "</ul>\n";
                echo "<p>SQL Executed:<br>\n";
                echo $sql; 
                echo "</p>\n";
                echo "<p>Error Code:<br>\n";
                echo mysql_error();
                echo "</div>\n";
                echo "</BODY>\n";
                echo "</HTML>\n";
                exit();
        	}
			if ( $func == "login" )
			{
				echo "There is a problem with the database. Please try again later.\n";
				$link = "<a href=\"javascript:history.go(-1)\">here</a>";
				echo  "Click $link to go back to the login screen.";
					exit();
			}
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
        echo "$error\r\n";
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////
function mysqlInsert($ourData, $table)
{       //////////////////////////////////////////////////////////////////////////////////////////////////
        // This allows us to append to the database - we return with the mysql_insert_id

        // $ourData can be a string, or an associate array - meaning it can be
        // something like "firstname='randell'" or it can be something like
        // $records['firstname']="randell".  The array part can be useful
        // when wanting to add a large update which would stretch the query
        // string in to unreadable code.
                if(is_array($ourData))
                {
                        $newTableValues=array();
                        // We only insert data that has a value hence why we check on each field/column/cell values
                        // length. We trim the sides too - just to make sure our db table contents is tidy and makes
                        // best use of available space.
                        foreach($ourData as $key=>$value)
                        {
                                $value=trim($value);
                                if(strlen($value)>0)
                                {
                                        $newTableValues[]="$key='".smart_escape($value)."'";
                                }
                        }
                        $insertString=implode(",",$newTableValues);
                        unset($newTableValues);
                }
                else
                {
                        $insertString=$ourData; 
                }
        // Be nice and clean up as we go along
                unset($ourData);
        // Build our query
                $query="INSERT $table SET $insertString";
                mysql_query($query);
        // Trap any known errors here, else we'll call our mysqlError function
                $mysqlError=mysql_errno();
                if($mysqlError==0)
                {
                        return(mysql_insert_id()); 
                }

                switch($mysqlError)
                {       case 1062:      // Duplicate record found
                                return(-1062);
                                break;

                        case 1146:      // Table does not exist
                                mysqlError("Query failed - Table $table does not exist.");
                                break;

                        default:
                                mysqlError("Query failed:<br>$query");
                }
        // Be nice to our systems resources
                unset($query);
        // Return and return with the isert id (the is the unique numeric id that should (must?)
        // be in every table to differenciate it from other records (help when making faster updates).
                return(FALSE);
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
function mysqlUpdate($ourData, $table, $conditions, $limit="1")
{       //////////////////////////////////////////////////////////////////////////////////////////////////
        // Simple function to update one or more records in a table - it is assumed that only one record
        // should be updated unless otherwise specified.  An update limit of zero means update all records
        // where $conditions are found to be true.
        // The function returns a numeric value which equals the number of records changed.
        //
        // IMPORTANT, a return of zero means no records were updated - this could be for one of two reasons
        // 1. No records were found where $conditions proved true
        // 2. Records were found where $conditions proved true, but the data we want to
        //    write as an update already exists (ie another update was done previously
        //    by someone else).
        //
        // Sample usage:
        //              $ourData['firstname']="randell";
        //              $ourData['lastname']="darcy";
        //              mysqlUpdate($ourData, "tableName", "username='randelld'");
        //
        // Simple sanity check - make sure we don't do a global update unless its expicitly asked
                if(strlen($conditions)<1)
                {
                        die("table update failed because no conditions specified."); }
        // $ourData can be a string, or an associate array - meaning it can be
        // something like "firstname='randell'" or it can be something like
        // $records['firstname']="randell".  The array part can be useful
        // when wanting to add a large update which would stretch the query
        // string in to unreadable code.
                if(is_array($ourData))
                {
                        $newTableValues=array();
                        foreach($ourData as $key=>$value)
                        {       if(strlen($value)>0)
                                {
                                        $newTableValues[]="$key='$value'";
                                }
                        }
                        $updateString=implode(",",$newTableValues);
                        unset($newTableValues);
                }
                else
                {       $updateString=$ourData; }

                unset($ourData);
        // LIMIT helps performance and by default is set to one however a limit of zero
        // means updates all records that are found when the $conditions prove true
                $limitClause="LIMIT $limit";
                if($limit==0)
                {       $limitClause=""; }
        // Build our query
                $query="UPDATE $table SET $updateString WHERE $conditions $limitClause";
                mysql_query($query) or mysqlError("Query failed:<br>$query");
        // Be nice to our systems resources
                unset($query);
        // Return and pass the number of records we changed/updated -
                return(mysql_affected_rows());
        return;
}
function mysqlError($ourMessage="")
{       //////////////////////////////////////////////////////////////////////////////////////////////////
        // A useful function which is more likely to be of use in a debugging environment but also plays a
        // role in a production environment - Namely, if we have problems making a request to our MySQL db
        // PHP has the facility to tell us the exact text message the server threw back.  This function is
        // called when an error is caught, and it can be passed text too, which will be output to the client
        // before the script aborts.  I suggest that you at very least pass it the query string that failed
        // to help with resolution.  Search for the mysqlError function name in this include file to see
        // examples of its advantages/usage...
        //
        // I've created some actions pending some errors - For example, one could 'trap' 1146 after an insert
        // to a non-existing table - This could be taken to create the table and therefore implement an part
        // automatic software installation.

                $mysqlErrorNumber=mysql_errno();
                $mysqlMessageText=mysql_error();
                die("<hr>$ourMessage<br>Mysql Error $mysqlErrorNumber<p>$mysqlMessageText");

                return;
}
function mysqlSelect(&$records, $what="*", $table, $conditions="", $limit="1")
{       //////////////////////////////////////////////////////////////////////////////////////////////////
        // Simple function to select one or more records from a table - we assume we're
        // looking for one record unless we're told otherwise

        // Basic syntax checking - make sure we know what table, and what the conditions of our selection are
                if(strlen($table)<1)
                {       die("db select from unknown table"); }
                if(strlen($conditions)<1)
                {       die("db select from $table but unknown conditions."); }
        // LIMIT helps performance and by default is set to one however a limit of zero
        // means return all records that are found when the $conditions prove true
                $limitClause="LIMIT $limit";
                if("$limit"=="0")
                {       $limitClause=""; }
        // Build our query
                $query="SELECT $what from $table where $conditions $limitClause";
                #echo "<br>$query<br>";
        // Perform our query
                $result=mysql_query($query) or mysqlError("Query failed:<br>$query");
                if($result==FALSE)
                {       return(0); }
        // Did we get anything?
                $numberOfSelectedRecords=mysql_affected_rows();
                if($numberOfSelectedRecords>0)
                {       // Great - we got at least one record
                        // Now, read our selection in to a special assoiated array where by
                        // $records[0] is a single row from the table - It will be an array
                        // containing columns of data from that single record.
                                while($row=mysql_fetch_array($result, MYSQL_ASSOC))
                                {       // Unescape our data from the database
                                                foreach($row as $key=>$value)
                                                {       $row[$key]=stripslashes($value); }
                                        // Record the record
                                                $records[]=$row;
                                }
                        // If limit=1 then $record should not be multi-dimensional since parent calling function
                        // is expecting only one record to be returned.
                                if($limit=="1")
                                {       $records=array_shift($records); }
                        // Be nice to our system recources
                                unset($row);
                }
        // Free resources and return
                mysql_free_result($result);
                return($numberOfSelectedRecords);
}

?>
