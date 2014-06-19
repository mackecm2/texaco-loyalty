<?php

	require "../../include/DB.inc";
	require "../../DBInterface/FileProcessRecord.php";
	require "../../DBInterface/WelcomePackInterface.php";
	require "../../DBInterface/RedemptionInterface.php";
	require "../../DBInterface/FraudInterface.php";
	require "../../DBInterface/TrackingInterface.php";
	require "../../mailsender/class.phpmailer.php";
	$db_user = "ReportGenerator";
	$db_pass = "tldttoths";

	$uname = "HouseKeeper";
	
	function MailingList()
	{
		return array('Sally Gibson' => 'sally.gibson@dawleys.com', 
		'Michelle Cooper' => 'michelle.cooper@dawleys.com', 
		'John Aldred' => 'john.aldred@dawleys.com', 
		'Peter Seymour' => 'pseymour@rsm2000.co.uk');
	}
	
	function ReportSafeToRedeem()
	{
		
		$mail = new phpmailer();
		$mail->IsSendmail()	;
		$mail->Host 		= 	"texaco.rsmsecure.com"	; // SMTP servers
		$mail->FromName	= ServerEnvironment.' Star Rewards Application'; // text for "From" shown to recipient e.g. RSM Admin
		$mail->From	= 'root@texaco.rsmsecure.com'; // email address for "From" shown to recipient
		$mail->AddReplyTo('root@texaco.rsmsecure.com', 'RSM 2000 Ltd'); // the reply to mail address and name
		$mail->Sender =	'root@texaco.rsmsecure.com'; // the envelope sender(server) of the email for undeliverable mail
		$mail->AddAddress('mmackechnie@rsm2000.co.uk', 'Mike MacKechnie');

		foreach(MailingList() as $name => $email)
		{
			$mail->AddCC($email, $name);
		}

		$mail->Subject = 'Redemptions Check - All OK';		
		$mail->WordWrap = 70; // set word wrap
		$mail->IsHTML(true); // set mail as html
		
		$mailfooter = '<p></p>Texaco Loyalty Card Monitoring System<BR><BR></font></font>'."\n";
		$mailfooter .= '</font><font face=Verdana size=1><EM>The content of this email and any attachment is private and may be legally privileged. '."\n";
		$mailfooter .= 'If you are not the intended recipient, any use, disclosure, copying or forwarding of this email '."\n";
		$mailfooter .= 'and/or its attachments is unauthorised.<BR>If you have received this email in '."\n";
		$mailfooter .= 'error please notify the sender by email and delete this message and any '."\n";
		$mailfooter .= 'attachments immediately from this system.<BR>RSM2000 Ltd - Suite One, Second '."\n";
		$mailfooter .= 'Floor, Wrest House, Wrest Park, Silsoe, United Kingdom, MK45 4HR.<BR>Registered '."\n";
		$mailfooter .= 'Address: 16 St. Cuthberts Street, Bedford, United Kingdom MK40 3JG, Company '."\n";
		$mailfooter .= 'Registration Number: 3703548</EM></font></font></p>';
	

		// HTML Message Body
		$mail->Body =
		'<font size=2><font face=Verdana><p>The daily check for Negative Account Balances has found no issues, so redemptions can be processed as normal.<p>' 
		.$line2
		.$mailfooter;
		
		// Text Message Body
		$mail->AltBody = "Redemptions Check - All OK\n\n"
			.$line2;

		// send the email and check on its success
		if (!$mail->Send())
		{
			$dbMailSent = " Problem Sending Mail to Dawleys!!!\n\n";
		} 
		else
		{
			$dbMailSent = " Redemptions Check - All OK - Mail Sent to Dawleys\n\n";
		}
			echo date("Y-m-d H:i:s").$dbMailSent;	
	}
		
