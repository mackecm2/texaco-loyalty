<?php

	define("TrackingAdditionalCard", 13 );
	define("TrackingCardLost", 10 );
	define("TrackingHomeSiteChanged", 672 );
	define("TrackingContactChange", 812 );
	define("TrackingStatementPref", 1096 );

	define("TrackingMergeAccount", 1114 );
	define("TrackingNewAccount", 1115 );
	define("TrackingPreferenceChange", 1116 );
	define("TrackingModifyAccount", 1125 );
 
	define("TrackingUnmergeMember", 1117 );
	define("TrackingNewCardAdded", 1118 );

	define("TrackingAdditionalMember", 1119 );
	define("TrackingMultipleCards", 1120 );
	define("TrackingAwardStopChanged", 1121 );
	define("TrackingRedeemStopChanged", 1122 );
	define("TrackingMergeAccountCard", 1123 );
	define("TrackingBonusFile", 1124 );
	define("TrackingCardLinked", 1138 );
	define("TrackingEmailBonus50", 1140 );
	define("TrackingGoneAway", 1141 );
	define("TrackingUKFAccountCard", 1142 );
	define("TrackingCancelRedemption", 1143 );
	define("MerchantInterfaceCredit", 1144 );
	define("TrackingQ8Merge", 1145 );
	define("TrackingCardMoved", 1146 );
	define("TrackingMoveMember", 1147 );
 	define("TrackingMemberDelete", 1148 );
 	define("TrackingWebUpdate", 1150 );
	define("TrackingPrizeWinner", 1153 );
	define("TrackingReleaseStoppedPoints", 1154 );
	define("BalanceTransfer", 1225 );
	define("TrackingAltDeliveryAddress", 1228 );
	define("TrackingStatusChange", 1229 );

	define("TrackingTypeList", 'L' );
	define("TrackingTypeAdjustment", 'C' );
	define("TrackingTypeAutomatic", 'A' );
	define("TrackingTypeStops", 'S' );
	define("TrackingTypeAccountClose", 'X' );
	define("TrackingTypeAccountReinstate", 'R' );

	function InsertTrackingMember( $RecordType, $MemberNo, $Comment, $Stars )
	{
		global $uname;

		$sql = "Select AccountNo from Members where MemberNo = $MemberNo";
		$AccountNo = DBSingleStatQuery( $sql );
		if( $AccountNo )
		{
			$fields = "MemberNo, AccountNo, TrackingCode, CreatedBy, CreationDate";
			$values = "$MemberNo, $AccountNo, '$RecordType', '$uname', now()";
			if( $Comment != "" )
			{
				$fields .= ",Notes";
				$values .= ", '".mysql_real_escape_string($Comment)."'";
			}
			if( $Stars != 0 )
			{
				$fields .= ",Stars";
				$values .= ",$Stars";
			}
			$sql = "Insert into Tracking ($fields) values ($values)";
			$results = DBQueryExitOnFailure( $sql );
		}
		else
		{
		   echo "Failed to find Account for MemberNo $MemberNo";
		}
	}

	function InsertTrackingRecord( $RecordType,  $MemberNo, $AccountNo, $Comment, $Stars )
	{
		global $uname;
		$fields = "MemberNo, AccountNo, TrackingCode, CreatedBy, CreationDate";
		$values = "$MemberNo, $AccountNo, '$RecordType', '$uname', now()";
		if( $Comment != "" )
		{
			$fields .= ",Notes";
			$values .= ", '".mysql_real_escape_string($Comment)."'";
		}

		if( $Stars != 0 )
		{
			$fields .= ",Stars";
			$values .= ",$Stars";
		}
		$sql = "Insert into Tracking( $fields ) values ( $values )";

		$results = DBQueryExitOnFailure( $sql );
	}

	function AdjustBalance( $RecordType, $MemberNo, $AccountNo, $Comment, $Adjustment )
	{
		global $uname;
		$fields = "MemberNo, AccountNo, TrackingCode, CreatedBy, CreationDate";
		$values = "$MemberNo, $AccountNo, $RecordType, '$uname', now()";
		if( $Comment != "" )
		{
			$fields .= ",Notes";
			$values .= ", '".mysql_real_escape_string($Comment)."'";
		}

		if( $Adjustment != 0 )
		{
			$fields .= ",Stars";
			$values .= ", '$Adjustment'";

			if( $Adjustment > 0 )
			{
				$Adjustment = "+ " . $Adjustment;
			}
			$sql = "Update Accounts set Balance = Balance $Adjustment where AccountNo = $AccountNo";
			$results = DBQueryExitOnFailure( $sql );
		}

		$sql = "Insert into Tracking( $fields ) values ( $values )";
		$results = DBQueryExitOnFailure( $sql );
	}

	function GetTrackingOptions()
	{
		$results = GetTrackingCodes( TrackingTypeList, false );

		$TrackingOptions = array();

		while( $row = mysql_fetch_row( $results ) )
		{
			$TrackingOptions[$row[0]] = $row[1];
		}
		return $TrackingOptions;
	}
	
	function GetTrackingOptionsAccountClose()
	{
		$results = GetTrackingCodes( TrackingTypeAccountClose, false );

		$TrackingOptions = array();

		while( $row = mysql_fetch_row( $results ) )
		{
			$TrackingOptions[$row[0]] = $row[1];
		}
		return $TrackingOptions;
	}
	
		function GetTrackingOptionsAccountReinstate()
	{
		$results = GetTrackingCodes( TrackingTypeAccountReinstate, false );

		$TrackingOptions = array();

		while( $row = mysql_fetch_row( $results ) )
		{
			$TrackingOptions[$row[0]] = $row[1];
		}
		return $TrackingOptions;
	}

	function GetTrackingCodeDetails()
	{
			$sql = "SELECT TrackingCode, Description, Active, Priority, CreditDebit, AddTracking, StopTracking from TrackingCodes where Letters = 'N' and Active='Y' order by Active, Priority";
			return DBQueryExitOnFailure( $sql );
	}


	function GetTrackingCodes( $ListType, $all )
	{


		if( $ListType == TrackingTypeList )
		{
			$sql = "SELECT TrackingCode, Description, Active, Priority from TrackingCodes where AddTracking = 'Y' and Active = 'Y' order by Priority";
		}
		else if( $ListType == TrackingTypeAdjustment )
		{
			$sql = "SELECT TrackingCode, Description, Active, Priority from TrackingCodes where CreditDebit = 'Y' and Active = 'Y' order by Priority";
		}
		else if( $ListType == TrackingTypeStops )
		{
			$sql = "SELECT TrackingCode, Description, Active, Priority from TrackingCodes where StopTracking = 'Y' and Active = 'Y' order by Priority";
		}
		else if( $ListType == TrackingTypeAccountClose )
		{
			$sql = "SELECT TrackingCode, SUBSTR(Description,18) AS Description, Active, Priority from TrackingCodes where StopTracking = 'Y' and Active = 'Y' and Description LIKE 'Account Closed - %' order by Priority";
		}
		else if( $ListType == TrackingTypeAccountReinstate )
		{
			$sql = "SELECT TrackingCode, SUBSTR(Description,21) AS Description, Active, Priority from TrackingCodes where StopTracking = 'Y' and Active = 'Y' and Description LIKE 'Account Reinstated - %' order by Priority";
		}
		return DBQueryExitOnFailure( $sql );
	}

	function DeleteTrackingCode( $code )
	{
		$sql = "UPDATE TrackingCodes set Active = 'N', Priority = 9999 where TrackingCode = $code";
		DBQueryExitOnFailure( $sql );
	}

	function EnableTrackingCode( $code, $priority, $C, $T, $S )
	{
		$sql = "UPDATE TrackingCodes set Active = 'Y', Priority = $priority, CreditDebit = '$C', AddTracking = '$T', StopTracking = '$S'  where TrackingCode = $code";
		DBQueryExitOnFailure( $sql );
	}

	function InsertTrackingCode( $ListType, $Desc )
	{
		global $uname;

		$sql = "Select * from TrackingCodes where Description = '$Desc'";
		$results = DBQueryExitOnFailure( $sql );
		if( mysql_num_rows( $results ) == 0 )
		{

			$sql = "INSERT into TrackingCodes ( Description, Priority, Active, CreatedBy, CreationDate ) values (  '$Desc', 0, 'Y', '$uname', now() )";
			DBQueryExitOnFailure( $sql );
		}
	}

	function UpdateTrackingCode( $code, $Desc )
	{
		$sql = "UPDATE TrackingCodes set Active = 'Y', Description = '$Desc' where TrackingCode = $code";
		DBQueryExitOnFailure( $sql );
	}

	function GetAdjustmentOptions()
	{
		$results = GetTrackingCodes( TrackingTypeAdjustment, false );
		$TrackingOptions = array();

		while( $row = mysql_fetch_row( $results ) )
		{
			$TrackingOptions[$row[0]] = $row[1];
		}
		return $TrackingOptions;
	}

	function GetStopsOptions()
	{
		$results = GetTrackingCodes( TrackingTypeStops, false );
		$TrackingOptions = array();

		while( $row = mysql_fetch_row( $results ) )
		{
			$TrackingOptions[$row[0]] = $row[1];
		}
		return $TrackingOptions;
	}



	function GetTrackingHistory( $AccountNo )
	{
		$sql = "
		(SELECT Date_Format( Tracking.CreationDate, '%Y-%m-%d %H:%i') as Date, Description, Notes, Stars, Tracking.CreatedBy as Agent from Tracking Left JOIN TrackingCodes using( TrackingCode ) where AccountNo = $AccountNo)
		UNION
		(SELECT Date_Format( Tracking.CreationDate, '%Y-%m-%d %H:%i') as Date, Description, Notes, Stars, Tracking.CreatedBy as Agent from Tracking Left JOIN TrackingCodes using( TrackingCode ) Left Join MergeHistory on(Tracking.AccountNo = MergeHistory.SourceAccount) where MergeHistory.DestinationAccount = $AccountNo)
		ORDER BY Date DESC";

		return DBQueryExitOnFailure( $sql );
	}

	function GetRecentTrackingHistory( $AccountNo, $limit )
	{
//		$sql = "SELECT DATE_FORMAT(Tracking.CreationDate, '%d/%m/%y') as Date, Description, Stars, Notes, Tracking.CreatedBy from Tracking left join TrackingCodes using (TrackingCode) where AccountNo = $AccountNo order by Tracking.CreationDate DESC limit $limit";
		$sql = "SELECT DATE_FORMAT(Tracking.CreationDate, '%d/%m/%y') as Date, DATE_FORMAT(Tracking.CreationDate, '%Y-%m-%d %H:%i') as LongDate, Description, Stars, Notes, Tracking.CreatedBy from Tracking left join TrackingCodes using (TrackingCode) where AccountNo = $AccountNo order by LongDate DESC limit $limit";
		return DBQueryExitOnFailure( $sql );
	}
	
		function GetGroupTrackingHistory( $MemberNo )
	{
		$sql = "
		(SELECT Date_Format( Tracking.CreationDate, '%Y-%m-%d %H:%i') as Date, Description, Notes, Stars, Tracking.CreatedBy as Agent from Tracking Left JOIN TrackingCodes using( TrackingCode ) where MemberNo = $MemberNo)
		ORDER BY Date DESC";

		return DBQueryExitOnFailure( $sql );
	}

?>