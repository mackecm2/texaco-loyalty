<?php 
$db_user = "ReportGenerator";
$db_pass = "tldttoths";
$db_host = "weoudb";
$db_name = 'texaco';

require "../../include/DB.inc";
#require "../../Reporting/GeneralReportFunctions.php";													

$timedate = date("Y-m-d H:i:s");

$count = 0;
$delaccounts = 0;
$delmembers = 0;

function ExtractMatchString( $Postcode )
{
	$SpacePos = strpos( $Postcode, " " );
	if( $SpacePos )
	{
		if( substr( $Postcode, $SpacePos + 1, 1) == " " )
		{
			return substr( $Postcode, 0, $SpacePos + 1 ) . substr( $Postcode, $SpacePos + 2	, 1 );
		}
		else
		{
			return substr( $Postcode, 0, $SpacePos + 2 );
		}
	}
	else
	{
		return substr( $Postcode, 0, 4 ) . " ".substr( $Postcode, 4	, 1 );
		#return false;
	}
}


echo "Sitedata PostCode update\n\r";
echo "Process Started - $timedate\n\r";

#$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$lineno 			= 0;

	

$sql = "select PostCode,SiteCode from sitedata where 1 ";
echo "$sql\r\n";

$siteRes = mysql_query( $sql, $master ) or die( mysql_error($master) );

#echo "Number of Cards - ". mysql_num_rows($slaveRes). "\n\r";
if( $row = mysql_fetch_assoc( $siteRes ) )
{
	
	$NewSitePostCode = ExtractMatchString( $row['PostCode'] );
	
	#echo "old site = $row[PostCode], new site = $NewSitePostCode\r\n";
	#echo "old customer = $PostCode, new customer = $NewCustomerPostCode\r\n";


	$sql = "update sitedata set ShortPostCode = '$NewSitePostCode' where SiteCode = $row[SiteCode]";
	#echo "sql - $sql\r\n";
	
	$result = DBSingleStatQueryNoError( $sql );

	
}



$timedate = date("Y-m-d H:i:s");
echo "$timedate Process Completed\n\r";
?>