<?php

	
	function GetTransactionHistory( $AccountNo )
	{
		$sql = "SELECT STRAIGHT_JOIN Cards.CardNo as 'Card Number' , DATE_FORMAT( TransTime, '%Y-%m-%d %H:%i:%s') as 'Date', TransValue as 'Spend Value', PointsAwarded as 'Points Awarded', SiteCode as 'Site Code', Transactions.CreatedBy as 'Created By', Date_format( Transactions.CreationDate, '%Y-%m-%d') as CD from Members join Cards using (MemberNo) JOIN Transactions force index( CardNo) Using( CardNo ) where Members.AccountNo = $AccountNo order by Members.MemberNo DESC, Cards.CardNo DESC, TransTime DESC";
		return DBQueryExitOnFailure( $sql );
	}

	function GetPrintTransactionHistory( $AccountNo )
	{
		$sql = "SELECT STRAIGHT_JOIN  Cards.CardNo, DATE_FORMAT( TransTime, '%Y-%m-%d %H:%i:%s') as 'Date', TransValue as 'Spend Value', PointsAwarded, SiteCode from Members join Cards using (MemberNo) left join Transactions force index( CardNo) using (CardNo) where Members.AccountNo = $AccountNo order by Members.MemberNo, Cards.CardNo, TransTime";
		return DBQueryExitOnFailure( $sql );
	}

	function GetGroupLoyaltyTransactionHistory( $MemberNo )
	{
		$sql = "SELECT STRAIGHT_JOIN Cards.CardNo as 'Card Number' , DATE_FORMAT( TransTime, '%Y-%m-%d %H:%i:%s') as 'Date', TransValue as 'Spend Value', PointsAwarded as 'Points Awarded', SiteCode as 'Site Code', Transactions.CreatedBy as 'Created By', Date_format( Transactions.CreationDate, '%Y-%m-%d') as CD from Members join Cards using (MemberNo) JOIN Transactions force index( CardNo) Using( CardNo ) where Members.MemberNo = $MemberNo order by Cards.CardNo DESC, TransTime DESC";
		return DBQueryExitOnFailure( $sql );
	}
?>