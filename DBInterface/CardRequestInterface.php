<?php
	define( "RequestAdditionalMember",  'AM' );
	define( "RequestReplacementCard", 'RC' );
	define( "RequestAdditionalCard", 'AC' );
	define( "RequestNewMember", 'NM' );
	define( "RequestMultipleCards", 'BR' );
	define( "RequestGroupTreasure", 'GT' );
	define( "RequestGroupSecretairy", 'GS' );
	define( "RequestGroupMember", 'GM' );
	define( "RequestGroupLoyaltyMember", 'GL' );

	function InsertRequestRecord( $MemberNo, $RequestCode )
	{
		global $uname;
		$sql = "Insert into CardRequests( MemberNo, RequestCode, CreationDate, CreatedBy ) values ( $MemberNo, '$RequestCode', now(), '$uname' )";

		$results = DBQueryExitOnFailure( $sql );

	}

	function checkCardRequestNumber( $requestNumber )
	{
		$sql = "Select MemberNo, Status from CardRequests where RequestNo = $requestNumber";
		$results = DBQueryExitOnFailure( $sql );
		$numrows = mysql_num_rows($results);
		if( $numrows == 1 )
		{
			$row = mysql_fetch_row( $results );
			if( $row[1] != 'O' )
			{
				DBLogError( "RequestNumber $requestNumber already satisifed $row[1]\n");
				return false;
			}
			else
			{
				return $row[0];
			}
		}
		else
		{
			DBLogError( "Unrecognised requestNumber $requestNumber\n");
			return false;
		}
	}


	function GetUnsatisifiedCardRequestBatches( $limit )
	{
		$sql = "SELECT IFNULL(BatchTime,'New Batch') AS BatchTime, count( * ) AS Unsatisfied, 
			CardRanges.Comments AS Organisation1, RequestCode 
			FROM CardRequests 
			JOIN Members 
			USING ( MemberNo ) 
			LEFT JOIN CardRanges USING ( AccountNo )
			WHERE STATUS != 'S' GROUP BY BatchTime, Organisation1 
			ORDER BY BatchTime DESC LIMIT $limit";
		return DBQueryExitOnFailure( $sql );

	}
	

	function MakeUpBatch( $timestamp, $group )
	{
		if( $group )
		{
			$sql = "Update CardRequests Join Members using (MemberNo) JOIN CardRanges USING( AccountNo ) 
			set BatchTime='$timestamp', Status='O' where Status='N' and Comments = '$group'";
		}
		else 
		{
			$sql = "Update CardRequests Join Members using (MemberNo) LEFT JOIN CardRanges USING( AccountNo ) 
			set BatchTime='$timestamp', Status='O' where Status='N' and Comments IS NULL";
		}
		$results = DBQueryExitOnFailure( $sql );
	}
	
	function GetBatchData( $timestamp )
	{
		$sql = "select RequestNo As RefNo, RequestCode As RequestType,
		 Title, Forename As FirstName, Surname,
		  IFNULL(Organisation, CardRanges.Comments) AS Organisation,
		   Address1, Address2, Address3, Address4, Address5, PostCode from Members
		    Join CardRequests using (MemberNo) LEFT JOIN CardRanges USING( AccountNo )
		    where BatchTime='$timestamp' and Status = 'O'";

		return DBQueryExitOnFailure( $sql );

	}

	function SatisfyRequest( $requestNo )
	{
		$sql = "Update CardRequests Set Status='S' where RequestNo = $requestNo and Status ='O'";

		return DBQueryExitOnFailure( $sql );
	}

	function GetOutstandingCardRequestForMember( $MemberNo )
	{
		$sql = "Select RequestNo from CardRequests where Status != 'S' and MemberNo = $MemberNo";
		$result = DBQueryExitOnFailure( $sql );
		return mysql_num_rows( $result );
	}

?>