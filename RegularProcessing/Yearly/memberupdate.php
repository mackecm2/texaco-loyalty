<?php 
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$timedate = date("Y-m-d H:i:s");

//	Start of Script.

print "Update Member PostCodes\n\r";
echo "Process Started - $timedate\n\r";


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


//	Start of Script.


print "Post Codes file\n";

mysqlSelect($memberData,"PostCode,MemberNo","texaco.Members","ShortPostCode = ''",'1000000');

foreach($memberData as $member)
{

	$NewPostCode = ExtractMatchString( $member['PostCode'] );

	$ourData['ShortPostCode'] = $NewPostCode;
	
	$update = mysqlUpdate($ourData,"texaco.Members","MemberNo = '$member[MemberNo]'");

	unset($ourData);
	unset($member);

}



$timedate = date("Y-m-d H:i:s");

echo "Process Complete - $timedate\n\r";

?>







