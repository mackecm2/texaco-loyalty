<?php

require "GeneralReportFunctions.php";
require "../include/DB.inc";

function CreateRecruitmentSites( $Month )
{
	$sql = "drop table RecruitmentSites$Month";
	mysql_query( $sql );

	$sql = "create Table RecruitmentSites$Month
		Select count(*) as NewCardSwipes, FirstSwipeLoc as SiteCode, sum(if( TransValue > 20, 1, 0 )) Over20 
		from texaco.Cards Join texaco.Transactions$Month as t on( Cards.CardNo = t.CardNo and FirstSwipeDate = TransTime ) 
		where Date_Format( FirstSwipeDate , '%Y%m') = $Month group by FirstSwipeLoc";
	DBQueryExitOnFailure( $sql );
}

# calculate the member site spends

function CreateSiteCardSwipes( $Month )
{
 	$sql = "drop table SiteCardSwipes$Month";
	mysql_query( $sql );
	$sql = "create table SiteCardSwipes$Month 
			select CardNo, SiteCode, sum(TransValue) as SpendValue, count(* ) as Swipes 
			from texaco.Transactions$Month group by CardNo, SiteCode";
		DBQueryExitOnFailure( $sql );

	$sql = "Alter table SiteCardSwipes$Month add index( CardNo )";
		DBQueryExitOnFailure( $sql );
}

function InsertIntoReport( $Month)
{
	$sql = "replace into LeagueSiteRecruitmentReportData
			select 
			$Month,
			r.SiteCode,
			COT,
			AreaCode,
			RegionCode,
			sum(NewCardSwipes),
			sum(Over20)
			from RecruitmentSites$Month as r left join texaco.sitedata on( r.SiteCode = sitedata.SiteCode )
			group by
			r.SiteCode,
			SiteType,
			AreaCode,
			RegionCode";
		DBQueryExitOnFailure( $sql );
}

# fill in column 5 and 6 (and 4.5)

function CreateAvgSpendData( $Month )
{
	$sql = "replace into SiteAverageCardSpend 
		select $Month as YearMonth, SiteCode, count(*) as ActiveMembers, sum(SpendValue), avg(SpendValue) 
		from  SiteCardSwipes$Month
		group by SiteCode";
		DBQueryExitOnFailure( $sql );
}

function HomeSiteMembersData( $Month )
{
# fill in columns 3 and 4
	$sql = "replace into HomeSiteMembers
	select $Month as Month, HomeSite, count(*) as TotalMembers, sum(OKMail = 'Y') as Mailable 
	from texaco.Members join texaco.Accounts using(AccountNo) 
	where PrimaryMember = 'Y' and HomeSite is not null
	group by HomeSite";
		DBQueryExitOnFailure( $sql );
}

function LeagueSiteTableData( $Month )
{
	$sql = "replace into LeagueSiteTables
			select 
			$Month,
			L.SiteCode,
			SiteType,
			AreaCode,
			RegionCode,
			TotalRecruited,
			Over20,
			TotalMembers,
			Mailable, 
			TotalSpend,
			AvgSpend
			from HomeSiteMembers  as H
			left join LeagueSiteRecruitmentReportData  as L on( L.SiteCode = H.HomeSite and L.CreationMonth = $Month )
			left join SiteAverageMemberSpend as S on( L.SiteCode = S.SiteCode and L.CreationMonth = $Month ) ";
		DBQueryExitOnFailure( $sql );
}

function CreateLegueSiteMonth( $Month )
{
	echo "Processing $Month for League Site";
	CreateRecruitmentSites( $Month );
	echo ".";
	CreateSiteCardSwipes( $Month );
	echo ".";
	InsertIntoReport( $Month);
	echo ".";
	CreateAvgSpendData( $Month );
	echo ".";
	HomeSiteMembersData( $Month );
	echo ".";
	LeagueSiteTableData( $Month );
	echo "Done\n";
}

connectToDB();

$timedate = date("Y-m-d")." ".date("H:i:s");
echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$LastMonth = GetLastMonth();

CreateLegueSiteMonth($LastMonth);

echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>