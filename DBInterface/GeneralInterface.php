<?php
	function GetSQLTime()
	{
		$sql = "Select now()";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		return $row[0];
	}


	function GetSQLTime1()
	{
		$sql = "Select DATE_FORMAT( now(), '%d %b %Y')";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		return $row[0];
	}


	function GetBatchFilename( $timestamp )
	{
		$sql = "Select date_format('$timestamp', '%Y_%m_%d_%H_%i')";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		return $row[0];
	}

	function GetBatchFilenameDateOnly( $timestamp )
	{
		$sql = "Select date_format('$timestamp', '%Y_%m_%d')";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		return $row[0];
	}


	function GetSearchResults(  $cardNo, $postCode, $surname, $email, $limit )
	{
		$sql = "";
		$fields = "Members.AccountNo, Members.MemberNo,  Members.DOB, Title, Initials, Forename, Surname, Address1, Address2, PostCode, PrimaryMember, Balance, MemberBalance"; 
		if( $cardNo != "" )
		{
			$sql = "select $fields, CardNo, CardType, CardRanges.AccountNo AS GroupAccountNo, FraudStatus, Status from Cards left join Members using (MemberNo) left join Accounts using (AccountNo) left join AccountStatus using (AccountNo) left join CardRanges using (CardType) where CardNo = '$cardNo' AND SUBSTR(CardNo,1,2) <> '01' LIMIT 1";
		}
		else 
		{
			$where = "";
			$and = "";
			if( $postCode != "" )
			{
				$where .= "$and Postcode Like '$postCode%'";
				$and = "and ";
			}
			if( $surname != "" )
			{
				$where .= "$and Surname = '$surname'";
				$and = "and ";
			}
			if( $email != "" )
			{
				$where .= "$and Email Like '$email%'";
				$and = "and ";
			}

			if( $where != "" )
			{
				$sql = "select $fields, PrimaryCard as CardNo, Balance, Status from Members join Accounts using (AccountNo) join AccountStatus using (AccountNo) where SUBSTR(PrimaryCard,1,2) <> '01' and $where limit $limit";
			}
		}

		if( $sql != "" )
		{
			return DBQueryExitOnFailure( $sql );
		}
		else
		{
			return false;
		}
	}
?>