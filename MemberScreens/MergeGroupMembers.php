<?php 
error_reporting(E_ALL);

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/MemberInterface.php";
	include "../DBInterface/CardInterface.php";
	include "../DBInterface/CardRequestInterface.php";

	if( isset( $_REQUEST["MemberNo"] ) )
	{
		$MemberNo = $_REQUEST['MemberNo'];
	}
	else
	{
		$MemberNo = "";
	}
	
	if( isset( $_REQUEST["AccountNo"] ) )
	{
		$AccountNo = $_REQUEST['AccountNo'];
	}
	else
	{
		$AccountNo = "";
	}

	if( isset( $_REQUEST["CardNo"] ) )
	{
		$CardNo = $_REQUEST['CardNo'];
	}
	else
	{
		$CardNo = "";
	}
	
	if( isset( $_REQUEST["SMemberNo"] ) )
	{
		$SMemberNo = $_REQUEST['SMemberNo'];
	}
	else
	{
		$SMemberNo = "";
	}
		
	if( isset( $_REQUEST["SAccountNo"] ) )
	{
		$SAccountNo = $_REQUEST['SAccountNo'];
	}
	else
	{
		$SAccountNo = "";
	}	
	
	if( isset( $_REQUEST["SCardNo"] ) )
	{
		$SCardNo = $_REQUEST['SCardNo'];
	}
	else
	{
		$SCardNo = "";
	}	
		
	if( isset( $_REQUEST["Action"] ) )
	{
		$Action = $_REQUEST["Action"];
	}
	else
	{
		$Action = "";
	}

//	var_dump($_GET);
//	echo "Action is ".$Action;

	switch( $Action )
	{
		case "LinkGLC":
			$sql = "SELECT CardNo FROM Cards WHERE CardNo='".$CardNo."'";
			$results = DBQueryExitOnFailure( $sql );
			$numrows = mysql_num_rows($results);
			if( $numrows ==0 )
			{
				CreateRawCard( $CardNo );
			}
			$MemberNo = CreateMember( $CardNo, $AccountNo );
		case "Second2First":
			if( $AccountNo != "" && $SAccountNo != "" )
			{
				// Begin Transaction
				MergeAccounts( $AccountNo, $SAccountNo ); 		
				// End Transaction
			}
			else if( $MemberNo != "" && $SCardNo != "" )
			{
				// Begin Transaction
				MergeCardToMember( $SCardNo, $MemberNo, false );		
				// End Transaction
			}
			else if( $SMemberNo != "" && $CardNo != "" )
			{
				// Begin Transaction
				MergeCardToMember( $CardNo, $SMemberNo, false );
				$AccountNo = $SAccountNo;
				$MemberNo = $SMemberNo;
				// End Transaction				
			}
			$SMemberNo = "";
			$SAccountNo = "";
			$SCardNo = "";
		break;
	}
	if( $AccountNo != "" && $SAccountNo != "" )
	{
		// Begin Transaction
		InsertTrackingRecord( TrackingMergeAccount, $SMemberNo, $AccountNo, "Merged Account Number $SAccountNo" );
		MergeAccounts( $AccountNo, $SAccountNo ); 		
		// End Transaction
	}
	else if( $MemberNo != "" && $SCardNo != "" )
	{
		// Begin Transaction
		MergeCardToMember( $SCardNo, $MemberNo, false );		
		InsertTrackingRecord( TrackingMergeCard, $SMemberNo, $AccountNo, "Merged Card Number $SCardNo" );
		// End Transaction
	}
	
	// Now we need to request a new card for this customer
	
	$CardsReqested = 1;

	$TMode = TrackingAdditionalCard;
	$Notes = "";
	$Mode = RequestGroupLoyaltyMember;

	InsertTrackingRecord( TrackingAdditionalMember, $MemberNo, $AccountNo,  "", 0 );

	InsertRequestRecord( $MemberNo, $Mode );

	InsertTrackingRecord( $TMode, $MemberNo, $AccountNo, $Notes, 0 );
	
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo");
?>
