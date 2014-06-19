<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.

print "Home Site Travel Time Import\n\r";
echo "Process Started - $timedate\n\r";

$handle = fopen ("/data/dataimport/homesitetraveltimes.csv","r");


$lineno 	= 0;
$MemberNo	= 0;
$HomeSite	= 0;
$TravelTime	= 0;
$updatedmembers	= 0;
$accountupdates	= 0;
$no_site_match	= 0;
$nomember	= 0;

while ($data = fgetcsv ($handle, 1000, ","))
{


	#	Only do this if this isnt a header row

	if($data[0] <> 'ID')
	{
		$lineno ++;
		
		$MemberNo		= $data[1];
		$HomeSite		= $data[18];
		$TravelTime		= $data[20];	
		
		#echo "$MemberNo   $HomeSite    $TravelTime<br>";
		
		if(mysqlSelect($memberData,"Members.MemberNo,HomeSite,Accounts.AccountNo","Members join Accounts using(AccountNo) ","Members.MemberNo = '$MemberNo'","1") >0)
		{
				
			
			#echo "Home site is $memberData[HomeSite]<br>";
			
			#	We have a Member so compare the Home Site
			
			if($HomeSite == $memberData['HomeSite'])
			{
				#	Home site matches - write the data away
				
				$updatedata['HomeSiteTravelTime'] = $TravelTime;
				$update = mysqlUpdate($updatedata,"Members","Members.MemberNo = '$MemberNo'",'1');
				unset($updatedata);
				
				#echo "MemberNo $MemberNo updated \n\r";
				
				$updatedmembers ++;
				
			
			}
			elseif($memberData['HomeSite'] == '')
			{
			
				#	Home Site is blank so insert our entry.
				
				$accountsupdate['HomeSite'] = $HomeSite;
				$accountupdate = mysqlUpdate($accountsupdate,"Accounts","AccountNo = '$memberData[AccountNo]'",'1');
				unset($accountsupdate);
				
				$accountupdates ++;
				
				$updatedata['HomeSiteTravelTime'] = $TravelTime;
				$update = mysqlUpdate($updatedata,"Members","Members.MemberNo = '$MemberNo'",'1');
				unset($updatedata);
				
				#echo "MemberNo $MemberNo updated \n\r";
				
				$updatedmembers ++;			
				
			
			}
			else
			{
			
				#	Home Site Does not match - do not update.
				
				#echo "MemberNo $MemberNo not matching HomeSite \n\r";
								
				$no_site_match ++;
				
			
			}
			
			

		}
		else
		{
			$nomember ++;		
		
		}
		
		
		unset($memberData);

		

	}



}  // end while ($data = fgetcsv ($handle, 1000, ","))


$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
print "$timedate Import Complete\n\r";
print " \n\r";


echo "Summary\n\r";
echo "----------------------------------------------\n\r";
echo "$updatedmembers Members Updated\n\r";
echo "$accountupdates NULL HomeSite Account Records changed\n\r";
echo "$no_site_match Member's HomeSite did not match\n\r";
echo "$nomember Member's not found\n\r";

?>

</BODY>
</HTML>