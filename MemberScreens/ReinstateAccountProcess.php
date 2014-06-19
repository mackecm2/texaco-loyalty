<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";

	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];
	$Balance = $_GET['Balance'];
	$Code   = $_GET['Code'];
	if( isset($_GET['StoppedPoints']) && $_GET['StoppedPoints'] != "" )
	{
		$StoppedPoints   = $_GET['StoppedPoints'];
	}
	else
	{
		$StoppedPoints   = 0;
	}

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

		$sql = "UPDATE AccountStatus JOIN Accounts USING( AccountNo ) 
				SET Status = 'Open', StatusSetDate = NOW(),";
		
		// see if there's any stopped points to take off the cards
		if( $StoppedPoints > 0 )
		{
			$sql3 = "SELECT CardNo, StoppedPoints
				FROM Cards
				JOIN Members
				USING ( MemberNo ) 
				JOIN Accounts
				USING ( AccountNo ) 
				WHERE AccountNo = $AccountNo";
			$result = DBQueryExitOnFailure( $sql3 );	
			while( $row = mysql_fetch_array( $result ))
			{
				if( $row[StoppedPoints] > 0 )
				{
					$sql2 = "UPDATE Cards SET StoppedPoints = 0 WHERE CardNo = '$row[CardNo]'";
					DBQueryExitOnFailure( $sql2 );	
					$SPComment = "$row[StoppedPoints] returned to balance from card $row[CardNo]";
					InsertTrackingRecord( TrackingReleaseStoppedPoints, $MemberNo, $AccountNo, $SPComment, 0 );
				}		
			}
			$sql .= " Balance = Balance + $StoppedPoints,";
		}

		$sql .= " FraudStatus = IF(FraudStatus = '4','3',IF(FraudStatus = '3','3','0')), FraudStatusSetDate = NOW(), 
			AwardStopDate = NULL,
			RedemptionStopDate = NULL  
			WHERE AccountNo = $AccountNo";

		DBQueryExitOnFailure( $sql );	

//		Write a Tracking Record about the Account Reinstatement
		
		if( $StoppedPoints > 0 )
		{
			$Comment .= ", $StoppedPoints points returned to Account";
		}

		InsertTrackingRecord( $Code, $MemberNo, $AccountNo, $Comment, NULL );

	}
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");

?>