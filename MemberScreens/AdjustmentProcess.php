<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";

	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];
	$CardNo = $_GET['CardNo'];
	$Code   = $_GET['Code'];

	if( isset($_GET["Notes"]) && $_GET["Notes"] != "" )
	{
		$Comment = $_GET["Notes"];
	}
	else
	{
		$Comment = "";
	}

	if( isset($_GET["Stars"]) && $_GET["Stars"] != "" )
	{
		$Stars = $_GET["Stars"];
	}
	else
	{
		$Stars = 0;
	}

	AdjustBalance(  $Code, $MemberNo, $AccountNo, $Comment, $Stars );
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");
?>
