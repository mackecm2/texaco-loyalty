<?php

	function luhnCheck( $CardNumber ) 
	{
		$CardNumber = Trim( $CardNumber ); 
		if(!is_numeric($CardNumber)) 
		{
			return false;
		}
		//* MRM next bit inserted for Mantis 09/03/09
		if (substr($CardNumber, 0, 2) == '01')
		{
			return true;
		}
		else
		{
			$no_digit = strlen($CardNumber);
			$oddoeven = (($no_digit % 2) == 1);
			$sum = 0;
			for ( $count = 0; $count < $no_digit; $count++) 
			{
				$digit = intval(substr( $CardNumber, $count, 1 ));
				if (!(($count % 2) == 1 xor $oddoeven)) 
				{
					$digit *= 2;
					if ($digit > 9)
					{
						// very cleverly adds in one as well
						$digit -= 9;
					}
				}
				$sum += $digit;
			}
			return ($sum % 10 == 0);
		}	
	}
	
// assuming that the Card Number has passed the LUHN check. let's check it against our Card Ranges table   MRM 16/02/10
	function CardRangeCheck( $CardNumber ) 
	{
		$CardNumber = Trim( $CardNumber ); 
		$sql = "SELECT CardType FROM CardRanges WHERE CardStart < '$CardNumber' AND CardFinish > '$CardNumber'";
		$cardtype =  DBSingleStatQueryNoError($sql);
		if ( !$cardtype )
		{
			return "Unknown";
		}
		else
		{
			return $cardtype;
		}
	}
	
	function CreateRawCard( $CardNo )
	{
		global $uname;
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
		$sql = "Insert into Cards (CardNo, CardType, CreatedBy, CreationDate ) values ('$CardNo', '$CardType', '$uname', now() )"; 
		$results = DBQueryExitOnFailure( $sql );
	}
	
	function GetCardMemberNo( $CardNo )
	{
		$sql = "Select MemberNo from Cards where CardNo = '$CardNo'";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		if( $row and $row[0] != 0 and $row[0] != "" )
		{
			return $row[0];
		}
		else
		{
			return false;
		}
	}

	function CheckMemberHasPrimary( $CardNo, $MemberNo )
	{
		if( $CardNo == "" )
		{
			// See if the member has a card as primary that he owns

			$sql = "Select Members.MemberNo as M1, Cards.MemberNo as M2 from Members join Cards on (Members.PrimaryCard = Cards.CardNo) where Members.MemberNo = $MemberNo and Cards.MemberNo = $MemberNo";
			$results = DBQueryExitOnFailure( $sql );
			if( mysql_num_rows( $results ) == 0 )
			{
				$sql = "Select CardNo from Cards where MemberNo = $MemberNo order by CreationDate limit 1";
				$results = DBQueryExitOnFailure( $sql );

				if( mysql_num_rows( $results ) > 0 )
				{
					$row = mysql_fetch_row( $results );
					$CardNo = $row[0];
				}
				else
				{
					// log error

					return false;
				}
			}
			else
			{
				return true;
			}
		}
		$sql = "Update Members set PrimaryCard = '$CardNo' where MemberNo = $MemberNo";
		$results = DBQueryExitOnFailure( $sql );
	}

	function MergeCardToMember( $CardNo, $MemberNo, $bPreviousMember )
	{
		global $uname;
		$StoppedPoints = 0;
		$sql = "Select MemberNo, StoppedPoints from Cards where CardNo = '$CardNo'";
		$results = DBQueryExitOnFailure( $sql );
		if( mysql_num_rows($results) > 0 )
		{
			$row = mysql_fetch_row( $results );
			$StoppedPoints = $row[1];
			if( $bPreviousMember && $row[0] != "" )
			{
				$bPreviousMember = $row[0];
			}
			else
			{
				if( $row[0] != "" )
				{
					return false;
				}
				else
				{
					$bPreviousMember = false;
				}
			}
		}
		else
		{
			$bPreviousMember = false;
			$sql = "insert into Cards (CardNo, MemberNo, IssueDate, CreatedBy, CreationDate ) values ( '$CardNo', $MemberNo, now(), '$uname', now() )";
			$results = DBQueryExitOnFailure( $sql );
		}

		if( $bPreviousMember )
		{
			InsertTrackingMember( TrackingCardMoved, $bPreviousMember, "$CardNo moved to $MemberNo", 0 );
			$sql = "Update Members set PrimaryCard = null where MemberNo = $bPreviousMember and PrimaryCard = '$CardNo'";
			DBQueryExitOnFailure( $sql );
			if( mysql_affected_rows() != 0)
			{
				CheckMemberHasPrimary( "", $bPreviousMember );
			}
		}

		$sql = "Update Cards set MemberNo=$MemberNo where CardNo = '$CardNo'";
		$results = DBQueryExitOnFailure( $sql );


		CheckMemberHasPrimary( $CardNo, $MemberNo );

		$sql = "Select AccountNo from Members where MemberNo = $MemberNo";
		$AccountNo  = DBSingleStatQuery( $sql );

		$total = ReleaseStoppedPoints( $AccountNo, $MemberNo );

		if( $bPreviousMember )
		{
			InsertTrackingMember( TrackingCardMoved, $MemberNo, "$CardNo moved from $bPreviousMember", $StoppedPoints );
		}
		else
		{
			InsertTrackingMember( TrackingCardLinked, $MemberNo, "$CardNo Linked", $StoppedPoints );
		}
		return true;
	}

	function ReleaseStoppedPoints( $AccountNo,  $MemberNo )
	{
		$total = 0;

		$sql = "Select sum(StoppedPoints), AccountType from Cards join Members using(MemberNo) join Accounts using (AccountNo) where Accounts.AccountNo = $AccountNo and AwardStopDate is null and StoppedPoints > 0 group by Accounts.AccountNo";
		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) > 0 )
		{
			$row = mysql_fetch_row( $results );
			$total = $row[0];

			if( $total != "" )
			{
				$sql = "Update Accounts set Balance = Balance + $total where AccountNo = $AccountNo";
				$results = DBQueryExitOnFailure( $sql );

				$sql = "Update Cards, Members, Accounts set stoppedPoints = 0 where Cards.MemberNo = Members.MemberNo and Members.AccountNo = Accounts.AccountNo and Accounts.AccountNo = $AccountNo";
				$results = DBQueryExitOnFailure( $sql );
				
				if( $row[1] == 'G')
				{
					$sql = "Update Members set MemberBalance = $total where MemberNo = $MemberNo";
					$results = DBQueryExitOnFailure( $sql );
					echo " $total transferred to Member Balance .... ";
				}
			}
			else
			{
				LogWarning( "Total Blank for Account No $AccountNo\n");
			}
		}
		else
		{
//			LogWarning( "No points to release for Account No $AccountNo\n");
		}
		return $total;
	}

	function MarkCardAsLost( $CardNo, $MemberNo )
	{
		$sql = "Update Cards set LostDate = now() where CardNo='$CardNo'";
		$results = DBQueryExitOnFailure( $sql );

//		$sql = "Update Members set PrimaryCard=null where PrimaryCard='$CardNo' and MemberNo=$MemberNo";
//		$results = DBQueryExitOnFailure( $sql );
	}




?>