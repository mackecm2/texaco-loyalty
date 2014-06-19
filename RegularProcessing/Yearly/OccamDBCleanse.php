<?php


////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.

print "Occam Data Cleanse Import\n\r";
echo "Process Started - $timedate\n\r";


$handle = fopen ("/tmp/OccamDataCleanse.csv","r");
#$handle = fopen ("/tmp/TestFile.csv","r");


$lineno 			= 0;
$AddressChange 			= 0;
$Deceased			= 0;
$GoneAway			= 0;


while ($data = fgetcsv ($handle, 2000, ","))
{



	#	Only do this if this isnt a header row

	if($data[0] <> 'PERSON_ID')
	{
		$lineno ++;
		
		#	Assemble an array of the data we have received
		$ourData['Title']	= $data[5];
		$ourData['Initials']	= $data[7];
		$ourData['Forename']	= $data[6];		
		$ourData['Surname']	= $data[8];		
		$ourData['Address1']	= $data[10];		
		$ourData['Address2']	= $data[11];		
		$ourData['Address3']	= $data[12];		
		$ourData['Address4']	= $data[15];		
		$ourData['Address5']	= $data[16];		
		$ourData['PostCode']	= $data[17];
		
		$NM_OTHER 	= $data[22];
		$SUPPRESS 	= $data[23];
		$CardNo 	= $data[38];
		

		if ($NM_OTHER == 'Y')
		{
			#	Set client to GoneAway
			$GoneAway ++;
			$ourData['GoneAway']	= 'Y';
		
		}
		
		if ($SUPPRESS == 'MSCREEN' OR $SUPPRESS == 'BEREAVE')
		{
			#	Deceased Indicator set
			$Deceased ++;
			$ourData['Deceased']	= 'Y';
		}		
		elseif ($SUPPRESS == 'NCOA' OR $SUPPRESS == 'GAS')
		{
			#	Set client to GoneAway
			$GoneAway ++;
			$ourData['GoneAway']	= 'Y';
		}		


		//	Now get the MemberNo
		
		mysqlSelect($MemberData,"Cards.MemberNo","Cards","CardNo = '$CardNo'",1);
		$MemberNo = $MemberData['MemberNo'];
		
		echo "We have Card $CardNo and Member $MemberNo Suppress = $SUPPRESS, NM_OTHER = $NM_OTHER\r\n";
		
		
		escapeData($ourData) ;
		$updaterecord = mysqlUpdate($ourData,"Members","MemberNo = '$MemberNo'");

		print "$MemberNo updated\n\r";

		unset($ourData);
		unset($MemberData);
		
		
		
		if( ($lineno % 10000) == 0 )
		{
			#die();
			echo date("h:i:s");
			echo " - $lineno lines processed\n";
		}
		
		
		

	}


}  // end while ($data = fgetcsv ($handle, 1000, ","))


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
print "$timedate Import Complete\n\r";
print " \n\r";


echo "Summary\n\r";
echo "----------------------------------------------\n\r";
echo "$lineno lines processed\n\r";
echo "$Deceased Deceased lines\n\r";
echo "$GoneAway GoneAway lines\n\r";






?>

</BODY>
</HTML>