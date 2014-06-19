<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/FraudInterface.php";
	include "../DBInterface/LettersInterface.php";

	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];
	$Balance = $_GET['Balance'];
	$Code   = $_GET['Code'];
	if( isset($_GET['Refer']))
	{
		$Refer = $_GET['Refer'];
	}
	else 
	{
		$Refer = 0;
	}
	if( isset($_GET["Notes"]) && $_GET["Notes"] != "" )
	{
		$Comment = $_GET["Notes"];
	}
	else
	{
		$Comment = "";
	}
	if( $Code == 1207 or $Code == 1208 or $Code == 1223 )
	{
		$Fraud = true;
	}
	else
	{
		$Fraud = false;
	}
	CloseAccount( $MemberNo, $AccountNo, $Balance, $Code, $Refer, $Fraud);
//		Write a Tracking Record about the Account Closure
	InsertTrackingRecord( $Code, $MemberNo, $AccountNo, $Comment, 0 );
	
	//		Request an Account Closed Letter

	
	if( $Code == 1207 or $Code == 1208 )
	{
		AddLetterRequest( $Code, $MemberNo, "" );
	}
	
	if( $Refer == 0 )
	{
		header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");
	}
?>