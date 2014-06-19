<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/RedemptionInterface.php";

	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];
	$CardNo = $_GET['CardNo'];
	$VirginNo = $_GET['VirginNo'];
	
	RedeemAgainstAccount( $AccountNo, $MemberNo, "Virgin 600", 3, "VIR600", "", $VirginNo, 600, 1, "VIRGIN" ); 
	header("Location:DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");
?>
