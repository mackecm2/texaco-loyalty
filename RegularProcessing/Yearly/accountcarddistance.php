<?php 

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													

$timedate = date("Y-m-d H:i:s");

$count = 0;
$delaccounts = 0;
$delmembers = 0;
$db_user = "pma001";
$db_pass = "amping";

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


echo "Account Card Check Distances\n\r";
echo "Process Started - $timedate\n\r";

$slave = connectToDB( ReportServer, TexacoDB );
#$master = connectToDB( MasterServer, TexacoDB );

$handle = fopen ("/tmp/LapsedRecords.csv","r");
$outputfile = fopen("/tmp/LapsedRecordsOut.csv", "a");


$lineno 			= 0;


while ($data = fgetcsv ($handle, 2000, ","))
{
	$lineno++;
	
	$CustNo = $data[0];
	$CustName = $data[1];
	$Contact = $data[2];
	$Add1 = $data[3];
	$Add2 = $data[4];
	$Town = $data[5];
	$County = $data[6];
	$PostCode = $data[7];
	$Phone = $data[8];
	$Fax = $data[9];	
	$Email = $data[10];
	$StartDate = $data[11];
	$HomeSite = $data[12];
	$Status = $data[13];
	$Miles = "Miles";
	$SearchStatus = "Search Status";
		
	if($lineno <> '1')
	{
	

		$sql = "select PostCode from sitedata where SiteCode = '$HomeSite' limit 1 ";
		$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

		#echo "Number of Cards - ". mysql_num_rows($slaveRes). "\n\r";
		if( $row = mysql_fetch_assoc( $slaveRes ) )
		{
			
			$NewSitePostCode = ExtractMatchString( $row['PostCode'] );
			$NewCustomerPostCode = ExtractMatchString( $PostCode );
			
			echo "old site = $row[PostCode], new site = $NewSitePostCode\r\n";
			echo "old customer = $PostCode, new customer = $NewCustomerPostCode\r\n";
			
		}
		else
		{
			echo "HomeSite $HomeSite does not exist\r\n";
			$Miles = '';
			$SearchStatus = 'HomeSite Not Found';
		}
		
		
		if( $NewSitePostCode and $NewCustomerPostCode )
		{
			$sql = "Select Miles from  postcodedata where Source = '$NewCustomerPostCode' and Target = '$NewSitePostCode'";
			echo "sql - $sql\r\n";
			
			$Miles = DBSingleStatQueryNoError( $sql );
			if( $Miles )
			{
				echo "We have miles - $Miles\r\n";
				$SearchStatus = 'OK';
			}
			else
			{
				echo "Unable to calculate\r\n";
				$Miles = '';
				$SearchStatus = 'Not in PostCode Data';
			}

		}		
		else
		{
			echo "Bad PostCodes\r\n";
			$Miles = '';
			$SearchStatus = 'Bad PostCode';
		}		
		
		
	
	}
	
	// Now we're here we need to export the file again.
	
	
	$outputfilerow = "$CustNo,$CustName,$Contact,$Add1,$Add2,$Town,$County,$PostCode,$Phone,$Fax,,$StartDate,$HomeSite,$Status,$Miles,$SearchStatus\r\n";
	
	fwrite($outputfile, $outputfilerow);
	


}


fclose($outputfile);
  

$timedate = date("Y-m-d H:i:s");
echo "$timedate Process Completed\n\r";
?>