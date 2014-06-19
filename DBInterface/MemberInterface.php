<?php

//function CreateMember( $CardNo, &$AccountNo, &$MemberNo  )
function CreateMember( $CardNo, &$AccountNo, &$MemberNo, &$SiteCode, &$AccountType, $Organisation  )
{
	global $uname;
	$StoppedPoints = 0;
	if( $AccountNo == "" )
	{
		$sql = "INSERT into Accounts (CreatedBy, CreationDate ) values ('$uname', now())";
		DBQueryExitOnFailure( $sql );
		$AccountNo = mysql_insert_id();
		
		$sql = "INSERT INTO AccountStatus (AccountNo, Status, StatusSetDate, FraudStatus, RevisedDate)
				VALUES ('$AccountNo', 'Open', NULL , '0', NOW( ))";
		DBQueryExitOnFailure( $sql );
		
		$PrimaryMember = 'Y';
		if ( $AccountType == "G" )
		{
			$cardtypelabel = "GR".$AccountNo;
			$sql = "INSERT into CardRanges (AccountNo, CardType,  Comments,  LastUpdate) values ($AccountNo, '$cardtypelabel', '$Organisation', now())";
			DBQueryExitOnFailure( $sql );
		}
		
	}
	else
	{
		$PrimaryMember = 'N';
	}

	if( $CardNo != "" )
	{
		$sql = "INSERT into Members (AccountNo, PrimaryMember, PrimaryCard, CanRedeem, CreatedBy, CreationDate ) values( $AccountNo, '$PrimaryMember', '$CardNo', 'Y', '$uname', now())";
	}
	else
	{
		if( $AccountType == 'D' )
		{	
			$sql = "select StaffRegistrations, SiteCode from SiteRegistrations where SiteCode= $SiteCode";
			$results = DBQueryExitOnFailure( $sql );
			$numrows = mysql_num_rows($results);
			if( $numrows >0 )
			{
				$row = mysql_fetch_assoc( $results );
				$Details['StaffID'] = ($row['SiteCode']*1000) + ($row['StaffRegistrations'] + 1);
				$staffid = $Details['StaffID'];
				$sql = "update SiteRegistrations set StaffRegistrations = (StaffRegistrations + 1) where SiteCode= $SiteCode";
				$results = DBQueryExitOnFailure( $sql );
				$CardNo = "01".$staffid.date("Ymd");
				$sql = "INSERT into Members (AccountNo, PrimaryMember, PrimaryCard, CanRedeem, CreatedBy, CreationDate, StaffID) values( $AccountNo, '$PrimaryMember', '$CardNo', 'Y', '$uname', now(), $staffid)";
			}
			else 
			{
			echo "Site Code not found";
			return false;	
			}
			
		}
		else
		{
			$sql = "INSERT into Members (AccountNo, PrimaryMember, CanRedeem, CreatedBy, CreationDate) values( $AccountNo, '$PrimaryMember', 'Y', '$uname', now())";
		}
	}
	DBQueryExitOnFailure( $sql );
	$MemberNo = mysql_insert_id();

	if  ( $AccountType == null )
	{
		$sql = "SELECT AccountType FROM Accounts JOIN Members USING( AccountNo ) WHERE `MemberNo` = $MemberNo";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		if( $row )
		{
			$AccountType = $row[0];
		}
	}

	if( $CardNo != "" )
	{
		$sql = "Select MemberNo, StoppedPoints from Cards where CardNo = '$CardNo'";
	 	$results = DBQueryExitOnFailure( $sql );

		$row = mysql_fetch_row( $results );
		if( $row )
		{
			$oldMember = $row[0];
			$StoppedPoints = $row[1];
			if( $oldMember != 0 )
			{
				InsertTrackingMember( TrackingNewAccount, $oldMember, "Moved card $CardNo to new  $AccountNo", -$StoppedPoints);
			}
			$sql = "Update Cards set MemberNo=$MemberNo where CardNo='$CardNo'";
			DBQueryExitOnFailure( $sql );
			
			if  ( $AccountType == 'G' && $StoppedPoints > 0 )
			{
				InsertTrackingMember( TrackingAdditionalMember, $MemberNo, "Points From $CardNo", $StoppedPoints);
				ReleaseStoppedPoints( $AccountNo,  $MemberNo );			
			}
			else if  ( $StoppedPoints > 0 )
			{
				InsertTrackingMember( TrackingNewAccount, $MemberNo, "Points From $CardNo", $StoppedPoints);
			}
  		}
		else
		{
			// MRM 16 JUL 09 Uses new table CardRanges to assign a Card Type
			$CardType = "WEOU"; // The default
			$sql = "SELECT CardType FROM CardRanges WHERE CardStart <= '$CardNo' AND CardFinish >= '$CardNo'";
			$results = DBQueryExitOnFailure( $sql );
			$numrows = mysql_num_rows($results);
			if( $numrows >0 )
			{
				$row = mysql_fetch_assoc( $results );
				$CardType = $row['CardType'];
			}
			
			$sql = "Insert into Cards ( CardNo, CardType, MemberNo, CreatedBy, CreationDate, IssueDate ) values	( '$CardNo', '$CardType', $MemberNo, '$uname', now(), now() )";
			DBQueryExitOnFailure( $sql );
			InsertTrackingRecord( TrackingNewCardAdded, $MemberNo, $AccountNo, "$CardNo Created", 0 );
		}

	}
	else
	{
		if( $PrimaryMember == 'Y' )
		{
			InsertTrackingRecord( TrackingNewAccount, $MemberNo, $AccountNo, "Account Created", 0 );
		}
		else
		{
			InsertTrackingRecord( TrackingAdditionalMember, $MemberNo, $AccountNo, "Member Added $MemberNo", 0 );
		}
	}
	return $MemberNo;
}

