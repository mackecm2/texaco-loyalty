<?php
function GetMonthlyCardHistory( $AccountNo )
{
	$sql = "SELECT CardMonthly.CardNo, YearMonth, SpendVal, PointsEarned, Swipes from Members join Cards using( MemberNo ) join CardMonthly Using( CardNo ) where Members.AccountNo = $AccountNo Order By PrimaryMember DESC, Cards.LastSwipeDate, YearMonth DESC";
	
	return DBQueryExitOnFailure( $sql );
}

function GetMonthlyAccountHistory( $AccountNo )
{
	$sql = "SELECT  YearMonth, Swipes As Swipes, PointsEarned as PointsEarned, PointsRedeemed as PointsRedeemed, AdjustPlus as AdjPlus, AdjustMinus as AdjMinus from AccountMonthly where AccountNo = $AccountNo Order By YearMonth DESC ";

	return DBQueryExitOnFailure( $sql );
}
function GetMonthlyGroupCardHistory( $MemberNo )
{
	$sql = "SELECT CardMonthly.CardNo, YearMonth, SpendVal, PointsEarned, Swipes from Members join Cards using( MemberNo ) join CardMonthly Using( CardNo ) where Members.MemberNo = $MemberNo Order By PrimaryMember DESC, Cards.LastSwipeDate, YearMonth DESC";
	
	return DBQueryExitOnFailure( $sql );
}
?>