// - - - - - - - - S E N D   E - M A I L   I F   A C C O U N T   I S   I N   N E G A T I V E   B A L A N C E - - - - - - - - -
	function RedemptionAlert($AccountNo,$Balance)
	{
		$sql2 = "SELECT CardNo, MemberNo FROM Cards AS C
					JOIN Members AS M
					USING ( MemberNo ) 
					JOIN Accounts AS A
					USING ( AccountNo ) WHERE A.AccountNo = $AccountNo ORDER BY LastSwipeDate DESC LIMIT 1";
		$results2 = DBQueryLogOnFailure( $sql2 );
		$row2 = mysql_fetch_assoc( $results2 );
		$line2 .="Card No $row2[CardNo], Member No $row2[MemberNo] has a balance of $Balance <br>\n" ;
					
		$results = GetRedemptionHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			$line2 .= "<h3>Recent Redemptions</h3>";
			$line2 .= "<table width = 100%><tr bgcolor=\"#6699FF\" class = \"headertext\">";
			$line2 .= "<td>Date<td>Product Id<td>Description<td>Merchant<td>Type<td>Quantity<td>TotalCost<td>Agent<td>Status\n";
			$count = 1;
			while(($row = mysql_fetch_assoc($results)) && ($count <= 10))
			{
				$count++;
				if ($count & 1)
				{
					$color = "#99CCFF";
					$font = "#004080";
				}
				else
				{
					$color = "#ccffff";
					$font = "#004080";
				}
				$line2 .= "<tr class = \"bodytext\" bgcolor=$color>\n" ;
	
				$line2 .= "<td>$row[Date]<td>$row[ProductId]<td>$row[Description]<td>$row[MerchantName]<td>$row[Type]<td>$row[Quantity]<td>$row[TotalCost]<td>$row[Agent]<td>$row[StatusDescrip]\n";
				if( $row["Status"] == 'P' and $row["AccountNo"] == $AccountNo )
				{
					$line2 .= "<td><button>Cancel</button>\n";
				}
			}
			$line2 .="</table><br>\n" ;
		}
		else 
		{             // 06 09 10 MRM Mantis 2550 
			$line2 .="No Redemptions found. RSM will investigate why this account is in negative balance.\n" ;
		}
		$mail = new phpmailer();
		$mail->IsSendmail()	;
		$mail->Host 		= 	"texaco.rsmsecure.com"	; // SMTP servers
		$mail->FromName	= ServerEnvironment.' Star Rewards Application'; // text for "From" shown to recipient e.g. RSM Admin
		$mail->From	= 'root@texaco.rsmsecure.com'; // email address for "From" shown to recipient
		$mail->AddReplyTo('root@texaco.rsmsecure.com', 'RSM 2000 Ltd'); // the reply to mail address and name
		$mail->Sender =	'root@texaco.rsmsecure.com'; // the envelope sender(server) of the email for undeliverable mail
		$mail->AddAddress('mmackechnie@rsm2000.co.uk', 'Mike MacKechnie');

		foreach(MailingList() as $name => $email)
		{
			$mail->AddCC($email, $name);
		}
				
		$mail->Subject = 'Duplicate Redemptions';		
		$mail->WordWrap = 70; // set word wrap
		$mail->IsHTML(true); // set mail as html
		
		$mailfooter = '<p></p>Texaco Loyalty Card Monitoring System<BR><BR></font></font>'."\n";
		$mailfooter .= '</font><font face=Verdana size=1><EM>The content of this email and any attachment is private and may be legally privileged. '."\n";
		$mailfooter .= 'If you are not the intended recipient, any use, disclosure, copying or forwarding of this email '."\n";
		$mailfooter .= 'and/or its attachments is unauthorised.<BR>If you have received this email in '."\n";
		$mailfooter .= 'error please notify the sender by email and delete this message and any '."\n";
		$mailfooter .= 'attachments immediately from this system.<BR>RSM2000 Ltd - Suite One, Second '."\n";
		$mailfooter .= 'Floor, Wrest House, Wrest Park, Silsoe, United Kingdom, MK45 4HR.<BR>Registered '."\n";
		$mailfooter .= 'Address: 16 St. Cuthberts Street, Bedford, United Kingdom MK40 3JG, Company '."\n";
		$mailfooter .= 'Registration Number: 3703548</EM></font></font></p>';
	

		// HTML Message Body
		$mail->Body =
		'<font size=2><font face=Verdana><p>The daily check for Negative Account Balances has found the following problem:<p>' 
		.$line2
		.$mailfooter;
		
		// Text Message Body
		$mail->AltBody = "Duplicate Redemptions\n\n"
			.$line2;

		// send the email and check on its success
		if (!$mail->Send())
		{
			$dbMailSent = "Problem Sending Mail to Dawleys!!!\n\n";
		} 
		else
		{
			$dbMailSent = "Mail Sent to Dawleys\n\n";
		}
			echo $dbMailSent;	
	}
		
	// 30 03 09 MRM Mantis 895 ForcePrimaryCard function resurrected
