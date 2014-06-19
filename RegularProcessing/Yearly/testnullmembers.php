<?php 

	require "../../include/DB.inc";
	require "../../Reporting/GeneralReportFunctions.php";													

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

$count = 0;

$db_user = "pma001";
$db_pass = "amping";

echo "Liability Reduction Process\n\r";
echo "Registered XD Accounts with LastSwipeDate (of any of the Accounts Cards) older than 24 months or no LastSwipeDate at all. \n\r";
echo "Process Started - $timedate\n\r";

	$slave = connectToDB( ReportServer, TexacoDB );
	
	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "select distinct(AccountNo),Stars from Tracking where AccountNo is not NULL and MemberNo is not NULL and CreatedBy = 'March06Liability';";

	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

	echo "Number of Accounts - ". mysql_num_rows($slaveRes). "\n\r";


	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$count++;
		
		#	Now reset the Balance from the Account

		$sql = "Update Accounts set Balance = '$row[Stars]' where AccountNo = '$row[AccountNo]' limit 1;";
		
		#echo "SQL - $sql\n\r";
	
		mysql_query( $sql, $master )  or die( mysql_error($master) );

		if( ($count % 50000) == 0 )
		{
			echo date("h:i:s");
			echo "Processed $count\n\r";
		}	
	}
  

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
echo "$timedate Process Completed\n\r";
?>