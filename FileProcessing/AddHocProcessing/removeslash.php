<?php
	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";		
//	$db_pass ="";
require '../../include/DB.inc';

function cleaninput($data)
{

	#  Set up some variables to clean up the input on the website.

		$patterns[0] = "/\\\\/";

		$replacements[0] = "";


		$data 		=   preg_replace($patterns, $replacements, $data);
		$data 		=   str_replace("\r\n", " ", $data);

		return $data;

}

connectToDB();

#	First get a list of the transactions we need to amend.

$query = "select MemberNo,Surname from Members where Surname like '%\\\\\%'";

echo "Process Started $timedate\n\r";

// Perform our query

echo "$query\n";

$result=DBQueryExitOnFailure($query);



if ($row = mysql_fetch_array($result))
{

	#	Now we have a set of results we can pulse through each one and find the first transaction location.


	do{

			echo "We have Record $row[Surname]<br>";

					$Surname		=	cleaninput($row["Surname"]);


					$sql = "UPDATE Members SET Surname = '". mysql_real_escape_string( $Surname)."' WHERE	MemberNo = $row[MemberNo] LIMIT 1";
															   
					echo "$sql";

					$updateresult=DBQueryExitOnFailure($sql);

					echo mysql_affected_rows();
					echo "Record Updated - Surname $Surname<br>";

	} while($row = mysql_fetch_array($result));


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

	echo "Process Completed $timedate<br>";



} else {print "Sorry, no records were found!";}


?>