// - - - - - - - - S E T   P R I M A R Y   C A R D   T O   M O S T   R E C E N T L Y   U S E D - - - - - - - - -
	function ForcePrimaryCard()
	{
		$sql = "SELECT MemberNo, PrimaryCard FROM Members JOIN AccountStatus USING ( AccountNo ) WHERE Status = 'Open'";
		$results = DBQueryLogOnFailure( $sql );
		$num_rows = mysql_num_rows($results);
		echo date("Y-m-d H:i:s")." Processing Primary Cards - $num_rows to process\r\n";
		$membersupdated = 0;
		$membersprocessed = 0;
		while( $row = mysql_fetch_assoc( $results )  )
		{
			$membersprocessed++;
			if( ($membersprocessed % 100000) == 0 )
			{
				echo date("Y-m-d H:i:s")." Processed $membersprocessed\r\n";
			}
			// let's only do this for Star Rewards/WeOU Cards  MRM 02/07/09 Mantis 1198
			// let's exclude Group Loyalty Cards from this  MRM 11/12/09 Mantis 1686
			// Account Type is Null ? MRM 09/03/10 Mantis 1994
			$sql2 = "SELECT Max(LastSwipeDate) FROM Cards JOIN Members AS M
				USING ( MemberNo ) 
				JOIN Accounts AS A
				USING ( AccountNo ) WHERE MemberNo =  $row[MemberNo]
				  AND (CardType = 'StarRewards' OR CardType = 'WEOU') AND ( A.AccountType <> 'G' OR A.AccountType IS NULL )";
			$LastSwipeDate = DBSingleStatQueryNoError( $sql2 );
			if( $LastSwipeDate )
			{
				$sql3 = "SELECT CardNo FROM Cards WHERE LastSwipeDate = '$LastSwipeDate' AND MemberNo = $row[MemberNo] AND (CardType = 'StarRewards' OR CardType = 'WEOU')";
				$RealPrimaryCard = DBSingleStatQueryNoError( $sql3 );
				if ( $RealPrimaryCard != $row[PrimaryCard] )
				{
					$sql = "UPDATE Members SET PrimaryCard = $RealPrimaryCard WHERE MemberNo = $row[MemberNo]";
					DBQueryLogOnFailure( $sql );
					$membersupdated++;
				}
			}
			else 
			{
				// for UK Fuels members who have never swiped their SR Card
				$sql4 = "SELECT CardType FROM Cards WHERE CardNo = '$row[PrimaryCard]'";
				$cardtype = DBSingleStatQueryNoError( $sql4 );
				if ( $cardtype == 'UKFuels' )
				{
					$sql5 = "SELECT MAX(CardNo) FROM Cards WHERE MemberNo = $row[MemberNo] AND CardNo LIKE '707655%'";
					$cardnumber = DBSingleStatQueryNoError( $sql5 );
					if( $cardnumber )
					{
						$sql6 = "UPDATE Members SET PrimaryCard = $cardnumber WHERE MemberNo = $row[MemberNo]";
						DBQueryLogOnFailure( $sql6 );
						$membersupdated++;	
					}
				}
			}
	 	}
		echo date("Y-m-d H:i:s")." Members Processed = ".$membersprocessed." \r\n";
		echo date("Y-m-d H:i:s")." Members Updated = ".$membersupdated." \r\n";
	}
