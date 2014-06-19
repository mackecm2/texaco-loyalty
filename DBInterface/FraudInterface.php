<?php

function GetFraudOptions($FraudStatus)
{
	$allcodes = array("No Action", "Under Investigation", "Previously Investigated", "Cleared",  "Fraud"); 	
	$codes = array();
	$arraycount = count($allcodes);
	$i=0;
	while($i < $arraycount )
  	{
  		if ($FraudStatus != $i )
  		{
  			$codes[] = $allcodes[$i];
  		}
  		else 
  		{
  			$codes[] = NULL;
  		}
  		$i++;
  	}
	
	return $codes;
}

function SelectFraudOption($FraudStatus)
{
	$allcodes = array("No Action", "Under Investigation", "Previously Investigated", "Cleared",  "Fraud"); 	
	$arraycount = count($allcodes);
	$i=0;
	while($i < $arraycount )
  	{
  		if ($FraudStatus == $i )
  		{
  			$meaning = $allcodes[$i];
  		}
  		$i++;
  	}
	
	return $meaning;
}


function CheckFraudHistory( $accountno )
{
	$sql = "";

	if( $accountno != "" )
	{
		$sql = "SELECT ConfirmSpend1SentDate FROM AccountStatus where AccountNo = $accountno AND ConfirmSpend1SentDate IS NOT NULL LIMIT 1";
	}
	else 
	{
		$sql = "";
	}

	$fraudhistory =  DBSingleStatQueryNoError($sql);

	if ( !$fraudhistory )
	{
		return false;
	}
	else
	{
		return true;
	}
}

