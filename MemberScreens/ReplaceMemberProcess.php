<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/CardInterface.php";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/CardRequestInterface.php";

	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];

	$Mode = $_GET["Mode"];

	$Notes = "null";

	$TMode = TrackingAdditionalCard;
	if( $Mode == RequestReplacementCard )
	{
		$CardNo = $_GET['CardNo'];
		MarkCardAsLost( $CardNo, $MemberNo );
		$TMode = TrackingCardLost;
		$Notes = $CardNo;
	}
	
	$CardsReqested = 1;

	if( isset( $_GET["Number"]) && $_GET["Number"] > 1 )
	{
		$Mode = RequestMultipleCards;
		$TMode = TrackingMultipleCards;
		$Notes = "$_GET[Number] Cards Requested";
		$CardsReqested = $_GET["Number"];
	}

	while( $CardsReqested > 0 )
	{ 
		InsertRequestRecord( $MemberNo, $Mode ); 
		$CardsReqested--;
	}

	InsertTrackingRecord( $TMode, $MemberNo, $AccountNo, $Notes, 0 );
	
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo");
?>