// - - - - - - - - S E T   H O M E   S I T E   T O   L A S T   S W I P E - - - - - - - - -
	function SetHomeSiteToFirstSwipe()
	{
		$sql = "SELECT AccountNo FROM Accounts JOIN AccountStatus USING ( AccountNo ) 
				WHERE Balance >0 AND HomeSite IS NULL AND RevisedDate > '2004-10-25' AND STATUS = 'Open'";
		$results = DBQueryLogOnFailure( $sql );
	 	$num_rows = mysql_num_rows($results);
	 	echo date("Y-m-d H:i:s")." Processing Home Sites - $num_rows to process\r\n";
		$membersmissing = 0;
		$membersupdated = 0;
		while( $row = mysql_fetch_assoc( $results )  )
		{
			$membersmissing++;
			$sql = "select FirstSwipeLoc from Members join Cards on(PrimaryCard = CardNo ) where AccountNo = $row[AccountNo] and PrimaryMember = 'Y' and FirstSwipeLoc is not null limit 1";

			$results2 = DBQueryLogOnFailure( $sql );
			$row2 = mysql_fetch_assoc( $results2 );
			if( $row2 )
			{
				$sql = "Update Accounts set HomeSite = $row2[FirstSwipeLoc], HomeSiteDate = now() where AccountNo = $row[AccountNo]";
				DBQueryLogOnFailure( $sql );
				$membersupdated++;
			}

		}
		if( $membersmissing > 0 )
		{
			DBLogError( "Members missing Homesite $membersmissing. Updated $membersupdated"	);
		}
	}

// - - - - - - - - N E G A T I V E   B A L A N C E S - - - - - - - - -

	function ReportNegativeAccounts()
	{
		$sql = "SELECT A.AccountNo,A.Balance,A.TotalRedemp,A.LastStatement,
			A.RedemptionStopDate,A.AccountType,A.SegmentCode,
			IF(N.Reason IS NULL, '*************** NEW NEGATIVE BALANCE! ***************', N.Reason) AS Reason
			FROM Accounts AS A
			LEFT JOIN AccountsNegativeBalances AS N USING ( AccountNo )
			WHERE A.Balance < 0
			AND A.SegmentCode NOT LIKE 'X%'
			ORDER BY A.RevisedDate DESC";
		$results = DBQueryLogOnFailure( $sql );
		$num_rows = mysql_num_rows($results);
		
		$success = 1;
		
		if ( $num_rows > 0 )
		{
			echo "\r\nNegative Balances\r\n";
			echo "+-----------+---------+-------------+---------------+--------------------+-------------+-------------+----------------------------------------------------------------------------------------------------+\r\n";
			echo "| AccountNo | Balance | TotalRedemp | LastStatement | RedemptionStopDate | AccountType | SegmentCode | Reason                                                                                             |\r\n";
			echo "+-----------+---------+-------------+---------------+--------------------+-------------+-------------+----------------------------------------------------------------------------------------------------+\r\n";
		
			while( $row = mysql_fetch_assoc( $results ) )
			{
				echo ("| ".str_pad($row["AccountNo"],9));
				echo (" | ".str_pad($row["Balance"],7));
				echo (" | ".str_pad($row["TotalRedemp"],11));
				echo (" | ".str_pad($row["LastStatement"],13));
				echo (" | ".str_pad($row["RedemptionStopDate"],18));
				echo (" | ".str_pad($row["AccountType"],11));
				echo (" | ".str_pad($row["SegmentCode"],11));
				echo (" | ".str_pad($row["Reason"],98)." |\r\n" );
				if ($row["Reason"] == '*************** NEW NEGATIVE BALANCE! ***************')
				{
					RedemptionAlert($row["AccountNo"],$row["Balance"]);
					$success = 0;
				}
	
	  		}
			echo "+-----------+---------+-------------+---------------+--------------------+-------------+-------------+----------------------------------------------------------------------------------------------------+\r\n";
				
		}
		else 
		{
			echo "\r\n+++++ No Negative Balances found +++++\r\n";
		}
	
		return $success;
	}

