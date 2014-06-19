<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/MemberInterface.php";
	include "../DBInterface/CardInterface.php";

	$Direction   = $_GET['Direction'];


	if( $Direction == "First2Second" )
	{
		$MemberNo = $_POST['SMemberNo'];
		$AccountNo = $_POST['SAccountNo'];
		$CardNo = $_POST['SCardNo'];
		$SMemberNo = $_POST['MemberNo'];
		$SAccountNo = $_POST['AccountNo'];
		$SCardNo = $_POST['CardNo'];
	}
	else
	{
		$MemberNo = $_POST['MemberNo'];
		$AccountNo = $_POST['AccountNo'];
		$CardNo = $_POST['CardNo'];
		$SMemberNo = $_POST['SMemberNo'];
		$SAccountNo = $_POST['SAccountNo'];
		$SCardNo = $_POST['SCardNo'];
	}

	if( $AccountNo != "" && $SAccountNo != "" )
	{
		// Begin Transaction
		InsertTrackingRecord( TrackingMergeAccount,  $AccountNo, "Merged Account Number $SAccountNo" );
		MergeAccounts( $AccountNo, $SAccountNo ); 		
		// End Transaction
	}
	else if( $MemberNo != "" && $SCardNo != "" )
	{
		// Begin Transaction
		MergeCardToMember( $SCardNo, $MemberNo, false );		
		InsertTrackingRecord( TrackingMergeCard,  $AccountNo, "Merged Card Number $SCardNo" );
		// End Transaction
	}


	header("Location: ../MemberScreens/MergeMembers.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");
?>
