<?php

	function GetLettersList()
	{
		$sql = "SELECT TrackingCode, Description from TrackingCodes where Active = 'Y' and Letters = 'Y' order by Priority";
		$results = DBQueryExitOnFailure( $sql );
		$LettersList = array();
		while( $row = mysql_fetch_row( $results ) )
		{
			$LettersList[$row[0]] = $row[1];
		}
		return $LettersList;
	}

	function GetLettersCodes()
	{
		$sql = "SELECT TrackingCode as LetterCode, Description, Active, Template from TrackingCodes where Letters = 'Y' order by Active, Priority";

		return DBQueryExitOnFailure( $sql );
	}

	function AddLetterRequest( $Code, $MemberNo, $Comments )
	{
		global $uname;
		$fields = "MemberNo, TrackingCode, CreatedBy, CreationDate";
		$values = "$MemberNo, $Code, '$uname', now()";
		if( $Comments != "" )
		{
			$fields .= ",Notes";
			$values .= ", '$Comments'";
		}
		
		$sql = "Insert into LetterRequests( $fields ) values ( $values )";

		$results = DBQueryExitOnFailure( $sql );
		
		if($Code == 1212 ) // Confirm Spend 1
		{
			$sql = "UPDATE AccountStatus JOIN Members USING ( AccountNo ) SET ConfirmSpend1SentDate  = NOW( ), RevisedDate = NOW( ) WHERE MemberNo = $MemberNo";
			$results = DBQueryExitOnFailure( $sql );
		}
		if($Code == 1213 ) // Confirm Spend 2
		{
			$sql = "UPDATE AccountStatus JOIN Members USING ( AccountNo ) SET ConfirmSpend2SentDate  = NOW( ), RevisedDate = NOW( ) WHERE MemberNo = $MemberNo";
			$results = DBQueryExitOnFailure( $sql );
		}
		if($Code == 1214 ) // Proof Of Receipts 
		{
			$sql = "UPDATE AccountStatus JOIN Members USING ( AccountNo ) SET ProofOfReceiptsSentDate = NOW( ), RevisedDate = NOW( ) WHERE MemberNo = $MemberNo";
			$results = DBQueryExitOnFailure( $sql );
		}
		if($Code == 1209 ) // Cleared 
		{
			$sql = "UPDATE AccountStatus JOIN Members USING ( AccountNo ) SET AccountClearedDate = NOW( ), RevisedDate = NOW( ) WHERE MemberNo = $MemberNo";
			$results = DBQueryExitOnFailure( $sql );
		}
		if($Code == 1207 or $Code == 1208 ) // Closed Fraud or Closed No Response
		{
			$sql = "UPDATE AccountStatus JOIN Members USING ( AccountNo ) SET AccountClosedDate = NOW( ), RevisedDate = NOW( ) WHERE MemberNo = $MemberNo";
			$results = DBQueryExitOnFailure( $sql );
		}
	}

	function GetRequestedLetters( $timestamp )
	{
		$sql = "select Template, RequestNo, Title, Forename, Initials, Surname, Address1, Address2, Address3, Address4, Address5, PostCode, Balance, PrimaryCard, Members.StaffID AS StaffID, now() as BalDate, DATE_FORMAT(now(), '%W, %D %M %Y') as SystemDate from TrackingCodes join LetterRequests using( TrackingCode ) join Members using ( MemberNo ) join Accounts using( AccountNo ) where Printed = 'S' and PrintStamp = '$timestamp' order by Priority";

		return DBQueryExitOnFailure( $sql );
	}

	function GetUnconfirmedPrintBatches( $limit )
	{
		$sql = "SELECT PrintStamp, count(*) as Unconfirmed from LetterRequests where Printed != 'Y' group by PrintStamp order by PrintStamp limit $limit" ;

		return DBQueryExitOnFailure( $sql );

	}

	function GetLettersSQLTime()
	{
		$sql = "Select now()";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		return $row[0];
	}

	function MakeUpLetterBatch( $timestamp )
	{
		$sql = "Update LetterRequests set PrintStamp='$timestamp', Printed='S' where Printed='N'";
		DBQueryExitOnFailure( $sql );
	}


	function ConfirmLetterBatch( $timestamp )
	{
		$sql = "Update LetterRequests Set Printed='Y' where PrintStamp='$timestamp' and Printed ='S'";

		DBQueryExitOnFailure( $sql );
	}

	function DeleteLetterTemplate( $code )
	{
		$sql = "UPDATE TrackingCodes set Active = 'N', Priority = 9999 where TrackingCode = $code";
		DBQueryExitOnFailure( $sql );
	}

	function EnableLetterTemplate( $code, $priority )
	{
		$sql = "UPDATE TrackingCodes set Active = 'Y', Priority = $priority where TrackingCode = $code";
		DBQueryExitOnFailure( $sql );
	}

	function InsertLetterTemplate( $Desc, $temp )
	{
		global $uname;
		$sql = "INSERT into TrackingCodes ( Description, Template, Priority, Active, Letters, CreatedBy, CreationDate ) values ( '$Desc','$temp', 0, 'Y', 'Y', '$uname', now() )";
		DBQueryExitOnFailure( $sql );
	}

	function UpdateLetterTemplate( $code, $Desc, $temp )
	{
		$sql = "UPDATE TrackingCodes set Active = 'Y', Description = '$Desc', Template = '$temp' where TrackingCode = $code";
		DBQueryExitOnFailure( $sql );
	}
	
?>