// - - - - - - - - N E G A T I V E   S T O P P E D   P O I N T S - - - - - - - - -

	function ReportNegativeStoppedPoints()
	{
		$sql = "SELECT Cards.CardNo, Cards.MemberNo, Members.AccountNo, Cards.StoppedPoints 
			FROM Cards LEFT JOIN Members USING (MemberNo) WHERE Cards.StoppedPoints < 0 ";
		$results = DBQueryLogOnFailure( $sql );
		$num_rows = mysql_num_rows($results);
		
		$success = 1;
		
		if ( $num_rows > 0 )
		{
			echo "\r\nNegative Stopped Points\r\n";
			echo "+---------------------+----------+-----------+---------------+\r\n";
			echo "| CardNo              | MemberNo | AccountNo | StoppedPoints |\r\n";
			echo "+---------------------+----------+-----------+---------------+\r\n";
			while( $row = mysql_fetch_assoc( $results ) )
			{ 
				echo ("| ".str_pad($row["CardNo"],19));
				echo " | ".str_pad($row["MemberNo"],8);
				echo (" | ".str_pad($row["AccountNo"],9));
				echo (" | ".str_pad($row["StoppedPoints"],13));
				echo " |\r\n";
				$success = 0;
	  		}
			echo "+---------------------+----------+-----------+---------------+\r\n";
		}
		else 
		{
			echo "\r\n+++++ No Negative Stopped Points found +++++\r\n";
		}
	
		return $success;
	}	
// - - - - - - - - S T O P   S T A F F   R E D E M P T I O N - - - - - - - - -

	function StopStaffRedemption()
	{
		$sql = "SELECT M.StaffID, M.MemberNo, A.RedemptionStopDate, M.Title, M.Initials, M.Forename, M.Surname, M.PrimaryCard,
		 A.AccountNo, A.CreationDate, A.CreatedBy FROM texaco.Members AS M JOIN texaco.Accounts AS A USING ( AccountNo ) WHERE ( A.AccountType = 'D' )";
		$results = DBQueryLogOnFailure( $sql );
		$membersprocessed = 0;
		$membersupdated = 0;
		while( $row = mysql_fetch_assoc( $results )  )
		{
			$membersprocessed++;
			if ( !$row[RedemptionStopDate] or $row[RedemptionStopDate] == '')
			{
				$sql = "Update Accounts set RedemptionStopDate = now() where AccountNo = $row[AccountNo]";
				DBQueryLogOnFailure( $sql );
				$membersupdated++;
			}
		}
			DBLogError( "$membersprocessed Staff Incentive Accounts processed. $membersupdated updated with Redemption Stop Date"	);
	}

// - - - - - - - - S E T   C A R D   T Y P E S - - - - - - - - -
//* next section added 17/03/09 MRM Mantis 807 for any new cards that are actually WeOU - version 57
//* updated 26 03 09 - modified card ranges
//* updated 04 11 09 - using new CardRanges table
	function SetCardTypes()
	{
			$sql2 = "Select CardType, CardStart, CardFinish from CardRanges WHERE CardStart <> '' OR CardFinish <> ''";
			$results2 = DBQueryExitOnFailure( $sql2 );
			while( $row2 = mysql_fetch_assoc( $results2 ))
			{
				$sql = "UPDATE Cards SET CardType = '$row2[CardType]' WHERE CardNo BETWEEN '$row2[CardStart]' AND '$row2[CardFinish]' ";
				DBQueryLogOnFailure( $sql );
				if ($row2[CardType] != 'StarRewards')
				{
					echo date("Y-m-d H:i:s")." New $row2[CardType] Cards Processed = ".mysql_affected_rows()." \r\n";
				}
			}
	}
	//* end of addition

// - - - - - - - - S E T   M E M B E R   B A L A N C E S - - - - - - - - -
//* next section added 07/08/09 MRM Mantis 1128 to create member balances for group loyalty cards
function SetMemberBalances()
	{
		echo date("Y-m-d H:i:s")." Member Balances started \r\n";
		$sql = "UPDATE Members SET MemberBalance = 0 WHERE MemberBalance <> 0 OR MemberBalance IS NULL";
		DBQueryLogOnFailure( $sql );
		
		// Just Crystal Palace for the moment
		$sql = "SELECT MemberNo, PointsAwarded From Transactions JOIN Cards USING ( CardNo )WHERE CardNo BETWEEN '7076550201000000001' AND '7076550201000999999'";
		$res = DBQueryLogOnFailure( $sql );
		$count = 0;
		while( $row = mysql_fetch_assoc( $res ) )
		{
			if ($row['MemberNo'])
			{
				$sql1 = "UPDATE Members SET MemberBalance = MemberBalance + $row[PointsAwarded] WHERE MemberNo = $row[MemberNo]";
				DBQueryLogOnFailure( $sql1 );
				$count++;	
			}
		}
		echo date("Y-m-d H:i:s")." $count members updated with Member Balances \r\n";
	}
	
