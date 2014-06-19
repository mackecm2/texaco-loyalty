<?php 
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$timedate = date("Y-m-d H:i:s");

//	Start of Script.

print "Calculate Member Proximity\n\r";
print "Process Started - $timedate\n\r";

#unlink("/tmp/SiteProximityData.csv");
#$outputfile = fopen("/tmp/SiteProximityData.csv", "a");


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

function updatesitedata()
{


	mysqlSelect($siteData,"s.SiteCode, s.PostCode","sitedata as s","1", 0);
	foreach($siteData as $site)
	{

		#$NewSitePostCode = ExtractMatchString( $row['PostCode'] );

		$updateData['ShortPostCode'] = ExtractMatchString( $site['PostCode'] );

		mysqlUpdate($updateData,"sitedata","SiteCode = $site[SiteCode]");

		unset($site);	
		unset($updateData);
		
	}
	
	unset($siteData);



}



function updatememberproximitytable()
{

	//	Empty original table
	mysqlQuery("delete from MemberProximity where 1");
	

	mysqlSelect($memberData,"MemberNo,ShortPostCode,SegmentCode","Members join Accounts using (AccountNo)","(SegmentCode like 'A%' or SegmentCode like 'N%') and ShortPostCode <> ''",0);
	foreach($memberData as $member)
	{

		$MemberShortPostCode = $member['ShortPostCode'];


		echo "sql is select SiteCode,Miles from sitedata as s join newpostcodedata as p where (p.Source = '$MemberShortPostCode' AND p.Target = s.ShortPostCode) and (s.Status = 'Live' or s.status='Live/Pending')<br>\r\n";
		mysqlSelect($siteData,"s.SiteCode, p.Miles","sitedata as s, newpostcodedata as p"," (p.Source = '$MemberShortPostCode' AND p.Target = s.ShortPostCode) and (s.Status = 'Live' or s.status='Live/Pending')", 0);
		foreach($siteData as $site)
		{

			$ourData['MemberNo'] = $member['MemberNo'];
			$ourData['SiteCode'] = $site['SiteCode'];
			$ourData['Distance'] = $site['Miles'];
			mysqlInsert($ourData,"MemberProximity");


			#$outputfilerow = "$site[SiteCode],$member[MemberNo],$member[SegmentCode],$site[Miles]\r\n";
			#fwrite($outputfile, $outputfilerow);
			unset($site);
			unset($ourData);
		}

		unset($siteData);
		unset($member);
	}


	unset($memberData);


}






#updatesitedata();
updatememberproximitytable();




















$timedate = date("Y-m-d H:i:s");

echo "Process Complete - $timedate\n\r";










?>







