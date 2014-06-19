<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$timedate = date("Y-m-d H:i:s");

//	Start of Script.

print "Update Bad Email Addresses\n\r";
echo "Process Started - $timedate\n\r";

$handle = fopen ("/data/dataimport/bademails.csv","r");

connectToDB( AnalysisServer, AnalysisDB );

$lineno 	= 0;
$email		= 0;
$updatedmembers	= 0;

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

$timedate = date("Y-m-d H:i:s");
print "$timedate Import Complete\n\r";
print " \n\r";


echo "Summary\n\r";
echo "----------------------------------------------\n\r";
echo "$lineno Members Updated\n\r";

?>

</BODY>
</HTML>