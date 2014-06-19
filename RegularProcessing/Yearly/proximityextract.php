<?php 
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$timedate = date("Y-m-d H:i:s");

//	Start of Script.

print "Update Member PostCodes\n\r";
print "Process Started - $timedate\n\r";

unlink("/tmp/SiteProximityData.csv");

$outputfile = fopen("/tmp/SiteProximityData.csv", "a");





print "Proximity Data file<br>\n";

mysqlSelect($memberData,"MemberNo,ShortPostCode,SegmentCode","Members join Accounts using (AccountNo)","(SegmentCode like 'A%' or SegmentCode like 'N%') and ShortPostCode <> ''",0);

foreach($memberData as $member)
{

	$MemberShortPostCode = $member['ShortPostCode'];

	
	//echo "sql is select SiteCode,Miles from sitedata as s join newpostcodedata as p where (p.Source = '$MemberShortPostCode' AND p.Target = s.ShortPostCode) and Miles < 50<br>\r\n";
	mysqlSelect($siteData,"s.SiteCode, p.Miles","sitedata as s, newpostcodedata as p"," (p.Source = '$MemberShortPostCode' AND p.Target = s.ShortPostCode) and Miles < 50 and (s.Status = 'Live' or s.status='Live/Pending')", 0);
	foreach($siteData as $site)
	{
		$outputfilerow = "$site[SiteCode],$member[MemberNo],$member[SegmentCode],$site[Miles]\r\n";
		fwrite($outputfile, $outputfilerow);
		unset($site);		
	}
	
	unset($siteData);
	unset($member);
}


unset($memberData);



	





fclose($outputfile);
 

$timedate = date("Y-m-d H:i:s");

echo "Process Complete - $timedate\n\r";










?>







