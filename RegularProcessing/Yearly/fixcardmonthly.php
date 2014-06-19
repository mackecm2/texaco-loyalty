<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

echo "$timedate Process Started\n\r";

#	We have to fix the following tables :
#
#	CardMonthly2003 - where YearMonth <= 200410
#	CardMonthly2000 - all records
#
/*
echo "<br>Fixing CardMonthly2000<br>";


$YearMonth = array('200210');




foreach($YearMonth as $SingleYearMonth)
{

	$MonthToFix = 	$SingleYearMonth;

	echo "<br>Fixing $MonthToFix<br>";

	if(mysqlSelect($records,"CardNo,Swipes,PointsEarned","CardMonthly2000","YearMonth = $MonthToFix","14599") >1)
	{

		foreach($records as $singlerecord)
		{
			$updatedata['PointsEarned'] = $singlerecord['Swipes'];
			$updatedata['Swipes'] = $singlerecord['PointsEarned'];

			$update = mysqlUpdate($updatedata,"CardMonthly2000","CardNo = '$singlerecord[CardNo]' AND YearMonth = $MonthToFix",'1');
			unset($updatedata);
		}
	}
}


unset($YearMonth);
unset($SingleYearMonth);
unset($records);
unset($singlerecord);
*/


echo "<br>Fixing CardMonthly2003<br>";

/*
$YearMonth = array('200301','200302','200303','200304','200305','200306',
				   '200307','200308','200309','2003010','200311','200312',
				   '200401','200402','200403','200404','200405','200406',
				   '200407','200408','200409');

*/
$YearMonth = array('200210');

foreach($YearMonth as $SingleYearMonth)
{

	$continue = 'TRUE';
	$offset = '0';
	$limit = '200';
	$MonthToFix = 	$SingleYearMonth;

	echo " $SingleYearMonth";


	do
	{

		$sql = "
								SELECT
									CardNo,Swipes,PointsEarned
								FROM
									CardMonthly2000
								WHERE
									YearMonth = $MonthToFix
								LIMIT $offset,$limit

							";


		# echo "<br>$sql";
		$result = mysql_query($sql);
		if ($row = mysql_fetch_array($result))
		{

			do {

			$sql = "
								UPDATE
									CardMonthly2000
								SET
									PointsEarned 	= $row[Swipes],
									Swipes		= $row[PointsEarned]
								WHERE
									CardNo = '".$row['CardNo']."'
								AND YearMonth = $MonthToFix
								LIMIT 1;

							";

		# echo "<br>$sql";

				$update = mysql_query($sql);

			} while($row = mysql_fetch_array($result));
		}
		else
		{
			$continue = 'FALSE';
		}

		$offset += $limit;
		
	} while($continue == 'TRUE');

}



$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

echo "$MonthToFix fixed - $timedate Process Completed\n\r";


?>
