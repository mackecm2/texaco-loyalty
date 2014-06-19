
<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
#   require 'slavedb_connect.php';


require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													

$db_user = "pma001";
$db_pass = "amping";

$slave = connectToDB( ReportServer, AnalysisDB );
#$master = connectToDB( MasterServer, AnalysisDB );

$numtransactions = 0;
$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.

print "Repair Feb07\n\r";
echo "Process Started - $timedate\n\r";

$handle = fopen ("/data/zipfiles/Feb07StatementData.csv","r");



#die("$thismonth");

while ($data = fgetcsv ($handle, 2000, ","))
{


	#	Only do this if this isnt a header row
	$numtransactions ++;
	
	$PrimaryCard		= $data[1];
	$Balance		= $data[50];

	
	$sql = "select AccountNo from texaco.Cards join texaco.Members using (MemberNo) where CardNo = '$PrimaryCard' limit 1";
	#echo "$sql\n";	
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	if( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		
		$AccountNo = $row['AccountNo'] ;
		
		
		$sql = "Insert into Feb07StatementBalance( AccountNo, Balance ) 
		values ( $AccountNo,$Balance)";
		
		#echo "$sql\n";	

		$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		
		
	}
	
	
	if( ($numtransactions % 5000) == 0 )
	{
		echo date("h:i:s");
		echo " $numtransactions\n";
	}
	
	

		
		
}  // end while ($data = fgetcsv ($handle, 1000, ","))


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
print "$timedate Complete\n\r";
print " \n\r";


?>
