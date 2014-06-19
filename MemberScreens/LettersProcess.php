<?php
	include_once "../include/Session.inc";
	include_once "../include/CacheCntl.inc";
	include_once "../DBInterface/LettersInterface.php";
	include_once "../DBInterface/TrackingInterface.php";
	include_once "../DBInterface/FraudInterface.php";

function CreateLetter($MemberNo, $AccountNo, $CardNo, $Code, $Refer)
{
	
	if( $Code == 1212 )
	{
		$FraudStatus = "1";
		$FraudStatustext = "Under Investigation";
//		$echostringmiddle = ", and Confirm Spend 1 Letter has been generated.";
		SetFraudStatus( $AccountNo, $FraudStatus, $Refer );
		$comment = "Status changed to Under Investigation";
	}
	if( $Code == 1209 )
	{
		$FraudStatus = "3";
		$FraudStatustext = "Cleared";
//		$echostringmiddle = " and Account Cleared Letter has been generated.";
		SetFraudStatus( $AccountNo, $FraudStatus, $Refer );
		if( $Refer == 2 && GetFraudStatus($AccountNo) == '0')
		{                                         //   If the Fraud Status has not been set there's no point in writing a tracking rec to say it's been changed
			$trackingcode = 1122;
			$comment = "Redemption Stop Flag removed";
		}
		else 
		{
			$comment = "Status changed to Cleared";
		}
	}
	if( $Code == 1208 )
	{
		$FraudStatus = "4";
		$FraudStatustext = "Fraud";
//		$echostringmiddle = " and Account Closed No Response Letter has been generated.";
		SetFraudStatus( $AccountNo, $FraudStatus, $Refer );
		$comment = "Account Closed No Response Letter has been generated";
	}
	if( $Code == 1207 )
	{
		$FraudStatus = "4";
		$FraudStatustext = "Fraud";
//		$echostringmiddle = " and Account Closed Letter has been generated.";
		if( $Refer != 2 )
		{
			SetFraudStatus( $AccountNo, $FraudStatus, $Refer );
			$Balance = GetAccountBalance( $AccountNo );
			CloseAccount( $MemberNo, $AccountNo, $Balance, $Code, $Refer, true );
			$comment = "Status changed to Fraud - $Balance transferred to stopped points";
		}
		else 
		{
			SetFraudStatus( $AccountNo, "1a", $Refer );
		}
	}
	
	// MRM 06 MAY 10 - if Refer is 0 we do not want to issue the letter 
	
	if( $Refer == 0 or $Refer == 2 )
	{
		if ( $trackingcode != 1122 )
		{
			$trackingcode = TrackingStatusChange;
		}
		InsertTrackingRecord( $trackingcode,  $MemberNo, $AccountNo, $comment, 0 );
	}
	else
	{

		AddLetterRequest( $Code, $MemberNo, "" );
		InsertTrackingRecord( $Code,  $MemberNo, $AccountNo, $comment, 0 );
	}
	

	return; 
}
	
// - - - - -   M A I N   P R O C E S S 	
	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];
	$CardNo = $_GET['CardNo'];
	$Code   = $_GET['Code'];
	$Refer   = $_GET['Refer'];
//		echo " LettersProcess.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Code=$Code&Refer=$Refer";
//		exit;
	if ( isset($_GET['Refer']) )
	{
		if( $Code != '' )
			{
				CreateLetter($MemberNo, $AccountNo, $CardNo, $Code, $Refer);
			}
		header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");
	}
	else 
	{
		$Refer = 99;
		if($Code)
		{
			if( $Code == "cs1c" or $Code == "cs2c" or $Code == "porc" or $Code == "clsd" or $Code == "clrd")
			{
					header("Location: ../MemberScreens/Comments.php?AccountNo=$AccountNo&MemberNo=$MemberNo");
			}
			else if( $Code == "cs1" or $Code == "cs2" or $Code == "por" )
			{
				$sql = "UPDATE AccountStatus SET ";	
				switch($Code)
				{ 
					case "cs1":
						$sql .= "ConfirmSpend1ReturnedDate = NOW() ";
						$comment = "Confirm Spend 1 Returned";
						break;
					case "cs2": 		
						$sql .= "ConfirmSpend2ReturnedDate  = NOW() ";
						$comment = "Confirm Spend 2 Returned";
						break;
					case "por": 
						$sql .= "ProofOfReceiptsReturnedDate  = NOW() ";
						$comment = "Proof Of Receipts Returned";
						break;
					default: 
						break;
				}
			
				$sql .= "WHERE AccountNo = $AccountNo";
				$results = DBQueryExitOnFailure( $sql );
				
				InsertTrackingRecord( 1181, $MemberNo, $AccountNo, $comment, 0 );
			}
			else 
			{
				CreateLetter($MemberNo, $AccountNo, $CardNo, $Code, $Refer);
				if( $Code == 1209 )
				{   // may need to reinstate points 
					$sql = "SELECT CardNo, StoppedPoints FROM Cards WHERE MemberNo = $MemberNo";
					$result = DBQueryExitOnFailure( $sql );	
					while( $row = mysql_fetch_array( $result ))
					{
						if( $row[StoppedPoints] > 0 )
						{
							$sql2 = "UPDATE Cards SET StoppedPoints = 0 WHERE CardNo = '$row[CardNo]'";
							DBQueryExitOnFailure( $sql2 );	
							$Comment = "$row[StoppedPoints] returned to balance from card $row[CardNo]";
							InsertTrackingRecord( TrackingReleaseStoppedPoints, $MemberNo, $AccountNo, $Comment, 0 );
							$sql3 = "UPDATE Accounts SET Balance = Balance + $row[StoppedPoints] WHERE AccountNo = $AccountNo";
							DBQueryExitOnFailure( $sql3 );	
			
						}
					}
				} 
			
			}
		}
		
		header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");
	}
?>