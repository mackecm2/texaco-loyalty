<?php 


require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													


function validatecard($cardnumber)
{
    $cardnumber=preg_replace("/\D|\s/", "", $cardnumber);  # strip any non-digits
    $cardlength=strlen($cardnumber);
    $parity=$cardlength % 2;
    $sum=0;
    for ($i=0; $i<$cardlength; $i++) {
      $digit=$cardnumber[$i];
      if ($i%2==$parity) $digit=$digit*2;
      if ($digit>9) $digit=$digit-9;
      $sum=$sum+$digit;
    }
    $valid=($sum%10==0);
    return $valid;
}


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

$db_user = "pma001";
$db_pass = "amping";
$count = 0;

echo "Card Check File\n\r";
echo "Process Started - $timedate\n\r";

$slave = connectToDB( ReportServer, TexacoDB );
#$master = connectToDB( MasterServer, TexacoDB );

$sql = "select MemberNo,AccountNo,PrimaryCard,CreationDate,CreatedBy from Members where PrimaryCard <> '';";

$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

echo "Number of Members - ". mysql_num_rows($slaveRes). "\n\r";


while( $row = mysql_fetch_assoc( $slaveRes ) )
{


	// Check the card
	
	if(!validatecard($row['PrimaryCard']))
	{
	
		// If the card is bad we need the other details
		
		echo"$row[MemberNo],$row[AccountNo],$row[PrimaryCard],$row[CreationDate],$row[CreatedBy]\n\r";
		
		
	
	}


	#if( ($count % 100) == 0 )
	#{
	#	echo date("h:i:s");
	#	echo "Processed $count\n\r";
	#}
	
	$count++;
	
}
  

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";



echo "$timedate Process Completed\n\r";
?>