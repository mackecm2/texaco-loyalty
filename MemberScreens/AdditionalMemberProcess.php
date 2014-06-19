<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/CardRequestInterface.php";;
	include "../DBInterface/MemberInterface.php";

	$pMemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];
	$AccountType = $_GET['AccountType'];

	// Begin Transaction
	$MemberNo = CopyMember( $pMemberNo, $AccountType );

	$CardsReqested = 1;
	$Mode = RequestAdditionalMember;
	$TMode = TrackingAdditionalCard;
	$Notes = "";
	if( isset( $_GET["Cards"]) && $_GET["Cards"] > 1 )
	{
		$Mode = RequestMultipleCards;
		$TMode = TrackingMultipleCards;
		$Notes = "$_GET[Cards] Cards Requested";
		$CardsReqested = $_GET["Cards"];
	}
	$sql = "SELECT * FROM CardRanges WHERE AccountNo = $AccountNo";
	$results = DBQueryLogOnFailure( $sql );
	if( mysql_num_rows( $results ) != 0 )
	{
		$Mode = RequestGroupMember;
	}
	InsertTrackingRecord( TrackingAdditionalMember, $MemberNo, $AccountNo,  "", 0 );
	while( $CardsReqested > 0 )
	{ 
		InsertRequestRecord( $MemberNo, $Mode ); 
		$CardsReqested--;
	}
	InsertTrackingRecord( $TMode, $MemberNo, $AccountNo, $Notes, 0 );
	
	// End Transaction
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo");
?>