function CopyMember( $OldMemberNo )
{
	global $uname;

	$sql = "select AccountNo, Address1, Address2, Address3, Address4, Address5, PostCode, HomePhone, OKHomePhone, OKMail, TOKMail from Members where MemberNo=$OldMemberNo";

	$results = DBQueryExitOnFailure( $sql );
	if( mysql_num_rows( $results ) > 0 )
	{
		$row = mysql_fetch_assoc( $results );

		$sql = "Insert into Members ( PrimaryMember,  AccountNo, Address1, Address2, Address3, Address4, Address5, PostCode, HomePhone, OKHomePhone, OKMail, TOKMail, CreationDate, CreatedBy ) values ( 'N', $row[AccountNo], 'mysql_real_escape_string($row[Address1])', 'mysql_real_escape_string($row[Address2])', '$mysql_real_escape_string($row[Address3])', 'mysql_real_escape_string($row[Address4])', 'mysql_real_escape_string($row[Address5])', '$row[PostCode]', '$row[HomePhone]', '$row[OKHomePhone]', '$row[OKMail]', '$row[TOKMail]', now(), '$uname' )";

		$results = DBQueryExitOnFailure( $sql );

		return mysql_insert_id();
	}
	else
	{
		false;
	}
}

function GetAccountNo( $MemberNo )
{
	$sql = "Select AccountNo from Members where MemberNo = $MemberNo ";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	return $row[0];
}

function CheckNumberCoMembers( $MemberNo )
{
	$AccountNo = GetAccountNo( $MemberNo );
	$sql = "Select count(*) from Members where AccountNo = $AccountNo";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	return $row[0];
}

