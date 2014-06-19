<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.


print "Experian Data Cleanse Import - XD Members file\n\r";

echo "Process Started - $timedate\n\r";


$handle = fopen ("/tmp/VCCP_XDMembers_Output.txt","r");


$lineno 			= 0;
$AddressChange 			= 0;
$PAFAddressVerified 		= 0;
$PAFAddressCloseVerified 	= 0;
$PAFAddressFailed 		= 0;
$PAFForeignAddress 		= 0;
$ForeignAddress			= 0;
$NonResidentialAddress		= 0;
$Deceased			= 0;
$GoneAway			= 0;


while ($data = fgetcsv ($handle, 1000, ","))
{



	#	Only do this if this isnt a header row

	if($data[0] <> 'MemberNo')
	{
		$lineno ++;
		
		#	Assemble an array of the data we have received
		$MemberNo		= $data[0];
		$ourData['Title']	= $data[5];
		$ourData['Initials']	= $data[6];
		$ourData['Forename']	= $data[7];		
		$ourData['Surname']	= $data[8];		
		$ourData['Company']	= $data[9];		
		$ourData['Address1']	= $data[10];		
		$ourData['Address2']	= $data[11];		
		$ourData['Address3']	= $data[12];		
		$ourData['Address4']	= $data[13];		
		$ourData['Address5']	= $data[14];		
		$ourData['PostCode']	= $data[15];		
		
		if ($data[18] == 'C')
		{
			#	NCOA Indicator set
			$AddressChange ++;
		}
		if ($data[18] == 'I')
		{
			#	NCOA Indicator set
			$AddressChange ++;
			$PAFForeignAddress ++;			
			$ourData['NonUKAddress']	= 'Y';				
		}		
		
		
		if ($data[19] == 'V')
		{
			#	PAF Indicator set
			$PAFAddressVerified ++;
		}		
		else if ($data[19] == 'C')
		{
			#	PAF Indicator set
			$PAFAddressCloseVerified ++;
		}		
		else if ($data[19] == 'F')
		{
			#	PAF Indicator set
			$PAFAddressFailed ++;
		}
		else if ($data[19] == 'I')
		{
			#	PAF Indicator set
			$PAFForeignAddress ++;
			$ourData['NonUKAddress']	= 'Y';			
			
		}
		
		if ($data[20] == 'Y')
		{
			#	Foreign Indicator set
			$ForeignAddress ++;
			$ourData['NonUKAddress']	= 'Y';
		}		
		
		if ($data[21] == 'Y')
		{
			#	Non Residential Indicator set
			$NonResidentialAddress ++;
			$ourData['NonResidential']	= 'Y';
			
		}
		
		if (($data[22] == 'I') OR ($data[22] == 'F'))
		{
			#	Deceased Indicator set
			$Deceased ++;
			$ourData['Deceased']	= 'Y';
			
		}		
		
		if (($data[23] == 'I') OR ($data[23] == 'F'))
		{
			#	Deceased Indicator set
			$GoneAway ++;
			$ourData['GoneAway']	= 'Y';
			
		}
		
		escapeData($ourData) ;
		$updaterecord = mysqlUpdate($ourData,"Members","MemberNo = '$MemberNo'");

		#print "$MemberNo updated\n\r";

		unset($ourData);
		
		
		#	Now we need to store the Experian Data
		
		$expData['MemberNo']		= $data[0];
		$expData['AccountNo']		= $data[1];
		$expData['HomeSite']		= $data[4];		
		$expData['Title']		= $data[5];
		$expData['Initials']		= $data[6];
		$expData['Forename']		= $data[7];		
		$expData['Surname']		= $data[8];		
		$expData['Company']		= $data[9];		
		$expData['Address1']		= $data[10];		
		$expData['Address2']		= $data[11];		
		$expData['Address3']		= $data[12];		
		$expData['Address4']		= $data[13];		
		$expData['Address5']		= $data[14];		
		$expData['PostCode']		= $data[15];		
		$expData['NCOAIndicator']	= $data[18];
		$expData['PAFIndicator']	= $data[19];
		$expData['ForeignIndicator']	= $data[20];		
		$expData['NonResidentialIndicator']	= $data[21];		
		$expData['Deceased']		= $data[22];		
		$expData['GoneAway']		= $data[23];		
		$expData['IndividualId']	= $data[24];		
		$expData['IndividualDuplicate']	= $data[25];		
		escapeData($expData) ;
		$newrecord = mysqlInsert($expData,"experiandata");
		
		unset($expData);		
		
		
	#if (	$lineno == '100')
	#{
	#	break;
	#}
		
		
		

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
echo "$AddressChange NCOA Addresses changed\n\r";
echo "$PAFAddressVerified PAF Verified lines\n\r";
echo "$PAFAddressCloseVerified PAF Close Address lines\n\r";
echo "$PAFAddressFailed PAF Failed Address lines\n\r";
echo "$PAFForeignAddress Foreign Address lines\n\r";
echo "$NonResidentialAddress NonResidential Address lines\n\r";
echo "$Deceased Deceased lines\n\r";
echo "$GoneAway GoneAway lines\n\r";






?>

</BODY>
</HTML>