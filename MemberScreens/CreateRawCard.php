<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/CardInterface.php";

	$CardNo = $_GET['CardNo'];
	$Action = $_GET['Action'];

	CreateRawCard( $CardNo );
	if( $Action == "NewAccount" )
	{
		header("Location: DisplayMember.php?CardNo=$CardNo&Action=NewAccount") ;
	}
	else if( $Action == "LinkAccount" )
	{
		header("Location:MergeMembers.php?CardNo=$CardNo");
	}
	else
	{
		echo "Error";
	}
?>
