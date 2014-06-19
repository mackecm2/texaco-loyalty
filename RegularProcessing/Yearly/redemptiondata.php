<?php 
////////////////////////////////////////////////////////////////////////////////////////////////////
// Connect to the database and make our database custom functions available to our environment.
   require 'db_connect.php';

$timedate = date("Y-m-d H:i:s");

//	Start of Script.

print "Extract Member Data\n\r";
print "Process Started - $timedate\n\r";

unlink("/tmp/MemberData.csv");

$outputfile = fopen("/tmp/MemberData.csv", "a");

/*

create table RedemptionAnalysis
select
M.MemberNo,
A.AccountNo,
M.PrimaryCard as CardNo,
A.SegmentCode,
CONCAT( RPad( April.Recency, 2, ' '), RPad( April.Value, 2, ' '),  RPad( April.Frequency, 2, ' ')) as AprilSegmentCode,
CONCAT( RPad( May.Recency, 2, ' '), RPad( May.Value, 2, ' '),  RPad( May.Frequency, 2, ' ')) as MaySegmentCode,
CONCAT( RPad( June.Recency, 2, ' '), RPad( June.Value, 2, ' '),  RPad( June.Frequency, 2, ' ')) as JuneSegmentCode,
case 
when CreatedBy = 'WEB' then 'Online' 
else 'Phone'
end as RegChannel, 
GenderCode,
DOB,
M.PostCode
from texaco.Members as M
join texaco.Accounts as A using (AccountNo)
join Reporting.RawKPIData200604 as April 
join Reporting.RawKPIData200605 as May 
join Reporting.RawKPIData200605 as June 
where M.PrimaryCard = April.CardNo
AND M.PrimaryCard = May.CardNo
AND M.PrimaryCard = June.CardNo;

*/


$outputfilerow = "MemberNo,AccountNo,CardNo,SegmentCode,AprilSegmentCode,MaySegmentCode,JuneSegmentCode,RegChannel,GenderCode,DOB ,PostCode ,HomeSite,FirstSwipeDate,LastSwipeDate,FirstRedempDate,LastRedempDate\r\n";
fwrite($outputfile, $outputfilerow);



mysqlSelect($memberData,"M.MemberNo,A.AccountNo,M.PrimaryCard as CardNo,A.SegmentCode,case when M.CreatedBy = 'WEB' then 'Online' else 'Phone' end as RegChannel,
			GenderCode,DOB,M.PostCode,HomeSite,C.FirstSwipeDate,C.LastSwipeDate,A.FirstRedempDate,A.LastRedempDate",
			"Members as M join Accounts as A using (AccountNo) join Cards as C","A.SegmentCode not like 'X%' AND M.PrimaryCard = C.CardNo and PrimaryMember = 'Y'",0);
foreach($memberData as $member)
{


	mysqlSelect($aprilData,"CONCAT( RPad( Recency, 2, ' '), RPad( Value, 2, ' '),  RPad( Frequency, 2, ' ')) as SegmentCode","Reporting.RawKPIData200704","CardNo = '$member[CardNo]'", 1);
	mysqlSelect($mayData,"CONCAT( RPad( Recency, 2, ' '), RPad( Value, 2, ' '),  RPad( Frequency, 2, ' ')) as SegmentCode","Reporting.RawKPIData200705","CardNo = '$member[CardNo]'", 1);
	mysqlSelect($juneData,"CONCAT( RPad( Recency, 2, ' '), RPad( Value, 2, ' '),  RPad( Frequency, 2, ' ')) as SegmentCode","Reporting.RawKPIData200706","CardNo = '$member[CardNo]'", 1);


	$outputfilerow = "$member[MemberNo],$member[AccountNo],$member[CardNo],$member[SegmentCode],$aprilData[SegmentCode],$mayData[SegmentCode],$juneData[SegmentCode],$member[RegChannel],$member[GenderCode],$member[DOB] ,$member[PostCode] ,$member[HomeSite],$member[FirstSwipeDate],$member[LastSwipeDate],$member[FirstRedempDate],$member[LastRedempDate]\r\n";
	
	#echo "Output $outputfilerow\r\n";
	
	fwrite($outputfile, $outputfilerow);


	
	unset($aprilData);
	unset($mayData);
	unset($juneData);
	unset($member);
}


unset($memberData);

fclose($outputfile);
 

$timedate = date("Y-m-d H:i:s");

echo "Process Complete - $timedate\n\r";










?>