// - - - - - - - - C L O S E   O L D   L E G A C Y   A C C O U N T S - - - - - - - - -
//* next section added 22/04/10 MRM Mantis 1893 to close old accounts
function CloseLegacyAccounts()
	{
		echo date("Y-m-d H:i:s")." Close Legacy Accounts started \r\n";
		$sql = "UPDATE texaco.AccountStatus JOIN texaco.Accounts USING ( AccountNo )
					JOIN Analysis.AccountsLastSwipe
					USING ( AccountNo ) SET Status = 'Closed', StatusSetDate = NOW() 
					WHERE Accounts.RedemptionStopDate IS NOT NULL
					AND ( DATEDIFF( NOW(),LastSwipeDate ) > 730 or ( LastSwipeDate is NULL AND AccountType <> 'G') )
					AND Status <> 'Closed'";
		$res = DBQueryLogOnFailure( $sql );
		echo date("Y-m-d H:i:s")." Old Legacy Accounts Closed = ".mysql_affected_rows()." \r\n";
		
		$sql = "UPDATE texaco.AccountStatus JOIN texaco.Accounts
					USING ( AccountNo ) 
					LEFT JOIN Members AS M
					USING ( AccountNo )  
					SET Status = 'Closed', StatusSetDate = NOW() 
					WHERE `MemberNo` IS NULL AND Status <> 'Closed'";
		$res = DBQueryLogOnFailure( $sql );
		echo date("Y-m-d H:i:s")." Orphaned Accounts Closed = ".mysql_affected_rows()." \r\n";
	}	
	
// - - - - - - - - S E T   O L D   C L E A R E D   A C C O U N T S   T O   P R E V I O U S L Y   I N V E S T I G A T E D - - - - - - - - -
//* next section added 04/05/10 MRM Mantis 1970 to change status of cleared accounts to previously investigated after 6 months
function ResetOldClearedAccounts()
	{
		echo date("Y-m-d H:i:s")." Reset Old Cleared Accounts started \r\n";
		$sql = "UPDATE AccountStatus SET FraudStatus = '2', FraudStatusSetDate = NOW() WHERE FraudStatus = '3' AND DATEDIFF( NOW(),FraudStatusSetDate  ) > 183";
		$res = DBQueryLogOnFailure( $sql );
		echo date("Y-m-d H:i:s")." Old Cleared Accounts Reset = ".mysql_affected_rows()." \r\n";
	}
	
// - - - - - - - - D E L E T E   M S G   R E F   R E C O R D S   M O R E   T H A N   O N E   Y E A R   O L D - - - - - - - - -
//* next section added 10/01/11 MRM Mantis 2936 to delete Msgref records 
function DeleteMsgRefRecords()
	{
		echo date("Y-m-d H:i:s")." delete Old Msg Ref Records started \r\n";
		$sql = "DELETE FROM Msgref WHERE DATEDIFF( NOW( ) , CreateDate ) > 365";
		$res = DBQueryLogOnFailure( $sql );
		echo date("Y-m-d H:i:s")." Old Msg Ref Records Deleted = ".mysql_affected_rows()." \r\n";
	}	
	
// - - - - - - - - D E L E T E   E R R O R   M E S S A G E S  M O R E   T H A N   O N E   Y E A R   O L D - - - - - - - - -
//* next section added 10/01/11 MRM Mantis 2961 to delete NewErrorLog records 
function DeleteErrorLogRecords()
	{
		echo date("Y-m-d H:i:s")." delete Old ErrorLog Messages started \r\n";
		$sql = "DELETE FROM NewErrorLog WHERE DATEDIFF( NOW( ) , CreationDate ) > 365";
		$res = DBQueryLogOnFailure( $sql );
		echo date("Y-m-d H:i:s")." Old ErrorLog Messages Deleted = ".mysql_affected_rows()." \r\n";
	}	
		