function GetFraudHistory( $accountno )
{
	$sql = "";

	if( $accountno != "" )
	{
		$sql = "SELECT * FROM AccountStatus where AccountNo = $accountno LIMIT 1";
	}
	else 
	{
		$sql = "";
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

function FraudHistoryDisplay( $sentdate, $returneddate, $comments, $option )

{
	echo "<fieldset><legend>$option</legend><table border=0 cellspacing=0 cellpadding=5>
	  <tbody><tr><td></td><td></td><td width=150 style=\"TEXT-ALIGN: left\">Date</td>
	  <td style=\"TEXT-ALIGN: left\">Comments</tr>
	  <tr><td>";
	if ($option == 'AccountClosed' or $option ==  'AccountCleared')
 	{
 		echo "<p></p>"; 
 	}
 	else 
 	{
 		echo "<p align=right>Sent</p>";
 	}
	echo "</td>";
	  
 	if( $sentdate )
	 {
		echo "<td><input value=0 type=checkbox disabled name=".$option."Sent></td>"; 
	 	echo "<td>".date('d/m/Y',strtotime($sentdate))."</td>";
	 }
	 else if ($option == 'AccountClosed' or $option ==  'AccountCleared')
	 	{
	 		echo "<td></td>"; 
	 		echo "<td></td>";
	 	}
	 	else
	 	{
	 		echo "<td><input type=checkbox disabled name=".$option."Sent></td>"; 
	 		echo "<td></td>";
//	 		echo "<td><input type=checkbox onclick=DateSet(this, '".$option."SentDate') name=".$option."Sent></td>"; 
//	 		echo "<td><input id=".$option."SentDate value=".date('d/m/Y',strtotime($sentdate))."></td>";
		 }
	 
       echo "<td rowspan=2>".$comments."</td><tr><td>";
//       echo "<td rowspan=2><textarea style=\"WIDTH: 400px; HEIGHT: 70px\" rows=4 cols=19 name=".$option."Comments>".$comments."</textarea></td> 
//	       	  <tr><td>";
       
	 	if ($option == 'AccountClosed' or $option ==  'AccountCleared')
	 	{
	 		echo "<p></p>";
	 	}
	 	else 
	 	{
	 		echo "<p align=right>Returned</p>";
	 	} 

 	if( $returneddate )
	 {
	 	if ($option == 'AccountClosed' or $option ==  'AccountCleared')
	 	{
	 		echo "<td></td>"; 
	 	}
	 	else 
	 	{
	 		echo "<td><input value=0 type=checkbox disabled onclick=name=".$option."Returned></td>"; 
	 	}
	 	 echo "<td>".date('d/m/Y',strtotime($returneddate))."</td>";
	 }
	 else 
	 {
	 	echo "<td><input type=checkbox disabled name=".$option."Returned></td>"; 
	 	echo "<td></td>";
	 }
	    
	    
	    echo "</tr></tbody></table></fieldset>";
}
function SetFraudStatus( $accountno, $status, $refer )
{
	$sql = "UPDATE Accounts JOIN AccountStatus USING ( AccountNo ) SET RevisedDate = NOW(), FraudStatusSetDate=NOW(), FraudStatus = LEFT('$status',1)";
	if( $refer != 9 )   /// 29 SEP 10 MRM Mantis 2910 if just changing the radio buttons, don't do anything else
	{
		switch($status)
		{ 
		case '1':
			if( $refer == 0 or $refer == 2 )
			{
				$sql .= ", RedemptionStopDate = IF(RedemptionStopDate IS NULL,NOW(),RedemptionStopDate) ";
			}
			else 
			{
				$sql .= ", ConfirmSpend1SentDate = NOW(), RedemptionStopDate = IF(RedemptionStopDate IS NULL,NOW(),RedemptionStopDate) ";
			}			
			break;
		case '1a':
			$sql .= ", RedemptionStopDate = IF(RedemptionStopDate IS NULL,NOW(),RedemptionStopDate), AwardStopDate = IF(AwardStopDate IS NULL,NOW(),AwardStopDate) ";
			break;	
		case '3':     // need to check if fraud status was set to Under Investigation before setting it to Cleared MRM 07 06 10 
			$sql = "UPDATE Accounts JOIN AccountStatus USING ( AccountNo ) SET RevisedDate = NOW(), ";
			if( $refer != 9 )  // MRM 09 09 10 Don't re-open the account if updating Status button Mantis 2510
			{
				$sql .= "RedemptionStopDate = NULL, AwardStopDate = NULL, StatusSetDate = IF(Status = '0pen',StatusSetDate,NOW()), Status = IF(Status = '0pen',FraudStatus,'Open'),";
			}
			$sql .= "FraudStatusSetDate = IF(FraudStatus = '0',FraudStatusSetDate,NOW()), 
			FraudStatus = IF(FraudStatus = '0',FraudStatus,LEFT('$status',1)), 
			AccountClearedDate = IF(FraudStatus = '0',AccountClearedDate,NOW()) ";
			break;
		case '4': 
			$sql .= ", AccountClosedDate = NOW(), RedemptionStopDate = IF(RedemptionStopDate IS NULL,NOW(),RedemptionStopDate), AwardStopDate = IF(AwardStopDate IS NULL,NOW(),AwardStopDate), Status = 'Closed', StatusSetDate = NOW() ";
			break;
		default: 
			$sql .= " ";
			break;
		}	
	}

	
	$sql .= "WHERE AccountNo = $accountno";
	return DBQueryExitOnFailure( $sql );	
}

function GetAccountBalance( $AccountNo )
{
	$sql = "Select Balance from Accounts where AccountNo = $AccountNo";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	$Balance = $row[0];
	return $Balance;
}

function GetFraudStatus( $accountno )
{
	$sql = "";

	if( $accountno != "" )
	{
		$sql = "SELECT FraudStatus FROM AccountStatus where AccountNo = $accountno LIMIT 1";
	}
	else 
	{
		$sql = "";
	}

	return DBSingleStatQueryNoError($sql);
}


function TransferBalanceToStoppedPoints( $accountno, $memberno, $balance )
{
//		Transfer Balance to Stopped Points on the highest numbered card
	$sql = "SELECT Max( C.CardNo ) AS CardNo FROM Cards AS C
		JOIN Members AS M
		USING ( MemberNo ) 
		JOIN Accounts AS A
		USING ( AccountNo ) 
		WHERE A.AccountNo = $accountno
		GROUP BY AccountNo";
	
	$cardno = DBSingleStatQuery( $sql );
	$sql = "UPDATE Cards SET StoppedPoints = StoppedPoints + $balance, LastUpdate = NOW( ) WHERE CardNo = '$cardno'";
	$results = DBQueryExitOnFailure( $sql );
//			Write a Tracking Record about transferring the balance
	$comment = "Balance of $balance moved to Stopped Points on $cardno";
	$sql = "UPDATE Accounts SET Balance = 0 WHERE AccountNo = $accountno";
	DBQueryExitOnFailure( $sql );			
	InsertTrackingRecord( BalanceTransfer, $memberno, $accountno, $comment, null );
}

function CloseAccount( $MemberNo, $AccountNo, $Balance, $Code, $Refer, $Fraud ) 
{
	if( $Code > 0 )
	{
		if( isset($_GET["Notes"]) && $_GET["Notes"] != "" )
		{
			$Comment = $_GET["Notes"];
		}
		else
		{
			$Comment = "";
		}

		if ( $Balance != 0 && $Balance != NULL && $Code != 1208)
		{
			TransferBalanceToStoppedPoints( $AccountNo, $MemberNo, $Balance );
		}
		
//		Close the Account

		$sql = "UPDATE AccountStatus JOIN Accounts USING( AccountNo ) SET Status = 'Closed', StatusSetDate = NOW( ), ";
		if( $Fraud == true )
		{
			$sql .= "FraudStatus = '4', ";
		}
		$sql .= "RedemptionStopDate = IF(RedemptionStopDate IS NULL,NOW(),RedemptionStopDate), 
		AwardStopDate = IF( AwardStopDate IS NULL, NOW( ), AwardStopDate )  WHERE AccountNo = $AccountNo";
		DBQueryExitOnFailure( $sql );

//		Write a Tracking Record about the Account Closure
//		InsertTrackingRecord( $Code, $MemberNo, $AccountNo, $Comment, 0 );
		

	}
}	

?>