function MergeAccounts( $PAccountNo, $SAccountNo )
{
	global $uname;

//	echo "<p><strong><font color=#800080>Account $SAccountNo merged into $PAccountNo</font></strong></p>";
	echo "<strong><font color=#800080>Account $SAccountNo merged into $PAccountNo</font></strong>";
	$sql = "Select Balance, AccountType from Accounts where AccountNo = $SAccountNo";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	$Balance = $row[0];
	$AccountType = $row[1];

	$sql = "Select StoppedPoints from Cards Join Members using(MemberNo) where AccountNo = $SAccountNo";
	$StoppedPoints = DBSingleStatQuery( $sql );

	$sql = "Insert into MergeHistory ( SourceAccount, DestinationAccount, MemberNo, TransferBalance ) select $SAccountNo, $PAccountNo, MemberNo, if( PrimaryMember = 'Y', $Balance, 0 ) from Members where AccountNo = $SAccountNo";

	$results = DBQueryExitOnFailure( $sql );

	$sql = "Select MemberNo from Members where AccountNo = $SAccountNo ORDER BY PrimaryMember DESC limit 1";
	$MemberNo = DBSingleStatQuery( $sql );

	$sql = "Update Members set AccountNo = $PAccountNo, PrimaryMember = 'N', CanRedeem='N', RevisedDate = now(), RevisedBy = '$uname' where AccountNo = $SAccountNo";
	$results = DBQueryExitOnFailure( $sql );

	AdjustBalance( TrackingMergeAccount, $MemberNo, $SAccountNo, "Merged to $PAccountNo", -$Balance );
	AdjustBalance( TrackingMergeAccount, $MemberNo, $PAccountNo, "Merged from $SAccountNo", $Balance );

	if( $StoppedPoints > 0 )
	{
		InsertTrackingRecord( TrackingMergeAccount, $MemberNo, $SAccountNo, "Stopped Points moved", -$StoppedPoints );
		InsertTrackingRecord( TrackingMergeAccount, $MemberNo, $PAccountNo, "Stopped Points moved", $StoppedPoints );
	}
	CheckAccountHasPrimary( $PAccountNo );
	
	$sql = "Select AccountType from Accounts where AccountNo = $PAccountNo limit 1";
	$PAccountType = DBSingleStatQuery( $sql );
	
	if  ( $PAccountType == 'G' )
	{
		$sql = "Update Members set MemberBalance = $Balance where MemberNo = $MemberNo";
		DBQueryExitOnFailure( $sql );
	}

}

function GetMemberSourceAccount( $MemberNo, &$Balance )
{
	$sql = "SELECT SourceAccount, TransferBalance from Members Join MergeHistory on( MergeHistory.DestinationAccount = Members.AccountNo and MergeHistory.MemberNo = Members.MemberNo ) where PrimaryMember = 'N' and Members.MemberNo = $MemberNo";

	$results = DBQueryExitOnFailure( $sql );

	if( mysql_num_rows( $results ) == 0 )
	{
		$Balance = 0;
		return false;
	}
	else
	{
		$row = mysql_fetch_row( $results );
		$Balance = $row[1];
		return $row[0];
	}

}

function UnmergeMember( $MemberNo, $SAccountNo )
{
	global $uname;

	$sql = "select sum(StoppedPoints) as Points from Cards where MemberNo = $MemberNo";
	$StoppedPoints = DBSingleStatQuery( $sql );

	$DAccountNo = GetMemberSourceAccount( $MemberNo, $Balance );
	if( !$DAccountNo )
	{
		$sql = "INSERT into Accounts (CreatedBy, CreationDate) values ('$uname', now())";
		DBQueryExitOnFailure( $sql );
		$DAccountNo = mysql_insert_id();
	}
	$sql = "update Members set AccountNo = $DAccountNo, RevisedBy = '$uname', RevisedDate = now() where MemberNo = $MemberNo";
	DBQueryExitOnFailure( $sql );

	AdjustBalance( TrackingUnmergeMember, $MemberNo, $SAccountNo, "Moved To $DAccountNo", -$Balance );
	AdjustBalance( TrackingUnmergeMember, $MemberNo, $DAccountNo, "Moved From $SAccountNo", $Balance );

	if( $StoppedPoints != 0 )
	{
		InsertTrackingRecord( TrackingUnmergeMember, $MemberNo, $SAccountNo, "Stopped Moved out Points", -$StoppedPoints );
		InsertTrackingRecord( TrackingUnmergeMember, $MemberNo, $DAccountNo, "Stopped Moved In Points", $StoppedPoints );
	}

	CheckAccountHasPrimary( $SAccountNo );
	CheckAccountHasPrimary( $DAccountNo );

	$sql = "Delete from MergeHistory where MemberNo=$MemberNo and DestinationAccount = $SAccountNo";
	DBQueryExitOnFailure( $sql );

	return $DAccountNo;
}


