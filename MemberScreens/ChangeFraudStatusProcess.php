<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/FraudInterface.php";

	$AccountNo = $_GET['AccountNo'];
	$MemberNo = $_GET['MemberNo'];
	$OldStatus   = $_GET['FraudStatus'];
	$NewStatus   = $_GET['NewStatus'];
	
	$Comment = SelectFraudOption($OldStatus). " => ".SelectFraudOption($NewStatus)."  " ;
	
	if( $OldStatus  != $NewStatus )
	{
		if( isset($_GET["Notes"]) && $_GET["Notes"] != "" )
		{
			$Comment .= $_GET["Notes"];
		}
		else
		{
			$Comment .= "";
		}
    
		SetFraudStatus( $AccountNo, $NewStatus, 9 );
		InsertTrackingRecord( 1229, $MemberNo, $AccountNo, $Comment, 0 );
			
	}
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");

?>