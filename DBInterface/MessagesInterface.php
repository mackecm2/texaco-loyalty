<?php


function GetNextMessageNo()
{
	$sql = "Select (MessageNo + 1) as MessageNo from MessageDetail where 1 order by MessageNo DESC limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_assoc( $result );

	return ($row['MessageNo']);
}

function GetCurrentMessages()
{
	$sql = "Select * from MessageDetail where 1 order by Priority";

	return DBQueryExitOnFailure( $sql );
}


function GetCurrentWebActiveMessages()
{
	$sql = "Select * from MessageDetail where Active = 'Y' and Web = 'Y' and StartDate <= now() and ExpiryDate >= now() order by Priority";

	return DBQueryExitOnFailure( $sql );
}

function GetMessagesFieldNameList()
{
	$sql = "Select Distinct FieldName from MessagesFieldComparisons";

	$results = DBQueryExitOnFailure( $sql );

	$fieldsList = array( "" => "");

	while( $row = mysql_fetch_row( $results ) )
	{
		$fieldsList[$row[0]] = $row[0];
	}

	return $fieldsList;
}

function GetCurrentMessageSettings( $MessageNo )
{

		$sql = "Select * from MessageDetail where MessageNo = '$MessageNo'";

		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) > 0 )
		{
			return mysql_fetch_assoc( $results ) ;
		}
		return false;
}

function GetCurrentMessageCriteria( $MessageNo )
{

		$sql = "Select MessageCriteria.FieldName, FullFieldName, MessageCriteria.ComparisonType, ComparisonCriteria, Boolean, PopulateType, Populate  from MessageCriteria left join MessagesFieldComparisons using (FieldName, ComparisonType) where MessageNo = '$MessageNo' order by CriteriaNo";
		#echo "<br>$sql";
		return DBQueryExitOnFailure( $sql );
}

function GetAbreviatedMessageCriteria( $MessageNo )
{
		$criteria = "";
		$sql = "Select * from MessageCriteria where MessageNo = '$MessageNo' order by CriteriaNo";

		$results = DBQueryExitOnFailure( $sql );
		while( $row= mysql_fetch_assoc( $results ) )
		{
			$criteria .=" $row[FieldName] $row[ComparisonType] '$row[ComparisonCriteria]' $row[Boolean]";
		}
		return $criteria;
}

function GetMessageFieldComparisonOptions( $FieldName )
{
		$sql = "Select ComparisonType from MessagesFieldComparisons where FieldName = '$FieldName'";
		#echo "<br>$sql<br>";
		$results = DBQueryExitOnFailure( $sql );

		$compList = array();
		while( $row = mysql_fetch_assoc( $results ) )
		{
			$compList[$row["ComparisonType"]] = htmlspecialchars( $row["ComparisonType"]);
		}
		return $compList;
}


function GetMessageFieldValues( $sql )
{
	$singleList = array();
	if( !is_null( $sql ) )
	{
		$results = DBQueryExitOnFailure( $sql );
		while( $row = mysql_fetch_row( $results ) )
		{
			$singleList[$row[1]] = htmlspecialchars( $row[0]);
		}
	}
	return $singleList;
}


function SetMessagePriority( $MessageNo, $priority )
{
	$sql = "Update MessageDetail set Priority = $priority where MessageNo = '$MessageNo'";
	DBQueryExitOnFailure( $sql );
}

function checkmembermessage ($MemberNo, $row)
{

	$currentCriteria = GetCurrentMessageCriteria( $row[MessageNo] );
	$criteria = "";
	while( $criteriarow = mysql_fetch_assoc( $currentCriteria ) )
	{
		if($criteriarow[FieldName] == 'All Members')
		{
			 #echo "All members found<br>";
			return(true);
		}
		else
		{

			#echo "<br>Setting criteria as  $criteriarow[FullFieldName]";
			$criteria .= " $criteriarow[FullFieldName] $criteriarow[ComparisonType] '$criteriarow[ComparisonCriteria]' $criteriarow[Boolean]";
		}
	}

	$sql = "select * from Members join Accounts using (AccountNo) where $criteria and Members.MemberNo = $MemberNo";

	#echo "$sql<br>";

	$memberresult =  DBQueryExitOnFailure( $sql );
	if(mysql_fetch_assoc( $memberresult ) < 1)
	{
		#	This message does not relate to this member so return false.
		return(false);
	}
	else
	{
		#	We need to check if this message has a DisplayTimes set

		if( $row[DisplayTimes] > '0')
		{
			#	Now fetch the messagesreceived record.

			$messagerecsql = "select DisplayTimes from MessagesReceived where MessageNo = $row[MessageNo] and MemberNo = $MemberNo";
			$messagerecresult =  DBQueryExitOnFailure( $messagerecsql );
			if( mysql_num_rows( $messagerecresult ) < 1 )
			{

				logmessagerec($MemberNo, $row);

				return(false);
			}
			else
			{
				#	A record has been found so have they viewed this message enough ?

				$messagerec = mysql_fetch_assoc( $messagerecresult );
				if($messagerec[DisplayTimes] >= $row[DisplayTimes])
				{
					return(false);
				}
				else
				{
					updatemessagerec($MemberNo, $row);
				}
			}

			return(true);
		}
		return(true);
	}


}



function logmessagerec ( $MemberNo, $row )
{

	#	We only need to write away if LogEvents is set

	if($row[LogEvents] == 'Y')
	{

		$createmessagerecsql = "INSERT INTO messagesreceived
							( `MemberNo` , `MessageNo` , `CreationDate` , `DisplayTimes`, `LastViewed`  )
							VALUES ($MemberNo, $row[MessageNo], now(), '1',now() )";
		$createmessagerecresult =  DBQueryExitOnFailure( $createmessagerecsql );
	}
	return(true);

}


function updatemessagerec ( $MemberNo, $row )
{

	$createmessagerecsql = "update messagesreceived set DisplayTimes = (DisplayTimes + 1), LastViewed = now()   where MessageNo = $row[MessageNo] and MemberNo = $MemberNo";
	$createmessagerecresult =  DBQueryExitOnFailure( $createmessagerecsql );

	return(true);

}
?>