function CheckAccountHasPrimary( $AccountNo )
{
	$sql = "Select sum( if( PrimaryMember = 'Y', 1, 0 )) as NoPrimaryMembers, count(*) as NoMembers from Members where AccountNo = $AccountNo";

	$results = DBQueryExitOnFailure( $sql );

	$row = mysql_fetch_assoc( $results );

	if( $row["NoPrimaryMembers"] == 0 and $row["NoMembers"] > 0 )
	{
		$sql = "Update Members set PrimaryMember = 'Y' where AccountNo = $AccountNo limit 1";
		DBQueryExitOnFailure( $sql );
	}
	else if( $row["NoPrimaryMembers"] > 0 )
	{
		// Multiple Primary Members
		$limit =  $row["NoPrimaryMembers"] - 1;
		$sql = "Update Members set PrimaryMember = 'N' where AccountNo = $AccountNo and PrimaryMember = 'Y' limit $limit";
		DBQueryExitOnFailure( $sql );
	}
}

// Moving A Member also moves the stopped points which causes the points to move account.
// We need to track this movement

function MoveMember( $AccountNo, $MemberNo )
{
	global $uname;

	// Get Members stopped points

	$sql = "select sum(StoppedPoints) as Points from Cards where MemberNo = $MemberNo";

	$Points = DBSingleStatQuery( $sql );

	$sql = "select AccountNo from Members where MemberNo = $MemberNo";
 	$SAccountNo = DBSingleStatQuery( $sql );

	InsertTrackingMember( TrackingMoveMember, $MemberNo, "Merged to $AccountNo", -$Points);

	$sql = "Insert into MergeHistory ( SourceAccount, DestinationAccount, MemberNo, TransferBalance ) values( $SAccountNo, $AccountNo,  $MemberNo, $Points )";

	$results = DBQueryExitOnFailure( $sql );

	$sql = "update Members set AccountNo = $AccountNo, PrimaryMember='N', CanRedeem='N', RevisedDate = now(), RevisedBy = '$uname' where MemberNo = $MemberNo";

	InsertTrackingMember( TrackingMoveMember, $MemberNo, "Merged from $SAccountNo ", $Points );

  	CheckAccountHasPrimary( $AccountNo );
	CheckAccountHasPrimary( $SAccountNo );

	DBQueryExitOnFailure( $sql );
}

// There should be no cards associated with a member to be deleted.
// Probably should check
// But no points envolved

function DeleteMemberFromAccount( $MemberNo, $AccountNo )
{
	global $uname;
	$sql = "Insert into MergeHistory ( SourceAccount, MemberNo ) select AccountNo, $MemberNo from Members where MemberNo = $MemberNo";

	$results = DBQueryExitOnFailure( $sql );

	$sql = "update Members Set AccountNo = null, RevisedBy = '$uname', RevisedDate= now() where MemberNo = $MemberNo";

	InsertTrackingRecord( TrackingMemberDelete, $MemberNo, $AccountNo, "Deleted Member $MemberNo from $AccountNo", 0 );

	DBQueryExitOnFailure( $sql );
  	CheckAccountHasPrimary( $AccountNo );
}

function GetAccountTypeList()
{
	$sql = "Select * from AccountTypes WHERE Active = 'Y'";
	$results = DBQueryExitOnFailure( $sql );
	$AccountTypes = array();
	while( $row = mysql_fetch_assoc( $results ) )
	{
		$AccountTypes[$row["AccountType"]] = $row["Description"];
	}
	return $AccountTypes;
}

function GetMonthlySpendList()
{
	$sql = "Select * from MonthlySpends";
	$results = DBQueryExitOnFailure( $sql );
	$MonthlySpends = array();
	while( $row = mysql_fetch_assoc( $results ) )
	{
		$MonthlySpends[$row["SpendId"]] = $row["Description"];
	}
	return $MonthlySpends;
}


function GetMemberTitles()
{
	$Titles = array();
	$Titles["Mr"] = "Mr";
	$Titles["Master"] = "Master";
	$Titles["Ms"] = "Ms";
	$Titles["Mrs"] = "Mrs";
	$Titles["Miss"] = "Miss";
	$Titles["Dr"] = "Dr";
	$Titles["Other"] = "Other";
	$Titles[""] = "";
	return $Titles;
}


