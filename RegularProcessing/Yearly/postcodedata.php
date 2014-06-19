<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
//   require 'db_connect.php';



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
function mysqlQuery($query)
{	// Escape our query
		$query=$query;
		#echo "<br>$query";
		return mysql_query("$query") or mysqlError("Query failed:<br>$query");
}
function backuptable()
{
	mysqlQuery("TRUNCATE oldpostcodedata");
	echo date("Y-m-d H:i:s")." oldpostcodedata truncated\r\n";
	mysqlQuery("INSERT INTO texaco.oldpostcodedata SELECT * FROM texaco.postcodedata");
	echo date("Y-m-d H:i:s")." postcodedata copied to oldpostcodedata\r\n";
	mysqlQuery("TRUNCATE postcodedata");
	echo date("Y-m-d H:i:s")." postcodedata truncated\r\n";
}
//* * * * * * * *   M A I N   F U N C T I O N   * * * * * * * *//

require "../../include/DB.inc";
require "../../DBInterface/FileProcessRecord.php";


if( $argc == 1 )
{
	echo " You need to specify the Post Codes file name... for example nohup php postcodedata.php Sec2SecOffPeakCarDistanceMilesTime60.csv\r\n";
}
else
{
	$inputfile = $argv[1];
	
	$db_user = "ReportGenerator";
	$db_pass = "tldttoths";

	$master = connectToDB( MasterServer, TexacoDB );

	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started\r\n";
	$handle = fopen ($inputfile,"r"); 
	$lineno = 1;
	backuptable();
	
	while ($data = fgetcsv ($handle, 1000, ","))
	{
			#	First - check the file is what we expect.
		if(($data[2] == '') && ($lineno == '1'))
		{
			#	The file is bad - report to the user
			echo "File is not in the correct format - please check\r\n";
			exit();
		}
		$lineno += 1;
			#	Only do this if this isnt a header row
		if($data[0] <> 'Source')
		{
			#	Assemble an array of the data we have received
			$ourData['Source']	= $data[0];
			$ourData['Target']	= $data[1];
			$ourData['Miles']	= $data[2];
			escapeData($ourData) ;
			$newrecord = mysqlInsert($ourData,"texaco.postcodedata");
			unset($ourData);
		}

		if( $lineno % 100000 == 0 )
		{
			echo date("Y-m-d H:i:s")." ".$lineno." processed\r\n";
		}
   	} 

	#	Now we need to clean up the PostCodes.
	echo date("Y-m-d H:i:s")." ".$lineno." processed\r\n";
	mysqlQuery("update postcodedata set Source = (concat( trim( mid(Source, 1, 2)), trim( mid(Source, 3, 2)), mid(Source, 5, 2))) where 1"  );
	echo date("Y-m-d H:i:s")." postcodedata Source updated\r\n";
	mysqlQuery("update postcodedata set Target = (concat( trim( mid(Target, 1, 2)), trim( mid(Target, 3, 2)), mid(Target, 5, 2))) where 1"  );
	echo date("Y-m-d H:i:s")." postcodedata Target updated\r\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
}
?>