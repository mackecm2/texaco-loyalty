<?php

	function GetAccountCards( $AccountNo, $SortBy )
	{
		$sql = "Select GAccountNo, Members.MemberNo, Members.AccountNo, Cards.CardNo as CardNo, concat_ws( ' ', Title, Forename, Surname ) as Name, Address1, Postcode from AccountCards join Cards using(CardNo) left join Members using( MemberNo ) where Active = 'Y'";

		if($AccountNo != "")
		{
			$sql .= " and Members.AccountNo = '$AccountNo' ";
  		}

		if( isset( $SortBy ) and $SortBy != "" )
		{
			$sql .= " ORDER BY $SortBy";		
		}
		else
		{

			$sql .= " ORDER BY Surname";
		}
		return DBQueryExitOnFailure( $sql );
	}

	function AddAccountCard( $GAccountNo, $CardNo )
	{
		global $db_user;
		$sql = "replace into AccountCards( GAccountNo, CardNo, CreationDate, CreatedBy, Active ) values ( $GAccounNo, 'CardNo', now(), '$db_user', 'Y' )";
		return DBQueryExitOnFailure( $sql );
	}


function UpdateUKFCard ($newUKFAccountNo, $cardno  )
{
	$sql = "replace into AccountCards set
			GAccountNo 	= '$newUKFAccountNo',
			CardNo		= '$cardno',
			Active		= 'Y',
			CreationDate = now(),
			CreatedBy	= '$_SESSION[username]'	";

	DBQueryExitOnFailure( $sql );

	return mysql_affected_rows() == 1;
}

function RemoveUKFCard( $originalUKFAccountNo )
{
	$sql = "Update AccountCards set Active = 'N' where GAccountNo = $originalUKFAccountNo";
	DBQueryExitOnFailure( $sql );
	return mysql_affected_rows() == 1;
}

function CheckAccount ($AccountNo )
{
	#	This function is called from AddAccountCardProcess.php and checks the account to see if it has
	#	the account type of null - if so it is set as a type A = Local Account

	$sql = "SELECT AccountType from Accounts where AccountNo = '$AccountNo' and AccountType is NULL LIMIT 1";

	#echo "<br>$sql";
	$result = DBQueryExitOnFailure( $sql );

	if ($row = mysql_fetch_array($result))
	{
		#	The account type is null for this record so we need to update it.

		$updatesql = "UPDATE Accounts set AccountType = 'A' WHERE AccountNo = '$AccountNo' LIMIT 1";
		DBQueryExitOnFailure( $updatesql );

			#echo "<br>$updatesql";

		return true;
	}
	else
	{
		return false;
	}
}

function DisplaySegmentCode( $segmentcode )
{
	$SegmentRecency = substr($segmentcode,0,2);
	$SegmentValue = substr($segmentcode,2,2);
	$SegmentFrequency = substr($segmentcode,4,1);
	switch($SegmentRecency)
	{ 
		case 'A1': 
		case 'A2':
			$TitleText = "Recently Active, ";
			break;
		case 'N1': 	
		case 'N2': 	
			$TitleText = "New Customer, ";
			break;
		case 'L ': 
			$TitleText = "Lapsed, ";
			break;
		case 'D ': 
			$TitleText = "Dormant, ";
			break;
		case 'XD': 
			$TitleText = "Extra Dormant, ";
			break;
		default: 
			$TitleText = "Unknown";
			break;
	} 
	
	switch($SegmentValue)
	{ 
		case 'L ': 
			$TitleText .= "Low Value, ";
			$StarDisplay = "<img src=valuestar.gif>";
			break;
		case 'M ': 	
			$TitleText .= "Medium Value, ";
			$StarDisplay = "<img src=valuestar.gif><img src=valuestar.gif>";
			break;
		case 'MH': 
			$TitleText .= "Medium/High Value, ";
			$StarDisplay = "<img src=valuestar.gif><img src=valuestar.gif>";
			break;
		case 'H ': 
			$TitleText .= "High Value, ";
			$StarDisplay = "<img src=valuestar.gif><img src=valuestar.gif><img src=valuestar.gif>";
			break;
	} 
	
	switch($SegmentFrequency)
	{ 
		case 'L': 
			$TitleText .= "Low Frequency";
			break;
		case 'M': 	
			$TitleText .= "Medium Frequency";
			break;
		case 'H': 
			$TitleText .= "High Frequency";
			break;
	} 
	
	$SegmentFormat = "<a title='$TitleText'>";
		if ($SegmentRecency == 'A1' or  
		$SegmentRecency == 'A2' or 
		$SegmentRecency == 'N1' or   	
		$SegmentRecency == 'N2' )
	{ 
			$SegmentFormat .= $SegmentFormat.$StarDisplay;
	} 
	return $SegmentFormat;
}

?>