function GetCardFields( $blank )
{
	if( $blank )
	{
		$sql = ",'' as CardNo, Null as LastSwipeLoc, Null as LastSwipeDate, null as IssueDate, null as LostDate, 0 as StoppedPoints";
	}
	else
	//* Mantis 807 - Display Card Type MRM 16/03/09 
	{
		 $sql =  ", CardNo, CardType, LastSwipeLoc, DATE_FORMAT(LastSwipeDate, '%d %b %Y') as LastSwipeDate, DATE_FORMAT(IssueDate, '%d %b %Y') as IssueDate, LostDate, StoppedPoints";
	}
	return $sql;
}

function GetMemberFields( $blank, $copy )
{
	if( $blank )                // Mantis 2262 MRM 22 06 10 set OKMail and TOKMail to Y
	{
		$sql = ", '' as DOB, '' as Title, '' as Forename, '' as Surname,  0 as AddNdsVfy, 'Y' as CanRedeem, 'Y' as OKMail,'N' as OKEMail,'Y' as TOKMail, 'N' as Deceased, 'N' as GoneAway, 'Y' as OKSMS, 'N' as OKEMail, '' as Email, 0 as EmailNdsVfy, 'N' as OKWorkPhone, '' as WorkPhone, '' as HomePhone, 0 as HomeNdsVfy, 0 as WorkNdsVfy, 'N' as StatementPreference, null as Passwrd, 'Y' as PassNdsSet, null as Organisation, null as MemberType, 0 as MemberBalance";
		if( $copy )
		{
			$sql .= ",'' As MemberNo, Address1, Address2, Address3, Address4, Address5, PostCode, OKHomePhone";
		}
		else
		{
			 $sql .= ",'' as MemberNo, '' as Address1, '' as Address2, '' as Address3, '' as Address4, '' as Address5, '' as PostCode, '' as HomePhone, 'N' as OKHomePhone, '' as StaffID";

		}
	}
	else
	{
		$sql =  ", DOB, Title, Forename, Surname,  (Date_sub( now(), Interval 1 year ) > AddressVerified or isnull(AddressVerified)) as AddNdsVfy, CanRedeem, OKMail, TOKMail, Deceased, GoneAway, OKSMS, OKEMail, Email, (Date_sub( now(), Interval 1 year ) > EmailVerified or isnull(EmailVerified)) as EmailNdsVfy, OKWorkPhone, WorkPhone, (Date_sub( now(), Interval 1 year ) > WorkVerified or isnull(WorkVerified)) as WorkNdsVfy, (Date_sub( now(), Interval 1 year ) > HomeVerified or isnull(HomeVerified)) as HomeNdsVfy,  StatementPref as StatementPreference, Passwrd, if( Passwrd is null or Passwrd = '', 'Y', 'N') as PassNdsSet, Organisation, MemberType";

		$sql .= ",Members.MemberNo, Address1, Address2, Address3, Address4, Address5, PostCode, HomePhone, OKHomePhone, StaffID, MemberBalance";
	}
	return $sql;
}

function GetAccountFields( $blank )
{
	if( $blank )
	{
		
		$sql = ", '' as AccountNo, 0 as Balance, Null as RedemptionStopDate, null as AwardStopDate, 'U' as AccountType, Null as HomeSite, Null as HomeSiteDate, Null as VirginNo, null as MonthlySpend, 0 as FraudStatus, 'Open' as Status";
	}
	else
	{
		 $sql =  ", Accounts.AccountNo, Balance, DATE_FORMAT(RedemptionStopDate, '%d %b %Y') as RedemptionStopDate, DATE_FORMAT(AwardStopDate, '%d %b %Y') as AwardStopDate, AccountType,  Accounts.HomeSite, HomeSiteDate, VirginNo, MonthlySpend, SegmentCode, FraudStatus, Status";
	}
	return $sql;

}

