<?
/////////////////////////////////////////////////////////////////////////////////////////////////////////
function escapeData(&$ourData)
{	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// Pass the function data, and we'll escape it, making it safe for writing to the db.

	// We copy the data in to a temporary variable so that our changes are reflected in the original
	// variable, that has been passed by reference
	$tmpData=$ourData;
	if (!is_array($ourData))
	{
		// dirtyData is a string variable
		$ourData=mysql_real_escape_string($tmpData);
	}
	else
	{
		// $dirtyData is an array variable
		if(count($ourData)>0)
		{
			foreach($tmpData as $key=>$value)
			{
				if (!is_array($value))
				{
					$value=trim($value);
					$ourData[$key]=mysql_real_escape_string($value);
				}
				else
				{
					// the array element is another array so call stripData again for this sub-array
					// we'll continue through the main array from the current function call
					// when the new function call returns, this is a RECURSIVE function call
					stripData($value);
				}
			}
		}
	}
	return;
}


function stripData(&$ourData)
{
	// Pass the function data, and we'll un-escape it, perhaps for consistency in always using
	// escapeData() for mysql_escape_string() regardless of magic_quotes_gpc() setting
	// or for redisplaying variables to the user which have previously been escaped

	// We copy the data in to a temporary variable so that our changes are reflected in the original
	// variable, that has been passed by reference
	$tmpData=$ourData;
	if (!is_array($ourData))
	{
		// dirtyData is a string variable
		$ourData=stripslashes($tmpData);
	}
	else
	{
		// $dirtyData is an array variable
		if (count($ourData)>0)
		{
			foreach ($tmpData as $key=>$value)
			{
				if (!is_array($value))
				{
					$value=trim($value);
					$ourData[$key]=stripslashes($value);
				}
				else
				{
					// the array element is another array so call stripData again for this sub-array
					// we'll continue through the main array from the current function call
					// when the new function call returns, this is a RECURSIVE function call
					stripData($value);
				}
			}
		}
	}
	return;
}


