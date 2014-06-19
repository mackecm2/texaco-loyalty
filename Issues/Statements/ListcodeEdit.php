
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

print "ListCode Edit Feb07\n\r";
echo "Process Started - $timedate\n\r";




	
	
	$sql = "select AccountNo from EmailOptOuts join texaco.Members where (Members.Email = EmailOptOuts.email)";
	echo "$sql\n";	
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		
		#	Only do this if this isnt a header row
		$numtransactions ++;
		$sql = "update May07StatementListcodes set ListCode = '2' where AccountNo = '$row[AccountNo]'";
		
		echo "$sql\n";	

		$slaveRes2 = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		#if( ($numtransactions % 5000) == 0 )
		#if( $numtransactions == 10 )
		#{
		#	break;
		#	echo date("h:i:s");
		#	echo " $numtransactions\n";
		#}
			
		
	}
	
	

	


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
print "$timedate Complete\n\r";
print " \n\r";


?>