function GetGroupAccountFields( $blank )
{
	if( $blank )
	{
		
		$sql = ", '".$blank."' as AccountNo, 0 as Balance, Null as RedemptionStopDate, null as AwardStopDate, 'U' as AccountType, Null as HomeSite, Null as HomeSiteDate, Null as VirginNo, null as MonthlySpend";
	}
	else
	{
		 $sql =  ", Accounts.AccountNo, Balance, DATE_FORMAT(RedemptionStopDate, '%d %b %Y') as RedemptionStopDate, DATE_FORMAT(AwardStopDate, '%d %b %Y') as AwardStopDate, AccountType,  Accounts.HomeSite, HomeSiteDate, VirginNo, MonthlySpend";
	}
	return $sql;

}

function GetBlankFields()
{
	$sql = "select '' as PrimaryCard, 'Y' as PrimaryMember ". GetAccountFields( true ) . GetMemberFields( true, false ) . GetCardFields( true );

	return DBQueryExitOnFailure( $sql );
}

function GetBlankGroupFields($AccountNo)
{
	$sql = "select '' as PrimaryCard, 'Y' as PrimaryMember ". GetGroupAccountFields( $AccountNo ) . GetMemberFields( true, false ) . GetCardFields( true );
//	echo $sql;
	return DBQueryExitOnFailure( $sql );
}

function GetFieldList()
{
	return "PrimaryCard, PrimaryMember ". GetAccountFields( false ) . GetMemberFields( false, false ) . GetCardFields( false );
}

function GetMemberCopy( $OldMemberNo)
{
	$sql = "select '' as PrimaryCard, 'N' as PrimaryMember " . GetAccountFields( false ) . GetMemberFields( true, true ) . GetCardFields( true ) . " from Members join Accounts using( AccountNo ) left join AccountStatus using( AccountNo ) where MemberNo=$OldMemberNo";
	return DBQueryExitOnFailure( $sql );
}

function GetMemberNoCopy( $OldMemberNo)
{
	$sql = "select '' as PrimaryCard, 'N' as PrimaryMember " . GetAccountFields( false ) . GetMemberFields( true, false ) . GetCardFields( true ) . " from Members join Accounts using( AccountNo ) left join AccountStatus using( AccountNo ) where MemberNo=$OldMemberNo";
	return DBQueryExitOnFailure( $sql );
}


function GetMemberDetails( $MemberNo )
{
	$fields = GetFieldList();
	$sql = "select $fields from Accounts Join AccountStatus using(AccountNo) Join Members using(AccountNo) left Join Cards using(MemberNo) where Members.MemberNo=$MemberNo";
	return DBQueryExitOnFailure( $sql );
}

function GetAccountDetails( $AccountNo )
{
	$fields = "'' as PrimaryCard, 'N' as PrimaryMember ". GetAccountFields( false ) . GetMemberFields( true, false ) . GetCardFields( true );
	$sql = "select $fields from Accounts left join AccountStatus using( AccountNo ) where AccountNo = $AccountNo";
	return DBQueryExitOnFailure( $sql );
}

function GetCardOnly( $CardNo )
{
	$fields = GetFieldList();
	$sql = "select $fields from Cards left JOIN Members using(MemberNo) Left Join Accounts using(AccountNo) left join AccountStatus using (AccountNo) where Cards.CardNo='$CardNo'";

	return DBQueryExitOnFailure( $sql );
}

function GetAllCardsByAccount( $AccountNo )
{
	$fields = GetFieldList();
	$sql = "select $fields from Accounts Join Members using(AccountNo) left join AccountStatus using (AccountNo) left Join Cards using(MemberNo) where Members.AccountNo=$AccountNo order by PrimaryMember, MemberNo";
	return DBQueryExitOnFailure( $sql );
}

function GetPrimaryMemberDetails( $AccountNo )
{
	$fields = "Title, Surname ,Address1, Address2, Address3, Address4, Address5, PostCode, now() as SystemDate, PrimaryCard, Balance, now() as BalDate";

	$sql = "Select $fields from Accounts Join Members using( AccountNo ) where Accounts.AccountNo = $AccountNo and PrimaryMember = 'Y'";

	$results = DBQueryExitOnFailure( $sql );

	if( mysql_num_rows( $results ) == 0 )
	{
		$sql = "Select $fields from Accounts Join Members using( AccountNo ) where Accounts.AccountNo = $AccountNo";
		$results = DBQueryExitOnFailure( $sql );
	}
	return $results;
}

?>