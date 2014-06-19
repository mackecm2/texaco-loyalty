<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/CardInterface.php";

	$MemberNo = $_POST['MemberNo'];
	$AccountNo = $_POST['AccountNo'];
	$CardNo = $_POST['cardNumber'];

	if( $MemberNo != "" && $CardNo != "" )
	{
		// Begin Transaction
		MergeCardToMember( $CardNo, $MemberNo, false );
		// End Transaction
	}
	else if( $MemberNo != "" && $SCardNo != "" )
	{
		include "../include/NoPermission.php";
		exit();
	}
	
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");
?>
