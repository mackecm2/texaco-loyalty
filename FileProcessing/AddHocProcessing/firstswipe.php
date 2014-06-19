<?php
require 'db_connect.php';

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

#	First get a list of the transactions we need to amend.


$query="SELECT CardNo,FirstSwipeDate from Cards where FirstSwipeDate >= '2004-01-01'";

#$query="SELECT CardNo,FirstSwipeDate from Cards WHERE FirstSwipeDate >= '2004-01-01'
#AND FirstSwipeDate <= '2004-02-01'";

echo "Process Started $timedate\n\r";

// Perform our query
$result=mysql_query($query) or mysqlError("Query failed:\n$query");

if ($row = mysql_fetch_array($result))
{

	#	Now we have a set of results we can pulse through each one and find the first transaction location.



	do{

			$expdate = explode("-",$row['FirstSwipeDate']);
			$date = $expdate['0'].$expdate['1'];

			if($date <= '200503')
			{

				echo "We have CardNo - $row[CardNo] swiped on $row[FirstSwipeDate] formatted $date \r\n";


				#	Now select the first transaction for this Card Number

				$query="SELECT SiteCode,TransTime from Transactions$date where CardNo='$row[CardNo]' ORDER BY TransTime ASC LIMIT 1";
				#echo"$query\r\n";
				// Perform our query
				$result2=mysql_query($query) or mysqlError("Query failed:<br>$query");

				if($txrow = mysql_fetch_array($result2))
				{

					#	Having retrieved the original payment details we need to update the failing
					#	transaction with some correct Original Tx Details.
					echo "Record Retrieved - SiteCode $txrow[SiteCode]/TransTime - $txrow[TransTime]\r\n";

					$sql = "UPDATE Cards SET
								`FirstSwipeLoc` = '$txrow[SiteCode]'
							WHERE
								`CardNo` = '$row[CardNo]'
							LIMIT 1;";

					$updateresult=mysql_query($sql);

					echo "Record Updated - CardNo $row[CardNo]\r\n";



				}
			}
			else
			{
				echo "Bad Date found $date\r\n";
			}


	} while($row = mysql_fetch_array($result));


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

	echo "Process Completed $timedate\n\r";



} else {print "Sorry, no records were found!";}


?>
