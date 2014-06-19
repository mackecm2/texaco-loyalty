<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/FraudInterface.php";

	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];
	$Code   = $_GET['Code'];
	$CardNo = $_GET['CardNo'];

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

		InsertTrackingRecord(  $Code, $MemberNo, $AccountNo, $Comment, 0 );
		
		if( $Code == 1109 )
		{
			SetFraudStatus( $AccountNo, '3', null );
		}
	}
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");

?>