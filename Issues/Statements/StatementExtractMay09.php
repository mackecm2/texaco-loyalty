<?php
// To use this file you need to change the data in the next two lines and
#$PreviousStatementIdentifier = "Jan06";
$PreviousStatemntStopTime = "2009-03-05 11:03:20";

select M.AccountNo, M.MemberNo,PrimaryCard,'WEOU' as CardType,
case 
when (Accounts.SegmentCode like 'A%' or Accounts.SegmentCode like 'N%'  or Accounts.SegmentCode like 'L%'  or Accounts.SegmentCode like 'D%'    ) and M.Email like '%@%'   then 1
when (Accounts.SegmentCode like 'A%' or Accounts.SegmentCode like 'N%'  or Accounts.SegmentCode like 'L%')  and M.Email not like '%@%' and M.GoneAway = 'N'  then 2 
when (Accounts.SegmentCode like 'A%' or Accounts.SegmentCode like 'N%'  or Accounts.SegmentCode like 'L%')  and M.Email is null and M.GoneAway = 'N'  then 2
end as ListCode, 
Balance,
Accounts.SegmentCode,
StatementPref
from texaco.Accounts join texaco.Members as M using(AccountNo) join texaco.Cards on (Cards.CardNo = PrimaryCard)
where PrimaryMember = 'Y'  
and Cards.LostDate is null
and RedemptionStopDate is null  
and AwardStopDate is null  
and M.Deceased = 'N'  
and M.NonUKAddress = 'N'
and substring( Accounts.SegmentCode, 1 , 1) in ('A', 'N', 'L','D' ) 
and Balance > 0
and (AccountType <> 'D' OR AccountType is NULL)
";

//	Add an index.

$sql = "ALTER TABLE $ListcodeTable ADD INDEX ( `AccountNo` ) ";
$result = DBQueryExitOnFailure( $sql );


//	We need to remove the blank ListCodes as it is possible due to the where clause including all A,N,L,D,  
//	SegmentCodes that in the Case statement for ListCode you could have a Dormant individual with a bad or null Email address.

$sql = "delete from $ListcodeTable where ListCode = '' OR ListCode is NULL";
$result = DBQueryExitOnFailure( $sql );


$sql = "update $ListcodeTable join texaco.Cards using (MemberNo) set 
PrimaryCard = Cards.CardNo,$ListcodeTable.CardType = 'Star' where Cards.CardType = 'StarRewards'";

$result2 = DBQueryExitOnFailure( $sql );

//	Now update the listcode tables ListCode to distinguish between old WEOU and New Star Rewards
$sql = "update $ListcodeTable set 
ListCode = '3' where CardType = 'Star' and ListCode = '1'";
$result2 = DBQueryExitOnFailure( $sql );

$sql = "update $ListcodeTable set 
ListCode = '4' where CardType = 'Star' and ListCode = '2'";
$result2 = DBQueryExitOnFailure( $sql );


	$sql = "select S.AccountNo from $ListcodeTable join Apr09StatementListcodes as S using (AccountNo) ";
	#echo "$sql\n";	
	$slaveRes = mysql_query( $sql) ;
	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		
	$sql = "delete from May09StatementListcodes where AccountNo = '$row[AccountNo]'";
	
	#echo "$sql\r\n";
	
	$result2 = DBQueryExitOnFailure( $sql );	
			
		
	}




$result3 = DBQueryExitOnFailure( $sql );

select S.AccountNo, S.MemberNo, S.PrimaryCard, S.CardType,
case 
when S.Listcode = '1' then 5
when S.Listcode = '2' then 6
when S.Listcode = '3' then 5
when S.Listcode = '4' then 6
end as ListCode, 
A.Balance,
A.SegmentCode,
S.StatementPref
from Apr09StatementListcodes as S join texaco.Accounts as A using(AccountNo)  where 1";

$result4 = DBQueryExitOnFailure( $sql );


#die("Stopped\r\n");

#CreateOutputFile( $StatementIdentifier, FileName, Criteria );
	$sql = "update May09StatementBalance set BroughtForward = (Balance - StandardAwarded - BonusAwarded  + Redemptions - AdjustMents) where Balance !=  BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents";
	$result2 = DBQueryExitOnFailure( $sql );

	$sql = "Select
	Listcode, L.AccountNo, PrimaryCard, CardType,
	case 
	when Surname = '' OR Surname is null or Title = '' or Title is null  then 'Dear Star Rewards Member'
	when Surname is not null and Title is not NULL then CONCAT_WS( ' ', 'Dear', Title, Surname )
	end as Salutation,
	Title,
	Forename, Initials,Surname,
	Address1, Address2, Address3, Address4, Address5, Postcode,
	Email, 
	L.SegmentCode,
	M.StatementPref,
	$stuff	
	S.TotalSpend, S.TotalSwipes, 
	cast( Datediff( S.LastSwipeDate, S.FirstSwipeDate )/30 AS signed) as Relationship,
	S.FirstSwipeDate,
	M.CreationDate,
	M.CreatedBy = 'WEB' as WebRegistration,
	B.BroughtForward,
	B.StandardAwarded,
	B.BonusAwarded + B.Adjustments,
	B.Redemptions,
	B.Balance	
	from 
		".$StatementIdentifier."StatementListcodes as L 
		join  ".$StatementIdentifier."StatementBalance as B using( AccountNo ) 
		join texaco.Members as M using( AccountNo )
		left join ".$StatementIdentifier."SpendTotals as S using (AccountNo )
		left join ".$StatementIdentifier."AccountTotals using( AccountNo ) 
		where PrimaryMember = 'Y' and $Criteria";
