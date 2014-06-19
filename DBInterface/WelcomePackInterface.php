<?php

	function UnwelcomedGroups()
	{
		//return "if( Members.CreatedBy = 'ScratchCard', 'ScratchCard', 'Normal' )"; 
		return "'Ready to Create'";
	}
				

	function UpdateWelcomeLimit()
	{
		$sql = "select min(MemberNo) from Members where CreationDate > (now() - interval 3 month)";
		$v = DBSingleStatQuery( $sql );

		$sql = "replace into VariableTable values( 'WelcomeLimit', '$v' )";
		DBQueryExitOnFailure( $sql );
	}

	function GetLimitMemberNo()
	{
		$sql = "select VariableValue from VariableTable where VariableName = 'WelcomeLimit'";
		return DBSingleStatQuery( $sql );
	}


	function UnwelcomedCount()
	{
		$l = GetLimitMemberNo();

		// 11 seconds
		$GroupBy = "";
		$UG = UnwelcomedGroups();										
		if( $UG != "" ) 
		{
			$UG = ", $UG as BatchGroup";
			$GroupBy =  "Group by BatchGroup";
		}
		$sql = "select count(*) as NoRecords $UG from Members left join CampaignHistory on( Members.MemberNo = CampaignHistory.MemberNo and CampaignType = 'WELCOME' ) where Members.MemberNo > $l and Members.CreationDate > (now() - interval 3 month) and CampaignHistory.CreationDate is null and PrimaryMember = 'Y' and PrimaryCard is not null and PrimaryCard not like '01%' and PrimaryCard not like '7076550201%' and MemberType is null $GroupBy ";

		// alternative query 19 Seconds
		// $sql = "select count(*) from Members where MemberNo > 1756531 and CreationDate > '2004-10-22' and MemberNo not in (select MemberNo from CampaignHistory where CampaignType = 'WELCOME' ) and PrimaryMember = 'Y' and PrimaryCard is not null ";
				
		$results = DBQueryExitOnFailure( $sql );		
		return $results;
	}

	function GetWelcomePackBatches()
	{
		$sql = "SELECT count(*) as NoRecords, sum( ListCode in (1, 5,7, 6, 8) ) as NMC, sum(ListCode=2) as Standard, sum(ListCode=3) as NoBonus, CreationDate as BatchTime from CampaignHistory where CampaignType = 'WELCOME' group by CreationDate order by CreationDate desc limit 17";
		return DBQueryExitOnFailure( $sql );
	}
	
	function GetNMCBatches()
	{
		$sql = "SELECT count(*) as NoRecords, sum( ListCode in ( 5, 7 )) as EmailPhase2, sum( ListCode in (6, 8 )) as MailPhase2, sum( ListCode = 7 ) as EmailPhase3, sum( ListCode = 8 ) as MailPhase3, CampaignHistory.CreationDate as BatchTime from CampaignHistory join Members using( MemberNo) where CampaignType = 'WELCOME'  and ListCode in (1,5,6,7,8) and GoneAway = 'N' and Deceased = 'N' group by CampaignHistory.CreationDate order by CampaignHistory.CreationDate desc limit 17";
		return DBQueryExitOnFailure( $sql );
	}

	function CreateWelcomePackBatch( $BatchTime, $WelcomeCode, $BatchGroup )
	{
		global $uname;
		$l = GetLimitMemberNo();
		$UG = "";
		//$UG = UnwelcomedGroups();
 		//if( $UG != "" )  
		//{
		//	$UG = "and $UG = '$BatchGroup'";
		//}

		$sql = "insert into CampaignHistory ( MemberNo, AccountNo, CampaignType, CampaignCode, CreationDate, CreatedBy ) select Members.MemberNo, Members.AccountNo, 'WELCOME', '$WelcomeCode', '$BatchTime', '$uname'  from Members left join CampaignHistory on( Members.MemberNo = CampaignHistory.MemberNo and CampaignType = 'WELCOME' ) where Members.MemberNo > $l and Members.CreationDate > (now() - interval 3 month) and CampaignHistory.CreationDate is null  and PrimaryMember = 'Y' and PrimaryCard is not null and PrimaryCard not like '01%' and MemberType is null $UG";
		//echo $sql;
		return DBQueryExitOnFailure( $sql );
	}

	function CreateNMCHistory( $BatchTime )
	{

	}

	function CreateWelcomePackLists( $BatchTime )
	{
		// Get some totals maybee.
//		$sql = "Select count(*) as TotalGroup, sum( (OKMail = 'Y' and char_length(Postcode) > 3) or (OKEmail = 'Y' and char_length(Email) > 9)) as OKContact, sum(  ) as ToOld from CampaignHistory join Members using(MemberNo) join Cards on( MemberNo.PrimaryCard = Cards.CardNo) where CampaignHistory.CreationDate = '$BatchTime' and (Cards.FirstSwipeDate is null or DateDiff( CampaignHistory.CreationDate, Cards.FirstSwipeDate  ) <= 90)";
		
		// Split those that are too old (in the senec that the time between first swipe and reg is greater than 3 months) or are not ok to contact between lists 2 and 3 randomly.

		$sql = "select CampaignHistory.MemberNo from CampaignHistory join Members using(MemberNo) join Cards on( PrimaryCard = Cards.CardNo) where CampaignHistory.CreationDate = '$BatchTime' and ((Cards.FirstSwipeDate is not null and DateDiff( '$BatchTime', Cards.FirstSwipeDate  ) > 90) or ( OKEmail = 'N' or char_length( Email ) < 9) and ( OKMail = 'N' or Address1 is null))";

		$results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_assoc($results ) )
		{
			$listcode = rand( 2, 3 );
			$sql = "Update CampaignHistory set ListCode = $listcode where MemberNo = $row[MemberNo] and CreationDate = '$BatchTime' limit 1";
			#echo $sql;
			DBQueryExitOnFailure( $sql );
		}
		//$sql = "Update  CampaignHistory join Members using (MemberNo) join Cards on (PrimaryCard = Cards.CardNo) set ListCode = if( Rand() > 0.5, 2, 3 ) where CampaignHistory.CreationDate = '$BatchTime' and ((Cards.FirstSwipeDate is not null and DateDiff( '$BatchTime', Cards.FirstSwipeDate  ) > 90) or ( OKEmail = 'N' or char_length( Email ) < 9) and ( OKMail = 'N' or Address1 is null))";

		//DBQueryExitOnFailure( $sql );

		// Split the rest accross the three groups

		// $sql = "Update CampaignHistory set ListCode = Interval( Rand(), 0.0, 0.8, 0.9, 1.1 )  where CampaignHistory.CreationDate  = '$BatchTime' and ListCode is null";

		$sql = "Select CampaignHistory.MemberNo from CampaignHistory where CreationDate  = '$BatchTime' and ListCode is null";

 		$results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_assoc($results ) )
		{
			$listcode = rand( 1, 10 );
			if( $listcode > 3 )
			{
				$listcode = 1;
			}
			#echo $sql;
			$sql = "Update CampaignHistory set ListCode = $listcode where MemberNo = $row[MemberNo] and CreationDate = '$BatchTime' limit 1";
			DBQueryExitOnFailure( $sql );
		}


		//DBQueryExitOnFailure( $sql );
	}



	function CopyMarketBatchToTracking( $BatchTime, $TrackingCode )
	{
		$sql = "INSERT into Tracking ( MemberNo, AccountNo, CreationDate, CreatedBy, TrackingCode ) select MemberNo, AccountNo, CreationDate, CreatedBy, $TrackingCode from CampaignHistory where CreationDate = '$BatchTime'";
		return DBQueryExitOnFailure( $sql );		
	}

	function NMCWhereClause( $Type )
	{
		$where = "";
		switch( $Type )
		{
			case "All":
			$where = "";
			break;
			case "NMC":
			$where = " and ListCode in ( 1, 5, 6, 7, 8 )";
			break;
			case "Standard":
			$where = " and ListCode = 2 ";
			break;
			case "NoBonus":
			$where = " and ListCode = 3 ";
			break;
			case "Email":
			$where = " and ListCode = 1 and OKEmail = 'Y' and char_length(Email) > 9 ";
			break;
			case "Mail":
			$where = " and ListCode = 1 and (OKEmail is null or OKEmail = 'N' or Email is null or char_length(Email) <= 9) and OKMail = 'Y' and Address1 is not null";
			break;
			case "EmailPhase2":
			$where = " and ListCode in( 5, 7) and OKEmail = 'Y' and char_length(Email) > 9 ";
			break;
			case "EmailPhase3":
			$where = " and ListCode = 7 and OKEmail = 'Y' and char_length(Email) > 9 ";
			break;
			case "MailPhase2":
			$where = " and ListCode in( 6, 8) and OKMail = 'Y' and Address1 is not null";
			break;
			case "MailPhase3":
			$where = " and ListCode = 8 and OKMail = 'Y' and Address1 is not null";
			break;			
		}
		return $where;
	}

	function GetWelcomePackBatchData( $BatchTime, $Type )
	{	
		$where = NMCWhereClause( $Type );
		$sql = "SELECT PrimaryCard, Title, Forename As FirstName, Surname, Organisation, Address1, Address2, Address3, Address4, Address5, PostCode from Members  Join CampaignHistory using( MemberNo ) where CampaignHistory.CreationDate = '$BatchTime' $where and GoneAway = 'N' and Deceased = 'N'";

//		echo $sql;

		return DBQueryExitOnFailure( $sql );		
	}

	function GetWelcomePackEmailBatchData( $BatchTime, $Type )
	{	
		$where = NMCWhereClause( $Type );
		$sql = "SELECT Email, PrimaryCard, Title, Forename, Surname  from Members  Join CampaignHistory using( MemberNo ) where CampaignHistory.CreationDate = '$BatchTime' $where and GoneAway = 'N' and Deceased = 'N'";

		return DBQueryExitOnFailure( $sql );		
	}


	function WriteWelcomePackBatchData( $FileName, $BatchTime, $Type ) 
	{
		$where = NMCWhereClause( $Type );
		$sql = "SELECT PrimaryCard, Title, Forename As FirstName, Surname, Email into outfile '$FileName' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\n' from Members  Join CampaignHistory using( MemberNo ) where CampaignHistory.CreationDate = '$BatchTime' $where and GoneAway = 'N' and Deceased = 'N'";

		return DBQueryExitOnFailure( $sql );		
	}

	function CopyNMCContactBack( $BatchTime, $Type, $NewTime, $OldListCode, $NewListCode  )
	{
		global $uname;

		$where = NMCWhereClause( $Type );

		$sql = "Update CampaignHistory Join Members using(MemberNo) set ListCode = $NewListCode, MiscData = concat_ws( ' ' , MiscData, $NewListCode, '$NewTime') where CampaignHistory.CreationDate = '$BatchTime' $where and GoneAway = 'N' and Deceased = 'N' and ListCode = $OldListCode";

		DBQueryExitOnFailure( $sql );
	}

	function CopyBatchToPersonalCampaign( $BatchTime, $StartDate, $EndDate, $PromoCode, $CampaignType )
	{
		global $uname;
		$sql = "Select MaximumHits from BonusPoints where PromotionCode = '$PromoCode'";
		$results = DBQueryExitOnFailure( $sql );

		if( $row = mysql_fetch_row( $results ) )
		{
			$AllowedHits = $row[0];

//			$sql = "INSERT into PersonalCampaigns ( MemberNo, PromotionCode, StartDate, EndDate, PeriodSpend, PromoHitsLeft, CreationDate, CreatedBy ) select MemberNo, '$PromoCode', '$StartDate', '$EndDate', 0, $AllowedHits, now(), '$uname'  from CampaignHistory where CreationDate = '$BatchTime' and CampaignType = 'WELCOME'";

			// Change for NMC processing

			$sql = "INSERT into PersonalCampaigns ( MemberNo, PromotionCode, StartDate, EndDate, PeriodSpend, PromoHitsLeft, CreationDate, CreatedBy ) select MemberNo, '$PromoCode', '$StartDate', '$EndDate', 0, $AllowedHits, now(), '$uname'  from CampaignHistory where CreationDate = '$BatchTime' and CampaignType = '$CampaignType' and (ListCode is null or ListCode in ( 1, 2 ))";
//			echo $sql;
			return DBQueryExitOnFailure( $sql );		
		}
		else
		{
			echo "Promotion Code not found $PromoCode";
		}
	}
	
	function GetSiteClosureBatches( $limit )
	{
  		$sql = "SELECT count(*) as NoRecords, CreationDate  as BatchTime, CampaignCode as SiteNo from CampaignHistory where CampaignType = 'SITECLOSE' group by CreationDate order by CreationDate desc limit $limit";
		return DBQueryExitOnFailure( $sql );		
	}

	function CreateSiteClosureBatch( $timestamp, $SiteNo )
	{
		global $uname;
		$sql = "insert into CampaignHistory Select MemberNo, Members.AccountNo, 'SITECLOSE', '$SiteNo', null, null, '$timestamp', '$uname' from Members join Accounts using(AccountNo) where HomeSite='$SiteNo' and Deceased = 'N' and GoneAway = 'N' and OKMail = 'Y'";
		DBQueryExitOnFailure( $sql );
	}

	function GetSiteCloseBatchData( $BatchTime, $Type )
	{	
		$sql = "SELECT PrimaryCard, Title, Forename As FirstName, Surname, Organisation, Address1, Address2, Address3, Address4, Address5, PostCode from Members  Join CampaignHistory using( MemberNo ) where CampaignHistory.CreationDate = '$BatchTime'";

		return DBQueryExitOnFailure( $sql );		
	}

?>
