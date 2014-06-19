<?php

require "GeneralReportFunctions.php";
require "../include/DB.inc";

function CreateRecruitmentSites( $Month )
{
	$sql = "drop table RecruitmentSites";
	mysql_query( $sql );

	$sql = "create Table RecruitmentSites
		Select count(*) as NewCardSwipes, FirstSwipeLoc as SiteCode, sum(if( TransValue > 20, 1, 0 )) as Over20 
		from texaco.Cards Join texaco.Transactions$Month as t on( Cards.CardNo = t.CardNo and FirstSwipeDate = TransTime ) 
		where Date_Format( FirstSwipeDate , '%Y%m') = $Month group by FirstSwipeLoc";
	DBQueryExitOnFailure( $sql );
}

# calculate the member site spends

function CreateSiteCardSwipes( $Month )
{
 	$sql = "drop table SiteCardSwipes";
	mysql_query( $sql );
	$sql = "create table SiteCardSwipes 
			select CardNo, SiteCode, sum(TransValue) as SpendValue, count(* ) as Swipes 
			from texaco.Transactions$Month group by CardNo, SiteCode";
		DBQueryExitOnFailure( $sql );

	$sql = "Alter table SiteCardSwipes add index( CardNo )";
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
			from RecruitmentSites as r left join texaco.sitedata on( r.SiteCode = sitedata.SiteCode )
			group by
			r.SiteCode,
			COT,
			AreaCode,
			RegionCode";
		DBQueryExitOnFailure( $sql );
}

# fill in column 5 and 6 (and 4.5)

function CreateAvgSpendData( $Month )
{
	$sql = "replace into SiteAverageCardSpend 
		select $Month as YearMonth, SiteCode, count(*) as ActiveMembers, sum(SpendValue) as TotalSpend, avg(SpendValue) as AvgSpend 
		from  SiteCardSwipes
		group by SiteCode";
		DBQueryExitOnFailure( $sql );
}

function HomeSiteMembersData( $Month )
{																	
# fill in columns 3 and 4
	$sql = "replace into HomeSiteMembers
	select $Month as Month, HomeSite, count(*) as TotalMembers, sum(OKMail = 'Y') as Mailable 
	from texaco.Members join texaco.Accounts using(AccountNo) 
	where PrimaryMember = 'Y' and HomeSite is not null and Date_format( Accounts.CreationDate, '%Y%m' ) <= $Month 
	group by HomeSite";
		DBQueryExitOnFailure( $sql );
}

function LeagueSiteTableData( $Month )
{
/*	$sql = "replace into LeagueSiteTables
			select 
			$Month,
			L.SiteCode,
			COT,
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
			left join SiteAverageCardSpend as S on( L.SiteCode = S.SiteCode and L.CreationMonth = $Month ) ";
		DBQueryExitOnFailure( $sql );
 */

		// Pul in all the known sites

		$sql = "replace into LeagueSiteTables( CreationMonth, SiteCode, SiteType, AreaCode, RegionCode )
			select $Month, SiteCode, COT, AreaCode, RegionCode from texaco.sitedata";

		DBQueryExitOnFailure( $sql );

		// Merge in the Recruitment data.

//		$sql = "insert into LeagueSiteTables( CreationMonth, SiteCode, TotalRecruited, Over20 )	select $Month, SiteCode, NewCardSwipes, Over20 as t from RecruitmentSites ON Duplicate key update TotalRecruited = values( TotalRecruited) , Over20 = Values(Over20)";

		$sql = "select $Month, SiteCode, NewCardSwipes, Over20 from RecruitmentSites";
					
		$Results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_assoc( $Results ) )
		{
			$sql = "insert into  LeagueSiteTables( CreationMonth, SiteCode, TotalRecruited, Over20 ) values ( $Month, $row[SiteCode], $row[NewCardSwipes], $row[Over20] ) on duplicate key update TotalRecruited = $row[NewCardSwipes], Over20 = $row[Over20]";   
			DBQueryExitOnFailure( $sql );
		}
		// Merge in the Homesite data

 //		$sql = "insert into LeagueSiteTables( CreationMonth, SiteCode, TotalMembers, Mailable )	select $Month, SiteCode, NewCardSwipes, Over20 from HomeSiteMembers where Month = $Month ON Duplicate key update  TotalMembers = values( TotalMembers) , Mailable = Values(Mailable)";
 		$sql = "select $Month, HomeSite, TotalMembers, Mailable from HomeSiteMembers";
					
		$Results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_assoc( $Results ) )
		{
			$sql = "insert into  LeagueSiteTables( CreationMonth, SiteCode, TotalMembers, Mailable ) values ( $Month, $row[HomeSite], $row[TotalMembers], $row[Mailable] ) on duplicate key update TotalMembers = $row[TotalMembers], Mailable = $row[Mailable]";   
			DBQueryExitOnFailure( $sql );
		}


		// Merge in the swipe data

//		$sql = "insert into LeagueSiteTables( CreationMonth, SiteCode, TotalSpend , AvgSpend )	select $Month, SiteCode, TotalSpend , AvgSpend from SiteAverageCardSpend ON Duplicate key update TotalSpend = values( TotalSpend ) , AvgSpend = Values(AvgSpend)";
 		$sql = "select YearMonth, SiteCode, TotalSpend, AvgSpend from SiteAverageCardSpend where YearMonth = $Month";

		$Results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_assoc( $Results ) )
		{
			$sql = "insert into  LeagueSiteTables( CreationMonth, SiteCode, TotalSpend, AvgSpend ) values ( $Month, $row[SiteCode], $row[TotalSpend], $row[AvgSpend] ) on duplicate key update TotalSpend = $row[TotalSpend], AvgSpend = $row[AvgSpend]";   
			DBQueryExitOnFailure( $sql );
		}
 
}

function CreateLegueSiteMonth( $Month )
{
	echo date("H:i:s");
	echo " Processing $Month for League Site";
	CreateRecruitmentSites( $Month );
	echo ".";
	CreateSiteCardSwipes( $Month );
	echo ".";
	InsertIntoReport( $Month);
	echo ".";
	CreateAvgSpendData( $Month );
	echo ".";
//  No longer done here
//	HomeSiteMembersData( $Month );
//	echo ".";
	LeagueSiteTableData( $Month );
	echo "Done\n";
}

	connectToDB( ReportServer, ReportDB );

	$Month = GetThisMonth();

	CreateReportLog( "Started League Site" );
	$timedate = date("Y-m-d H:i:s");
	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 30/06/2008
	//echo "CreateRecruitmentLeages.php - started $timedate\r\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	
	// This is done here because it doesn't back project the correct calculation
	HomeSiteMembersData( $Month );

	for( $i = 0; $i < 3; $i++ )
	{
		CreateReportLog( "League Site for $Month" );

		CreateLegueSiteMonth($Month);
		$Month = DecrementMonth( $Month );
	}

	CreateReportLog( "Finished League Site" );
	$timedate = date("Y-m-d H:i:s");
	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//	echo "CreateRecruitmentLeages.php - completed $timedate\r\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";


?>