// - - - - - - - - - - - - - - - - -  C L O S E   F R A U D U L E N T   A C C O U N T S - - - - - - - - - - - - - - - - -
//* next section added 07/05/10 MRM Mantis 1970 to set any open Fraud Accounts to closed , Account Type = F and balance transferred to stopped points
function CloseFraudAccounts()
	{
		echo date("Y-m-d H:i:s")." Close Fraudulent Accounts started \r\n";
		$sql = "SELECT MemberNo, AccountNo, Balance FROM Accounts JOIN AccountStatus USING ( AccountNo ) 
		JOIN Members USING ( AccountNo ) 
		WHERE  (STATUS = 'Open' AND FraudStatus = '4') OR (STATUS = 'Closed' AND FraudStatus = '4' AND Balance <> 0)  GROUP BY AccountNo";
		
		$res = DBQueryLogOnFailure( $sql );
		$count = 0;
		$fraud = true;
		while( $row = mysql_fetch_assoc( $res ) )
		{
			if ($row['MemberNo'])
			{
				CloseAccount( $row['MemberNo'], $row['AccountNo'], $row['Balance'], 1207, 1, $fraud );
				$count++;
			}
		}
		echo date("Y-m-d H:i:s")." Fraudulent Accounts Closed = ".$count." \r\n";
	}	
	
// - - - - T R A N S F E R   B A L A N C E   T O   S T O P P E D   P O I N T S   O N   N O   R E S P O N S E   A C C O U N T S   A F T E R   3 0   D A Y S - - - -
//* next section added 22/06/10 MRM Mantis 2253 deal with accounts closed due to No Response
function ProcessClosedNoResponseAccounts()
	{
		echo date("Y-m-d H:i:s")." Process Closed No Response Accounts started \r\n";
		$sql = "SELECT MemberNo, AccountNo, Balance, T.CreationDate
					FROM AccountStatus
					JOIN Accounts USING ( AccountNo ) 
					JOIN Members USING ( AccountNo ) 
					JOIN Tracking AS T USING ( MemberNo ) 
					WHERE FraudStatus = '4' AND Balance <> 0 AND T.TrackingCode = 1208 AND DATEDIFF( NOW(),T.CreationDate ) > 29";
		
		$res = DBQueryLogOnFailure( $sql );
		$count = 0;
		while( $row = mysql_fetch_assoc( $res ) )
		{
			TransferBalanceToStoppedPoints( $row['AccountNo'], $$row['MemberNo'], $row['Balance'] );
			$count++;
		}
		echo date("Y-m-d H:i:s")." Closed No Response Accounts Processed = ".$count." \r\n";
	}	
		

// - - - - - - - - M A I N   P R O C E S S - - - - - - - - -


	echo date("Y-m-d H:i:s").' '.__FILE__." version 1.9 started \r\n";

	connectToDB( MasterServer, TexacoDB );

	$rec = CreateProcessStartRecord( "HouseKeeper" );
	echo "-------------------------------------------------------------------------\r\n";
	SetCardTypes();
	echo "-------------------------------------------------------------------------\r\n";
	ForcePrimaryCard();
	echo "-------------------------------------------------------------------------\r\n";
	SetHomeSiteToFirstSwipe();
	#SetMemberBalances();
	#AnalysisTransactionStats();
	echo "-------------------------------------------------------------------------\r\n";
	UpdateWelcomeLimit();
	echo "-------------------------------------------------------------------------\r\n";
	CloseLegacyAccounts();
	echo "-------------------------------------------------------------------------\r\n";
	CloseFraudAccounts();
	echo "-------------------------------------------------------------------------\r\n";
	ProcessClosedNoResponseAccounts();
	echo "-------------------------------------------------------------------------\r\n";
	ResetOldClearedAccounts();
	echo "-------------------------------------------------------------------------\r\n";
	DeleteMsgRefRecords();
	echo "-------------------------------------------------------------------------\r\n";
	DeleteErrorLogRecords();
	echo "-------------------------------------------------------------------------\r\n";
	ReportNegativeStoppedPoints();
	echo "-------------------------------------------------------------------------\r\n";
	$success = ReportNegativeAccounts();
	if ($success == 1 && date("D") == "Mon")
	{
		ReportSafeToRedeem();
	}
	#StopStaffRedemption();
	echo "-------------------------------------------------------------------------\r\n";
	CompleteProcessRecord( $rec );
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>
