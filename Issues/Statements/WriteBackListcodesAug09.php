<?php 

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
$linecount = 0;

$db_user = "root";
$db_pass = "Trave1";
	
echo "Listcode Update Aug 09 Statement\n\r";
echo "Process Started - $timedate\n\r";

$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, AnalysisDB );

$handle = fopen ("/tmp/Aug09Listcodes.csv","r");

$count 		= 0;
$code1count 	= 0;
$code2count 	= 0;
$othercodecount = 0;

while ($data = fgetcsv ($handle, 1000, ","))
{
	
	$ListCode 	= mysql_escape_string($data['0']);
	$AccountNo 	= mysql_escape_string($data['1']);
	
	// Set counts so we can track success
	
	if($ListCode == '1')
	{
		$code1count++;
	}
	elseif($ListCode == '2')
	{
		$code2count++;
	}
	else
	{
		$othercodecount++;
	}
	
	$count++;
	
	$sql = "update Aug09StatementListcodes set ListCode = $ListCode where AccountNo = $AccountNo limit 1";
echo $sql."\r\n";
	mysql_query( $sql, $master )  or die( mysql_error($master) );

}

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

echo "$timedate Process Completed\n\r";
echo "Total number of lines:  $count\n\r";
echo "Total number of Listcode 1's :  $code1count\n\r";
echo "Total number of Listcode 2's :  $code2count\n\r";
echo "Total number of other Listcodes :  $othercodecount\n\r";

?>
