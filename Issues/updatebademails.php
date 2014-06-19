<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$timedate = date("Y-m-d H:i:s");

//	Start of Script.

print "Update Bad Email Addresses\n\r";
echo "Process Started - $timedate\n\r";

#$handle = fopen ("/tmp/bademails.csv","r");

#connectToDB( AnalysisServer, AnalysisDB );

$lineno 	= 0;
$email		= 0;
$updatedmembers	= 0;
/*
while ($data = fgetcsv ($handle, 1000, ","))
{


	#	Only do this if this isnt a header row

	if($data[0] <> 'email')
	{
		$lineno ++;
				
		$insertdata['email'] = $data[0];

		$update = mysqlInsert($insertdata,"bademail");


	}

	unset($insertdata);

}  // end while ($data = fgetcsv ($handle, 1000, ","))

*/

mysqlSelect($emaildata, "email","bademail","1",0);

foreach($emaildata as $singleline)
{

	#echo "Got email $singleline[email]\r\n";
	
	if(mysqlSelect($memberdata,"MemberNo","texaco.Members","Members.email = '$singleline[email]'",1) > 0)
	{
	
		# echo "MemberNo $memberdata[MemberNo] match\r\n";
		
		$updatedata['ListCode'] = '2';
		mysqlUpdate($updatedata,"Feb07StatementListcodes","MemberNo = '$memberdata[MemberNo]'");
		$updatedmembers++;
	
	}
	
	$lineno ++;
	unset($updatedata);
	unset($singleline);
	unset($memberdata);
}












$timedate = date("Y-m-d H:i:s");
print "$timedate Import Complete\n\r";
print " \n\r";


echo "Summary\n\r";
echo "----------------------------------------------\n\r";
echo "$lineno Members Searched, Updated - $updatedmembers\n\r";

?>

</BODY>
</HTML>