function mysqlError($ourMessage="")
{	//////////////////////////////////////////////////////////////////////////////////////////////////
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////
function mysqlSelect(&$records, $what="*", $table, $conditions="", $limit="1")
{	//////////////////////////////////////////////////////////////////////////////////////////////////
 	// Simple function to select one or more records from a table - we assume we're
	// looking for one record unless we're told otherwise

	// Basic syntax checking - make sure we know what table, and what the conditions of our selection are
		if(strlen($table)<1)
		{	die("db select from unknown table"); }
		if(strlen($conditions)<1)
		{	die("db select from $table but unknown conditions."); }
	// LIMIT helps performance and by default is set to one however a limit of zero
	// means return all records that are found when the $conditions prove true
		$limitClause="LIMIT $limit";
		if("$limit"=="0")
		{	$limitClause=""; }
	// Build our query
		$query="SELECT $what from $table where $conditions $limitClause";
		#echo "<br>$query<br>";
	// Perform our query
		$result=mysql_query($query) or mysqlError("Query failed:<br>$query");
		if($result==FALSE)
		{ 	return(0); }
	// Did we get anything?
		$numberOfSelectedRecords=mysql_affected_rows();
		if($numberOfSelectedRecords>0)
		{	// Great - we got at least one record
			// Now, read our selection in to a special assoiated array where by
			// $records[0] is a single row from the table - It will be an array
			// containing columns of data from that single record.
				while($row=mysql_fetch_array($result, MYSQL_ASSOC))
				{	// Unescape our data from the database
						foreach($row as $key=>$value)
						{	$row[$key]=stripslashes($value); }
					// Record the record
						$records[]=$row;
				}
			// If limit=1 then $record should not be multi-dimensional since parent calling function
			// is expecting only one record to be returned.
				if($limit=="1")
				{	$records=array_shift($records); }
			// Be nice to our system recources
				unset($row);
		}
	// Free resources and return
		mysql_free_result($result);
		return($numberOfSelectedRecords);
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
function mysqlInsert($ourData, $table)
{	//////////////////////////////////////////////////////////////////////////////////////////////////
	// This allows us to append to the database - we return with the mysql_insert_id

	// $ourData can be a string, or an associate array - meaning it can be
	// something like "firstname='randell'" or it can be something like
	// $records['firstname']="randell".  The array part can be useful
	// when wanting to add a large update which would stretch the query
	// string in to unreadable code.
		if(is_array($ourData))
		{	$newTableValues=array();
			// We only insert data that has a value hence why we check on each field/column/cell values
			// length. We trim the sides too - just to make sure our db table contents is tidy and makes
			// best use of available space.
			foreach($ourData as $key=>$value)
			{	$value=trim($value);
				if(strlen($value)>0)
				{	$newTableValues[]="$key='$value'";	}
			}
			$insertString=implode(",",$newTableValues);
			unset($newTableValues);
		}
		else
		{	$insertString=$ourData; }
	// Be nice and clean up as we go along
		unset($ourData);
	// Build our query
		$query="INSERT $table SET $insertString";

		#echo "<br>$query<br>";

		mysql_query($query);
	// Trap any known errors here, else we'll call our mysqlError function
		$mysqlError=mysql_errno();
		if($mysqlError==0)
		{		return(mysql_insert_id()); }

		switch($mysqlError)
		{	case 1062:	// Duplicate record found
				return(-1062);
				break;

			case 1146:	// Table does not exist
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
{	//////////////////////////////////////////////////////////////////////////////////////////////////
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
	// 		$ourData['firstname']="randell";
	// 		$ourData['lastname']="darcy";
	// 		mysqlUpdate($ourData, "tableName", "username='randelld'");
	//
	// Simple sanity check - make sure we don't do a global update unless its expicitly asked
		if(strlen($conditions)<1)
		{	die("table update failed because no conditions specified."); }
	// $ourData can be a string, or an associate array - meaning it can be
	// something like "firstname='randell'" or it can be something like
	// $records['firstname']="randell".  The array part can be useful
	// when wanting to add a large update which would stretch the query
	// string in to unreadable code.
		if(is_array($ourData))
		{
			$newTableValues=array();
			foreach($ourData as $key=>$value)
			{
				//if(strlen($value)>0)
				//{
					$newTableValues[]="$key='$value'";
				//}
			}
			$updateString=implode(",",$newTableValues);
			unset($newTableValues);
		}
		else
		{	$updateString=$ourData; }

		unset($ourData);
	// LIMIT helps performance and by default is set to one however a limit of zero
	// means updates all records that are found when the $conditions prove true
		$limitClause="LIMIT $limit";
		if($limit==0)
		{	$limitClause=""; }
	// Build our query
		$query="UPDATE $table SET $updateString WHERE $conditions $limitClause";
		#echo"<br>$query<br>";
		mysql_query($query) or mysqlError("Query failed:<br>$query");
	// Be nice to our systems resources
		unset($query);
	// Return and pass the number of records we changed/updated -
		return(mysql_affected_rows());
	return;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
function mysqlCreateTable($tableName,$tableStructure)
{	// Simple function to create a table if it doesn't exist - note we escape our table name
	// in case it was provided by the user - We do not escape the tableStructure as it could
	// contain characters that might wrongly be interpretered and wrongly escaped.
		$query="CREATE TABLE IF NOT EXISTS $tableName ($tableStructure) TYPE=MyISAM AUTO_INCREMENT=1";
		return mysql_query($query) or mysqlError("Query failed:<br>$query");
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
function mysqlQuery($query)
{	// Escape our query
		$query=$query;
		#echo "<br>$query";
		return mysql_query("$query") or mysqlError("Query failed:<br>$query");
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Before we do anything with client supplied data, we clean it up if nescessary
/////////////////////////////////////////////////////////////////////////////////////////////////////////

if( count($_REQUEST)>0 )
{
	if ( get_magic_quotes_gpc() )
	{
		// php is already set to automatically use addslashes()
		// strip the slashes so we can redo with mysql_escape_string() for consistency
		stripData($_REQUEST);
		stripData($_GET);
		stripData($_POST);
	}

	escapeData($_REQUEST);
	escapeData($_GET);
	escapeData($_POST);
}
?>